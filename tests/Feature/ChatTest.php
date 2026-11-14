<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private Room $room;

    private RoomUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('send-message:1');

        $this->room = Room::create(['code' => 'ABCD2345', 'expired_at' => now()->addDay()]);
        $this->user = $this->room->users()->create(['name' => 'Rafif', 'last_seen' => now()]);
    }

    private function asMember(): static
    {
        return $this->withSession(["room_user_{$this->room->id}" => $this->user->id]);
    }

    public function test_messages_can_be_sent_and_polled(): void
    {
        $this->asMember()
            ->postJson(route('room.messages.store', $this->room), ['body' => 'Halo semua'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Halo semua')
            ->assertJsonPath('message.mine', true);

        $this->asMember()
            ->getJson(route('room.messages.index', $this->room).'?after=0')
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'Halo semua');
    }

    public function test_messages_are_tag_stripped_before_storage(): void
    {
        $this->asMember()
            ->postJson(route('room.messages.store', $this->room), [
                'body' => '<b>Halo</b> <script>alert(1)</script>',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'body' => 'Halo alert(1)',
            'is_system' => false,
        ]);
    }

    public function test_message_polling_only_returns_newer_messages(): void
    {
        $first = $this->room->messages()->create([
            'room_user_id' => $this->user->id,
            'body' => 'pertama',
            'is_system' => false,
        ]);

        $this->room->messages()->create([
            'room_user_id' => $this->user->id,
            'body' => 'kedua',
            'is_system' => false,
        ]);

        $this->asMember()
            ->getJson(route('room.messages.index', $this->room).'?after='.$first->id)
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'kedua');
    }

    public function test_members_are_rate_limited_to_one_message_per_second(): void
    {
        $this->asMember()
            ->postJson(route('room.messages.store', $this->room), ['body' => 'pertama'])
            ->assertCreated();

        $this->asMember()
            ->postJson(route('room.messages.store', $this->room), ['body' => 'kedua'])
            ->assertStatus(429);
    }

    public function test_non_members_cannot_read_or_send_messages(): void
    {
        $this->getJson(route('room.messages.index', $this->room))->assertForbidden();
        $this->postJson(route('room.messages.store', $this->room), ['body' => 'halo'])->assertForbidden();
    }

    public function test_typing_status_is_read_only_and_excludes_the_current_user(): void
    {
        $other = $this->room->users()->create([
            'name' => 'Sinta',
            'last_seen' => now(),
            'typing_at' => now(),
        ]);

        $this->user->forceFill(['typing_at' => now()->subMinute()])->save();

        $this->asMember()
            ->getJson(route('room.typing.status', $this->room))
            ->assertOk()
            ->assertJsonPath('typing.0', 'Sinta')
            ->assertJsonPath('online', 2);

        // The read-only status poll must not refresh typing_at.
        $this->assertTrue($this->user->fresh()->typing_at->lt(now()->subSeconds(30)));
        $this->assertTrue($other->fresh()->typing_at->gte(now()->subSeconds(3)));
    }
}
