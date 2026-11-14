<?php

namespace App\Support;

use App\Models\Room;
use App\Models\RoomUser;

final class RoomAuth
{
    public static function sessionKey(Room $room): string
    {
        return 'room_user_'.$room->id;
    }

    public static function user(Room $room): ?RoomUser
    {
        $id = session(self::sessionKey($room));

        if (! $id) {
            return null;
        }

        return $room->users()->whereKey($id)->first();
    }

    public static function login(Room $room, RoomUser $user): void
    {
        session([self::sessionKey($room) => $user->id]);
    }

    public static function logout(Room $room): void
    {
        session()->forget(self::sessionKey($room));
    }
}
