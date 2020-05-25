<?php

namespace Tests\Feature;

use App\Repositories\RoomRepository;
use App\Events\SugorokuStarted;
use App\Models\Board;
use App\Models\Space;
use App\Models\User;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SugorokuTest extends TestCase
{

    use RefreshDatabase;

    private $owner;     // 部屋を所有しているUser
    private $members;   // 部屋に参加しているowner以外のUser
    private $room;      // 部屋

    protected function setUp():void
    {
        parent::setUp();
        $this->createRoom();
    }

    /**
     * ユーザ達の作成
     */
    private function createUsers()
    {
        $this->owner = factory(User::class)->create();
        $this->members = factory(User::class, 2)->create();
    }

    /**
     * 部屋の作成
     */
    private function createRoom()
    {
        $this->createUsers();

        $board = factory(Board::class)->create([
            'id'            => 1,
            'goal_position' => 10
        ]);

        // すごろくマスの作成
        factory(Space::class)->create([
            'board_id'  => 1,
            'position'  => 2,
        ]);

        $this->room = factory(Room::class)->create([
            'uname'     => uniqid(),
            'name'      => 'room',
            'owner_id'  => $this->owner->id,
            'board_id'  => $board->id,
            'max_member_count'  => 4,
            'member_count'      => 0,
            'status'    => config('const.room_status_open')
        ]);

        $repository = new RoomRepository();
        $repository->addMember($this->owner->id, $this->room->id);

        // 参加者は2人（最大人数 - 1）
        foreach ($this->members as $member) {
            $repository->addMember($member->id, $this->room->id);
        }

    }

    /**
     * 未ログイン状態でゲームスタートできない
     */
    public function testGuestCannotStartGame()
    {
        $response = $this->post('/api/sugoroku/start', [
            'room_id'   => $this->room->id
        ]);

        $this->assertDatabaseHas('rooms', [
            'id'        =>  $this->room->id,
            'status'    =>  $this->room->status
        ]);
    }

    /**
     * 部屋に参加していないログイン済みユーザはゲームスタートできない
     */
    public function testUserCannotStartGame()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        $response = $this->post('/api/sugoroku/start', [
            'room_id'   => $this->room->id
        ]);

        $this->assertDatabaseHas('rooms', [
            'id'        =>  $this->room->id,
            'status'    =>  $this->room->status
        ]);
    }

    /**
     * 部屋の参加者はゲームスタートできない
     */
    public function testMemberCannotStartGame()
    {
        Passport::actingAs($this->members[0]);
        $response = $this->post('/api/sugoroku/start', [
            'room_id'   => $this->room->id
        ]);

        $this->assertDatabaseHas('rooms', [
            'id'        =>  $this->room->id,
            'status'    =>  $this->room->status
        ]);
    }

    /**
     * 部屋のオーナーはゲームスタートできる
     */
    public function testOwnerCanStartGame()
    {
        Passport::actingAs($this->owner);
        $response = $this->post('/api/sugoroku/start', [
            'room_id'   => $this->room->id
        ]);

        // 部屋のステータスが変わる
        $this->assertDatabaseHas('rooms', [
            'id'        =>  $this->room->id,
            'status'    =>  config('const.room_status_busy')
        ]);

        $room = Room::find($this->room->id);
        $room_users = $room->users;

        // ユーザのプレイ順が設定される
        foreach ($room_users as $user) {
            $this->assertNotNull($user->pivot->go);
        }
    }

    /**
     * ゲームをスタートすると、SugorokuStartedイベントが発行される
     */
    public function testDispatchEventWhenGameStarted()
    {
        Event::fake();

        Passport::actingAs($this->owner);
        $response = $this->post('/api/sugoroku/start', [
            'room_id'   => $this->room->id
        ]);

        Event::assertDispatched(SugorokuStarted::class);
    }

    /**
     * ログイン済みのユーザのみコマの現在地が取得できる
     */
    public function testUsergetKomaPosition() 
    {
        Passport::actingAs($this->owner);
        $response = $this->get("/api/sugoroku/position/{$this->owner->id}/{$this->room->id}");

        $response->assertJson([
            'status'   => 'success',
            'position' => $this->room->users()->find($this->owner->id)->pivot['position']
        ]);

    }

    /**
     * ログイン済みのユーザのみコマの現在地が取得できない
     */
    public function testUserCannotgetKomaPosition() 
    {
        Passport::actingAs($this->owner);
        $response = $this->get("/api/sugoroku/position/{$this->owner->id}/99");

        $response->assertJson([
            'status'   => 'error',
        ]);
    }

}
