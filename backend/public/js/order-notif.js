(function () {
    // Hanya jalankan jika user sudah login (ada meta csrf)
    if (!document.querySelector('meta[name="csrf-token"]')) return;

    const POLL_URL  = '/api/poll/orders';
    const INTERVAL  = 10000;
    const prevStatus = {};
    let audioCtx = null;

    // ── Minta izin notifikasi ────────────────────────────────────────────
    function requestPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // ── Tampilkan browser notification ──────────────────────────────────
    function showNotif(order) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        const tableInfo = order.table ? ` · Meja ${order.table}` : '';
        const body      = order.customer_name
            ? `${order.customer_name}${tableInfo} — siap disajikan!`
            : `Pesanan #${order.order_number}${tableInfo} siap disajikan!`;

        const notif = new Notification('🍽️ Pesanan Siap!', {
            body,
            icon: '/favicon.ico',
            tag : 'ready-' + order.id, // cegah duplikat notif order sama
            requireInteraction: false,
        });

        // Klik notif → buka halaman detail order
        notif.onclick = function () {
            window.focus();
            window.location.href = '/cashier/orders/' + order.id;
            notif.close();
        };

        setTimeout(() => notif.close(), 8000);
    }

    // ── Suara notifikasi via Web Audio API ───────────────────────────────
    function playSound() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            // Dua nada: ting-ting
            [0, 0.2].forEach((delay, i) => {
                const osc  = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(i === 0 ? 880 : 1100, audioCtx.currentTime + delay);
                gain.gain.setValueAtTime(0.3, audioCtx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + delay + 0.4);
                osc.start(audioCtx.currentTime + delay);
                osc.stop(audioCtx.currentTime + delay + 0.4);
            });
        } catch (e) { /* browser tidak support */ }
    }

    // ── Toast in-app (muncul di pojok kanan bawah) ───────────────────────
    function showToast(order) {
        // Buat container jika belum ada
        let container = document.getElementById('global-notif-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'global-notif-container';
            container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
            document.body.appendChild(container);
        }

        const tableInfo = order.table ? ` · Meja ${order.table}` : '';
        const toast     = document.createElement('div');
        toast.style.cssText = `
            background:#fff; border:1.5px solid #22c55e; border-radius:14px;
            padding:14px 16px; box-shadow:0 8px 32px rgba(0,0,0,0.15);
            max-width:300px; pointer-events:all; cursor:pointer;
            animation:toastSlideUp 0.3s ease;
            display:flex; align-items:flex-start; gap:12px;
        `;

        toast.innerHTML = `
            <style>
                @keyframes toastSlideUp {
                    from { transform:translateY(16px); opacity:0; }
                    to   { transform:translateY(0);    opacity:1; }
                }
            </style>
            <div style="width:36px;height:36px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div style="flex:1; min-width:0;">
                <p style="font-size:13px;font-weight:700;color:#111827;margin:0;">Pesanan Siap!</p>
                <p style="font-size:12px;color:#6b7280;margin:3px 0 0;line-height:1.4;">
                    <strong>#${order.order_number}</strong>${tableInfo}<br>siap disajikan kepada pelanggan.
                </p>
            </div>
            <button style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;font-size:16px;line-height:1;flex-shrink:0;" aria-label="Tutup">✕</button>
        `;

        // Klik toast → buka detail order
        toast.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') {
                window.location.href = '/cashier/orders/' + order.id;
            }
        });

        // Tombol tutup
        toast.querySelector('button').addEventListener('click', () => {
            toast.remove();
        });

        container.appendChild(toast);

        // Auto hapus setelah 8 detik
        setTimeout(() => {
            toast.style.opacity    = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 8000);
    }

    // ── Polling ──────────────────────────────────────────────────────────
    async function poll() {
        try {
            const res = await fetch(POLL_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const orders = await res.json();

            orders.forEach(o => {
                const prev = prevStatus[o.id];

                // Deteksi perubahan status → ready
                if (prev && prev !== 'ready' && o.status === 'ready') {
                    showNotif(o);
                    showToast(o);
                    playSound();
                }

                prevStatus[o.id] = o.status;
            });

        } catch (e) { /* diam saja */ }
    }

    // ── Init ─────────────────────────────────────────────────────────────
    requestPermission();

    // Isi prevStatus dengan data awal agar tidak false-positive saat load
    fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(orders => {
            orders.forEach(o => { prevStatus[o.id] = o.status; });
        })
        .catch(() => {});

    setInterval(poll, INTERVAL);

})();