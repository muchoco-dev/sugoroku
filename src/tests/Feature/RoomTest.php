<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Board;
use App\Models\RoomUser;
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
     * member_countがmax_member_count以上ならfalseが返ってくる
     */
    public function testReturnFalseWhenMemberCountIsGreaterThanMaxMemberCount()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'first room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 12,
            'status'    => config('const.room_status_open'),
        ]);

        $repository = new RoomRepository();
        $result = $repository->IsCheckedEnteredRoom($room->owner_id, $room->id);
        $this->assertFalse($result);
    }

    /**
     * room_userテーブルに既に同じユーザと同じ部屋のペアで保存されているとfalseが返ってくる
     */
    public function testReturnFalseWhenRoomUserTableIsAlreadyStoredWithTheSameUserAndRoomPair()
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

        // 中間(room_user)テーブルの作成
        $roomUser = $room->users()->attach($user->id, [
            'go' => 0,
            'status' => config('const.piece_status_health'),
            'position' => 1
        ]);

        $repository = new RoomRepository();
        $result = $repository->IsCheckedEnteredRoom($user->id, $room->id);
        $this->assertFalse($result);
    }

    /**
     * room_userテーブルに新しいデータが保存され、部屋のmember_countが1増え、trueが返ってくる。
     */
    public function testReturnTrueAndRoomUserTableIsStoredWithNewDataAndMemberCountOfTheRoomHasIncreasedBy1()
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

        // returnでtrueが返されてるか確認
        $result = $repository->IsCheckedEnteredRoom($user->id, $room->id);
        $this->assertTrue($result);

        // room_userテーブルに新しいデータが保存されてるか確認
        $roomUser = $repository->getRoomUser($user->id, $room->id);
        $this->assertNotEquals($roomUser, null);

        // 部屋のmember_countが1増えてるかの確認
        $memberCount = $repository->getMemberCount($room->id);
        $this->assertEquals($memberCount, 1);
    }

}
