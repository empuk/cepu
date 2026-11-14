<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Support\RoomAuth;
use App\Support\Text;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home');
    }

    public function store(Request $request): RedirectResponse
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

        [$room, $user] = DB::transaction(function () use ($name): array {
            $room = Room::create([
                'code' => Room::generateCode(),
                'expired_at' => now()->addDay(),
            ]);

            $user = $room->users()->create([
                'name' => $name,
                'last_seen' => now(),
            ]);

            $room->messages()->create([
                'room_user_id' => $user->id,
                'body' => "{$name} joined the room",
                'is_system' => true,
            ]);

            return [$room, $user];
        });

        RoomAuth::login($room, $user);

        return redirect()->route('room.show', $room);
    }
}
