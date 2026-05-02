<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $payment->order->order_number }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; width: 76mm; padding: 4mm 2mm; color: #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .center   { text-align: center; }
        .right    { text-align: right; }
        .bold     { font-weight: bold; }
        .xlarge   { font-size: 15px; }
        .small    { font-size: 10px; }
        .divider  { border: none; border-top: 1px dashed #000; margin: 3mm 0; }
        .row      { display: flex; justify-content: space-between; margin: 1px 0; }
        .row span:first-child { flex: 1; }
        .row span:last-child  { flex-shrink: 0; margin-left: 4px; text-align: right; }
        .item-name   { word-break: break-word; }
        .item-detail { padding-left: 3mm; color: #333; }
        .item-note   { padding-left: 3mm; font-style: italic; color: #555; font-size: 10px; }
        .variant-row { padding-left: 3mm; font-size: 10px; color: #555; }
        .total-row   { font-size: 13px; font-weight: bold; margin: 1.5mm 0; }
        .footer      { margin-top: 3mm; text-align: center; font-size: 10px; }
        .no-print    { display: block; margin: 6px 0; text-align: center; }

        /* Bundle styles */
        .bundle-label   { font-size: 10px; font-weight: bold; }
        .bundle-contents { padding-left: 3mm; margin: 1mm 0 2mm; border-left: 1px solid #999; }
        .bundle-item     { font-size: 10px; color: #444; }
        .bundle-savings  { padding-left: 3mm; font-size: 10px; font-style: italic; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    @php
        $receiptItems = collect();

        foreach ($payment->order->items->whereNull('bundle_id') as $item) {
            $receiptItems->push(['type' => 'product', 'item' => $item]);
        }

        foreach ($payment->order->items->whereNotNull('bundle_id')->groupBy('bundle_id') as $items) {
            $bundle = $items->first()->bundlePackage;
            if (! $bundle) {
                foreach ($items as $item) {
                    $receiptItems->push(['type' => 'product', 'item' => $item]);
                }
                continue;
            }

            $bundleQty = $bundle->items
                ->map(function ($bundleItem) use ($items) {
                    $orderItem = $items->first(fn($item) => (int) $item->product_id === (int) $bundleItem->product_id
                        && (int) ($item->variant_id ?? 0) === (int) ($bundleItem->product_variant_id ?? 0));

                    if (! $orderItem || $bundleItem->quantity <= 0) {
                        return null;
                    }

                    return intdiv((int) $orderItem->quantity, (int) $bundleItem->quantity);
                })
                ->filter(fn($qty) => $qty !== null && $qty > 0)
                ->min() ?? 1;

            $subtotal = (float) $items->sum('subtotal');
            $normalTotal = $bundle->normalPrice() * $bundleQty;

            $receiptItems->push([
                'type' => 'bundle',
                'bundle' => $bundle,
                'quantity' => $bundleQty,
                'unit_price' => $bundleQty > 0 ? $subtotal / $bundleQty : $subtotal,
                'subtotal' => $subtotal,
                'normal_total' => $normalTotal,
                'savings' => max(0, $normalTotal - $subtotal),
                'special_notes' => $items->first()->special_notes,
            ]);
        }

        $printedBundleIds = [];
    @endphp

    <div class="no-print">
        <button onclick="window.print()" style="padding:6px 16px; background:#4f46e5; color:white; border:none; border-radius:6px; font-size:12px; cursor:pointer;">🖨 Cetak Sekarang</button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:6px; font-size:12px; cursor:pointer; margin-left:6px;">Tutup</button>
    </div>

    {{-- Header Toko --}}
    <div class="center bold xlarge">{{ $payment->order->store->name ?? config('app.name', 'DePOS') }}</div>
    @if($payment->order->store->address ?? '')
        <div class="center small">{{ $payment->order->store->address }}</div>
    @endif
    @if($payment->order->store->phone ?? '')
        <div class="center small">Telp: {{ $payment->order->store->phone }}</div>
    @endif

    <hr class="divider">

    {{-- Info Transaksi --}}
    <div class="row"><span>No. Pesanan</span><span>{{ $payment->order->order_number }}</span></div>
    @if($payment->order->customer_name)
        <div class="row"><span>Pelanggan</span><span>{{ $payment->order->customer_name }}</span></div>
    @endif
    <div class="row"><span>Kasir</span><span>{{ $payment->order->cashier?->name ?? '-' }}</span></div>
    <div class="row"><span>Waktu</span><span>{{ $payment->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Tipe</span><span class="bold">{{ $payment->order->order_type === 'dine_in' ? 'Dine-In' : 'Takeaway' }}</span></div>
    @if($payment->order->table)
        <div class="row"><span>Meja</span><span class="bold">{{ $payment->order->table->number }}</span></div>
    @endif

    <hr class="divider">

    {{-- Item Pesanan --}}
    @foreach($payment->order->items as $item)
        @if(!empty($item->bundle_id))
            @if(in_array($item->bundle_id, $printedBundleIds, true))
                @continue
            @endif
            @php
                $printedBundleIds[] = $item->bundle_id;
                $receiptItem = $receiptItems->first(fn($ri) => $ri['type'] === 'bundle' && (int) $ri['bundle']->id === (int) $item->bundle_id);
            @endphp
            {{-- ── BUNDLE ITEM ── --}}
            <div class="item-name bold">[BUNDLE] {{ $receiptItem['bundle']->name ?? $item->bundlePackage?->name ?? $item->product_name }}</div>

            {{-- Isi bundle --}}
            @if($item->bundlePackage)
                <div class="bundle-contents">
                    @foreach($item->bundlePackage->items as $bi)
                        <div class="bundle-item">
                            - {{ $bi->product->name }}
                            @if($bi->variant)({{ $bi->variant->name }})@endif
                            ×{{ $bi->quantity }}
                        </div>
                    @endforeach
                </div>
            @endif

            @if(($receiptItem['savings'] ?? 0) > 0)
                <div class="bundle-savings">
                    Normal: <span style="text-decoration:line-through;">Rp {{ number_format($receiptItem['normal_total'], 0, ',', '.') }}</span>
                </div>
                <div class="bundle-savings">Hemat: Rp {{ number_format($receiptItem['savings'], 0, ',', '.') }}</div>
            @endif

            <div class="row item-detail">
                <span>{{ $receiptItem['quantity'] ?? $item->quantity }} × Rp {{ number_format($receiptItem['unit_price'] ?? $item->unit_price, 0, ',', '.') }}</span>
                <span>Rp {{ number_format($receiptItem['subtotal'] ?? $item->subtotal, 0, ',', '.') }}</span>
            </div>
        @else
            {{-- ── PRODUK BIASA ── --}}
            <div class="item-name bold">{{ $item->product_name }}</div>
            @if($item->variant_name)
                <div class="variant-row">Variasi: {{ $item->variant_name }}</div>
            @endif
            @if($item->discount_label && $item->discount_amount > 0)
                <div class="item-detail" style="font-style:italic; color:#555;">
                    Diskon: {{ $item->discount_label }}
                </div>
                <div class="row item-detail">
                    <span>Harga asli</span>
                    <span style="text-decoration:line-through;">Rp {{ number_format($item->original_price, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="row item-detail">
                <span>{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endif

        @if($item->special_notes)
            <div class="item-note">* {{ $item->special_notes }}</div>
        @endif
    @endforeach

    @if($payment->order->notes)
        <hr class="divider">
        <div class="small"><span class="bold">Catatan:</span> {{ $payment->order->notes }}</div>
    @endif

    <hr class="divider">

    {{-- Breakdown Harga --}}
    <div class="row"><span>Subtotal</span><span>Rp {{ number_format($payment->order->subtotal, 0, ',', '.') }}</span></div>
    <div class="row"><span>Pajak ({{ rtrim(rtrim(number_format($payment->order->tax_rate, 2), '0'), '.') }}%)</span><span>Rp {{ number_format($payment->order->tax_amount, 0, ',', '.') }}</span></div>

    <div class="row total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($payment->order->total_amount, 0, ',', '.') }}</span>
    </div>

    {{-- Pembayaran --}}
    <div class="row">
        <span>{{ $payment->methodLabel() }}</span>
        <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
    </div>
    @if($payment->payment_method === 'cash' && $payment->amount_received)
        <div class="row"><span>Uang Diterima</span><span>Rp {{ number_format($payment->amount_received, 0, ',', '.') }}</span></div>
        <div class="row bold"><span>Kembalian</span><span>Rp {{ number_format(max(0, $payment->amount_received - $payment->amount), 0, ',', '.') }}</span></div>
    @endif

    <hr class="divider">

    <div class="footer">
        <div>Terima kasih atas kunjungan Anda!</div>
        <div style="margin-top:2mm;">Simpan sebagai bukti pembayaran</div>
        <div style="margin-top:2mm; font-size:9px; color:#666;">{{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <script>window.addEventListener('load', function() { window.print(); });</script>
</body>
</html>
