@extends('layouts.app')
@section('title', 'Detail Pesanan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto" id="order-detail" data-order-id="{{ $order->id }}">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('cashier.orders.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
            <h1 class="page-title">Pesanan #{{ $order->order_number }}</h1>
        </div>
        <span id="order-status-badge" class="badge badge-{{ $order->status }} text-sm px-3 py-1">
            {{ match($order->status) {
                'pending'   => '⏳ Menunggu',
                'cooking'   => '🔥 Dimasak',
                'ready'     => '✅ Siap Disajikan',
                'completed' => '✔ Selesai',
                'cancelled' => '✗ Dibatalkan',
                default     => ucfirst($order->status),
            } }}
        </span>
    </div>

    {{-- ── STATUS TRACKER ── --}}
    @if(!$order->isCancelled())
    <div class="card py-4" id="status-tracker">
        @php
            $steps = [
                ['key' => 'pending',   'label' => 'Pesanan Dibuat', 'icon' => '📋'],
                ['key' => 'paid',      'label' => 'Lunas',          'icon' => '💳'],
                ['key' => 'cooking',   'label' => 'Dimasak Dapur',  'icon' => '🔥'],
                ['key' => 'ready',     'label' => 'Siap Disajikan', 'icon' => '✅'],
                ['key' => 'completed', 'label' => 'Selesai',        'icon' => '🎉'],
            ];
            $isPaid = $order->isFullyPaid();
            $currentStep = match($order->status) {
                'pending'   => $isPaid ? 1 : 0,
                'cooking'   => 2,
                'ready'     => 3,
                'completed' => 4,
                default     => 0,
            };
        @endphp
        <div class="flex items-center justify-between px-2" id="tracker-steps"
             data-current="{{ $currentStep }}"
             data-is-paid="{{ $isPaid ? '1' : '0' }}">
            @foreach($steps as $i => $step)
                @php $done = $i <= $currentStep; @endphp
                <div class="flex flex-col items-center flex-1 {{ $i < count($steps)-1 ? 'relative' : '' }}">
                    @if($i < count($steps)-1)
                        <div class="tracker-line-{{ $i }} absolute top-4 left-1/2 w-full h-0.5 {{ $i < $currentStep ? 'bg-indigo-500' : 'bg-gray-200' }}" style="z-index:0"></div>
                    @endif
                    <div class="tracker-circle-{{ $i }} relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-sm
                        {{ $done ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                        {{ $done ? $step['icon'] : ($i+1) }}
                    </div>
                    <span class="tracker-label-{{ $i }} text-xs mt-1 text-center {{ $done ? 'text-indigo-700 font-semibold' : 'text-gray-400' }}">
                        {{ $step['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── AKSI: Selesai & Meja Kosong ── --}}
    <div id="complete-action">
        @if($order->status === 'ready' && $order->table_id)
            <div class="bg-green-50 border border-green-300 rounded-xl px-4 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-green-800">✅ Makanan sudah siap disajikan</p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika tamu sudah selesai makan dan meja kosong</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Konfirmasi meja {{ $order->table->number }} sudah kosong?')"
                                class="btn-success w-full sm:w-auto justify-center">
                            🪑 Meja Kosong &amp; Selesai
                        </button>
                    </form>
                </div>
            </div>
        @elseif($order->status === 'ready' && !$order->table_id)
            <div class="bg-green-50 border border-green-300 rounded-xl px-4 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-green-800">✅ Pesanan siap diambil</p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika pelanggan sudah mengambil pesanan</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="btn-success w-full sm:w-auto justify-center">
                            ✔ Selesai
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Info Pesanan --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Informasi Pesanan</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">No. Pesanan</dt><dd class="font-medium">{{ $order->order_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tipe</dt><dd class="capitalize">{{ str_replace('_',' ',$order->order_type) }}</dd></div>
                @if($order->table)
                    <div class="flex justify-between"><dt class="text-gray-500">Meja</dt><dd>{{ $order->table->number }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Kasir</dt><dd>{{ $order->cashier?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Waktu</dt><dd>{{ $order->created_at->format('d M Y, H:i') }}</dd></div>
                @if($order->notes)
                    <div class="flex justify-between"><dt class="text-gray-500">Catatan</dt><dd>{{ $order->notes }}</dd></div>
                @endif
                @if($order->sent_to_kitchen_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Masuk Dapur</dt>
                        <dd class="text-indigo-600 font-medium">{{ $order->sent_to_kitchen_at->format('H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Pembayaran --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Pembayaran</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Total Tagihan</dt><dd class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Sudah Dibayar</dt><dd class="text-green-600 font-semibold">Rp {{ number_format($order->totalPaid(),0,',','.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Sisa</dt>
                    <dd id="remaining-balance" class="{{ $order->remainingBalance() > 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">
                        Rp {{ number_format($order->remainingBalance(),0,',','.') }}
                    </dd>
                </div>
            </dl>

            @if($order->payments->count() > 0)
                <div class="mt-3 pt-3 border-t border-gray-100 space-y-1">
                    @foreach($order->payments as $p)
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ ucfirst($p->payment_method) }} — {{ $p->created_at->format('H:i') }}</span>
                            <span>Rp {{ number_format($p->amount,0,',','.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div id="payment-actions">
                @if($order->remainingBalance() > 0 && !$order->isCancelled())
                    <a href="{{ route('cashier.payments.create', $order) }}" class="btn-success w-full justify-center mt-4">
                        💳 Proses Pembayaran
                    </a>
                @elseif($order->isFullyPaid() && $order->isCompleted())
                    <a href="{{ route('cashier.receipts.show', $order->payments->last()) }}" class="btn-secondary w-full justify-center mt-4">
                        🧾 Cetak Struk
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-4">Item Pesanan</h2>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-100">
                <th class="py-2 text-left text-gray-500">Produk</th>
                <th class="py-2 text-center text-gray-500">Qty</th>
                <th class="py-2 text-right text-gray-500">Harga</th>
                <th class="py-2 text-right text-gray-500">Subtotal</th>
            </tr></thead>
            <tbody>
            @foreach($order->items as $item)
                <tr class="border-b border-gray-50">
                    <td class="py-2">
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        @if($item->special_notes)<p class="text-xs text-gray-400">{{ $item->special_notes }}</p>@endif
                    </td>
                    <td class="py-2 text-center">{{ $item->quantity }}</td>
                    <td class="py-2 text-right">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                    <td class="py-2 text-right font-semibold">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                </tr>
            @endforeach
            <tr class="border-t-2 border-gray-200">
                <td colspan="3" class="py-2 text-right font-semibold text-gray-700">Total</td>
                <td class="py-2 text-right font-bold text-lg">Rp {{ number_format($order->total_amount,0,',','.') }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    {{-- Aksi Edit/Batal --}}
    @if($order->isPending() && !$order->isFullyPaid())
        <div class="flex gap-3">
            <a href="{{ route('cashier.orders.edit', $order) }}" class="btn-secondary">Edit Pesanan</a>
            <form method="POST" action="{{ route('cashier.orders.cancel', $order) }}"
                  onsubmit="return confirm('Batalkan pesanan ini?')">
                @csrf
                <input type="hidden" name="cancel_reason" value="Dibatalkan oleh kasir">
                <button type="submit" class="btn-danger">Batalkan</button>
            </form>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function() {
    const orderId  = {{ $order->id }};
    const INTERVAL = 8000; // 8 detik
    const POLL_URL = '/api/poll/orders/' + orderId;

    const statusLabel = {
        pending:   '⏳ Menunggu',
        cooking:   '🔥 Dimasak',
        ready:     '✅ Siap Disajikan',
        completed: '✔ Selesai',
        cancelled: '✗ Dibatalkan',
    };
    const badgeClass = {
        pending:   'badge-pending',
        cooking:   'badge-cooking',
        ready:     'badge-ready',
        completed: 'badge-completed',
        cancelled: 'badge-cancelled',
    };
    const stepIcons = ['📋','💳','🔥','✅','🎉'];

    let lastStatus = '{{ $order->status }}';
    let lastIsPaid = {{ $order->isFullyPaid() ? 'true' : 'false' }};

    function getCurrentStep(status, isPaid) {
        if (status === 'completed') return 4;
        if (status === 'ready')     return 3;
        if (status === 'cooking')   return 2;
        if (isPaid)                 return 1;
        return 0;
    }

    function updateTracker(currentStep) {
        for (let i = 0; i <= 4; i++) {
            const circle = document.querySelector('.tracker-circle-' + i);
            const label  = document.querySelector('.tracker-label-' + i);
            const line   = document.querySelector('.tracker-line-' + i);
            const done   = i <= currentStep;

            if (circle) {
                circle.className = circle.className
                    .replace(/bg-indigo-600 text-white|bg-gray-100 text-gray-400/g, '')
                    .trim();
                circle.classList.add(...(done ? ['bg-indigo-600','text-white'] : ['bg-gray-100','text-gray-400']));
                circle.textContent = done ? stepIcons[i] : String(i + 1);
            }
            if (label) {
                label.className = label.className
                    .replace(/text-indigo-700 font-semibold|text-gray-400/g, '')
                    .trim();
                label.classList.add(...(done ? ['text-indigo-700','font-semibold'] : ['text-gray-400']));
            }
            if (line) {
                line.className = line.className
                    .replace(/bg-indigo-500|bg-gray-200/g, '')
                    .trim();
                line.classList.add(i < currentStep ? 'bg-indigo-500' : 'bg-gray-200');
            }
        }
    }

    async function pollOrder() {
        try {
            const res  = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            const changed = data.status !== lastStatus || data.is_paid !== lastIsPaid;
            if (!changed) return;

            lastStatus = data.status;
            lastIsPaid = data.is_paid;

            // Update badge status
            const badge = document.getElementById('order-status-badge');
            if (badge) {
                badge.className = 'badge ' + (badgeClass[data.status] || '') + ' text-sm px-3 py-1';
                badge.textContent = statusLabel[data.status] || data.status;
            }

            // Update tracker
            const step = getCurrentStep(data.status, data.is_paid);
            updateTracker(step);

            // Jika status jadi 'ready', reload untuk tampilkan tombol selesai
            if (data.status === 'ready' && lastStatus !== 'ready') {
                window.location.reload();
            }

            // Jika completed, reload untuk tampilkan tombol cetak struk
            if (data.status === 'completed') {
                window.location.reload();
            }

        } catch (e) {
            // Gagal poll
        }
    }

    setInterval(pollOrder, INTERVAL);
})();
</script>
@endpush