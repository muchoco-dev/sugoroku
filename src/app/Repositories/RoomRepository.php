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
}



