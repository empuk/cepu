<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TypingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/rooms', [HomeController::class, 'store'])->name('rooms.store');

Route::middleware('room.active')->group(function () {
    Route::get('/room/{room}', [RoomController::class, 'show'])->name('room.show');
    Route::post('/room/{room}/join', [RoomController::class, 'join'])->name('room.join');
    Route::post('/room/{room}/leave', [RoomController::class, 'leave'])->name('room.leave');

    Route::get('/room/{room}/messages', [MessageController::class, 'index'])->name('room.messages.index');
    Route::post('/room/{room}/messages', [MessageController::class, 'store'])->name('room.messages.store');

    Route::post('/room/{room}/typing', [TypingController::class, 'store'])->name('room.typing.store');
    Route::get('/room/{room}/typing/status', [TypingController::class, 'status'])->name('room.typing.status');
});
