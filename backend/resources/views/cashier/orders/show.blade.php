@extends('layouts.app')
@section('title', 'Detail Pesanan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto" id="order-detail" data-order-id="{{ $order->id }}">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="page-title">Pesanan #{{ $order->order_number }}</h1>
        </div>
        <span id="order-status-badge" class="badge badge-{{ $order->status }} text-sm px-3 py-1 inline-flex items-center gap-1.5">
            @php
                // SVG dirender inline agar JS bisa mengganti via innerHTML
            @endphp
            {{ match($order->status) {
                'pending'   => 'Menunggu',
                'cooking'   => 'Dimasak',
                'ready'     => 'Siap Disajikan',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default     => ucfirst($order->status),
            } }}
        </span>
    </div>

    {{-- ── STATUS TRACKER ── --}}
    @if(!$order->isCancelled())
    <div class="card py-4" id="status-tracker">
        @php
            $steps = [
                ['key' => 'pending',   'label' => 'Pesanan Dibuat'],
                ['key' => 'paid',      'label' => 'Lunas'],
                ['key' => 'cooking',   'label' => 'Dimasak Dapur'],
                ['key' => 'ready',     'label' => 'Siap Disajikan'],
                ['key' => 'completed', 'label' => 'Selesai'],
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
                @php $done = $i <= $currentStep; $active = $i === $currentStep; @endphp
                <div class="flex flex-col items-center flex-1 {{ $i < count($steps)-1 ? 'relative' : '' }}">
                    @if($i < count($steps)-1)
                        <div class="tracker-line-{{ $i }} absolute top-4 left-1/2 w-full h-1 rounded-full {{ $i < $currentStep ? 'bg-indigo-500' : 'bg-gray-200' }}" style="z-index:0"></div>
                    @endif
                    <div class="tracker-circle-{{ $i }} relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2
                        {{ $done ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-400 border-gray-300' }}
                        {{ $active ? 'ring-4 ring-indigo-100' : '' }}">
                        {{ $i + 1 }}
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
                        <p class="font-semibold text-green-800 flex items-center gap-1.5">
                            {{-- check-circle --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Makanan sudah siap disajikan
                        </p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika tamu sudah selesai makan dan meja kosong</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Konfirmasi meja {{ $order->table->number }} sudah kosong?')"
                                class="btn-success w-full sm:w-auto justify-center inline-flex items-center gap-1.5">
                            {{-- armchair / chair --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><path d="M3 16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v1.5a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5V11a2 2 0 0 0-4 0z"/><path d="M5 18v2"/><path d="M19 18v2"/>
                            </svg>
                            Meja Kosong &amp; Selesai
                        </button>
                    </form>
                </div>
            </div>
        @elseif($order->status === 'ready' && !$order->table_id)
            <div class="bg-green-50 border border-green-300 rounded-xl px-4 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-green-800 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Pesanan siap diambil
                        </p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika pelanggan sudah mengambil pesanan</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="btn-success w-full sm:w-auto justify-center inline-flex items-center gap-1.5">
                            {{-- check --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Selesai
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
                    <a href="{{ route('cashier.payments.create', $order) }}" class="btn-success w-full justify-center mt-4 inline-flex items-center gap-1.5">
                        {{-- credit-card --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        Proses Pembayaran
                    </a>
                @elseif($order->isFullyPaid() && $order->isCompleted())
                    <a href="{{ route('cashier.receipts.show', $order->payments->last()) }}"
                       class="w-full justify-center mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                       style="color: white; background-color: #EF8F00; border: 1px solid #EF8F00;"
                       onmouseover="this.style.backgroundColor='#cc7a00'; this.style.borderColor='#cc7a00';"
                       onmouseout="this.style.backgroundColor='#EF8F00'; this.style.borderColor='#EF8F00';"
                       onmousedown="this.style.backgroundColor='#a86400'; this.style.transform='scale(0.98)';"
                       onmouseup="this.style.backgroundColor='#cc7a00'; this.style.transform='scale(1)';">
                        {{-- printer --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Cetak Struk
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

    {{-- Tombol Kembali --}}
    <div class="flex justify-start">
        <a href="{{ route('cashier.orders.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
           style="color: white; background-color: #2D54BF; border: 1px solid #2D54BF;"
           onmouseover="this.style.backgroundColor='#1e3d8f'; this.style.borderColor='#1e3d8f';"
           onmouseout="this.style.backgroundColor='#2D54BF'; this.style.borderColor='#2D54BF';"
           onmousedown="this.style.backgroundColor='#181375'; this.style.transform='scale(0.98)';"
           onmouseup="this.style.backgroundColor='#1e3d8f'; this.style.transform='scale(1)';">
            {{-- arrow-left --}}
            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg> -->
            Kembali
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function() {
    const orderId  = {{ $order->id }};
    const INTERVAL = 8000;
    const POLL_URL = '/api/poll/orders/' + orderId;

    // SVG icons sebagai string (dipakai JS untuk update badge)
    const iconSVG = {
        pending: `<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>`,
        cooking: `<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>`,
        ready:   `<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        completed:`<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
        cancelled:`<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    };

    const statusText = {
        pending:   'Menunggu',
        cooking:   'Dimasak',
        ready:     'Siap Disajikan',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
    };
    const badgeClass = {
        pending:   'badge-pending',
        cooking:   'badge-cooking',
        ready:     'badge-ready',
        completed: 'badge-completed',
        cancelled: 'badge-cancelled',
    };

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
                circle.textContent = String(i + 1);
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
                badge.className = 'badge inline-flex items-center gap-1.5 ' + (badgeClass[data.status] || '') + ' text-sm px-3 py-1';
                badge.innerHTML = (iconSVG[data.status] || '') + (statusText[data.status] || data.status);
            }

            // Update tracker
            const step = getCurrentStep(data.status, data.is_paid);
            updateTracker(step);

            if (data.status === 'ready' && lastStatus !== 'ready') {
                window.location.reload();
            }
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