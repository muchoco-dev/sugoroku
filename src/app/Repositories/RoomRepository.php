<?php

namespace App\Repositories;

use App\Models\Room;

class RoomRepository
{

    protected $model;

    public function __construct(Room $room)
    {
        $this->model = $room;
    }

    /**
     * ユーザが作成したオープン中の部屋を取得
     */
    public function getOwnOpenRoom()
    {
        return $this->model::where('owner_id', Auth::id())
                    ->first();
    }

    public function create($data){}
    public function findByUname($uname){}
    public function getOpenRooms(){}
    public function changeStatus($id, $status){}
}



