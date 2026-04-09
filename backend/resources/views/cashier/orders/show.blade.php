@extends('layouts.app')
@section('title', 'Detail Pesanan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto" id="order-detail" data-order-id="{{ $order->id }}">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="page-title">Pesanan #{{ $order->order_number }}</h1>
        <span id="order-status-badge"
              class="badge badge-{{ $order->status }} text-sm px-3 py-1 inline-flex items-center gap-1.5">
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
        <div class="flex items-center justify-between px-2">
            @foreach($steps as $i => $step)
                @php $done = $i <= $currentStep; @endphp
                <div class="flex flex-col items-center flex-1 relative">
                    @if($i < count($steps) - 1)
                        <div id="tracker-line-{{ $i }}"
                             class="absolute top-4 left-1/2 w-full h-1 rounded-full {{ $i < $currentStep ? 'bg-indigo-500' : 'bg-gray-200' }}"
                             style="z-index:0;"></div>
                    @endif
                    <div id="tracker-circle-{{ $i }}"
                         class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2
                                {{ $done ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                        {{ $i + 1 }}
                    </div>
                    <span id="tracker-label-{{ $i }}"
                          class="text-xs mt-1 text-center {{ $done ? 'text-indigo-700 font-semibold' : 'text-gray-400' }}">
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Makanan sudah siap disajikan
                        </p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika tamu sudah selesai makan dan meja kosong</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" onclick="return confirm('Konfirmasi meja {{ $order->table->number }} sudah kosong?')"
                                class="btn-success w-full sm:w-auto justify-center inline-flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><path d="M3 16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v1.5a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5V11a2 2 0 0 0-4 0z"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Pesanan siap diambil
                        </p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika pelanggan sudah mengambil pesanan</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="btn-success w-full sm:w-auto justify-center inline-flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
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
                @if($order->customer_name)
                <div class="flex justify-between"><dt class="text-gray-500">Pelanggan</dt><dd class="font-medium text-gray-900">{{ $order->customer_name }}</dd></div>
                @endif
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
                <div class="flex justify-between"><dt class="text-gray-500">Masuk Dapur</dt><dd class="text-indigo-600 font-medium">{{ $order->sent_to_kitchen_at->format('H:i') }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Pembayaran --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Pembayaran</h2>
            <dl class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Subtotal</dt>
                    <dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Pajak ({{ number_format($order->tax_rate, 0) }}%)</dt>
                    <dd>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between pt-1.5 border-t border-gray-100 mt-1.5">
                    <dt class="font-semibold text-gray-700">Total Tagihan</dt>
                    <dd class="font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Sudah Dibayar</dt>
                    <dd class="text-green-600 font-semibold">Rp {{ number_format($order->totalPaid(), 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Sisa</dt>
                    <dd id="remaining-balance"
                        class="{{ $order->remainingBalance() > 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">
                        Rp {{ number_format($order->remainingBalance(), 0, ',', '.') }}
                    </dd>
                </div>
            </dl>

            @if($order->payments->count() > 0)
            <div class="mt-3 pt-3 border-t border-gray-100 space-y-1">
                @foreach($order->payments as $p)
                <div class="flex justify-between text-xs text-gray-500">
                    <span>{{ $p->methodLabel() }} — {{ $p->created_at->format('H:i') }}</span>
                    <span>Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div id="payment-actions">
                @if($order->remainingBalance() > 0 && !$order->isCancelled())
                    <a href="{{ route('cashier.payments.create', $order) }}"
                       class="btn-success w-full justify-center mt-4 inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Proses Pembayaran
                    </a>
                @elseif($order->isFullyPaid())
                    <a href="{{ route('cashier.receipts.print', $order->payments->last()) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="w-full justify-center mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                       style="color:white; background-color:#EF8F00; border:1px solid #EF8F00;"
                       onmouseover="this.style.backgroundColor='#cc7a00';"
                       onmouseout="this.style.backgroundColor='#EF8F00';">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
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
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="py-2 text-left text-gray-500">Produk</th>
                    <th class="py-2 text-center text-gray-500">Qty</th>
                    <th class="py-2 text-right text-gray-500">Harga</th>
                    <th class="py-2 text-right text-gray-500">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr class="border-b border-gray-50">
                    <td class="py-2">
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        @if($item->variant_name)
                            <p class="text-xs text-indigo-500">{{ $item->variant_name }}</p>
                        @endif
                        @if($item->discount_label && $item->discount_amount > 0)
                            <p class="text-xs text-red-500">🏷 {{ $item->discount_label }}</p>
                        @endif
                        @if($item->special_notes)
                            <p class="text-xs text-gray-400">{{ $item->special_notes }}</p>
                        @endif
                    </td>
                    <td class="py-2 text-center">{{ $item->quantity }}</td>
                    <td class="py-2 text-right">
                        @if($item->discount_amount > 0)
                            <span class="text-xs text-gray-400 line-through block">
                                Rp {{ number_format($item->original_price, 0, ',', '.') }}
                            </span>
                        @endif
                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="py-2 text-right font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
                <tr class="border-t border-gray-100">
                    <td colspan="3" class="py-1.5 text-right text-xs text-gray-400">Subtotal</td>
                    <td class="py-1.5 text-right text-xs text-gray-500">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="py-1 text-right text-xs text-gray-400">Pajak ({{ number_format($order->tax_rate, 0) }}%)</td>
                    <td class="py-1 text-right text-xs text-gray-400">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-t-2 border-gray-200">
                    <td colspan="3" class="py-2 text-right font-semibold text-gray-700">Total</td>
                    <td class="py-2 text-right font-bold text-lg">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($order->isPending() && !$order->isFullyPaid())
    <div class="flex gap-3">
        <a href="{{ route('cashier.orders.edit', $order) }}" class="btn-secondary">Edit Pesanan</a>
        <button type="button"
                onclick="document.getElementById('cancel-modal').style.display='flex'; document.body.style.overflow='hidden';"
                class="btn-danger">Batalkan</button>
    </div>
    @endif

    <div class="flex justify-start">
        <a href="{{ route('cashier.orders.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
           style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
           onmouseover="this.style.backgroundColor='#1e3d8f';"
           onmouseout="this.style.backgroundColor='#2D54BF';">
            Kembali
        </a>
    </div>
</div>

{{-- Modal Cancel --}}
<div id="cancel-modal"
     style="display:none; position:fixed; inset:0; z-index:50; background:rgba(0,0,0,0.45); align-items:center; justify-content:center;"
     onclick="if(event.target===this){this.style.display='none'; document.body.style.overflow='';}">
    <div style="background:#fff; border-radius:16px; padding:24px; width:100%; max-width:440px; margin:16px; box-shadow:0 20px 60px rgba(0,0,0,0.2);" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 text-base">Batalkan Pesanan</h3>
            <button type="button" onclick="document.getElementById('cancel-modal').style.display='none'; document.body.style.overflow='';" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Pesanan <strong class="text-gray-900">#{{ $order->order_number }}</strong> akan dibatalkan.</p>
        <form method="POST" action="{{ route('cashier.orders.cancel', $order) }}">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pembatalan <span class="text-red-500">*</span></label>
                <textarea id="cancel-reason-input" name="cancel_reason" rows="3" required maxlength="500"
                          class="form-input" placeholder="Tulis alasan pembatalan..."></textarea>
            </div>
            <div class="flex flex-wrap gap-2 mb-5">
                @foreach(['Pelanggan membatalkan', 'Stok habis', 'Salah input pesanan', 'Dibatalkan oleh kasir'] as $quick)
                <button type="button" onclick="document.getElementById('cancel-reason-input').value='{{ $quick }}'"
                        class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors border border-gray-200">
                    {{ $quick }}
                </button>
                @endforeach
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('cancel-modal').style.display='none'; document.body.style.overflow='';" class="btn-secondary text-sm">Kembali</button>
                <button type="submit" class="btn-danger text-sm inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Ya, Batalkan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const POLL_URL = '/api/poll/orders/{{ $order->id }}';
    const INTERVAL = 8000;
    const allBadgeClasses = ['badge-pending','badge-cooking','badge-ready','badge-completed','badge-cancelled'];
    const statusText = { pending:'Menunggu', cooking:'Dimasak', ready:'Siap Disajikan', completed:'Selesai', cancelled:'Dibatalkan' };

    // FIX: simpan status SEBELUM update, baru bandingkan
    let lastStatus = '{{ $order->status }}';
    let lastIsPaid = {{ $order->isFullyPaid() ? 'true' : 'false' }};

    function getStep(status, isPaid) {
        if (status === 'completed') return 4;
        if (status === 'ready')     return 3;
        if (status === 'cooking')   return 2;
        if (isPaid)                 return 1;
        return 0;
    }

    function updateTracker(step) {
        for (let i = 0; i <= 4; i++) {
            const c  = document.getElementById('tracker-circle-' + i);
            const l  = document.getElementById('tracker-label-' + i);
            const ln = document.getElementById('tracker-line-' + i);
            const done = i <= step;
            if (c)  c.className  = 'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 ' + (done ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-gray-400');
            if (l)  l.className  = 'text-xs mt-1 text-center ' + (done ? 'text-indigo-700 font-semibold' : 'text-gray-400');
            if (ln) { ln.className = 'absolute top-4 left-1/2 w-full h-1 rounded-full ' + (i < step ? 'bg-indigo-500' : 'bg-gray-200'); ln.style.zIndex = '0'; }
        }
    }

    async function poll() {
        try {
            const res  = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            const statusChanged = data.status !== lastStatus;
            const paidChanged   = data.is_paid !== lastIsPaid;

            if (!statusChanged && !paidChanged) return;

            // FIX: simpan nilai lama SEBELUM update, baru lakukan perbandingan untuk reload
            const prevStatus = lastStatus;
            lastStatus = data.status;
            lastIsPaid = data.is_paid;

            // Update badge
            const badge = document.getElementById('order-status-badge');
            if (badge) {
                badge.classList.remove(...allBadgeClasses);
                badge.classList.add('badge-' + data.status);
                badge.textContent = statusText[data.status] || data.status;
            }

            // Update tracker angka progres (tidak butuh reload)
            updateTracker(getStep(data.status, data.is_paid));

            // FIX: reload jika status berubah agar tombol aksi (Meja Kosong, Selesai, dll)
            // ikut terupdate — bandingkan prevStatus dengan data.status yang baru
            if (statusChanged) {
                window.location.reload();
            }

        } catch(e) { /* diam */ }
    }

    setInterval(poll, INTERVAL);
})();
</script>
@endpush