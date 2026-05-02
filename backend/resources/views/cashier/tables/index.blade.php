@extends('layouts.app')
@section('title', 'Status Meja')
@section('page-title', 'Status Meja')

@section('content')
<div class="space-y-5">

    {{-- Legend + Aksi --}}
    <div class="flex flex-wrap gap-3 items-center">
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-green-400"></span> Tersedia
        </span>
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-red-400"></span> Terisi
        </span>
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-yellow-400"></span> Reservasi
        </span>
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-gray-300"></span> Ditutup
        </span>
        <div class="ml-auto flex items-center gap-3">
            <span id="poll-indicator" class="flex items-center gap-1.5 text-xs text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Live
            </span>
            <a href="{{ route('cashier.reservations.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
                style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f';"
                onmouseout="this.style.backgroundColor='#2D54BF';">
                Reservasi
            </a>
            <!-- <a href="{{ route('cashier.reservations.create') }}" class="btn-secondary text-sm">+ Reservasi</a> -->
        </div>
    </div>

    {{-- Meja per Seksi --}}
    @php $sections = $tables->groupBy('section'); @endphp
    @forelse($sections as $section => $sectionTables)
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
                {{ strtoupper($section ?: 'Area Utama') }}
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                @foreach($sectionTables as $table)
                    @php
                        $activeOrder = $table->activeOrder;
                        $reservation = $table->activeReservation;
                        $status      = $table->status;
                        $colorMap = [
    'available' => 'border-green-200 bg-green-50',
    'occupied'  => 'border-red-200 bg-red-50',
    'reserved'  => 'border-yellow-200 bg-yellow-50',
    'closed'    => 'border-gray-100 bg-gray-100 opacity-70',
];
                        $dotMap = [
                            'available' => 'bg-green-400',
                            'occupied'  => 'bg-red-400',
                            'reserved'  => 'bg-yellow-400',
                            'closed'    => 'bg-gray-300',
                        ];
                    @endphp

                    <div id="table-card-{{ $table->id }}"
                         data-table-id="{{ $table->id }}"
                         data-table-number="{{ $table->number }}"
                         data-table-capacity="{{ $table->capacity }}"
                         data-status="{{ $status }}"
                         data-order-id="{{ $activeOrder?->id ?? '' }}"
                         data-order-status="{{ $activeOrder?->status ?? '' }}"
                         class="table-card border-2 rounded-xl p-3 transition-all {{ $colorMap[$status] ?? 'border-gray-200 bg-gray-50' }}">

                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Meja {{ $table->number }}</p>
                                <p class="text-xs text-gray-500">{{ $table->capacity }} kursi</p>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full mt-0.5 table-dot {{ $dotMap[$status] ?? 'bg-gray-300' }}"></span>
                        </div>

                        <div class="table-body">
                            @if($status === 'closed')
                                <div class="mt-1 text-center">
                                    <span class="text-xs text-gray-400 font-medium inline-flex items-center gap-1 justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                        Ditutup
                                    </span>
                                </div>
                            @elseif($activeOrder)
                                <div class="mt-2 pt-2 border-t border-red-200">
                                    <p class="text-xs font-medium text-red-700 truncate">#{{ $activeOrder->order_number }}</p>
                                    <p class="text-xs text-red-500">Rp {{ number_format($activeOrder->total_amount,0,',','.') }}</p>
                                    <p class="text-xs text-red-400 mt-0.5 inline-flex items-center gap-1">
                                        @php $orderStatus = $activeOrder->status; @endphp
                                        @if($orderStatus === 'pending')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/>
                                            </svg>
                                            Menunggu bayar
                                        @elseif($orderStatus === 'cooking')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                                            </svg>
                                            Dimasak
                                        @elseif($orderStatus === 'ready')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                            </svg>
                                            Siap disajikan
                                        @else
                                            {{ $activeOrder->status }}
                                        @endif
                                    </p>
                                    <a href="{{ route('cashier.orders.show', $activeOrder) }}"
                                       class="mt-1.5 block text-center text-xs bg-white border border-red-200 text-red-700 rounded-lg py-1 hover:bg-red-50 transition">
                                        Lihat Pesanan
                                    </a>
                                </div>
                            @elseif($reservation)
                                <div class="mt-2 pt-2 border-t border-yellow-200">
                                    <p class="text-xs font-medium text-yellow-700 truncate">{{ $reservation->customer_name }}</p>
                                    <p class="text-xs text-yellow-600">{{ \Carbon\Carbon::parse($reservation->reserved_at)->format('H:i') }}</p>
                                    <a href="{{ route('cashier.orders.create') }}?table={{ $table->id }}"
                                       class="mt-1.5 block text-center text-xs bg-indigo-600 text-white rounded-lg py-1.5 hover:bg-indigo-700 transition font-medium">
                                        Mulai Pesanan
                                    </a>
                                    <form method="POST" action="{{ route('cashier.reservations.cancel', $reservation) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="mt-1 w-full text-xs bg-white border border-yellow-200 text-yellow-700 rounded-lg py-1 hover:bg-yellow-50 transition">
                                            Batalkan
                                        </button>
                                    </form>
                                </div>
                            @else
                               <a href="{{ route('cashier.orders.create') }}?table={{ $table->id }}"
   class="mt-2 block text-center text-xs rounded-lg py-1 transition-all"
   style="color:white; background-color:#16a34a; border:1px solid #16a34a;"
   onmouseover="this.style.backgroundColor='#15803d';"
   onmouseout="this.style.backgroundColor='#16a34a';">
    Buat Pesanan
</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card text-center py-10 text-gray-400">
            <div class="flex justify-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><path d="M3 16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v1.5a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5V11a2 2 0 0 0-4 0z"/><path d="M5 18v2"/><path d="M19 18v2"/>
                </svg>
            </div>
            <p>Belum ada meja terdaftar</p>
        </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
(function() {
    const INTERVAL = 10000;
    const POLL_URL = '{{ route('poll.tables') }}';

    const colorMap = {
    available: 'border-green-200 bg-green-50',
    occupied:  'border-red-200 bg-red-50',
    reserved:  'border-yellow-200 bg-yellow-50',
    closed:    'border-gray-100 bg-gray-100 opacity-70',
};
    const dotMap = {
        available: 'bg-green-400',
        occupied:  'bg-red-400',
        reserved:  'bg-yellow-400',
        closed:    'bg-gray-300',
    };

    const svgLock = `<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;

    const statusLabel = {
        pending: `<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg> Menunggu bayar`,
        cooking: `<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg> Dimasak`,
        ready:   `<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Siap disajikan`,
    };

    // Route cancel reservasi — digunakan oleh JS untuk render form Batalkan.
    // Kita pakai CSRF token dari meta tag (harus ada di layout).
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function formatRp(n) {
        return new Intl.NumberFormat('id-ID').format(n);
    }

    async function pollTables() {
        try {
            const res  = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            data.forEach(t => {
                const card = document.getElementById('table-card-' + t.id);
                if (!card) return;

                const prevStatus      = card.dataset.status;
                const prevOrderId     = card.dataset.orderId;
                const prevOrderStatus = card.dataset.orderStatus;

                // Bandingkan semua state — termasuk reservation_id agar batalkan tidak hilang
                const prevReservationId = card.dataset.reservationId ?? '';
                const currReservationId = String(t.reservation?.id ?? '');

                const noChange =
                    prevStatus      === t.status &&
                    prevOrderId     === String(t.order_id ?? '') &&
                    prevOrderStatus === String(t.order_status ?? '') &&
                    prevReservationId === currReservationId;

                if (noChange) return;

                // Simpan state baru ke dataset
                card.dataset.status        = t.status;
                card.dataset.orderId       = t.order_id ?? '';
                card.dataset.orderStatus   = t.order_status ?? '';
                card.dataset.reservationId = currReservationId;

                // Update warna kartu
                const allColors = Object.values(colorMap).flatMap(c => c.split(' ')).filter(Boolean);
                card.classList.remove(...allColors);
                (colorMap[t.status] || '').split(' ').filter(Boolean).forEach(c => card.classList.add(c));

                // Update dot
                const dot = card.querySelector('.table-dot');
                if (dot) {
                    const allDots = Object.values(dotMap);
                    dot.classList.remove(...allDots);
                    dot.classList.add(dotMap[t.status] || 'bg-gray-300');
                }

                // Update body
                const body = card.querySelector('.table-body');
                if (!body) return;

                if (t.status === 'closed') {
                    body.innerHTML = `
                        <div class="mt-1 text-center">
                            <span class="text-xs text-gray-400 font-medium inline-flex items-center gap-1 justify-center">
                                ${svgLock} Ditutup
                            </span>
                        </div>`;

                } else if (t.status === 'occupied' && t.order_id) {
                    body.innerHTML = `
                        <div class="mt-2 pt-2 border-t border-red-200">
                            <p class="text-xs font-medium text-red-700 truncate">#${t.order_number}</p>
                            <p class="text-xs text-red-500">Rp ${formatRp(t.order_total_raw ?? 0)}</p>
                            <p class="text-xs text-red-400 mt-0.5 inline-flex items-center gap-1">
                                ${statusLabel[t.order_status] ?? t.order_status}
                            </p>
                            <a href="/cashier/orders/${t.order_id}"
                               class="mt-1.5 block text-center text-xs bg-white border border-red-200
                                      text-red-700 rounded-lg py-1 hover:bg-red-50 transition">
                                Lihat Pesanan
                            </a>
                        </div>`;

                } else if (t.status === 'reserved' && t.reservation) {
                    /*
                     * FIX: render KEDUA tombol — Mulai Pesanan + Batalkan.
                     * Tombol Batalkan menggunakan form dengan method spoofing DELETE
                     * dan CSRF token dari meta tag.
                     */
                    body.innerHTML = `
                        <div class="mt-2 pt-2 border-t border-yellow-200">
                            <p class="text-xs font-medium text-yellow-700 truncate">
                                ${t.reservation.name}
                            </p>
                            <p class="text-xs text-yellow-600">${t.reservation.time}</p>
                            <a href="/cashier/orders/create?table=${t.id}"
                               class="mt-1.5 block text-center text-xs bg-indigo-600 text-white
                                      rounded-lg py-1.5 hover:bg-indigo-700 transition font-medium">
                                Mulai Pesanan
                            </a>
                            <form method="POST"
                                  action="/cashier/reservations/${t.reservation.id}"
                                  onsubmit="return confirm('Batalkan reservasi ini?')"
                                  style="margin-top:4px;">
                                <input type="hidden" name="_token" value="${CSRF_TOKEN}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit"
                                        class="w-full text-xs bg-white border border-yellow-200
                                               text-yellow-700 rounded-lg py-1 hover:bg-yellow-50 transition">
                                    Batalkan
                                </button>
                            </form>
                        </div>`;

                } else {
                    // available (tidak ada order, tidak ada reservasi aktif)
                    body.innerHTML = `
                        <a href="/cashier/orders/create?table=${t.id}"
                           class="mt-2 block text-center text-xs bg-white border border-green-200
                                  text-green-700 rounded-lg py-1 hover:bg-green-50 transition">
                            Buat Pesanan
                        </a>`;
                }
            });

            // Update indikator Live
            const indicator = document.getElementById('poll-indicator');
            if (indicator) {
                indicator.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    Live · ${new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})}`;
            }

        } catch (e) {
            const indicator = document.getElementById('poll-indicator');
            if (indicator) {
                indicator.innerHTML = `<span class="w-2 h-2 rounded-full bg-red-400"></span> Offline`;
            }
        }
    }

    setInterval(pollTables, INTERVAL);
})();
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.getElementById('flash-success');
        if (flash) {
            setTimeout(() => {
                flash.style.transition = 'opacity 0.5s';
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 500);
            }, 3000);
        }
    });
</script>
@endpush
</script>
@endpush