<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['room_id', 'name', 'last_seen', 'typing_at'])]
class RoomUser extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_seen' => 'datetime',
            'typing_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function isOnline(): bool
    {
        return $this->last_seen !== null && $this->last_seen->gte(now()->subSeconds(30));
    }

    public static function uniqueName(Room $room, string $base, int $max = 30): string
    {
        $base = trim($base);
        $name = $base;
        $counter = 2;

        while ($room->users()->where('name', $name)->exists()) {
            $suffix = " ({$counter})";
            $name = mb_substr($base, 0, max(1, $max - mb_strlen($suffix))).$suffix;
            $counter++;
        }

        return $name;
    }
}
