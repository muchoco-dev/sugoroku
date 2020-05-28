<?php

namespace App\Repositories;

use App\Events\MemberAdded;
use App\Events\DiceRolled;
use App\Models\RoomUser;
use App\Models\RoomLog;
use App\Models\Room;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;

class RoomRepository
{

    protected $model;

    public function __construct()
    {
        $this->model = new Room;
    }

    /**
     * ユーザが作成したオープン中の部屋を取得
     */
    public function getOwnOpenRoom($userId)
    {
        return $this->model::where([
            'owner_id'  => $userId,
            'status'    => config('const.room_status_open')
        ])->first();
    }

    /**
     * ユーザが入室している部屋を取得
     */
    public function getJoinedRoom($userId)
    {
        $roomUser = RoomUser::where('user_id', $userId)->first();
        if (!$roomUser) return false;
        return $this->model::find($roomUser->room_id);
    }

    public function create($data)
    {
        $room = new Room;
        $room->uname = uniqid();
        $room->name = $data['name'];
        $room->owner_id = $data['owner_id'];
        $room->board_id = $data['board_id'];
        $room->max_member_count = config('const.max_member_count');
        $room->member_count = 0;
        $room->status = config('const.room_status_open');
        $room->save();

        return $room->id;
    }

    public function findByUname($uname)
    {
        return $this->model::where([
            'uname' => $uname
        ])->with('board.spaces')->first();
    }

    public function getOpenRooms()
    {
        return $this->model::where([
            'status'     => config('const.room_status_open')
        ])->get();
    }

    private function changeStatus($id, $status)
    {
        $room = $this->model::find($id);
        if (!$room) return false;

        $room->status = $status;
        $room->save();
    }

    /**
     * ゲーム開始処理
     */
    public function startGame($id)
    {
        $room = $this->model::find($id);
        if (!$room) return false;

        // 部屋のステータス変更
        $this->changeStatus($room->id, config('const.room_status_busy'));

        // メンバーにウイルスを追加
        $this->addMember(config('const.virus_user_id'), $room->id);

        // 参加者のプレイ順をセット
        $users = $room->users;
        $go_list = [];
        for ($i = 1; $i <= count($users); $i++) {
            array_push($go_list, $i);
        }
        shuffle($go_list);

        foreach ($users as $key => $user) {
            $room->users()->updateExistingPivot($user->id, ['go' => $go_list[$key]]);

            if ($user->id === config('const.virus_user_id') && $go_list[$key] === 1) {
                // ウィルスが1番手の場合は、最初の手番をここで消化する
                $this->moveVirus($room->id);
            }
        }

        return true;
    }

    /**
     * ログ保存
     */
    public function saveLog($userId, $roomId, $actionId, $effectId, $effectNum)
    {
        $log = new RoomLog;
        $log->user_id = $userId;
        $log->room_id = $roomId;
        $log->action_id = $actionId;
        $log->effect_id = $effectId;
        $log->effect_num = $effectNum;
        $log->save();
    }

    /**
     * ウィルスのターン
     */
    public function moveVirus($roomId)
    {
        $dice_num = rand(1, 6);
        $this->movePiece($roomId, config('const.virus_user_id'), $dice_num);
        event(new DiceRolled($roomId, config('const.virus_user_id'), $dice_num));
        $this->saveLog(config('const.virus_user_id'), $roomId, config('const.action_by_dice'), config('const.effect_move_forward'), $dice_num);
    }

    /**
     * コマを移動する
     */
    public function movePiece($roomId, $userId, $num)
    {
        $roomUser = RoomUser::where([
            'room_id'   => $roomId,
            'user_id'   => $userId
        ])->first();

        if (!$roomUser) {
            return false;
        }

        $roomUser->position = $roomUser->position + $num;
        $roomUser->save();
        // 感染処理呼び出し　TODOテストコードどうすれば良いかわからない
        $this->updateStatusSick($roomId, $userId, $roomUser->position, $roomUser->status);
        return $roomUser;
    }

    /**
     * 移動してきたコマ情報を元に
     * コマの移動中に感染中コマとすれ違ったら
     * ステータスを感染中に更新する
     */
    public function updateStatusSick($roomId, $userId, $position, $status)
    {
        // 移動しているコマユーザ以外の参加ユーザで
        // 移動しているコマより前にいるコマが対象
        $roomUsers = RoomUser::where([
            ['room_id',  '=',  $roomId],
            ['user_id',  '<>', $userId],
            ['position', '>',  $position]
        ])->pluck();

        foreach ($roomUsers as $roomUser) {
            if ($status === 'const.piece_status_sick') {
                // 移動してきたコマが感染中の場合
                if ($position >= $roomUser->position) {
                    // 移動してきたコマとすれ違ったコマのステータスを確認
                    if ($status !== $roomUser->status) {
                        // すれ違ったコマが感染中ではない場合は感染中に更新
                        RoomUser::where([
                            ['room_id', '=', $roomId],
                            ['user_id', '=', $roomUser->user_id]
                        ])->update(['status' => 'const.piece_status_sick']);                               
                    }
                }
            } else if ($status === 'const.piece_status_health') {
                // 移動してきたコマが健康状態の場合
                if ($position >= $roomUser->position) {
                    // 移動してきたコマとすれ違ったコマのステータスを確認
                    if ($roomUser->status === 'const.piece_status_sick') {
                        // すれ違ったコマが感染中の場合は
                        // 移動してきたコマを感染中に更新
                        RoomUser::where([
                            ['room_id', '=', $roomId],
                            ['user_id', '=', $userId],
                        ])->update(['status' => 'const.piece_status_sick']);                        
                    }
                }
            }
        }
    }

    /**
     * 入室処理
     */
    public function addMember($userId, $roomId)
    {
        $room = $this->model::where([
            'id' => $roomId
        ])->first();

        if ($userId !== config('const.virus_user_id') && $this->isMemberExceededMaxMember($room)) {
            // ウイルスは人数に関わらず参加可能
            return false;
        }

        if ($this->isMember($room, $userId, $roomId)) {
            return false;
        }

        if ($userId !== config('const.virus_user_id')) {
            $status = config('const.piece_status_health');
        } else {
            $status = config('const.piece_status_sick');
        }

        $room->users()->attach($userId,[
            'go' => 0,
            'status' => $status,
            'position' => 1
        ]);

        // Roomテーブルのmember_countを1足してDB更新
        if ($userId !== config('const.virus_user_id')) {
            $room->member_count = $room['member_count'] + 1;
            $room->save();
        }

        event(new MemberAdded($userId, $roomId));

        return true;

    }

    /**
     * メンバー数が最大メンバー数を超えているかチェックする
     */
    public function isMemberExceededMaxMember($room)
    {
        if ($room['member_count'] >= $room['max_member_count']) {
            return true;
        }
        return false;
    }

    /**
     * 入室済みかどうかをチェックする
     */
    public function isMember($room, $userId, $roomId)
    {
        $roomUserSearchResult = $room->users()->find($userId);
        if ($roomUserSearchResult != null) {
            if (
                $roomUserSearchResult->pivot['user_id'] == $userId
                && $roomUserSearchResult->pivot['room_id'] == $roomId
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * 現在の有効な部屋数を取得
     */
    public function getCurrentActiveRoomsCount()
    {
        return $this->model::where([
            'deleted_at' => NULL
        ])->count();
    }

    /**
     * ゲームボードのマスを取得
     */
    public function getSpaces(Room $room)
    {
        if ($room->spaces->count()) {
            $spaces = $room->spaces;
        } else {
            $spaces = $this->setSpaces($room);
        }

        $viewSpaces = [];
        foreach ($spaces as $space) {
            $viewSpaces[$space->position] = $space;
        }
        return $viewSpaces;
    }

    /**
     * 部屋のゲームボードにマスを配置する
     */
    private function setSpaces(Room $room)
    {
        $room->spaces()->detach();

        $spaces = Space::where('board_id', $room->board_id)->get();

        foreach ($spaces as $space) {
            // TODO:ランダム設置はアップデート時に実装
            $room->spaces()->attach($space->id, ['position' => $space->position]);
        }

        return $room->spaces;
    }

    /**
     * 解散機能(バルス)
     */
    public function balus($userId)
    {
        // オーナーが作成した部屋を取得
        $room = $this->model::where([
            'owner_id'      => $userId,
        ])->first();

        // ゲーム中の場合はバルス不可
        if ($room->status == config('const.room_status_busy')) {
            return false;
        }

        // room_userテーブルの物理削除
        foreach ($room->users as $user) {
            $user->pivot->forceDelete();
        }

        // 取得した部屋を論理削除(ソフトデリート)
        $room->delete();
        return true;
    }

    /**
     * ユーザが参加中の有効な部屋のIDを取得
     * TODO: バグあり。あとで確認する
     */
    public function getUserJoinActiveRoomId($userId)
    {
        $room = $this->model::where([
            'status'        => config('const.room_status_open'),
            'deleted_at'    => NULL
        ])->orWhere([
            'status'        => config('const.room_status_busy'),
            'deleted_at'    => NULL
        ])->first();

        if ($room == null) {
            return NULL;
        }

        $result = Room::find($room->id)->users()->get();
        foreach ($result as $item) {
            if ($item->pivot['user_id'] == $userId) {
                return $item->pivot['room_id'];
            }
        }
        return NULL;
    }

    /**
     * コマの現在地を返却する
     */
    public function getKomaPosition($userId, $roomId)
    {
        $room = $this->model::where([
            'id'      => $roomId
        ])->first();

        if (!$room) {
            return false;
        }

        return $room->users()->find($userId)->pivot['position'];
    }

    public function getMember(int $roomId, int $userId)
    {
        return RoomUser::where('room_id', $roomId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getNextGo($roomId)
    {
        $lastLog = RoomLog::where('room_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$lastLog) return 0;

        $roomUser = RoomUser::where([
            'user_id'   => $lastLog->user_id,
            'room_id'   => $lastLog->room_id
        ])->first();

        // 次の番
        $room = Room::find($roomId);
        if ($roomUser->go === $room->member_count+1)
        {
            $next_go = 1;
        } else {
            $next_go = $roomUser->go + 1;
        }

        // 次がウィルスの番のときは、ここで手番を消化する
        $virus = RoomUser::where([
            'user_id'   => config('const.virus_user_id'),
            'room_id'   => $roomId
        ])->first();
        if ($virus->go === $next_go) {
            $this->moveVirus($roomId);
            return $this->getNextGo($roomId);
        }

        return $next_go;
    }
}



