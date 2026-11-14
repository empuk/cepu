<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupExpiredRoomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_cleanup_command_deletes_only_expired_rooms(): void
    {
        $expired = Room::create(['code' => 'OLDX2345', 'expired_at' => now()->subMinute()]);
        $expired->users()->create(['name' => 'Rafif', 'last_seen' => now()]);
        $expired->messages()->create(['body' => 'lama', 'is_system' => false]);

        $active = Room::create(['code' => 'NEWX2345', 'expired_at' => now()->addDay()]);

        $this->artisan('rooms:cleanup')->assertSuccessful();

        $this->assertDatabaseMissing('rooms', ['id' => $expired->id]);
        $this->assertDatabaseMissing('room_users', ['room_id' => $expired->id]);
        $this->assertDatabaseMissing('messages', ['room_id' => $expired->id]);
        $this->assertDatabaseHas('rooms', ['id' => $active->id]);
    }
}
