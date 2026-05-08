@extends('layouts.app')
@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto" id="order-detail" data-order-id="{{ $order->id }}">
    @php $hasKitchen = $order->store?->has_kitchen ?? true; @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between px-2" style="min-width: 400px;">
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
    <div class="card py-4 overflow-x-auto" id="status-tracker">
        @php
            $steps = [
                ['key' => 'pending',   'label' => 'Dibuat'],
                ['key' => 'paid',      'label' => 'Lunas'],
               ['key' => 'cooking', 'label' => $hasKitchen ? 'Dimasak' : 'Dimasak'],
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
    class="absolute top-4 left-1/2 w-full h-1 rounded-full"
    style="z-index:0; background-color:{{ $i < $currentStep ? '#2D54BF' : '#e5e7eb' }}; right:0; width:100%;"></div>
                                    @endif
                                    <div id="tracker-circle-{{ $i }}"
                    class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2"
                    style="{{ $done ? 'background-color:#2D54BF; border-color:#2D54BF; color:white;' : 'background-color:white; border-color:#d1d5db; color:#9ca3af;' }}">
                    {{ $i + 1 }}
                </div>
                <span id="tracker-label-{{ $i }}"
    class="text-xs mt-1 text-center whitespace-nowrap"
    style="{{ $done ? 'color:#2D54BF; font-weight:600;' : 'color:#9ca3af;' }}">
    {{ $step['label'] }}
</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── AKSI: Selesai & Meja Kosong ── --}}
    {{-- Aksi Produksi oleh Kasir saat tidak memakai user kitchen --}}
    @if(!$hasKitchen && !$order->isCancelled() && !$order->isCompleted() && $order->isFullyPaid())
        @if($order->status === 'pending')
            <div class="bg-blue-50 border border-blue-300 rounded-xl px-4 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-blue-800 flex items-center gap-1.5">
                            Pesanan sudah lunas
                        </p>
                        <p class="text-sm text-blue-600 mt-0.5">Kasir dapat menandai pesanan mulai dimasak tanpa display dapur</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.status', $order) }}" class="flex-shrink-0">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="cooking">
                        <button type="submit" class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                                style="background-color:#2D54BF; border:1px solid #2D54BF;"
                                onmouseover="this.style.backgroundColor='#1e3d8f'"
                                onmouseout="this.style.backgroundColor='#2D54BF'">
                            Mulai Dimasak
                        </button>
                    </form>
                </div>
            </div>
        @elseif($order->status === 'cooking')
            <div class="bg-orange-50 border border-orange-300 rounded-xl px-4 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-orange-800 flex items-center gap-1.5">
                            Pesanan sedang dimasak
                        </p>
                        <p class="text-sm text-orange-600 mt-0.5">Tandai siap jika pesanan sudah dapat disajikan atau diambil</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.status', $order) }}" class="flex-shrink-0">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit"
        class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold rounded-lg transition-colors"
        style="background-color:#ea580c; color:white; border:1px solid #ea580c;"
        onmouseover="this.style.backgroundColor='#c2410c';"
        onmouseout="this.style.backgroundColor='#ea580c';">
    Tandai Siap
</button>
                    </form>
                </div>
            </div>
        @endif
    @endif

    <div id="complete-action">
        @if($order->status === 'ready' && $order->table_id)
            <div class="bg-green-50 border border-green-300 rounded-xl px-4 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-green-800 flex items-center gap-1.5">
                            Makanan sudah siap disajikan
                        </p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika tamu sudah selesai makan dan meja kosong</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" onclick="return confirm('Konfirmasi meja {{ $order->table->number }} sudah kosong?')"
                                class="btn-success w-full sm:w-auto justify-center inline-flex items-center gap-1.5">
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
                            Pesanan siap diambil
                        </p>
                        <p class="text-sm text-green-600 mt-0.5">Konfirmasi ketika pelanggan sudah mengambil pesanan</p>
                    </div>
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="btn-success w-full sm:w-auto justify-center inline-flex items-center gap-1.5">
                            Selesai
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

        {{-- Info Pesanan --}}
        <div class="card h-full">
            <h2 class="font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-3">Informasi Pesanan</h2>
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
                <div class="flex justify-between items-start gap-4">
                    <dt class="text-gray-500 flex-shrink-0">Catatan</dt>
                    <dd class="text-right break-words leading-relaxed text-justify" style="max-width:65%;">{{ $order->notes }}</dd>
                </div>
                @endif
                @if($hasKitchen && $order->sent_to_kitchen_at)
                <div class="flex justify-between"><dt class="text-gray-500">Masuk Dapur</dt><dd class="text-indigo-600 font-medium">{{ $order->sent_to_kitchen_at->format('H:i') }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Pembayaran --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-3">Pembayaran</h2>
            <dl class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Subtotal</dt>
                    <dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Pajak ({{ rtrim(rtrim(number_format($order->tax_rate, 2), '0'), '.') }}%)</dt>
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

            @php
            $hasPaidPayment = $order->payments->whereIn('status', ['paid', 'refunded'])->isNotEmpty();
                $visiblePayments = $order->payments->filter(function ($p) use ($hasPaidPayment, $order) {
                    if ($order->isCancelled()) {
                        return true; // tampilkan semua payment di pesanan yang dibatalkan
                    }
                    // Sembunyikan 'pending' jika sudah ada yang paid (sudah dihandle sebelumnya)
                    if ($hasPaidPayment && $p->status === 'pending') {
                        return false;
                    }
                    return true;
                })->sortBy('created_at');
            @endphp

            @if($visiblePayments->count() > 0)
            <div class="mt-3 pt-3 border-t border-gray-100 space-y-1" id="payment-list">
                @foreach($visiblePayments as $p)
                @php
                    $rowStyle = match($p->status) {
                        'paid'      => 'background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:6px 10px;',
                        'refunded'  => 'background:#fff7ed; border:1px solid #fdba74; border-radius:8px; padding:6px 10px;',
                        'cancelled' => 'background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:6px 10px; opacity:0.75;',
                        'pending'   => 'background:#fefce8; border:1px solid #fde047; border-radius:8px; padding:6px 10px;',
                        default     => 'background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:6px 10px;',
                    };
                    $methodColor = match($p->status) {
                        'paid'      => 'color:#15803d; font-weight:600;',
                        'refunded'  => 'color:#9a3412;',
                        'cancelled' => 'color:#dc2626; text-decoration:line-through;',
                        'pending'   => 'color:#854d0e;',
                        default     => 'color:#374151;',
                    };
                    $amountColor = match($p->status) {
                        'paid'      => 'color:#15803d; font-weight:600;',
                        'refunded'  => 'color:#9a3412;',
                        'cancelled' => 'color:#dc2626; text-decoration:line-through;',
                        'pending'   => 'color:#854d0e;',
                        default     => 'color:#374151;',
                    };
                @endphp
                <div class="flex justify-between items-center gap-2 text-xs" id="payment-row-{{ $p->id }}"
                    style="{{ $rowStyle }}">
                    <span class="flex items-center gap-1.5 flex-1 min-w-0" style="{{ $methodColor }}">
                        {{-- Ikon status --}}
                        @if($p->status === 'paid')
                        @elseif($p->status === 'cancelled')
                        @elseif($p->status === 'refunded')
                        @elseif($p->status === 'pending')
                            
                        @endif
                        <span class="truncate">{{ $p->methodLabel() }} — {{ $p->created_at->format('H:i') }}</span>
                        @if($p->status === 'pending')
                            <span style="flex-shrink:0; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:600; background:#fef08a; color:#713f12; border:1px solid #fde047;">Menunggu</span>
                        @elseif($p->status === 'cancelled')
                            <span style="flex-shrink:0; font-size:10px; color:#dc2626;">Dibatalkan</span>
                        @elseif($p->status === 'refunded')
                            <span style="flex-shrink:0; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:600; background:#ffedd5; color:#9a3412; border:1px solid #fdba74;">Refund</span>
                        @endif
                    </span>
                    <span class="flex items-center gap-2 flex-shrink-0" style="{{ $amountColor }}">
                        <span>Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                        @if($p->status === 'pending')
                            <button type="button"
                                    onclick="cancelPendingPayment({{ $p->id }})"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md transition-colors flex-shrink-0"
                                    style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; text-decoration:none;"
                                    onmouseover="this.style.background='#fecaca';"
                                    onmouseout="this.style.background='#fee2e2';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                                Batalkan
                            </button>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            @php $lastPayment = $order->payments->where('status', 'paid')->last(); @endphp

            <div id="payment-actions">
                @if($order->remainingBalance() > 0 && !$order->isCancelled())
                    <a href="{{ route('cashier.payments.create', $order) }}"
                       class="w-full justify-center mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
       style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
       onmouseover="this.style.backgroundColor='#1e3d8f';"
       onmouseout="this.style.backgroundColor='#2D54BF';">
                        Proses Pembayaran
                    </a>

               @elseif($order->isFullyPaid() && $lastPayment)
    <a href="{{ route('cashier.receipts.print', $lastPayment) }}"
       target="_blank" rel="noopener noreferrer"
       class="w-full justify-center mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
       style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
       onmouseover="this.style.backgroundColor='#1e3d8f';"
       onmouseout="this.style.backgroundColor='#2D54BF';">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak Struk
    </a>
    <button type="button"
            onclick="document.getElementById('send-receipt-modal').style.display='flex'; document.body.style.overflow='hidden';"
            class="w-full justify-center mt-2 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
            style="color:#2D54BF; background-color:#eef2ff; border:1px solid #2D54BF;"
            onmouseover="this.style.backgroundColor='#dce8ff';"
            onmouseout="this.style.backgroundColor='#eef2ff';">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
        Kirim Struk
    </button>
@endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-3">Item Pesanan</h2>

        @php
            $receiptItems = collect();

            foreach ($order->items->whereNull('bundle_id') as $item) {
                $receiptItems->push(['type' => 'product', 'item' => $item]);
            }

            foreach ($order->items->whereNotNull('bundle_id')->groupBy('bundle_id') as $bundleId => $bundleGroupItems) {
                $bundle = $bundleGroupItems->first()->bundlePackage;
                if (! $bundle) {
                    foreach ($bundleGroupItems as $item) {
                        $receiptItems->push(['type' => 'product', 'item' => $item]);
                    }
                    continue;
                }

                $bundleQty = $bundle->items
                    ->map(function ($bundleItem) use ($bundleGroupItems) {
                        $orderItem = $bundleGroupItems->first(fn($item) => (int) $item->product_id === (int) $bundleItem->product_id
                            && (int) ($item->variant_id ?? 0) === (int) ($bundleItem->product_variant_id ?? 0));
                        if (! $orderItem || $bundleItem->quantity <= 0) return null;
                        return intdiv((int) $orderItem->quantity, (int) $bundleItem->quantity);
                    })
                    ->filter(fn($qty) => $qty !== null && $qty > 0)
                    ->min() ?? 1;

                $subtotal    = (float) $bundleGroupItems->sum('subtotal');
                $normalTotal = $bundle->normalPrice() * $bundleQty;

                $receiptItems->push([
                    'type'          => 'bundle',
                    'bundle'        => $bundle,
                    'quantity'      => $bundleQty,
                    'unit_price'    => $bundleQty > 0 ? $subtotal / $bundleQty : $subtotal,
                    'subtotal'      => $subtotal,
                    'normal_total'  => $normalTotal,
                    'savings'       => max(0, $normalTotal - $subtotal),
                    'special_notes' => $bundleGroupItems->first()->special_notes,
                ]);
            }
        @endphp

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-500 pb-3">
                    <th class="py-2 text-left text-gray-500">Produk</th>
                    <th class="py-2 text-center text-gray-500">Qty</th>
                    <th class="py-2 text-right text-gray-500">Harga</th>
                    <th class="py-2 text-right text-gray-500">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($receiptItems as $ri)
                @if($ri['type'] === 'bundle')
                    @php $bundle = $ri['bundle']; @endphp
                    <tr class="border-b border-gray-50">
                        <td class="py-2 align-top">
                            <p class="font-medium text-gray-900">
                                {{ $bundle->name }}
                                <span class="ml-1 inline-block px-1.5 py-0.5 rounded text-xs font-semibold align-middle"
                                      style="background:#f0fdf4; border:1px solid #86efac; color:#16a34a;">Bundle</span>
                            </p>
                            <div class="mt-1 pl-2 border-l-2 border-green-200">
                                @foreach($bundle->items as $bi)
                                    <p class="text-xs text-gray-500">
                                        • {{ $bi->product->name }}
                                        @if($bi->variant)
                                            <span class="text-indigo-400">({{ $bi->variant->name }})</span>
                                        @endif
                                        ×{{ $bi->quantity }}
                                    </p>
                                @endforeach
    
                            </div>
                            @if($ri['special_notes'])
                                <p class="text-xs text-gray-400 mt-1 italic">* {{ $ri['special_notes'] }}</p>
                            @endif
                        </td>
                        <td class="py-2 text-center align-top">{{ $ri['quantity'] }}</td>
                        <td class="py-2 text-right align-top">
                            @if($ri['savings'] > 0)
                                <span class="text-xs text-gray-400 line-through block">
                                    Rp {{ number_format($ri['quantity'] > 0 ? $ri['normal_total'] / $ri['quantity'] : $ri['normal_total'], 0, ',', '.') }}
                                </span>
                            @endif
                            Rp {{ number_format($ri['unit_price'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 text-right font-semibold align-top">
                            Rp {{ number_format($ri['subtotal'], 0, ',', '.') }}
                        </td>
                    </tr>
                @else
                    @php $item = $ri['item']; @endphp
                    <tr class="border-b border-gray-50">
                        <td class="py-2">
                            <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                            @if($item->variant_name)
                                <p class="text-xs text-indigo-500">{{ $item->variant_name }}</p>
                            @endif
                            @if($item->discount_label && $item->discount_amount > 0)
                                <p class="text-xs text-red-500">{{ $item->discount_label }}</p>
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
                @endif
            @endforeach
                <tr class="border-t border-gray-100">
                    <td colspan="3" class="py-1.5 text-right text-xs text-gray-400">Subtotal</td>
                    <td class="py-1.5 text-right text-xs text-gray-500">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="py-1 text-right text-xs text-gray-400">Pajak ({{ rtrim(rtrim(number_format($order->tax_rate, 2), '0'), '.') }}%)</td>
                    <td class="py-1 text-right text-xs text-gray-400">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-t-2 border-gray-200">
                    <td colspan="3" class="py-2 text-right font-semibold text-gray-700">Total</td>
                    <td class="py-2 text-right font-bold text-lg">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $kitchenOrder   = $order->kitchenOrder;
            $kitchenCooking = $kitchenOrder && in_array($kitchenOrder->status, ['cooking', 'ready']);
            $canCancel      = ! $order->isCompleted() && ! $order->isCancelled() && ! $kitchenCooking;
            $canEdit        = $order->isPending() && ! $order->isFullyPaid();
        @endphp

        @if($hasKitchen && $kitchenOrder && ! $order->isCancelled() && ! $order->isCompleted())
            @php
                $kitchenStatusConfig = [
                    'waiting_payment' => ['label' => 'Menunggu Pembayaran', 'bg' => '#fefce8', 'border' => '#fde047', 'text' => '#854d0e'],
                    'queued'          => ['label' => 'Antrian Dapur',        'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8'],
                    'cooking'         => ['label' => 'Sedang Dimasak ⚠',    'bg' => '#fff7ed', 'border' => '#fb923c', 'text' => '#9a3412'],
                    'ready'           => ['label' => 'Siap Disajikan',       'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534'],
                ];
                $ksCfg = $kitchenStatusConfig[$kitchenOrder->status] ?? ['label' => ucfirst($kitchenOrder->status), 'bg' => '#f9fafb', 'border' => '#e5e7eb', 'text' => '#374151'];
            @endphp
            <div class="mt-3 flex items-center gap-2 text-xs">
    <span class="text-gray-500">Status Dapur:</span>
    <strong style="color:#dc2626;">{{ $ksCfg['label'] }}</strong>
    @if($kitchenCooking)
        <span style="color:#dc2626;">Pembatalan tidak tersedia</span>
    @endif
</div>
        @endif

        @if($canEdit || $canCancel)
        <div class="flex gap-3 mt-3 {{ $canEdit ? 'justify-between' : 'justify-end' }}">
            @if($canEdit)
            <a href="{{ route('cashier.orders.edit', $order) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
               style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
               onmouseover="this.style.backgroundColor='#1e3d8f';"
               onmouseout="this.style.backgroundColor='#2D54BF';">
                Edit Pesanan
            </a>
            @endif
            @if($canCancel)
            <button type="button"
                    onclick="document.getElementById('cancel-modal').style.display='flex'; document.body.style.overflow='hidden';"
                    class="btn-danger">
                Batalkan
            </button>
            @endif
        </div>
        @endif
    </div>

    <div class="flex justify-start">
        <a href="{{ route('cashier.orders.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
           style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
           onmouseover="this.style.backgroundColor='#f3f4f6';"
           onmouseout="this.style.backgroundColor='white';">
           Kembali
        </a>
    </div>
</div>

{{-- Modal Kirim Struk --}}
<div id="send-receipt-modal"
     style="display:none; position:fixed; inset:0; z-index:60; background:rgba(0,0,0,0.45); align-items:center; justify-content:center;"
     onclick="if(event.target===this){this.style.display='none'; document.body.style.overflow='';}">
    <div style="background:#fff; border-radius:16px; padding:24px; width:100%; max-width:420px; margin:16px; box-shadow:0 20px 60px rgba(0,0,0,0.2);"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-gray-900 text-base">Kirim Struk</h3>
            <button type="button"
                    onclick="document.getElementById('send-receipt-modal').style.display='none'; document.body.style.overflow='';"
                    class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email</label>
            <form method="POST"
                  action="{{ $lastPayment ? route('cashier.receipts.send-email', $lastPayment) : '#' }}"
                  id="send-email-form"
                  onsubmit="handleSendEmail(event)">
                @csrf
                <div class="flex gap-2">
                    <input type="email" name="email" id="receipt-email-input"
                           value="{{ $order->customer_email ?? '' }}"
                           placeholder="email@contoh.com" required
                           class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200">
                    <button type="submit" id="send-email-btn"
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg flex-shrink-0 transition-colors"
                            style="background-color:#6366f1;"
                            onmouseover="this.style.backgroundColor='#4f46e5';"
                            onmouseout="this.style.backgroundColor='#6366f1';">
                        Kirim
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">Struk akan dikirim dalam format HTML ke email yang dituju.</p>
            </form>
            <div id="email-feedback" style="display:none;" class="mt-3 text-sm rounded-lg px-3 py-2"></div>
        </div>
    </div>
</div>

{{-- Modal Cancel --}}
<div id="cancel-modal"
     style="display:none; position:fixed; inset:0; z-index:50; background:rgba(0,0,0,0.45); align-items:center; justify-content:center;"
     onclick="if(event.target===this){this.style.display='none'; document.body.style.overflow='';}">
    <div style="background:#fff; border-radius:16px; padding:24px; width:100%; max-width:440px; margin:16px; box-shadow:0 20px 60px rgba(0,0,0,0.2);" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4 border-b border-gray-200 pb-3">
            <h3 class="font-bold text-gray-900 text-base">Batalkan Pesanan</h3>
            <button type="button" onclick="document.getElementById('cancel-modal').style.display='none'; document.body.style.overflow='';" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-500">
                Pesanan <strong class="text-gray-900">#{{ $order->order_number }}</strong> akan dibatalkan.
            </p>
            @if($order->isFullyPaid())
            <div class="mt-2 flex items-start gap-2 px-3 py-2 rounded-lg text-xs"
                 style="background:#fff7ed; border:1px solid #fdba74; color:#9a3412;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Pesanan ini sudah <strong>lunas</strong>. Pembayaran sebesar <strong>Rp {{ number_format($order->totalPaid(), 0, ',', '.') }}</strong> akan otomatis direfund saat pesanan dibatalkan.</span>
            </div>
            @endif
        </div>
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
                <button type="button"
                        onclick="document.getElementById('cancel-modal').style.display='none'; document.body.style.overflow='';"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
           style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
           onmouseover="this.style.backgroundColor='#f3f4f6';"
           onmouseout="this.style.backgroundColor='white';">
                    Tidak
                </button>
                <button type="submit" class="btn-danger text-sm inline-flex items-center gap-1.5">
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
        if (c)  { c.className = 'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2'; c.style.cssText = done ? 'background-color:#2D54BF; border-color:#2D54BF; color:white;' : 'background-color:white; border-color:#d1d5db; color:#9ca3af;'; }
        if (l)  { l.className = 'text-xs mt-1 text-center'; l.style.cssText = done ? 'color:#2D54BF; font-weight:600;' : 'color:#9ca3af;'; }
        if (ln) { ln.className = 'absolute top-4 left-1/2 w-full h-1 rounded-full'; ln.style.backgroundColor = i < step ? '#2D54BF' : '#e5e7eb'; ln.style.zIndex = '0'; }
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

            lastStatus = data.status;
            lastIsPaid = data.is_paid;

            const badge = document.getElementById('order-status-badge');
            if (badge) {
                badge.classList.remove(...allBadgeClasses);
                badge.classList.add('badge-' + data.status);
                badge.textContent = statusText[data.status] || data.status;
            }

            updateTracker(getStep(data.status, data.is_paid));

            if (statusChanged) {
                window.location.reload();
            }
        } catch(e) { /* diam */ }
    }

    setInterval(poll, INTERVAL);
})();

async function cancelPendingPayment(paymentId) {
    if (!confirm('Batalkan pembayaran ini?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    try {
        const res = await fetch(`/cashier/payments/${paymentId}/cancel-pending`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':      csrfToken,
                'Accept':            'application/json',
                'X-Requested-With':  'XMLHttpRequest',
            },
        });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('payment-row-' + paymentId);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity    = '0';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            alert(data.message || 'Gagal membatalkan pembayaran.');
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

function handleSendEmail(e) {
    e.preventDefault();
    const btn      = document.getElementById('send-email-btn');
    const feedback = document.getElementById('email-feedback');
    const email    = document.getElementById('receipt-email-input').value;
    const form     = document.getElementById('send-email-form');

    btn.disabled    = true;
    btn.textContent = 'Mengirim...';
    btn.style.backgroundColor = '#a5b4fc';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ email })
    })
    .then(res => res.json())
    .then(data => {
        feedback.style.display = 'block';
        if (data.success) {
            feedback.className   = 'mt-3 text-sm rounded-lg px-3 py-2 bg-green-50 text-green-700 border border-green-200';
            feedback.textContent = 'Struk berhasil dikirim ke ' + email;
            btn.textContent      = 'Terkirim';
            btn.style.backgroundColor = '#22c55e';
        } else {
            feedback.className   = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
            feedback.textContent = data.message || 'Gagal mengirim email.';
            btn.disabled         = false;
            btn.textContent      = 'Kirim';
            btn.style.backgroundColor = '#6366f1';
        }
    })
    .catch(() => {
        feedback.style.display = 'block';
        feedback.className     = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
        feedback.textContent   = 'Gagal mengirim. Periksa koneksi.';
        btn.disabled           = false;
        btn.textContent        = 'Kirim';
        btn.style.backgroundColor = '#6366f1';
    });
}
</script>
@endpush