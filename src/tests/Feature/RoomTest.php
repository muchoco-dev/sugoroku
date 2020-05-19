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
        $result = $repository->addMember($room->owner_id, $room->id);
        $this->assertFalse($result);
    }

    /**
     * ユーザーが既に入室済かどうかを確認
     */
    public function testReturnFalseWhenUserIsAlreadyMember()
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
        $result = $repository->addMember($user->id, $room->id);
        $this->assertFalse($result);
    }

    /**
     * ユーザーが入室に成功したかどうかを確認
     */
    public function testUserIsAddedMember()
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

        $result = $repository->addMember($user->id, $room->id);
        $this->assertTrue($result);

        // room_userテーブルに新しいデータが保存されているかチェックする。
        $checkResult = $repository->isMember($room, $user->id, $room->id);
        $this->assertTrue($checkResult);

        // 部屋のmember_coountが1増えてる。
        $member_count = $room['member_count'] + 1;
        $this->assertEquals($member_count, 1);
    }

    /**
     * 現在の有効な部屋数を返せるかどうかを確認
     */
    public function testgetCurrentActiveRoomsCount()
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

        $count = $repository->getCurrentActiveRoomsCount();
        $this->assertEquals($count, 1);
    }

    /**
     * 現在の有効な部屋数が有効な部屋数以上ならば、新しく部屋を作ることはできない
     */
    public function testUserCannotCreateRoomWhenCurrentActivityRoomsIsGreaterThanMaxActivityRooms()
    {
        $users = factory(User::class, 20)->create();
        $uname = [
            '部屋1',
            '部屋2',
            '部屋3',
            '部屋4',
            '部屋5',
            '部屋6',
            '部屋7',
            '部屋8',
            '部屋9',
            '部屋10',
            '部屋11',
            '部屋12',
            '部屋13',
            '部屋14',
            '部屋15',
            '部屋16',
            '部屋17',
            '部屋18',
            '部屋19',
            '部屋20',
        ];
        $boards = factory(Board::class, 20)->create([
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

        $user = factory(User::class)->create();
        $board = factory(Board::class)->create([
            'goal_position' => 10
        ]);

        $repository = new RoomRepository();

        $uname = '部屋21';

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name' => $uname
        ])->assertJson([
            'status'    => 'error',
            'message'   => '現在の有効部屋数が有効部屋数を超えているようです'
        ]);

        $this->assertDatabaseMissing('rooms', [
            'owner_id'  => $user->id,
            'name'      => $uname,
            'status'    => config('const.room_status_open')
        ]);
    }

}
