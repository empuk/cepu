const app = document.getElementById('chat-app');

if (app) {
    initChat(app);
}

function initChat(root) {
    const urls = {
        messages: root.dataset.urlMessages,
        send: root.dataset.urlSend,
        typing: root.dataset.urlTyping,
        typingStatus: root.dataset.urlTypingStatus,
    };

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const messagesEl = document.getElementById('messages');
    const emptyEl = document.getElementById('empty-state');
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const charCount = document.getElementById('char-count');
    const onlineCount = document.getElementById('online-count');
    const countdownEl = document.getElementById('countdown');
    const typingEl = document.getElementById('typing-indicator');
    const typingText = document.getElementById('typing-text');
    const soundToggle = document.getElementById('sound-toggle');
    const copyButton = document.getElementById('copy-link');
    const leaveButton = document.getElementById('leave-button');
    const leaveModal = document.getElementById('leave-modal');
    const leaveCancel = document.getElementById('leave-cancel');
    const toastContainer = document.getElementById('toast-container');

    const state = {
        lastId: 0,
        sending: false,
        typingTimer: null,
        sound: localStorage.getItem('tempchat:sound') === 'on',
        expiredAt: new Date(root.dataset.expiredAt).getTime(),
        audioContext: null,
        loaded: false,
    };

    const nearBottom = () =>
        messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 120;

    const scrollToBottom = () => {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    function headers(extra = {}) {
        return {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
            ...extra,
        };
    }

    function toast(message, type = 'info') {
        const tone =
            type === 'error'
                ? 'border-red-400/50 text-red-200'
                : type === 'success'
                  ? 'border-tertiary/60 text-primary'
                  : 'border-secondary/40 text-primary';

        const el = document.createElement('div');
        el.className = `pointer-events-none rounded-md border bg-surface/95 px-4 py-3 font-mono text-[11px] shadow-lg backdrop-blur transition-opacity duration-300 ${tone}`;
        el.textContent = message;
        toastContainer.append(el);

        setTimeout(() => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 320);
        }, 3000);
    }

    function updateOnline(count) {
        if (typeof count === 'number') {
            onlineCount.textContent = String(count);
        }
    }

    function updateCharCount() {
        charCount.textContent = `${input.value.length}/500`;
    }

    function renderMessage(message) {
        const wrapper = document.createElement('div');

        if (message.system) {
            wrapper.className = 'flex justify-center py-1';
            const text = document.createElement('p');
            text.className =
                'rounded-sm border border-white/5 bg-surface px-3 py-1 font-mono text-[10px] uppercase tracking-[0.14em] text-secondary';
            text.textContent = message.body;
            wrapper.append(text);
            messagesEl.append(wrapper);
            return;
        }

        wrapper.className = `flex flex-col ${message.mine ? 'items-end' : 'items-start'}`;

        if (!message.mine) {
            const name = document.createElement('span');
            name.className = 'mb-1 font-mono text-[10px] uppercase tracking-[0.14em] text-secondary';
            name.textContent = message.name;
            wrapper.append(name);
        }

        const bubble = document.createElement('div');
        bubble.className = message.mine
            ? 'max-w-[82%] rounded-lg rounded-br-sm bg-tertiary px-4 py-2 text-sm text-neutral'
            : 'max-w-[82%] rounded-lg rounded-bl-sm border border-white/5 bg-surface px-4 py-2 text-sm text-primary';

        const body = document.createElement('p');
        body.className = 'whitespace-pre-wrap break-words';
        body.textContent = message.body;

        const time = document.createElement('span');
        time.className = message.mine
            ? 'mt-1 block text-right font-mono text-[10px] text-neutral/60'
            : 'mt-1 block font-mono text-[10px] text-secondary';
        time.textContent = message.time;

        bubble.append(body, time);
        wrapper.append(bubble);
        messagesEl.append(wrapper);
    }

    function applyMessages(list, online) {
        const stick = !state.loaded || nearBottom();
        let incoming = false;

        for (const message of list) {
            if (message.id <= state.lastId) {
                continue;
            }

            state.lastId = message.id;
            renderMessage(message);

            if (!message.mine && !message.system && state.loaded) {
                incoming = true;
            }
        }

        if (list.length > 0) {
            emptyEl?.classList.add('hidden');
        } else if (state.loaded && state.lastId === 0) {
            emptyEl?.classList.remove('hidden');
            emptyEl.textContent = 'Belum ada pesan. Mulai percakapan.';
        }

        updateOnline(online);

        if (stick) {
            scrollToBottom();
        }

        if (incoming && state.sound) {
            playBeep();
        }

        state.loaded = true;
    }

    function playBeep() {
        try {
            state.audioContext ??= new (window.AudioContext || window.webkitAudioContext)();
            const ctx = state.audioContext;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.value = 660;
            gain.gain.setValueAtTime(0.0001, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.22);

            osc.connect(gain).connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.24);
        } catch {
            // Audio is best-effort only.
        }
    }

    async function pollMessages() {
        try {
            const response = await fetch(`${urls.messages}?after=${state.lastId}`, {
                headers: headers(),
            });

            if (response.status === 403) {
                window.location.reload();
                return;
            }

            if (response.ok) {
                const data = await response.json();
                applyMessages(data.messages ?? [], data.online);
            }
        } catch {
            // Transient network failure: keep polling.
        } finally {
            setTimeout(pollMessages, 2000);
        }
    }

    async function pollTyping() {
        try {
            const response = await fetch(urls.typingStatus, { headers: headers() });

            if (response.ok) {
                const data = await response.json();
                renderTyping(data.typing ?? []);
                updateOnline(data.online);
            }
        } catch {
            // Ignore transient failures.
        } finally {
            setTimeout(pollTyping, 2000);
        }
    }

    function renderTyping(names) {
        typingText.textContent = '';

        if (!names.length) {
            typingEl.classList.add('hidden');
            typingEl.classList.remove('flex');
            return;
        }

        typingText.textContent = `${names.join(', ')} sedang mengetik`;
        typingEl.classList.remove('hidden');
        typingEl.classList.add('flex');
    }

    async function sendMessage() {
        const body = input.value.trim();

        if (body === '' || state.sending) {
            return;
        }

        state.sending = true;
        sendButton.disabled = true;

        try {
            const response = await fetch(urls.send, {
                method: 'POST',
                headers: headers({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ body }),
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 403) {
                window.location.reload();
                return;
            }

            if (!response.ok) {
                toast(data.message ?? 'Pesan gagal dikirim.', 'error');
                return;
            }

            input.value = '';
            updateCharCount();
            applyMessages([data.message], data.online);
            scrollToBottom();
        } catch {
            toast('Koneksi bermasalah. Coba lagi.', 'error');
        } finally {
            state.sending = false;
            sendButton.disabled = false;
            input.focus();
        }
    }

    async function postTyping() {
        try {
            await fetch(urls.typing, {
                method: 'POST',
                headers: headers({ 'Content-Type': 'application/json' }),
                body: '{}',
            });
        } catch {
            // Ignore typing failures; it is decorative.
        }
    }

    function tickCountdown() {
        const remaining = state.expiredAt - Date.now();

        if (remaining <= 0) {
            countdownEl.textContent = 'expired';
            countdownEl.classList.add('text-red-400');
            countdownEl.classList.remove('text-secondary');
            return;
        }

        const total = Math.floor(remaining / 1000);
        const hours = String(Math.floor(total / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
        const seconds = String(total % 60).padStart(2, '0');

        countdownEl.textContent = `${hours}:${minutes}:${seconds}`;
        countdownEl.classList.toggle('text-red-400', remaining < 3600 * 1000);
        countdownEl.classList.toggle('text-secondary', remaining >= 3600 * 1000);
    }

    function setSound(enabled) {
        state.sound = enabled;
        soundToggle.textContent = enabled ? 'Sound: on' : 'Sound: off';
        soundToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        localStorage.setItem('tempchat:sound', enabled ? 'on' : 'off');
    }

    async function copyLink() {
        const url = window.location.href;

        try {
            await navigator.clipboard.writeText(url);
            toast('Link room disalin.', 'success');
        } catch {
            const helper = document.createElement('textarea');
            helper.value = url;
            helper.style.position = 'fixed';
            helper.style.opacity = '0';
            document.body.append(helper);
            helper.select();
            document.execCommand('copy');
            helper.remove();
            toast('Link room disalin.', 'success');
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendMessage();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    input.addEventListener('input', () => {
        updateCharCount();
        clearTimeout(state.typingTimer);

        if (input.value.trim() === '') {
            return;
        }

        state.typingTimer = setTimeout(postTyping, 300);
    });

    soundToggle.addEventListener('click', () => {
        setSound(!state.sound);

        if (state.sound) {
            playBeep();
            toast('Notifikasi suara aktif.', 'success');
        }
    });

    copyButton.addEventListener('click', copyLink);

    leaveButton.addEventListener('click', () => {
        leaveModal.classList.remove('hidden');
        leaveModal.classList.add('flex');
    });

    leaveCancel.addEventListener('click', () => {
        leaveModal.classList.add('hidden');
        leaveModal.classList.remove('flex');
    });

    leaveModal.addEventListener('click', (event) => {
        if (event.target === leaveModal) {
            leaveModal.classList.add('hidden');
            leaveModal.classList.remove('flex');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            leaveModal.classList.add('hidden');
            leaveModal.classList.remove('flex');
        }
    });

    // Auto-grow the textarea up to its max height.
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 128)}px`;
    });

    setSound(state.sound);
    updateCharCount();
    scrollToBottom();
    tickCountdown();
    setInterval(tickCountdown, 1000);
    pollMessages();
    pollTyping();
}
