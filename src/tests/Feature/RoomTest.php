<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Board;
use App\Repositories\RoomRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
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

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
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
        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
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
        $users = factory(User::class, 2)->create();
        $uname = ['first room', 'second room'];
        $boards = factory(Board::class, 2)->create([
            'goal_position' => 10
        ]);

        $boardIds = [];

        foreach ($boards as $board) {
            $boardIds[] = $board['id'];
        }

        $roomCreateCount = 0;

        foreach ($users as $user) {
            factory(Room::class)->create([
                'uname'     => uniqid(),
                'name'      => $uname[$roomCreateCount],
                'owner_id'  => $user->id,
                'board_id'  => $boardIds[$roomCreateCount],
                'max_member_count'  => 10,
                'member_count'      => 0,
                'status'    => config('const.room_status_open')
            ]);
            $roomCreateCount++;
        }

        $repository = new RoomRepository();
        $rooms = $repository->getOpenRooms();

        $roomCheckCount = 0;

        foreach($rooms as $room) {
            if ($roomCheckCount == 0) {
                $this->assertEquals($room['name'], 'first room');
            } else {
                $this->assertEquals($room['name'], 'second room');
            }
            $roomCheckCount++;
        }
    }

    /**
     * deleted_atがNULLでない部屋は取得されない
     */
    public function testUserCannotGetRoomsAtDeletedAtIsNotNull()
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
            'status'    => config('const.room_status_open'),
            'deleted_at' => '2020-05-06 12:00:00'
        ]);

        $repository = new RoomRepository();
        $rooms = $repository->getOpenRooms();
        $this->assertEmpty($rooms);
    }

    /**
     * roomsテーブルのunameカラムと一致するデータを取得
     */
    public function testGetAMatchWithTheUnameColumnInTheRoomTable()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'first room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open'),
        ]);

        $repository = new RoomRepository();
        $roomObject = $repository->findByUname($room->uname);
        $this->assertEquals($room->uname, $roomObject['uname']);
    }

    /**
     * deleted_atがNULLでない部屋は取得されない(findByUname)
     */
    public function testUserCannotGetRoomsAtDeletedAtIsNotNullEvenIfFindByUname()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'first room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open'),
            'deleted_at' => '2020-05-06 12:00:00'
        ]);

        $repository = new RoomRepository();
        $roomObject = $repository->findByUname($room->uname);
        $this->assertEmpty($roomObject);
    }

}
