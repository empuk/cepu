<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomUser;
use App\Support\RoomAuth;
use App\Support\Text;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function show(Room $room): View
    {
        $user = RoomAuth::user($room);

        if (! $user) {
            return view('room.join', ['room' => $room]);
        }

        $user->forceFill(['last_seen' => now()])->save();

        return view('room.show', [
            'room' => $room,
            'me' => $user,
        ]);
    }

    public function join(Request $request, Room $room): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:30'],
        ], [
            'name.required' => 'Nama Anda wajib diisi.',
            'name.max' => 'Nama maksimal 30 karakter.',
        ]);

        $name = Text::clean($request->string('name')->toString(), 30);

        if ($name === '') {
            return back()->withErrors(['name' => 'Nama tidak valid.'])->withInput();
        }

        if (RoomAuth::user($room)) {
            return redirect()->route('room.show', $room);
        }

        $user = DB::transaction(function () use ($room, $name): RoomUser {
            $user = $room->users()->create([
                'name' => RoomUser::uniqueName($room, $name),
                'last_seen' => now(),
            ]);

            $room->messages()->create([
                'room_user_id' => $user->id,
                'body' => "{$user->name} joined the room",
                'is_system' => true,
            ]);

            return $user;
        });

        RoomAuth::login($room, $user);

        return redirect()->route('room.show', $room);
    }

    public function leave(Room $room): RedirectResponse
    {
        $user = RoomAuth::user($room);

        if ($user) {
            DB::transaction(function () use ($room, $user): void {
                $room->messages()->create([
                    'room_user_id' => $user->id,
                    'body' => "{$user->name} left the room",
                    'is_system' => true,
                ]);

                $user->forceFill([
                    'last_seen' => null,
                    'typing_at' => null,
                ])->save();
            });

            RoomAuth::logout($room);
        }

        return redirect()->route('home');
    }
}
