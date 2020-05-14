<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Board;
use App\Repositories\RoomRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoomTest extends TestCase
{
    public function testGetOwnOpenRoom()
    {
        $user = factory(User::class)->create();
        $board = factory(Board::class)->create([
            'goal_position' => 10
        ]);

        /*
        factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'test room',
            'owner_id'  => $user->id,
            'board_id'  => 1,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => 0
        ]);
        $repository = new RoomReposiory(new Room);
        $room = $repository->getOwnOpenRoom();
        var_dump($room);*/
    }

}
