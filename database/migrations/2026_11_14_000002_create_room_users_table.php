<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name', 30);
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('typing_at')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'name']);
            $table->index(['room_id', 'last_seen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_users');
    }
};
