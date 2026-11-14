@extends('layouts.app')

@section('title', 'TempChat — Temporary Chat Room')

@section('content')
    <main class="mx-auto flex min-h-screen w-full max-w-2xl flex-col justify-center px-6 py-16">
        <header class="mb-10">
            <p class="label">temporary / anonymous</p>
            <h1 class="mt-3 font-mono text-5xl font-medium tracking-tight text-primary">
                TempChat<span class="text-tertiary">.</span>
            </h1>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-secondary">
                Buat room chat sementara, bagikan link-nya, dan ngobrol tanpa perlu daftar.
                Room beserta seluruh pesannya otomatis terhapus setelah 24 jam.
            </p>
        </header>

        <form method="POST" action="{{ route('rooms.store') }}" class="card space-y-5">
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

            <button type="submit" class="btn-primary w-full">Create Room</button>
        </form>

        <p class="mt-6 font-mono text-[0.72rem] leading-relaxed text-secondary">
            Tanpa login · realtime via polling · auto-delete setelah 24 jam
        </p>
    </main>
@endsection
