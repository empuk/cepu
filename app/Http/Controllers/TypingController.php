<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Support\RoomAuth;
use Illuminate\Http\JsonResponse;

class TypingController extends Controller
{
    public function store(Room $room): JsonResponse
    {
        $me = RoomAuth::user($room);

        if (! $me) {
            return response()->json(['message' => 'Anda belum bergabung di room ini.'], 403);
        }

        $me->forceFill([
            'last_seen' => now(),
            'typing_at' => now(),
        ])->save();

        return response()->json(['ok' => true]);
    }

    public function status(Room $room): JsonResponse
    {
        $me = RoomAuth::user($room);

        if (! $me) {
            return response()->json(['message' => 'Anda belum bergabung di room ini.'], 403);
        }

        // Read-only poll: refresh presence, but do not touch typing_at.
        $me->forceFill(['last_seen' => now()])->save();

        $typing = $room->users()
            ->whereKeyNot($me->id)
            ->where('typing_at', '>=', now()->subSeconds(3))
            ->pluck('name')
            ->all();

        return response()->json([
            'typing' => $typing,
            'online' => $room->onlineCount(),
        ]);
    }
}
