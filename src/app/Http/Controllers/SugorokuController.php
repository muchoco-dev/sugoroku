<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartGameRequest;
use App\Http\Requests\SaveLogRequest;
use App\Http\Requests\DeleteRoomRequest;
use App\Repositories\RoomRepository;
use App\Events\SugorokuStarted;
use App\Events\DiceRolled;
use App\Models\RoomLog;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SugorokuController extends Controller
{
    /**
     * ゲーム開始処理
     */
    public function startGame(StartGameRequest $request)
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
        $repository->startGame($room->id);
        $repository->virusFirstTurnCheck($room->id);

        event(new SugorokuStarted($room->id));

        return response()->json([
            'status'    => 'success'
        ]);
    }

    /**
     * コマの変化記録
     */
    public function saveLog(SaveLogRequest $request)
    {
        $repository = new RoomRepository;

        $validated = $request->validated();
        $room = Room::find($validated['room_id']);

        // 部屋が存在しない
        // ユーザが部屋のメンバではない
        if (!$room || !$repository->isMember($room, Auth::id(), $room->id)) {
            return response()->json([
                'status' => 'error',
            ]);
        }

        $repository->saveLog(Auth::id(), $room->id, $validated['action_id'], $validated['effect_id'], $validated['effect_num']);

        switch ($validated['action_id']) {
            case config('const.action_by_dice'):
                $repository->movePiece($room->id, Auth::id(), $validated['effect_num']);
                event(new DiceRolled($room->id, Auth::id(), $validated['effect_num']));
                break;
            case config('const.action_by_space'):
                break;
        }

        return response()->json([
            'status' => 'success'
        ]);

    }

    public function getNextGo($roomId)
    {
        $repository = new RoomRepository;
        $room = Room::find($roomId);

        // 部屋が存在しない
        // ユーザが部屋のメンバではない
        if (!$room || !$repository->isMember($room, Auth::id(), $room->id)) {
            return response()->json([
                'status' => 'error',
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'next_go'   => $repository->getNextGo($room->id)
        ]);
    }


    public function getKomaPosition($user_id, $room_id)
    {
        $repository = new RoomRepository;
        $komaPositon = $repository->getKomaPosition($user_id, $room_id);

        if (!$komaPositon) {
            return response()->json([
                'status'    => 'error',
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'position'   => $komaPositon
        ]);
    }

    public function getMembers($room_id)
    {
        $room = Room::find($room_id);
        if (!$room) {
            return response()->json([
                'status' => 'error',
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'members'   => $room->users
        ]);
    }

    /**
     * 部屋を削除
     */
    public function deleteRoom(DeleteRoomRequest $request)
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
        $repository->balus($room->owner_id);

        return response()->json([
            'status'    => 'success'
        ]);
    }
}
