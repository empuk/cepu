@extends('layouts.app')

@section('title', "Room {$room->code} — TempChat")

@php
    $shareUrl = urlencode(route('room.show', $room));
    $shareText = urlencode("Join room {$room->code} di TempChat");
@endphp

@section('content')
    <div
        id="chat-app"
        class="mx-auto flex h-screen w-full max-w-4xl flex-col px-4 sm:px-6"
        data-me="{{ $me->id }}"
        data-expired-at="{{ $room->expired_at->toIso8601String() }}"
        data-url-messages="{{ route('room.messages.index', $room) }}"
        data-url-send="{{ route('room.messages.store', $room) }}"
        data-url-typing="{{ route('room.typing.store', $room) }}"
        data-url-typing-status="{{ route('room.typing.status', $room) }}"
    >
        <header class="flex flex-wrap items-center gap-x-3 gap-y-2 border-b border-white/5 py-4">
            <div class="mr-auto min-w-0">
                <p class="label">room</p>
                <div class="mt-1 flex items-center gap-2">
                    <span class="font-mono text-lg tracking-[0.2em]">{{ $room->code }}</span>
                    <button type="button" id="copy-link" class="btn-ghost !px-2 !py-1">Copy link</button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-sm border border-secondary/30 px-2 py-1 font-mono text-[10px] uppercase tracking-[0.14em] text-secondary">
                    <span id="online-count">0</span> online
                </span>

                <span
                    id="countdown"
                    class="rounded-sm border border-secondary/30 px-2 py-1 font-mono text-[10px] tracking-[0.14em] text-secondary"
                    title="Sisa waktu room"
                >--:--:--</span>

                <button type="button" id="sound-toggle" class="btn-ghost" aria-pressed="false">
                    Sound: off
                </button>

                <a
                    href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-ghost"
                >WA</a>
                <a
                    href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareText }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-ghost"
                >Telegram</a>

                <button type="button" id="leave-button" class="btn-ghost hover:!border-red-400/60 hover:!text-red-300">
                    Leave
                </button>
            </div>
        </header>

        <section
            id="messages"
            class="flex-1 space-y-3 overflow-y-auto py-6"
            aria-live="polite"
            aria-label="Pesan room"
        >
            <p id="empty-state" class="py-10 text-center font-mono text-xs uppercase tracking-[0.18em] text-secondary">
                Memuat pesan&hellip;
            </p>
        </section>

        <div id="typing-indicator" class="hidden items-center gap-1 pb-3 font-mono text-[11px] text-secondary">
            <span id="typing-text"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        </div>

        <form id="message-form" class="border-t border-white/5 py-4" autocomplete="off">
            <div class="flex items-end gap-3">
                <textarea
                    id="message-input"
                    name="body"
                    rows="1"
                    maxlength="500"
                    required
                    placeholder="Tulis pesan&hellip;"
                    class="field max-h-32 resize-none"
                ></textarea>
                <button type="submit" id="send-button" class="btn-primary shrink-0">Kirim</button>
            </div>
            <div class="mt-2 flex items-center justify-between gap-4">
                <span class="label hidden sm:inline">Enter kirim &middot; Shift+Enter baris baru</span>
                <span id="char-count" class="label ml-auto">0/500</span>
            </div>
        </form>
    </div>

    <div id="leave-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-neutral/80 p-6 backdrop-blur-sm">
        <div class="card w-full max-w-sm">
            <p class="label">konfirmasi</p>
            <h2 class="mt-2 font-mono text-xl">Keluar dari room?</h2>
            <p class="mt-2 text-sm leading-relaxed text-secondary">
                Pesan "left the room" akan dikirim dan link room tetap bisa dipakai orang lain.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="leave-cancel" class="btn-ghost">Batal</button>
                <form method="POST" action="{{ route('room.leave', $room) }}" id="leave-form">
                    @csrf
                    <button type="submit" class="btn-primary !bg-red-300 !text-neutral">Leave</button>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-container" class="fixed bottom-6 right-6 z-[60] flex w-full max-w-xs flex-col gap-2"></div>
@endsection
