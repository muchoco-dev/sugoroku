<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
use App\Repositories\RoomRepository;
use App\Http\Requests\StoreRoomRequest;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roomRepository = new RoomRepository();
        $userRepository = new UserRepository();


        $rooms = $roomRepository->getOpenRooms();
        $token = $userRepository->getPersonalAccessToken();

        return view('room.index', compact('rooms', 'token'));
    }

    public function store(StoreRoomRequest $request)
    {
        $repository = new RoomRepository();

        // オープン中の部屋を既に所有していたらエラー
        $room = $repository->getOwnOpenRoom(Auth::id());
        if ($room) {
            return response()->json([
                'status'    => 'error',
                'message'   => '既にオープン中の部屋があるようです'
            ]);
        }

        // 部屋作成
        $validated = $request->validated();
        $data = [
            'name'      => $validated['name'],
            'owner_id'  => Auth::id(),
            'board_id'  => 1
        ];
        $repository->create($data);

        return response()->json([
            'status'     => 'success'
        ]);
    }
}
