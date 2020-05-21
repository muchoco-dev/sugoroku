<?php

namespace App\Repositories;

use App\Events\MemberAdded;
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

    public function changeStatus($id, $status){}

    /**
     * 入室処理
     */
    public function addMember($userId, $roomId)
    {
        $room = $this->model::where([
            'id' => $roomId
        ])->first();

        if ($this->isMemberExceededMaxMember($room)) {
            return false;
        }

        if ($this->isMember($room, $userId, $roomId)) {
            return false;
        }

        $room->users()->attach($userId,[
            'go' => 0,
            'status' => config('const.piece_status_health'),
            'position' => 1
        ]);

        // Roomテーブルのmember_countを1足してDB更新
        $room->member_count = $room['member_count'] + 1;
        $room->save();

        event(new MemberAdded($userId, $roomId));

        return true;

    }

    /**
     * メンバー数が最大メンバー数を超えているかチェックする
     */
    public function isMemberExceededMaxMember($room)
    {
        if ($room['member_count'] > $room['max_member_count']) {
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
}



