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

    use RefreshDatabase;

    private function createBoard()
    {
        $board = factory(Board::class)->create([
            'id'            => 1,
            'goal_position' => 10
        ]);
        return $board;
    }

    private function createBoardB()
    {
        $board = factory(Board::class)->create([
            'id'            => 2,
            'goal_position' => 10
        ]);
        return $board;
    }


    /**
     * ユーザが作成した部屋を取得
     */
    public function testGetOwnOpenRoom()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'my room';
        factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => $name,
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open')
        ]);
        $repository = new RoomRepository();
        $room = $repository->getOwnOpenRoom($user->id);
        $this->assertEquals($room->name, $name);
    }

    /**
     * ユーザは部屋を作成することができる
     */
    public function testUserCanCreateRoom()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'new room';
        $response = $this->actingAs($user)->post('/api/room/create', [
            'name' => $name
        ])->assertJson([
            'status'  => 'success'
        ]);
        $this->assertDatabaseHas('rooms', [
            'owner_id'  => $user->id,
            'name'      => $name,
            'status'    => config('const.room_status_open')
        ]);
    }

    /**
     * 既にユーザが作ったオープン中の部屋がある場合、新しく部屋を作ることはできない
     *
     * @expectesException Illuminate\Auth\Access\AuthorizationException
     */
    public function testUserCannotCreateRooms()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'first room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open')
        ]);

        $name = 'second room';
        $response = $this->actingAs($user)->post('/api/room/create', [
            'name' => $name
        ])->assertJson([
            'status'    => 'error',
            'message'   => '既にオープン中の部屋があるようです'
        ]);

        $this->assertDatabaseMissing('rooms', [
            'owner_id'  => $user->id,
            'name'      => $name,
            'status'    => config('const.room_status_open')
        ]);

    }

    /**
     * オープン中の部屋全てを表示
     */
    public function testGetOpenRooms()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();
        $boardA = $this->createBoard();
        $boardB = $this->createBoardB();

        factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'first room',
            'owner_id'  => $userA->id,
            'board_id'  => $boardA->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open')
        ]);

        factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'second room',
            'owner_id'  => $userB->id,
            'board_id'  => $boardB->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open')
        ]);

        $repository = new RoomRepository();
        $rooms = $repository->getOpenRooms();

        $count = 0;

        foreach($rooms as $room) {
            if ($count == 0) {
                $this->assertEquals($room['name'], 'first room');
            } else {
                $this->assertEquals($room['name'], 'second room');
            }
            $count++;
        }
    }

}
