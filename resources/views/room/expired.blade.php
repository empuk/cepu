@extends('layouts.app')

@section('title', 'Room kedaluwarsa — TempChat')

@section('content')
    <main class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-6 py-16 text-center">
        <p class="label">410 / gone</p>
        <h1 class="mt-4 font-mono text-4xl font-medium tracking-tight">Room sudah kedaluwarsa</h1>
        <p class="mx-auto mt-4 max-w-sm text-sm leading-relaxed text-secondary">
            Room <span class="font-mono text-primary">{{ $room->code }}</span> melewati batas 24 jam,
            jadi seluruh pesannya sudah dihapus permanen.
        </p>
        <a href="{{ route('home') }}" class="btn-primary mx-auto mt-8">Buat room baru</a>
    </main>
@endsection
