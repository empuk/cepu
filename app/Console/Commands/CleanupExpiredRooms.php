<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;

class CleanupExpiredRooms extends Command
{
    protected $signature = 'rooms:cleanup';

    protected $description = 'Delete expired chat rooms together with their users and messages';

    public function handle(): int
    {
        $deleted = Room::where('expired_at', '<', now())->delete();

        $this->info("Cleaned up {$deleted} expired room(s).");

        return self::SUCCESS;
    }
}
