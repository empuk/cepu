<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_room_can_be_created_with_a_generated_code(): void
    {
        $response = $this->post('/rooms', ['name' => 'Rafif']);

        $room = Room::firstOrFail();

        $response->assertRedirect(route('room.show', $room));
        $this->assertSame(8, strlen($room->code));
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{8}$/', $room->code);
        $this->assertTrue($room->expired_at->between(now()->addHours(23), now()->addHours(25)));

        $this->assertDatabaseHas('room_users', ['room_id' => $room->id, 'name' => 'Rafif']);
        $this->assertDatabaseHas('messages', ['room_id' => $room->id, 'is_system' => true]);
    }

    public function test_a_visitor_must_join_before_opening_the_chat(): void
    {
        $room = Room::create(['code' => 'ABCD2345', 'expired_at' => now()->addDay()]);

        $this->get(route('room.show', $room))
            ->assertOk()
            ->assertSee('Masuk ke room');
    }

    public function test_duplicate_names_in_the_same_room_get_a_suffix(): void
    {
        $room = Room::create(['code' => 'ABCD2345', 'expired_at' => now()->addDay()]);

        $this->post(route('room.join', $room), ['name' => 'Rafif'])
            ->assertRedirect(route('room.show', $room));

        $this->flushSession();

        $this->post(route('room.join', $room), ['name' => 'Rafif'])
            ->assertRedirect(route('room.show', $room));

        $this->assertDatabaseHas('room_users', ['room_id' => $room->id, 'name' => 'Rafif']);
        $this->assertDatabaseHas('room_users', ['room_id' => $room->id, 'name' => 'Rafif (2)']);
    }

    public function test_leaving_writes_a_system_message_and_clears_presence(): void
    {
        $room = Room::create(['code' => 'ABCD2345', 'expired_at' => now()->addDay()]);
        $user = $room->users()->create(['name' => 'Rafif', 'last_seen' => now()]);

        $this->withSession(["room_user_{$room->id}" => $user->id])
            ->post(route('room.leave', $room))
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('messages', [
            'room_id' => $room->id,
            'is_system' => true,
            'body' => 'Rafif left the room',
        ]);

        $this->assertNull($user->fresh()->last_seen);
    }

    public function test_expired_rooms_return_gone(): void
    {
        $room = Room::create(['code' => 'ABCD2345', 'expired_at' => now()->subMinute()]);

        $this->get(route('room.show', $room))
            ->assertStatus(410)
            ->assertSee('Room sudah kedaluwarsa');
    }
}
