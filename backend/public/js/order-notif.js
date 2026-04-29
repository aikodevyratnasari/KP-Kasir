(function () {
    // Hanya jalankan jika user sudah login (ada meta csrf)
    if (!document.querySelector('meta[name="csrf-token"]')) return;

    const POLL_URL  = '/api/poll/orders';
    const INTERVAL  = 5000; // 5 detik (lebih cepat dari sebelumnya)
    const prevStatus = {};
    let audioCtx = null;
    let bannerTimeout = null;
    let currentBanner = null;

    // ── CSS sekali inject ────────────────────────────────────────────────
    const STYLES = `
        @keyframes bannerSlideDown {
            0%   { transform: translateX(-50%) translateY(-110%); opacity: 0; }
            60%  { transform: translateX(-50%) translateY(8px);   opacity: 1; }
            100% { transform: translateX(-50%) translateY(0px);   opacity: 1; }
        }
        @keyframes bannerSlideUp {
            0%   { transform: translateX(-50%) translateY(0);     opacity: 1; }
            100% { transform: translateX(-50%) translateY(-110%); opacity: 0; }
        }
        @keyframes bannerPulse {
            0%, 100% { box-shadow: 0 8px 40px rgba(22, 163, 74, 0.35), 0 2px 12px rgba(0,0,0,0.15); }
            50%       { box-shadow: 0 8px 60px rgba(22, 163, 74, 0.55), 0 2px 20px rgba(0,0,0,0.2); }
        }
        @keyframes checkPop {
            0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
            70%  { transform: scale(1.2) rotate(3deg);  opacity: 1; }
            100% { transform: scale(1)   rotate(0deg);  opacity: 1; }
        }
        @keyframes progressBar {
            from { width: 100%; }
            to   { width: 0%; }
        }
        #ready-banner {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-110%);
            z-index: 99999;
            background: #ffffff;
            border-radius: 20px;
            width: min(480px, calc(100vw - 32px));
            box-shadow: 0 8px 40px rgba(22, 163, 74, 0.35), 0 2px 12px rgba(0,0,0,0.15);
            overflow: hidden;
            border: 2px solid #86efac;
            cursor: pointer;
            user-select: none;
        }
        #ready-banner.show {
            animation: bannerSlideDown 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) forwards,
                       bannerPulse 2s ease-in-out 0.6s infinite;
        }
        #ready-banner.hide {
            animation: bannerSlideUp 0.35s ease-in forwards;
        }
        #ready-banner .banner-inner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px 16px;
        }
        #ready-banner .banner-icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: checkPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
        }
        #ready-banner .banner-text { flex: 1; min-width: 0; }
        #ready-banner .banner-title {
            font-size: 17px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.3px;
            margin: 0 0 3px;
        }
        #ready-banner .banner-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #ready-banner .banner-order {
            font-size: 12px;
            color: #16a34a;
            font-weight: 700;
            margin-top: 2px;
        }
        #ready-banner .banner-close {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f3f4f6;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            transition: background 0.15s, color 0.15s;
            font-size: 14px;
            line-height: 1;
        }
        #ready-banner .banner-close:hover { background: #fee2e2; color: #ef4444; }
        #ready-banner .banner-progress {
            height: 4px;
            background: #f0fdf4;
        }
        #ready-banner .banner-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            border-radius: 0 2px 2px 0;
            animation: progressBar 8s linear forwards;
        }
        #ready-banner .banner-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #16a34a;
            margin-top: 4px;
        }
    `;

    function injectStyles() {
        if (document.getElementById('ready-notif-styles')) return;
        const el = document.createElement('style');
        el.id = 'ready-notif-styles';
        el.textContent = STYLES;
        document.head.appendChild(el);
    }

    // ── Minta izin notifikasi ────────────────────────────────────────────
    function requestPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // ── Browser Notification (background tab) ───────────────────────────
    function showBrowserNotif(order) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        const tableInfo = order.table ? ` · Meja ${order.table}` : '';
        const body = order.customer_name
            ? `${order.customer_name}${tableInfo} — siap disajikan!`
            : `Pesanan #${order.order_number}${tableInfo} siap disajikan!`;

        const notif = new Notification('🍽️ Pesanan Siap!', {
            body,
            icon: '/favicon.ico',
            tag: 'ready-' + order.id,
            requireInteraction: false,
        });
        notif.onclick = function () {
            window.focus();
            window.location.href = '/cashier/orders/' + order.id;
            notif.close();
        };
        setTimeout(() => notif.close(), 8000);
    }

    // ── Suara notifikasi ─────────────────────────────────────────────────
    function playSound() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            // Tiga nada: ting-ting-ting (lebih energik)
            [[0, 880], [0.18, 1100], [0.36, 1320]].forEach(([delay, freq]) => {
                const osc  = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime + delay);
                gain.gain.setValueAtTime(0.35, audioCtx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + delay + 0.45);
                osc.start(audioCtx.currentTime + delay);
                osc.stop(audioCtx.currentTime + delay + 0.45);
            });
        } catch (e) { /* browser tidak support */ }
    }

    // ── Banner utama (besar, tengah atas) ────────────────────────────────
    function showBanner(order) {
        injectStyles();

        // Tutup banner sebelumnya jika ada
        if (currentBanner) {
            dismissBanner(currentBanner, true);
        }

        const tableInfo = order.table ? `Meja ${order.table}` : 'Takeaway';
        const customerLabel = order.customer_name ? order.customer_name : 'Pelanggan';

        const banner = document.createElement('div');
        banner.id = 'ready-banner';
        banner.innerHTML = `
            <div class="banner-inner">
                <div class="banner-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                         fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <div class="banner-text">
                    <p class="banner-title">🍽️ Pesanan Siap Disajikan!</p>
                    <p class="banner-subtitle">${customerLabel} · ${tableInfo}</p>
                    <div class="banner-order">#${order.order_number}</div>
                </div>
                <button class="banner-close" aria-label="Tutup">✕</button>
            </div>
            <div class="banner-progress">
                <div class="banner-progress-bar"></div>
            </div>
        `;

        document.body.appendChild(banner);

        // Trigger animasi masuk
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                banner.classList.add('show');
            });
        });

        currentBanner = banner;

        // Klik banner → buka detail order
        banner.addEventListener('click', (e) => {
            if (!e.target.closest('.banner-close')) {
                window.location.href = '/cashier/orders/' + order.id;
            }
        });

        // Tombol tutup
        banner.querySelector('.banner-close').addEventListener('click', (e) => {
            e.stopPropagation();
            dismissBanner(banner);
        });

        // Auto dismiss setelah 8 detik
        if (bannerTimeout) clearTimeout(bannerTimeout);
        bannerTimeout = setTimeout(() => dismissBanner(banner), 8000);
    }

    function dismissBanner(banner, immediate = false) {
        if (!banner || !banner.parentNode) return;
        if (bannerTimeout) { clearTimeout(bannerTimeout); bannerTimeout = null; }

        if (immediate) {
            banner.remove();
        } else {
            banner.classList.remove('show');
            banner.classList.add('hide');
            setTimeout(() => { if (banner.parentNode) banner.remove(); }, 400);
        }

        if (currentBanner === banner) currentBanner = null;
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

                if (prev && prev !== 'ready' && o.status === 'ready') {
                    showBanner(o);
                    showBrowserNotif(o);
                    playSound();
                }

                prevStatus[o.id] = o.status;
            });

        } catch (e) { /* diam saja */ }
    }

    // ── Init ─────────────────────────────────────────────────────────────
    injectStyles();
    requestPermission();

    // Isi prevStatus awal agar tidak false-positive saat load
    fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(orders => {
            orders.forEach(o => { prevStatus[o.id] = o.status; });
        })
        .catch(() => {});

    setInterval(poll, INTERVAL);

})();