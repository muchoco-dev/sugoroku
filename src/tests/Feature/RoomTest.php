<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Board;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use App\Repositories\RoomRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RoomTest extends TestCase
{

    use RefreshDatabase;

    public function setUp():void
    {
        parent::setUp();
        $this->artisan('passport:install');
    }

    /**
     * ゲームボードの作成
     */
    private function createBoard()
    {
        $board = factory(Board::class)->create([
            'id'            => 1,
            'goal_position' => 10
        ]);

        // すごろくマスの作成
        factory(Space::class)->create([
            'board_id'  => 1,
            'position'  => 2,
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
     * unameに該当する部屋が存在しない場合は404
     */
    public function testNotExistRoomFromUnameTo404() {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get('/room/囲碁')->assertStatus(404);
    }

    /**
     * unameに該当する部屋が存在するかつ
     * 入室していない場合は404
     */
    public function testExistRoomFromUnameNotMemberTo404() {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'exist room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open'),
        ]);

        $response = $this->actingAs($user)->get('/room/'.$room->uname)->assertStatus(404);
    }

    /**
     * unameに該当する部屋が存在するかつ
     * 入室している場合は正常確認（ステータス:200）
     */
    public function testExistRoomFromUnameMemberTo200() {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'exist room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open'),
        ]);

        $repository = new RoomRepository();
        $result = $repository->addMember($user->id, $room->id);
        $response = $this->actingAs($user)->get('/room/'.$room->uname)->assertStatus(200);
    }

    /**
     * 現在の有効な部屋数を返せるかどうかを確認
     */
    public function testGetCurrentActiveRoomsCount()
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
        $name = 'first room';
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
                'name'      => $name,
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

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name' => $name
        ])->assertJson([
            'status'    => 'error',
            'message'   => '現在の有効部屋数が有効部屋数を超えているようです'
        ]);

        $this->assertDatabaseMissing('rooms', [
            'owner_id'  => $user->id,
            'name'      => $name,
            'status'    => config('const.room_status_open')
        ]);
    }

    /**
     * 最初のユーザが部屋に入ったときにゲームボードのマス配置が保存される
     */
    public function testSpacesPlacementIsDecidedWhenOwnerEnterRoom()
    {

        $user = factory(User::class)->create();
        $board = $this->createBoard();

        Passport::actingAs($user);
        $this->post('/api/room/create', [
            'name' => 'test room'
        ]);

        $uname = uniqid();
        $room = factory(Room::class)->create([
            'uname'     => $uname,
            'name'      => 'test room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open')
        ]);

        // マス配置されていない
        $this->assertDatabaseMissing('room_space', [
            'room_id'  => $room->id,
        ]);

        $repository = new RoomRepository();
        $result = $repository->addMember($room->owner_id, $room->id);

        $this->actingAs($user)->get("/room/{$room->uname}")->assertStatus(200);

        // マス配置されている
        $this->assertDatabaseHas('room_space', [
            'room_id'  => $room->id,
        ]);

    }

    /*
     * 部屋を作成した後、オーナーが参加者として登録されている
     */
    public function testIsRegisteredOwner()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'new room';

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name'      => $name,
        ])->assertJson([
            'status'  => 'success'
        ]);

        $repository = new RoomRepository();
        $room = $repository->getOwnOpenRoom($user->id);

        $this->assertDatabaseHas('room_user', [
            'room_id' => $room->id,
            'user_id'  => $user->id,
            'go' => 0,
            'status' => config('const.piece_status_health'),
            'position' => 1
        ]);
    }

    /**
     * 部屋を作成した後、部屋の参加人数が1になっている
     */
    public function testIsOnePersonInTheRoom()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'new room';

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name'      => $name,
        ])->assertJson([
            'status'  => 'success'
        ]);

        $this->assertDatabaseHas('rooms', [
            'owner_id'      => $user->id,
            'member_count'  => 1,
            'status'        => config('const.room_status_open'),
        ]);
    }

    /**
     * unameに該当する有効な部屋が存在しない場合は404エラー
     */
    public function testNotEffectRoomFromUnameTo404()
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
        $response = $this->actingAs($user)->get('/room/'.$room->uname.'/join')->assertStatus(404);
    }

     /**
     * unameに該当する有効な部屋が存在するかつ
     * 入室済の場合は/room/{uname}にリダイレクト
     */
    public function testEffectRoomFromUnameisMemberRedirectToRoom() 
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'exist room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 10,
            'member_count'      => 0,
            'status'    => config('const.room_status_open'),
        ]);

        $repository = new RoomRepository();
        $result = $repository->addMember($user->id, $room->id);
        $response = $this->actingAs($user)->get('/room/'.$room->uname.'/join')->assertRedirect('/room/'.$room->uname);
    }

    /**
     * unameに該当する有効な部屋が存在するかつ
     * 入室できた場合は/room/{uname}にリダイレクト
     */
    public function testJoinEffectRoomFromUnameRedirectToRoom() {
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

        $response = $this->actingAs($user)->get('/room/'.$room->uname.'/join')->assertRedirect('/room/'.$room->uname);
    }

    /**
     * unameに該当する有効な部屋が存在するかつ
     * 入室できない場合はエラーを返却
     */
    public function testNotJoinEffectRoomFromUnameToError() {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'first room',
            'owner_id'  => $user->id,
            'board_id'  => $board->id,
            'max_member_count'  => 1,
            'member_count'      => 2,
            'status'    => config('const.room_status_open'),
        ]);

        $response = $this->actingAs($user)->get('/room/'.$room->uname.'/join')->assertJson([
            'status'    => 'error',
            'message'   => '入室できませんでした'
        ]);
    }

    /**
     * ログイン中ログイン画面に遷移しようとすると/roomsに遷移するようになっていること
     */
    public function testRedirectToRoomsLogin()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/rooms');
    }

    /**
     * ログイン後のリダイレクト先が/になっていること
     */
    public function testRedirectToRoomsAfterLogin()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('test1111'),
        ]);

        // まだ、認証されていない
        $this->assertFalse(Auth::check());

        $response = $this->from('login')->post('login', [
            'email'    => $user->email,
            'password' => 'test1111'
        ]);

        // 認証されている
        $this->assertTrue(Auth::check());

        $response->assertRedirect('/rooms');
    }

    /**
     * 有効な部屋に入室済みのユーザーはログイン後のリダイレクト先が/room/{uname}になっていること
     */
    public function testRedirectToRoomUnameAfterLogin()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('test1111'),
        ]);
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

        $response = $this->post('login', [
            'email'    => $user->email,
            'password' => 'test1111'
        ]);

        $response->assertRedirect('/rooms');

        $response = $this->actingAs($user)->get('rooms');

        $response->assertRedirect('/room/'.$room->uname);
    }

    /**
     * オーナーが部屋を解散したかどうかを確認
     */
    public function testBalus()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'new room';

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name'      => $name,
        ])->assertJson([
            'status'  => 'success'
        ]);

        $repository = new RoomRepository();
        $room = $repository->getOwnOpenRoom($user->id);

        $result = $repository->balus($user->id);
        $this->assertTrue($result);
    }

    /**
     * ゲーム開始中に解散ができないことを確認
     */
    public function testCannotBalusDuringPlayGame()
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
            'status'    => config('const.room_status_busy'),
        ]);


        $repository = new RoomRepository();
        $result = $repository->addMember($user->id, $room->id);
        $this->assertTrue($result);

        $result = $repository->balus($user->id);
        $this->assertFalse($result);
    }

    /**
     * 解散後、部屋がソフトデリートされていることを確認
     */
    public function testRoomSoftDeleteAfterDisband()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'new room';

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name'      => $name,
        ])->assertJson([
            'status'  => 'success'
        ]);

        $repository = new RoomRepository();
        $room = $repository->getOwnOpenRoom($user->id);

        $result = $repository->balus($user->id);
        $this->assertTrue($result);

        $this->assertSoftDeleted('rooms', [
            'owner_id'      => $user->id,
        ]);

        // room_userの物理削除を確認
        $this->assertDatabaseMissing('room_user', [
            'room_id'   => $room->id
        ]);
    }

    /**
     * 解散後、room_userが物理削除されていることを確認
     */
    public function testRoomUserDeleteAfterDisband()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'new room';

        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name'      => $name,
        ])->assertJson([
            'status'  => 'success'
        ]);

        $repository = new RoomRepository();
        $room = $repository->getOwnOpenRoom($user->id);

        $result = $repository->balus($user->id);
        $this->assertTrue($result);

        $this->assertDatabaseMissing('room_user', [
            'room_id'   => $room->id
        ]);
    }

    /**
     * 部屋作成後にstatusに成功、unameに作成後のユニークキーが設定されている
     */
    public function testCreateRoomAfterStatusSuccessUnameUniqId()
    {
        $user = factory(User::class)->create();
        $board = $this->createBoard();

        $name = 'first room';
        Passport::actingAs($user);
        $response = $this->post('/api/room/create', [
            'name' => $name
        ]);

        $repository = new RoomRepository();
        $room = $repository->getOwnOpenRoom($user->id);

        $response->assertJson([
            'status'   => 'success',
            'uname'    => $room->uname
        ]);
    }
}
