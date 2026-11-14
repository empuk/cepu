<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use App\Support\RoomAuth;
use App\Support\Text;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class MessageController extends Controller
{
    public function index(Request $request, Room $room): JsonResponse
    {
        $me = RoomAuth::user($room);

        if (! $me) {
            return response()->json(['message' => 'Anda belum bergabung di room ini.'], 403);
        }

        // Presence heartbeat only. Never touch typing_at from a read-only poll.
        $me->forceFill(['last_seen' => now()])->save();

        $after = max(0, (int) $request->query('after', 0));

        $query = $room->messages()->with('user')->orderBy('id');

        if ($after > 0) {
            $messages = $query->where('id', '>', $after)->limit(200)->get();
        } else {
            $messages = $room->messages()
                ->with('user')
                ->latest('id')
                ->limit(200)
                ->get()
                ->reverse()
                ->values();
        }

        return response()->json([
            'messages' => $messages->map(fn (Message $message) => $this->payload($message, $me->id))->all(),
            'online' => $room->onlineCount(),
        ]);
    }

    public function store(Request $request, Room $room): JsonResponse
    {
        $me = RoomAuth::user($room);

        if (! $me) {
            return response()->json(['message' => 'Anda belum bergabung di room ini.'], 403);
        }

        $key = 'send-message:'.$me->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return response()->json(['message' => 'Terlalu cepat. Tunggu sebentar.'], 429);
        }

        $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ], [
            'body.required' => 'Pesan tidak boleh kosong.',
            'body.max' => 'Pesan maksimal 500 karakter.',
        ]);

        $body = Text::clean($request->string('body')->toString(), 500);

        if ($body === '') {
            return response()->json(['message' => 'Pesan tidak valid.'], 422);
        }

        RateLimiter::hit($key, 1);

        $me->forceFill(['last_seen' => now()])->save();

        $message = $room->messages()->create([
            'room_user_id' => $me->id,
            'body' => $body,
            'is_system' => false,
        ]);

        return response()->json([
            'message' => $this->payload($message, $me->id),
            'online' => $room->onlineCount(),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Message $message, int $meId): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'system' => $message->is_system,
            'mine' => ! $message->is_system && $message->room_user_id === $meId,
            'name' => $message->user?->name ?? '',
            'time' => $message->created_at->format('H:i'),
        ];
    }
}
