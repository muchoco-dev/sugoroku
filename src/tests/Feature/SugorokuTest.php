<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Board;
use App\Models\Space;
use App\Repositories\RoomRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $this->members = factory(User::class, 3)->create();
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
    }

    /**
     * 未ログイン状態でゲームスタートできない
     */
    public function testGuestCannotStartGame()
    {
    }

    /**
     * 部屋に参加していないログイン済みユーザはゲームスタートできない
     */
    public function testUserCannotStartGame()
    {
    
    }

    /**
     * 部屋の参加者はゲームスタートできない
     */
    public function testMemberCannotStartGame()
    {
        $this->assertTrue(true); 
    }
}
