<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StartGameRequest;

class SugorokuController extends Controller
{
    public function gameStart(StartGameRequest $request)
    {
        $validated = $request->validated();
        $room = Room::find($validated['room_id']);

        // 部屋が存在しない
        // ユーザが部屋の所有者ではない
        if (!$room || $room->owner_id !== Auth::id()) {
            return response()->json([
                'status'    => 'error'
            ]);

        }

        $repository = new RoomRepository;
        $repository->gameStart($room->id);

        return response()->json([
            'status'    => 'success'
        ]);
    }
}
