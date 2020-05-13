<?php

namespace App\Repositories;

use App\Models\Room;

class RoomRepository {

    protected $model;

    public function __construct(Room $room){}
    public function create($data){}
    public function findByUname($uname){}
    public function getOpenRooms(){}
    public function changeStatus($id, $status){}
}



