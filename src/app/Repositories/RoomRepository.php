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
        ])->first();
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

        return true;

    }

    // メンバー数が最大メンバー数を超えているかチェックする
    public function isMemberExceededMaxMember($room)
    {
        if ($room['member_count'] > $room['max_member_count']) {
            return true;
        }
        return false;
    }

    // 入室済みかどうかをチェックする
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
}



