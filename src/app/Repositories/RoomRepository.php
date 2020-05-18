<?php

namespace App\Repositories;

use App\Models\Room;
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
        $room->max_member_count = 10;
        $room->member_count = 1;
        $room->status = config('const.room_status_open');
        $room->save();

    }

    public function findByUname($uname){}

    public function getOpenRooms()
    {
        return $this->model::where([
            'status'     => config('const.room_status_open')
        ])->get();
    }

    public function changeStatus($id, $status){}

    /**
     * 部屋に入室済みかどうかチェック
     */
    public function IsCheckedEnteredRoom($userId, $roomId)
    {
        $room = $this->model::where([
            'id' => $roomId
        ])->first();

        // 部屋のmember_countがmax_member_count以上ならfalseを返す。
        if ($room['member_count'] > $room['max_member_count']) {
            return false;
        }

        // また、room_userテーブルに既に同じユーザと同じ部屋のペアで保存されていてもfalseを返す。
        // room_userテーブルの検索(検索ワード：user_id)
        $roomUserSearchResult = $room->users()->find($userId);
        if ($roomUserSearchResult != null) {
            if (
                $roomUserSearchResult->pivot['user_id'] == $userId
                && $roomUserSearchResult->pivot['room_id'] == $roomId
            ) {
                return false;
            }
        }

        $roomUser = $room->users()->attach($userId,[
            'go' => 0,
            'status' => config('const.piece_status_health'),
            'position' => 1
        ]);

        // Roomテーブルのmember_countを1足してDB更新
        $room->member_count = $room['member_count'] + 1;
        $room->save();

        return true;

    }

    // room_userテーブルの取得
    public function getRoomUser($userId, $roomId)
    {
        $room = $this->model::where([
            'id' => $roomId
        ])->first();

        return $room->users()->find($userId);
    }

    // member_countの取得
    public function getMemberCount($roomId)
    {
        $room = $this->model::where([
            'id' => $roomId
        ])->first();
        return $room->member_count;
    }
}



