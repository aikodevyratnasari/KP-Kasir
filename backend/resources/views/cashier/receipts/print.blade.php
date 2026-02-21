<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #{{ $payment->order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: monospace; font-size: 11px; width: 280px; padding: 8px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        .large { font-size: 14px; }
        @media print { body { margin: 0; } button { display: none; } }
    </style>
</head>
<body>
    <div class="center bold large">{{ $payment->order->store->name ?? 'DePOS' }}</div>
    <div class="center">{{ $payment->order->store->address ?? '' }}</div>
    <div class="center">{{ $payment->order->store->phone ?? '' }}</div>

    <div class="divider"></div>

    <div class="row"><span>No. Pesanan</span><span>{{ $payment->order->order_number }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $payment->cashier?->name ?? '-' }}</span></div>
    <div class="row"><span>Waktu</span><span>{{ $payment->created_at->format('d/m/Y H:i') }}</span></div>
    @if($payment->order->table)
        <div class="row"><span>Meja</span><span>{{ $payment->order->table->number }}</span></div>
    @endif

    <div class="divider"></div>

    @foreach($payment->order->items as $item)
        <div>{{ $item->product_name }}</div>
        <div class="row" style="padding-left:10px">
            <span>{{ $item->quantity }} x Rp {{ number_format($item->unit_price,0,',','.') }}</span>
            <span>Rp {{ number_format($item->subtotal,0,',','.') }}</span>
        </div>
    @endforeach

    <div class="divider"></div>

    <div class="row bold large"><span>TOTAL</span><span>Rp {{ number_format($payment->order->total_amount,0,',','.') }}</span></div>
    <div class="row"><span>{{ ucfirst($payment->payment_method) }}</span><span>Rp {{ number_format($payment->amount,0,',','.') }}</span></div>
    @if($payment->payment_method === 'cash' && $payment->amount_received)
        <div class="row"><span>Diterima</span><span>Rp {{ number_format($payment->amount_received,0,',','.') }}</span></div>
        <div class="row bold"><span>Kembalian</span><span>Rp {{ number_format(max(0,$payment->amount_received-$payment->amount),0,',','.') }}</span></div>
    @endif

    <div class="divider"></div>
    <div class="center">Terima kasih atas kunjungan Anda!</div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>