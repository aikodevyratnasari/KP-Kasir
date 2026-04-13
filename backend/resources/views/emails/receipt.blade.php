<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            color: #1a1a1a;
            background-color: #f2f2f2;
            padding: 24px 12px;
        }

        .wrapper { max-width: 520px; margin: 0 auto; }

        .card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
        }

        /* Header */
        .header {
            background: #ffffff;
            padding: 28px 28px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        .store-name  { font-size: 18px; font-weight: 700; color: #111; margin-bottom: 2px; }
        .store-meta  { font-size: 12px; color: #888; line-height: 1.6; }
        .order-number { margin-top: 14px; font-size: 12px; color: #888; }
        .badge-paid {
            display: inline-block; margin-top: 8px;
            padding: 3px 12px; border-radius: 99px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.04em;
            background: #e8f5e9; color: #2e7d32;
        }

        /* Section */
        .section { padding: 20px 28px; border-bottom: 1px solid #f0f0f0; }
        .section:last-child { border-bottom: none; }
        .section-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #aaa; margin-bottom: 14px;
        }

        /* Info rows — label kiri, value kanan */
        .row          { display: table; width: 100%; padding: 5px 0; }
        .row-label    { display: table-cell; font-size: 13px; color: #666; vertical-align: top; width: 42%; }
        .row-value    { display: table-cell; font-size: 13px; font-weight: 600; color: #111; text-align: right; vertical-align: top; }

        /* Items */
        .item         { padding: 10px 0; border-bottom: 1px solid #f7f7f7; }
        .item:last-child { border-bottom: none; }
        .item-top     { display: table; width: 100%; }
        .item-name-col  { display: table-cell; vertical-align: top; }
        .item-price-col { display: table-cell; vertical-align: top; text-align: right; white-space: nowrap; padding-left: 12px; min-width: 90px; }

        .item-name     { font-size: 13px; font-weight: 600; color: #111; }
        .item-qty      { font-size: 12px; color: #888; margin-top: 2px; }
        .item-variant  { font-size: 11px; color: #5c6bc0; margin-top: 2px; }
        .item-discount { font-size: 11px; color: #e53935; margin-top: 1px; }
        .item-original { font-size: 11px; color: #bbb; text-decoration: line-through; margin-top: 1px; }
        .item-note     { font-size: 11px; color: #aaa; font-style: italic; margin-top: 2px; }
        .item-subtotal { font-size: 13px; font-weight: 700; color: #111; }
        .item-unit     { font-size: 11px; color: #aaa; margin-top: 2px; }

        /* Notes */
        .notes-box {
            background: #fafafa; border-left: 3px solid #e0e0e0; border-radius: 4px;
            padding: 10px 14px; font-size: 12px; color: #666; margin-top: 12px;
        }
        .notes-box strong { color: #444; }

        /* Summary — semua pakai table display agar label kiri, angka kanan */
        .sum-row    { display: table; width: 100%; padding: 6px 0; }
        .sum-label  { display: table-cell; font-size: 13px; color: #555; vertical-align: middle; }
        .sum-amount { display: table-cell; font-size: 13px; font-weight: 600; color: #111; text-align: right; white-space: nowrap; padding-left: 12px; vertical-align: middle; }

        .divider-thin { height: 1px; background: #f0f0f0; margin: 8px 0; }
        .divider-bold { height: 2px; background: #e8e8e8; margin: 10px 0; }

        .total-row    { display: table; width: 100%; padding: 8px 0; }
        .total-label  { display: table-cell; font-size: 15px; font-weight: 700; color: #111; vertical-align: middle; }
        .total-amount { display: table-cell; font-size: 15px; font-weight: 700; color: #111; text-align: right; white-space: nowrap; padding-left: 12px; vertical-align: middle; }

        /* Footer */
        .footer { background: #fafafa; padding: 20px 28px; text-align: center; }
        .footer-store { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 4px; }
        .footer-note  { font-size: 11px; color: #aaa; line-height: 1.7; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    {{-- Header --}}
    <div class="header">
        <div class="store-name">{{ $payment->order->store->name }}</div>
        @if($payment->order->store->address)
            <div class="store-meta">{{ $payment->order->store->address }}</div>
        @endif
        @if($payment->order->store->phone)
            <div class="store-meta">Telp: {{ $payment->order->store->phone }}</div>
        @endif
        <div class="order-number">No. Pesanan: <strong>#{{ $payment->order->order_number }}</strong></div>
        <div><span class="badge-paid">✓ Lunas</span></div>
    </div>

    {{-- Detail Transaksi --}}
    <div class="section">
        <div class="section-label">Detail Transaksi</div>

        <div class="row">
            <span class="row-label">Tanggal</span>
            <span class="row-value">{{ $payment->created_at->format('d M Y, H:i') }}</span>
        </div>
        @if($payment->order->customer_name)
        <div class="row">
            <span class="row-label">Pelanggan</span>
            <span class="row-value">{{ $payment->order->customer_name }}</span>
        </div>
        @endif
        <div class="row">
            <span class="row-label">Kasir</span>
            <span class="row-value">{{ $payment->order->cashier?->name ?? '-' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Tipe</span>
            <span class="row-value">{{ $payment->order->order_type === 'dine_in' ? 'Dine-In' : 'Takeaway' }}</span>
        </div>
        @if($payment->order->table)
        <div class="row">
            <span class="row-label">Meja</span>
            <span class="row-value">{{ $payment->order->table->number }}</span>
        </div>
        @endif
    </div>

    {{-- Item Pesanan --}}
    <div class="section">
        <div class="section-label">Item Pesanan</div>

        @foreach($payment->order->items as $item)
        <div class="item">
            <div class="item-top">
                <div class="item-name-col">
                    <div class="item-name">{{ $item->product_name }}</div>
                    <div class="item-qty">× {{ $item->quantity }}</div>
                    @if($item->variant_name)
                        <div class="item-variant">{{ $item->variant_name }}</div>
                    @endif
                    @if(!empty($item->discount_label) && $item->discount_amount > 0)
                        <div class="item-discount">{{ $item->discount_label }}</div>
                        <div class="item-original">Rp {{ number_format($item->original_price, 0, ',', '.') }}</div>
                    @endif
                    @if($item->special_notes)
                        <div class="item-note">* {{ $item->special_notes }}</div>
                    @endif
                </div>
                <div class="item-price-col">
                    <div class="item-subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                    @if($item->quantity > 1)
                        <div class="item-unit">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        @if($payment->order->notes)
        <div class="notes-box">
            <strong>Catatan:</strong> {{ $payment->order->notes }}
        </div>
        @endif
    </div>

    {{-- Rincian Pembayaran --}}
    <div class="section">
        <div class="section-label">Rincian Pembayaran</div>

        <div class="sum-row">
            <span class="sum-label">Subtotal</span>
            <span class="sum-amount">Rp {{ number_format($payment->order->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span class="sum-label">Pajak ({{ number_format($payment->order->tax_rate, 0) }}%)</span>
            <span class="sum-amount">Rp {{ number_format($payment->order->tax_amount, 0, ',', '.') }}</span>
        </div>

        <div class="divider-bold"></div>

        <div class="total-row">
            <span class="total-label">Total</span>
            <span class="total-amount">Rp {{ number_format($payment->order->total_amount, 0, ',', '.') }}</span>
        </div>

        <div class="divider-bold"></div>

        <div class="sum-row" style="margin-top: 4px;">
            <span class="sum-label">{{ $payment->methodLabel() }}</span>
            <span class="sum-amount">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
        </div>

        @if($payment->payment_method === 'cash' && $payment->amount_received)
        <div class="sum-row">
            <span class="sum-label">Uang Diterima</span>
            <span class="sum-amount">Rp {{ number_format($payment->amount_received, 0, ',', '.') }}</span>
        </div>
        <div class="divider-thin"></div>
        <div class="sum-row">
            <span class="sum-label" style="font-weight:700; color:#2e7d32;">Kembalian</span>
            <span class="sum-amount" style="font-weight:700; color:#2e7d32;">
                Rp {{ number_format(max(0, $payment->amount_received - $payment->amount), 0, ',', '.') }}
            </span>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-store">{{ $payment->order->store->name }}</div>
        <div class="footer-note">
            Terima kasih atas kunjungan Anda!<br>
            Email ini dikirim otomatis, mohon tidak membalas.
        </div>
    </div>

</div>
</div>
</body>
</html>