<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }
    .header { background: #181375; color: white; padding: 16px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 18px; font-weight: bold; }
    .header p { font-size: 11px; margin-top: 4px; opacity: 0.85; }
    .container { padding: 0 24px 24px; }
    .section { margin-bottom: 20px; }
    .section-title { font-size: 12px; font-weight: bold; color: #181375; text-transform: uppercase;
                     letter-spacing: 0.05em; padding: 6px 0; border-bottom: 2px solid #181375; margin-bottom: 10px; }
    .summary-grid { display: table; width: 100%; margin-bottom: 20px; }
    .summary-card { display: table-cell; width: 33%; padding: 12px; background: #f8fafc;
                    border: 1px solid #e5e7eb; border-radius: 6px; }
    .summary-label { font-size: 9px; text-transform: uppercase; color: #6b7280; font-weight: bold; }
    .summary-value { font-size: 15px; font-weight: bold; color: #181375; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    th { background: #f1f5f9; text-align: left; padding: 7px 8px; font-size: 10px;
         font-weight: bold; color: #374151; border-bottom: 1px solid #d1d5db; }
    th.right, td.right { text-align: right; }
    td { padding: 6px 8px; font-size: 10px; color: #374151; border-bottom: 1px solid #f1f5f9; }
    tr:last-child td { border-bottom: none; }
    tr:nth-child(even) td { background: #fafafa; }
    .empty { text-align: center; padding: 16px; color: #9ca3af; font-style: italic; }
    .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e5e7eb;
              font-size: 9px; color: #9ca3af; text-align: center; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    .gap { height: 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>
        @php
            $titles = ['sales'=>'Laporan Penjualan','products'=>'Laporan Produk','revenue'=>'Analitik Revenue','cashiers'=>'Laporan Kasir'];
        @endphp
        {{ $titles[$type] ?? 'Laporan' }}
    </h1>
    <p>Periode: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    <p>Dicetak: {{ now()->format('d M Y, H:i') }}</p>
</div>

<div class="container">

@if($type === 'sales')

    {{-- Summary --}}
    <div class="section">
        <div class="section-title">Ringkasan</div>
        <table>
            <tr>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:4px;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Total Penjualan</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Total Pesanan</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">{{ $totalOrders }}</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Rata-rata/Transaksi</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Per Metode Pembayaran --}}
    <div class="section">
        <div class="section-title">Per Metode Pembayaran</div>
        <table>
            <thead><tr><th>Metode</th><th class="right">Total</th><th class="right">Transaksi</th></tr></thead>
            <tbody>
            @forelse($byPaymentMethod as $m)
                <tr><td style="text-transform:capitalize">{{ $m->payment_method }}</td>
                    <td class="right">Rp {{ number_format($m->total, 0, ',', '.') }}</td>
                    <td class="right">{{ $m->count }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Per Tipe Pesanan --}}
    <div class="section">
        <div class="section-title">Per Tipe Pesanan</div>
        <table>
            <thead><tr><th>Tipe</th><th class="right">Total</th><th class="right">Pesanan</th></tr></thead>
            <tbody>
            @forelse($byOrderType as $t)
                <tr><td style="text-transform:capitalize">{{ str_replace('_',' ',$t->order_type) }}</td>
                    <td class="right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    <td class="right">{{ $t->count }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tren Harian --}}
    <div class="section">
        <div class="section-title">Tren Harian</div>
        <table>
            <thead><tr><th>Tanggal</th><th class="right">Total Penjualan</th><th class="right">Transaksi</th></tr></thead>
            <tbody>
            @forelse($dailyTrend as $d)
                <tr><td>{{ \Carbon\Carbon::parse($d->date)->format('d M Y') }}</td>
                    <td class="right">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                    <td class="right">{{ $d->count }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

@elseif($type === 'products')

    {{-- Top by Qty --}}
    <div class="section">
        <div class="section-title">🔥 Top Produk (Qty)</div>
        <table>
            <thead><tr><th>Produk</th><th class="right">Qty Terjual</th><th class="right">Pendapatan</th></tr></thead>
            <tbody>
            @forelse($topByQty as $p)
                <tr><td>{{ $p->product_name }}</td>
                    <td class="right">{{ $p->total_qty }}</td>
                    <td class="right">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Top by Revenue --}}
    <div class="section">
        <div class="section-title">💰 Top Produk (Revenue)</div>
        <table>
            <thead><tr><th>Produk</th><th class="right">Pendapatan</th><th class="right">Qty</th></tr></thead>
            <tbody>
            @forelse($topByRevenue as $p)
                <tr><td>{{ $p->product_name }}</td>
                    <td class="right">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                    <td class="right">{{ $p->total_qty }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

@elseif($type === 'revenue')

    <div class="section">
        <div class="section-title">Data Revenue — {{ ucfirst($period ?? 'Harian') }}</div>
        <table>
            <thead><tr><th>Periode</th><th class="right">Total Revenue</th><th class="right">Transaksi</th></tr></thead>
            <tbody>
            @php $rows = $data['data'] ?? collect(); @endphp
            @forelse($rows as $row)
                <tr><td>{{ $row->period ?? $row->date ?? '-' }}</td>
                    <td class="right">Rp {{ number_format($row->revenue ?? $row->total ?? 0, 0, ',', '.') }}</td>
                    <td class="right">{{ $row->count ?? '-' }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
        @php $totalRev = $data['total'] ?? 0; @endphp
        @if($totalRev)
        <div style="text-align:right;margin-top:8px;font-weight:bold;font-size:11px;">
            Total: Rp {{ number_format($totalRev, 0, ',', '.') }}
        </div>
        @endif
    </div>

@elseif($type === 'cashiers')

    @php
        $cashiers   = $data['cashiers']    ?? collect();
        $totalSales = $data['total_sales'] ?? 0;
        $best       = $data['best']        ?? null;
    @endphp

    @if($best)
    <div class="section">
        <div class="section-title">Kasir Terbaik</div>
        <table><tr>
            <td style="padding:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;">
                <div style="font-weight:bold;font-size:13px;">{{ $best->cashier }}</div>
                <div style="font-size:10px;color:#16a34a;margin-top:2px;">Rp {{ number_format($best->total_sales, 0, ',', '.') }} · {{ $best->order_count }} pesanan</div>
            </td>
            <td style="width:4px;"></td>
            <td style="padding:10px;background:#f8fafc;border:1px solid #e5e7eb;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;">Total Penjualan Keseluruhan</div>
                <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
            </td>
        </tr></table>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Performa per Kasir</div>
        <table>
            <thead><tr>
                <th>Nama Kasir</th>
                <th class="right">Pesanan</th>
                <th class="right">Total Penjualan</th>
                <th class="right">Rata-rata</th>
                <th class="right">Avg Proses (mnt)</th>
            </tr></thead>
            <tbody>
            @forelse($cashiers as $row)
                <tr>
                    <td>{{ $row->cashier }}</td>
                    <td class="right">{{ $row->order_count }}</td>
                    <td class="right">Rp {{ number_format($row->total_sales, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row->avg_order_value, 0, ',', '.') }}</td>
                    <td class="right">{{ $row->avg_processing_min ? number_format($row->avg_processing_min, 1) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

@endif

</div>

<div class="footer">
    DePOS — Laporan digenerate otomatis pada {{ now()->format('d M Y H:i:s') }}
</div>

</body>
</html>