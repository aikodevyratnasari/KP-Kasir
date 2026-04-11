<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 520px; margin: 0 auto; padding: 24px 16px; background: #f9fafb; }
        .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .header { text-align: center; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 2px solid #6366f1; }
        .store-name { font-size: 22px; font-weight: bold; color: #111; }
        .store-info { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .order-num { font-size: 13px; color: #6b7280; margin-top: 6px; }
        .badge-paid { display: inline-block; margin-top: 8px; padding: 3px 14px; border-radius: 99px; font-size: 12px; font-weight: 700; background: #dcfce7; color: #166534; }
        .info-table { width: 100%; font-size: 13px; margin-bottom: 16px; }
        .info-table td { padding: 3px 0; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
        .info-table td:last-child { font-weight: 600; text-align: right; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 14px 0; }
        table.items { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.items th { text-align: left; font-size: 11px; color: #6b7280; text-transform: uppercase; padding: 6px 0; border-bottom: 1px solid #e5e7eb; }
        table.items th.r { text-align: right; }
        table.items td { padding: 9px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        table.items td.c { text-align: center; }
        table.items td.r { text-align: right; }
        .item-variant { font-size: 11px; color: #6366f1; margin-top: 2px; }
        .item-discount { font-size: 11px; color: #ef4444; margin-top: 1px; }
        .item-original { font-size: 11px; color: #9ca3af; text-decoration: line-through; }
        .item-note { font-size: 11px; color: #9ca3af; font-style: italic; margin-top: 1px; }
        .summary { font-size: 13px; }
        .summary { font-size: 13px; }
        .summary-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 4px 0; 
            gap: 16px;
        }
        .summary-row span:first-child { flex: 1; }
        .summary-row span:last-child { flex-shrink: 0; text-align: right; }
        .summary-row.total { 
            font-weight: bold; 
            font-size: 16px; 
            padding-top: 10px; 
            margin-top: 6px; 
            border-top: 2px solid #e5e7eb; 
        }
        .summary-row.payment { color: #6b7280; padding-top: 8px; }
    </style>
</head>
<body>
<div class="card">

    {{-- Header --}}
    <div class="header">
        <div class="store-name">{{ $payment->order->store->name }}</div>
        @if($payment->order->store->address)
            <div class="store-info">{{ $payment->order->store->address }}</div>
        @endif
        @if($payment->order->store->phone)
            <div class="store-info">Telp: {{ $payment->order->store->phone }}</div>
        @endif
        <div class="order-num">Struk Pesanan #{{ $payment->order->order_number }}</div>
        <div><span class="badge-paid">✓ Lunas</span></div>
    </div>

    {{-- Info Transaksi --}}
    <table class="info-table">
        <tr>
            <td>Tanggal</td>
            <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
        </tr>
        @if($payment->order->customer_name)
        <tr>
            <td>Pelanggan</td>
            <td>{{ $payment->order->customer_name }}</td>
        </tr>
        @endif
        <tr>
            <td>Kasir</td>
            <td>{{ $payment->order->cashier?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tipe</td>
            <td>{{ $payment->order->order_type === 'dine_in' ? 'Dine-In' : 'Takeaway' }}</td>
        </tr>
        @if($payment->order->table)
        <tr>
            <td>Meja</td>
            <td>{{ $payment->order->table->number }}</td>
        </tr>
        @endif
    </table>

    <hr class="divider">

    {{-- Item Pesanan --}}
    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="r" style="width:36px;">Qty</th>
                <th class="r">Harga</th>
                <th class="r">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->order->items as $item)
            <tr>
                <td>
                    <div>{{ $item->product_name }}</div>
                    @if($item->variant_name)
                        <div class="item-variant">{{ $item->variant_name }}</div>
                    @endif
                    @if(!empty($item->discount_label) && $item->discount_amount > 0)
                        <div class="item-discount">🏷 {{ $item->discount_label }}</div>
                        <div class="item-original">Rp {{ number_format($item->original_price, 0, ',', '.') }}</div>
                    @endif
                    @if($item->special_notes)
                        <div class="item-note">* {{ $item->special_notes }}</div>
                    @endif
                </td>
                <td class="c">{{ $item->quantity }}</td>
                <td class="r" style="white-space:nowrap;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="r" style="font-weight:600; white-space:nowrap;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($payment->order->notes)
        <div class="notes-box">
            <span style="font-weight:600;">Catatan:</span> {{ $payment->order->notes }}
        </div>
    @endif

    <hr class="divider">

    {{-- Ringkasan Harga --}}
    <div class="summary">
        <div class="summary-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($payment->order->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Pajak ({{ number_format($payment->order->tax_rate, 0) }}%)</span>
            <span>Rp {{ number_format($payment->order->tax_amount, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($payment->order->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row payment">
            <span>{{ $payment->methodLabel() }}</span>
            <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
        </div>
        @if($payment->payment_method === 'cash' && $payment->amount_received)
        <div class="summary-row payment">
            <span>Uang Diterima</span>
            <span>Rp {{ number_format($payment->amount_received, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row" style="font-weight:600;">
            <span>Kembalian</span>
            <span>Rp {{ number_format(max(0, $payment->amount_received - $payment->amount), 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

</div>

<div class="footer">
    <p>Terima kasih atas kunjungan Anda!</p>
    <p style="margin-top:4px;">{{ $payment->order->store->name }}</p>
    <p style="margin-top:4px; font-size:11px;">Email ini dikirim otomatis, mohon tidak membalas.</p>
</div>
</body>
</html>