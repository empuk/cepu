<?php

namespace App\Http\Middleware;

use App\Models\Room;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoomIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $room = $request->route('room');

        if (! $room instanceof Room) {
            abort(404);
        }

        if ($room->isExpired()) {
            return response()->view('room.expired', ['room' => $room], 410);
        }

        return $next($request);
    }
}
