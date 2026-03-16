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
            {{-- Live indicator --}}
            <span id="poll-indicator" class="flex items-center gap-1.5 text-xs text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Live
            </span>
            <a href="{{ route('cashier.reservations.create') }}" class="btn-secondary text-sm">+ Reservasi</a>
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
                            'available' => 'border-green-300 bg-green-50 hover:bg-green-100',
                            'occupied'  => 'border-red-300 bg-red-50',
                            'reserved'  => 'border-yellow-300 bg-yellow-50',
                            'closed'    => 'border-gray-200 bg-gray-100 opacity-70',
                        ];
                        $dotMap = [
                            'available' => 'bg-green-400',
                            'occupied'  => 'bg-red-400',
                            'reserved'  => 'bg-yellow-400',
                            'closed'    => 'bg-gray-300',
                        ];
                    @endphp

                    {{-- data-table-id dipakai JS untuk update partial --}}
                    <div id="table-card-{{ $table->id }}"
                         data-table-id="{{ $table->id }}"
                         data-table-number="{{ $table->number }}"
                         data-table-capacity="{{ $table->capacity }}"
                         data-status="{{ $status }}"
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
                                    <span class="text-xs text-gray-400 font-medium">🔒 Ditutup</span>
                                </div>
                            @elseif($activeOrder)
                                <div class="mt-2 pt-2 border-t border-red-200">
                                    <p class="text-xs font-medium text-red-700 truncate">#{{ $activeOrder->order_number }}</p>
                                    <p class="text-xs text-red-500">Rp {{ number_format($activeOrder->total_amount,0,',','.') }}</p>
                                    <p class="text-xs text-red-400 mt-0.5">
                                        {{ match($activeOrder->status) {
                                            'pending'  => '⏳ Menunggu bayar',
                                            'cooking'  => '🔥 Dimasak',
                                            'ready'    => '✅ Siap disajikan',
                                            default    => $activeOrder->status,
                                        } }}
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
                                    <form method="POST" action="{{ route('cashier.reservations.cancel', $reservation) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="mt-1.5 w-full text-xs bg-white border border-yellow-200 text-yellow-700 rounded-lg py-1 hover:bg-yellow-50 transition">
                                            Batalkan
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('cashier.orders.create') }}?table={{ $table->id }}"
                                   class="mt-2 block text-center text-xs bg-white border border-green-200 text-green-700 rounded-lg py-1 hover:bg-green-50 transition">
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
            <p class="text-4xl mb-2">🪑</p>
            <p>Belum ada meja terdaftar</p>
        </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
(function() {
    const INTERVAL = 10000; // 10 detik
    const POLL_URL = '{{ route('poll.tables') }}';

    const colorMap = {
        available: 'border-green-300 bg-green-50 hover:bg-green-100',
        occupied:  'border-red-300 bg-red-50',
        reserved:  'border-yellow-300 bg-yellow-50',
        closed:    'border-gray-200 bg-gray-100 opacity-70',
    };
    const dotMap = {
        available: 'bg-green-400',
        occupied:  'bg-red-400',
        reserved:  'bg-yellow-400',
        closed:    'bg-gray-300',
    };
    const statusLabel = {
        pending:  '⏳ Menunggu bayar',
        cooking:  '🔥 Dimasak',
        ready:    '✅ Siap disajikan',
    };

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

                const prevStatus = card.dataset.status;
                if (prevStatus === t.status &&
                    card.dataset.orderId === String(t.order_id ?? '') &&
                    card.dataset.orderStatus === String(t.order_status ?? '')) {
                    return; // tidak ada perubahan
                }

                // Update dataset
                card.dataset.status      = t.status;
                card.dataset.orderId     = t.order_id ?? '';
                card.dataset.orderStatus = t.order_status ?? '';

                // Update border & background
                const allColors = Object.values(colorMap).join(' ').split(' ').filter(Boolean);
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

                const num  = card.dataset.tableNumber;
                const cap  = card.dataset.tableCapacity;

                if (t.status === 'closed') {
                    body.innerHTML = `<div class="mt-1 text-center"><span class="text-xs text-gray-400 font-medium">🔒 Ditutup</span></div>`;
                } else if (t.status === 'occupied' && t.order_id) {
                    body.innerHTML = `
                        <div class="mt-2 pt-2 border-t border-red-200">
                            <p class="text-xs font-medium text-red-700 truncate">#${t.order_number}</p>
                            <p class="text-xs text-red-500">Rp ${t.order_total}</p>
                            <p class="text-xs text-red-400 mt-0.5">${statusLabel[t.order_status] ?? t.order_status}</p>
                            <a href="/cashier/orders/${t.order_id}"
                               class="mt-1.5 block text-center text-xs bg-white border border-red-200 text-red-700 rounded-lg py-1 hover:bg-red-50 transition">
                                Lihat Pesanan
                            </a>
                        </div>`;
                } else if (t.status === 'reserved' && t.reservation) {
                    body.innerHTML = `
                        <div class="mt-2 pt-2 border-t border-yellow-200">
                            <p class="text-xs font-medium text-yellow-700 truncate">${t.reservation.name}</p>
                            <p class="text-xs text-yellow-600">${t.reservation.time}</p>
                        </div>`;
                } else {
                    body.innerHTML = `
                        <a href="/cashier/orders/create?table=${t.id}"
                           class="mt-2 block text-center text-xs bg-white border border-green-200 text-green-700 rounded-lg py-1 hover:bg-green-50 transition">
                            Buat Pesanan
                        </a>`;
                }
            });

        } catch (e) {
            // Gagal poll — tidak perlu alert
        }
    }

    // Mulai polling
    setInterval(pollTables, INTERVAL);
})();
</script>
@endpush