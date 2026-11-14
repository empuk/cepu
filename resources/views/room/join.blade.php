@extends('layouts.app')

@section('title', "Join {$room->code} — TempChat")

@section('content')
    <main class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-6 py-16">
        <header class="mb-10">
            <a href="{{ route('home') }}" class="label transition hover:text-primary">&larr; TempChat</a>
            <h1 class="mt-4 font-mono text-3xl font-medium tracking-tight">Masuk ke room</h1>
            <p class="mt-2 text-sm text-secondary">
                Kamu diundang ke room
                <span class="font-mono tracking-[0.18em] text-tertiary">{{ $room->code }}</span>.
            </p>
        </header>

        <form method="POST" action="{{ route('room.join', $room) }}" class="card space-y-5">
            @csrf

            <div>
                <label for="name" class="label">Nama Anda</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    maxlength="30"
                    required
                    autofocus
                    autocomplete="nickname"
                    placeholder="mis. Rafif"
                    class="field mt-2"
                >
                @error('name')
                    <p class="mt-2 font-mono text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full">Join Room</button>
        </form>

        <p class="mt-6 font-mono text-[0.72rem] text-secondary">
            Room ini kedaluwarsa pada {{ $room->expired_at->format('d M Y, H:i') }}.
        </p>
    </main>
@endsection
