<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $payment->order->order_number }}</title>
    <style>
        /* ── Thermal 80mm paper settings ── */
        @page {
            size: 80mm auto;
            margin: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 76mm;
            padding: 4mm 2mm;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .center   { text-align: center; }
        .right    { text-align: right; }
        .bold     { font-weight: bold; }
        .large    { font-size: 13px; }
        .xlarge   { font-size: 15px; }
        .small    { font-size: 10px; }
        .divider  { border: none; border-top: 1px dashed #000; margin: 3mm 0; }
        .row      { display: flex; justify-content: space-between; margin: 1px 0; }
        .row span:first-child { flex: 1; }
        .row span:last-child  { flex-shrink: 0; margin-left: 4px; text-align: right; }
        .item-name   { word-break: break-word; }
        .item-detail { padding-left: 3mm; color: #333; }
        .item-note   { padding-left: 3mm; font-style: italic; color: #555; font-size: 10px; }
        .total-row   { font-size: 13px; font-weight: bold; margin: 1.5mm 0; }
        .footer      { margin-top: 3mm; text-align: center; font-size: 10px; }
        .no-print    { display: block; margin: 6px 0; text-align: center; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    {{-- ── TOMBOL CETAK (tidak muncul saat print) ── --}}
    <div class="no-print">
        <button onclick="window.print()"
                style="padding:6px 16px; background:#4f46e5; color:white; border:none; border-radius:6px; font-size:12px; cursor:pointer;">
            🖨 Cetak Sekarang
        </button>
        <button onclick="window.close()"
                style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:6px; font-size:12px; cursor:pointer; margin-left:6px;">
            Tutup
        </button>
    </div>

    {{-- ── HEADER TOKO ── --}}
    <div class="center bold xlarge">{{ $payment->order->store->name ?? config('app.name', 'DePOS') }}</div>
    @if($payment->order->store->address ?? '')
        <div class="center small">{{ $payment->order->store->address }}</div>
    @endif
    @if($payment->order->store->phone ?? '')
        <div class="center small">Telp: {{ $payment->order->store->phone }}</div>
    @endif

    <hr class="divider">

    {{-- ── INFO TRANSAKSI ── --}}
    <div class="row"><span>No. Pesanan</span><span>{{ $payment->order->order_number }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $payment->order->cashier?->name ?? '-' }}</span></div>
    <div class="row"><span>Waktu</span><span>{{ $payment->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Tipe</span><span class="bold">{{ $payment->order->order_type === 'dine_in' ? 'Dine-In' : 'Takeaway' }}</span></div>
    @if($payment->order->table)
        <div class="row"><span>Meja</span><span class="bold">{{ $payment->order->table->number }}</span></div>
    @endif

    <hr class="divider">

    {{-- ── ITEM PESANAN ── --}}
    @foreach($payment->order->items as $item)
        <div class="item-name bold">{{ $item->product_name }}</div>
        <div class="row item-detail">
            <span>{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($item->special_notes)
            <div class="item-note">* {{ $item->special_notes }}</div>
        @endif
    @endforeach

    @if($payment->order->notes)
        <hr class="divider">
        <div class="small"><span class="bold">Catatan:</span> {{ $payment->order->notes }}</div>
    @endif

    <hr class="divider">

    {{-- ── TOTAL ── --}}
    <div class="row total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($payment->order->total_amount, 0, ',', '.') }}</span>
    </div>
    <div class="row">
        <span>{{ ucfirst($payment->payment_method) }}</span>
        <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
    </div>
    @if($payment->payment_method === 'cash' && $payment->amount_received)
        <div class="row">
            <span>Uang Diterima</span>
            <span>Rp {{ number_format($payment->amount_received, 0, ',', '.') }}</span>
        </div>
        <div class="row bold">
            <span>Kembalian</span>
            <span>Rp {{ number_format(max(0, $payment->amount_received - $payment->amount), 0, ',', '.') }}</span>
        </div>
    @endif

    <hr class="divider">

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <div>Terima kasih atas kunjungan Anda!</div>
        <div style="margin-top:2mm;">Simpan sebagai bukti pembayaran</div>
        <div style="margin-top:2mm; font-size:9px; color:#666;">
            {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>