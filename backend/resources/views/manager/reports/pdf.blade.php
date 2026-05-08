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
    .section-title.red    { color: #991b1b; border-color: #991b1b; }
    .section-title.orange { color: #9a3412; border-color: #f97316; }
    .section-title.green  { color: #166534; border-color: #16a34a; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    th { background: #f1f5f9; text-align: left; padding: 7px 8px; font-size: 10px;
         font-weight: bold; color: #374151; border-bottom: 1px solid #d1d5db; }
    th.right, td.right { text-align: right; }
    td { padding: 6px 8px; font-size: 10px; color: #374151; border-bottom: 1px solid #f1f5f9; }
    tr:last-child td { border-bottom: none; }
    tr:nth-child(even) td { background: #fafafa; }
    tr.total-row td { font-weight: bold; background: #f1f5f9 !important; border-top: 1px solid #d1d5db; }
    .empty { text-align: center; padding: 16px; color: #9ca3af; font-style: italic; }
    .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e5e7eb;
              font-size: 9px; color: #9ca3af; text-align: center; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    .alert { padding: 8px 10px; border-radius: 6px; font-size: 10px; margin-bottom: 10px; }
    .alert-red    { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
    .alert-orange { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; }
    .alert-blue   { background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; }
    .alert-green  { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
    .gap { height: 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>
        @php
            $titles = [
                'sales'    => 'Laporan Penjualan',
                'products' => 'Laporan Produk',
                'revenue'  => 'Analitik Revenue',
                'cashiers' => 'Laporan Kasir',
                'payments' => 'Laporan Pembayaran',
            ];
        @endphp
        {{ $titles[$type] ?? 'Laporan' }}
    </h1>
    <p>Periode: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    <p>Dicetak: {{ now()->format('d M Y, H:i') }}</p>
</div>

<div class="container">

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- LAPORAN PENJUALAN                                               --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@if($type === 'sales')

    {{-- Summary --}}
    <div class="section">
        <div class="section-title">Ringkasan Penjualan (Lunas)</div>
        <table>
            <tr>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Total Penjualan (Lunas)</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Pesanan Aktif</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">{{ $totalOrders }}</div>
                    <div style="font-size:9px;color:#9ca3af;margin-top:2px;">Tidak termasuk dibatalkan</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Rata-rata/Transaksi</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Ringkasan Status Pembayaran --}}
    @php
        $byPayStatusMap = collect($byPaymentStatus ?? [])->keyBy('status');
        $paidPay     = $byPayStatusMap->get('paid');
        $refundedPay = $byPayStatusMap->get('refunded');
        $pendingPay  = $byPayStatusMap->get('pending');
    @endphp
    @if($byPayStatusMap->isNotEmpty())
    <div class="section">
        <div class="section-title green">Ringkasan Status Pembayaran</div>
        <table>
            <thead><tr>
                <th>Status Pembayaran</th>
                <th class="right">Jumlah Transaksi</th>
                <th class="right">Total (Rp)</th>
            </tr></thead>
            <tbody>
            @if($paidPay)
            <tr>
                <td><span class="badge" style="background:#dcfce7;color:#166534;">Lunas</span></td>
                <td class="right">{{ (int) $paidPay->count }}</td>
                <td class="right">Rp {{ number_format($paidPay->total, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($refundedPay)
            <tr>
                <td><span class="badge" style="background:#ffedd5;color:#9a3412;">Refund</span></td>
                <td class="right">{{ (int) $refundedPay->count }}</td>
                <td class="right">Rp {{ number_format($refundedPay->total, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($pendingPay)
            <tr>
                <td><span class="badge" style="background:#fef9c3;color:#854d0e;">Pending</span></td>
                <td class="right">{{ (int) $pendingPay->count }}</td>
                <td class="right">Rp {{ number_format($pendingPay->total, 0, ',', '.') }}</td>
            </tr>
            @endif
            </tbody>
        </table>
        @if($refundedPay && $refundedPay->count > 0)
        <div class="alert alert-orange" style="margin-top:8px;">
            ⚠ {{ (int) $refundedPay->count }} transaksi direfund senilai Rp {{ number_format($refundedPay->total, 0, ',', '.') }} dalam periode ini.
        </div>
        @endif
    </div>
    @endif

    {{-- Rekap Status Pesanan --}}
    @php
        $byStatusMap = collect($byStatus ?? [])->keyBy('status');
        $statusConfigPdf = [
            'completed' => ['label' => 'Selesai',   'bg' => '#f0fdf4', 'border' => '#86efac', 'color' => '#166534'],
            'ready'     => ['label' => 'Siap',       'bg' => '#eff6ff', 'border' => '#93c5fd', 'color' => '#1d4ed8'],
            'cooking'   => ['label' => 'Dimasak',    'bg' => '#fffbeb', 'border' => '#fcd34d', 'color' => '#92400e'],
            'pending'   => ['label' => 'Pending',    'bg' => '#fff7ed', 'border' => '#fdba74', 'color' => '#9a3412'],
            'cancelled' => ['label' => 'Dibatalkan', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'color' => '#991b1b'],
        ];
        $cancelledRow   = $byStatusMap->get('cancelled');
        $totalAllOrders = collect($byStatus ?? [])->sum('count');
    @endphp

    <div class="section">
        <div class="section-title">Rekap Status Semua Pesanan (Total: {{ $totalAllOrders }})</div>
        <table>
            <thead><tr>
                <th>Status</th>
                <th class="right">Jumlah Pesanan</th>
                <th class="right">Total Nilai (Rp)</th>
            </tr></thead>
            <tbody>
            @foreach(['completed','ready','cooking','pending','cancelled'] as $key)
                @php
                    $s   = $byStatusMap->get($key);
                    $cfg = $statusConfigPdf[$key];
                @endphp
                <tr>
                    <td>
                        <span style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};padding:2px 6px;border-radius:4px;font-size:9px;font-weight:bold;">
                            {{ $cfg['label'] }}
                        </span>
                    </td>
                    <td class="right">{{ $s ? $s->count : 0 }}</td>
                    <td class="right">Rp {{ number_format($s ? $s->total : 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
            <tr class="total-row">
                <td>TOTAL SEMUA</td>
                <td class="right">{{ $totalAllOrders }}</td>
                <td class="right">Rp {{ number_format(collect($byStatus ?? [])->sum('total'), 0, ',', '.') }}</td>
            </tr>
        </table>
        @if($cancelledRow && $cancelledRow->count > 0)
        <div class="alert alert-red" style="margin-top:8px;">
            ⚠ {{ $cancelledRow->count }} pesanan dibatalkan senilai Rp {{ number_format($cancelledRow->total, 0, ',', '.') }} tidak dihitung dalam total penjualan.
        </div>
        @endif
    </div>

    {{-- Per Metode Pembayaran --}}
    <div class="section">
        <div class="section-title">Per Metode Pembayaran (Hanya Lunas)</div>
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
        <div class="section-title">Per Tipe Pesanan (Tidak Termasuk Dibatalkan)</div>
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
        <div class="section-title">Tren Harian (dari Pembayaran Lunas)</div>
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
            @if(collect($dailyTrend)->count() > 0)
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="right">Rp {{ number_format(collect($dailyTrend)->sum('total'), 0, ',', '.') }}</td>
                <td class="right">{{ collect($dailyTrend)->sum('count') }}</td>
            </tr>
            @endif
        </table>
    </div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- LAPORAN PRODUK                                                  --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'products')

    <div class="alert alert-blue" style="margin-bottom:16px;">
        ℹ Data produk di bawah hanya dari pesanan yang <strong>tidak dibatalkan</strong>.
    </div>

    @if(isset($cancelledSummary) && $cancelledSummary && $cancelledSummary->count > 0)
    <div class="section">
        <div class="section-title red">Ringkasan Pesanan Dibatalkan</div>
        <div class="alert alert-red">
            Terdapat <strong>{{ $cancelledSummary->count }} pesanan dibatalkan</strong>
            dengan total nilai <strong>Rp {{ number_format($cancelledSummary->total_amount, 0, ',', '.') }}</strong> dalam periode ini.
        </div>
        @if(isset($cancelledItems) && collect($cancelledItems)->isNotEmpty())
        <table>
            <thead><tr><th>Produk (dalam pesanan batal)</th><th class="right">Qty Batal</th><th class="right">Nilai Hilang</th></tr></thead>
            <tbody>
            @foreach(collect($cancelledItems)->take(15) as $ci)
                <tr><td>{{ $ci->product_name }}</td>
                    <td class="right" style="color:#ef4444;font-weight:bold;">{{ $ci->total_qty }}</td>
                    <td class="right" style="color:#ef4444;">Rp {{ number_format($ci->total_lost, 0, ',', '.') }}</td></tr>
            @endforeach
            </tbody>
            <tr class="total-row">
                <td>TOTAL HILANG</td>
                <td class="right">{{ collect($cancelledItems)->sum('total_qty') }}</td>
                <td class="right">Rp {{ number_format(collect($cancelledItems)->sum('total_lost'), 0, ',', '.') }}</td>
            </tr>
        </table>
        @endif
    </div>
    @endif

    <div class="section">
        <div class="section-title">Top Produk (Qty)</div>
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

    <div class="section">
        <div class="section-title">Top Produk (Revenue)</div>
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

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ANALITIK REVENUE                                                --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'revenue')

    @php
        $rows             = $data['data']               ?? collect();
        $totalRev         = $data['total']              ?? 0;
        $nonRevenueOrders = $data['non_revenue_orders'] ?? collect();
        $refundSummary    = $data['refund_summary']     ?? ($refundSummary ?? null);

        $periodLabels = ['daily'=>'Harian','weekly'=>'Mingguan','monthly'=>'Bulanan','yearly'=>'Tahunan'];
    @endphp

    <div class="section">
        <div class="section-title">Ringkasan Revenue (dari Pembayaran Lunas)</div>
        <table>
            <tr>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Total Revenue</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($totalRev, 0, ',', '.') }}</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Granularitas</div>
                    <div style="font-size:12px;font-weight:bold;color:#181375;margin-top:3px;">{{ $periodLabels[$period] ?? $period }}</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:33%; padding:10px; background:#f8fafc; border:1px solid #e5e7eb;">
                    <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:bold;">Jumlah Periode</div>
                    <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">{{ $rows->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Ringkasan Refund --}}
    @if($refundSummary && $refundSummary->count > 0)
    <div class="section">
        <div class="section-title orange">Ringkasan Refund Periode Ini</div>
        <table>
            <tr>
                <td style="width:50%; padding:10px; background:#fff7ed; border:1px solid #fdba74;">
                    <div style="font-size:9px;color:#9a3412;font-weight:bold;">Transaksi Direfund</div>
                    <div style="font-size:16px;font-weight:bold;color:#9a3412;margin-top:2px;">{{ (int) $refundSummary->count }}</div>
                </td>
                <td style="width:4px;"></td>
                <td style="width:50%; padding:10px; background:#fff7ed; border:1px solid #fdba74;">
                    <div style="font-size:9px;color:#9a3412;font-weight:bold;">Total Nilai Refund</div>
                    <div style="font-size:14px;font-weight:bold;color:#9a3412;margin-top:2px;">Rp {{ number_format($refundSummary->total, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Pesanan Non-Revenue --}}
    @if(collect($nonRevenueOrders)->isNotEmpty())
    <div class="section">
        <div class="section-title orange">Pesanan Tidak Menghasilkan Revenue</div>
        <table>
            <thead><tr><th>Status</th><th class="right">Jumlah Pesanan</th><th class="right">Total Nilai (Rp)</th></tr></thead>
            <tbody>
            @php
                $nrLabels = ['cancelled'=>'Dibatalkan','pending'=>'Pending','cooking'=>'Dimasak','ready'=>'Siap Saji'];
                $nrColors = ['cancelled'=>'#991b1b','pending'=>'#9a3412','cooking'=>'#92400e','ready'=>'#1d4ed8'];
                $nrMap = collect($nonRevenueOrders)->keyBy('status');
            @endphp
            @foreach(['cancelled','pending','cooking','ready'] as $key)
                @php $nr = $nrMap->get($key); @endphp
                @if($nr)
                <tr>
                    <td style="color:{{ $nrColors[$key] ?? '#374151' }};font-weight:bold;">{{ $nrLabels[$key] ?? ucfirst($key) }}</td>
                    <td class="right">{{ $nr->count }}</td>
                    <td class="right">Rp {{ number_format($nr->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
        @php $cancelledNR = $nrMap->get('cancelled'); @endphp
        @if($cancelledNR)
        <div class="alert alert-red" style="margin-top:8px;">
            ⚠ {{ $cancelledNR->count }} pesanan dibatalkan senilai Rp {{ number_format($cancelledNR->total_amount, 0, ',', '.') }} tidak termasuk dalam total revenue.
        </div>
        @endif
    </div>
    @endif

    <div class="section">
        <div class="section-title">Data Revenue — {{ $periodLabels[$period] ?? $period }}</div>
        <table>
            <thead><tr><th>Periode</th><th class="right">Total Revenue</th><th class="right">Transaksi</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr><td>{{ $row->period ?? '-' }}</td>
                    <td class="right">Rp {{ number_format($row->revenue ?? 0, 0, ',', '.') }}</td>
                    <td class="right">{{ $row->count ?? '-' }}</td></tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
            @if($rows->count() > 0)
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="right">Rp {{ number_format($totalRev, 0, ',', '.') }}</td>
                <td class="right">{{ $rows->sum('count') }}</td>
            </tr>
            @endif
        </table>
    </div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- LAPORAN KASIR                                                   --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'cashiers')

    @php
        $cashiers            = $data['cashiers']              ?? collect();
        $totalSalesCashier   = $data['total_sales']           ?? 0;
        $best                = $data['best']                  ?? null;
        $cancelledPerCashier = $data['cancelled_per_cashier'] ?? collect();
        $cancelledMap        = collect($cancelledPerCashier)->keyBy('cashier_id');
        $totalCancelled      = $cancelledMap->sum('cancelled_count');
    @endphp

    @if($best)
    <div class="section">
        <div class="section-title">Kasir Terbaik</div>
        <table><tr>
            <td style="padding:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;">
                <div style="font-weight:bold;font-size:13px;">{{ $best->cashier }}</div>
                <div style="font-size:10px;color:#16a34a;margin-top:2px;">
                    Rp {{ number_format($best->total_sales, 0, ',', '.') }} · {{ $best->order_count }} pesanan
                </div>
            </td>
            <td style="width:4px;"></td>
            <td style="padding:10px;background:#f8fafc;border:1px solid #e5e7eb;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;">Total Penjualan (Semua Kasir)</div>
                <div style="font-size:14px;font-weight:bold;color:#181375;margin-top:3px;">Rp {{ number_format($totalSalesCashier, 0, ',', '.') }}</div>
                <div style="font-size:9px;color:#9ca3af;margin-top:2px;">Tidak termasuk pesanan dibatalkan</div>
            </td>
            @if($totalCancelled > 0)
            <td style="width:4px;"></td>
            <td style="padding:10px;background:#fef2f2;border:1px solid #fca5a5;">
                <div style="font-size:9px;color:#991b1b;text-transform:uppercase;">Total Dibatalkan</div>
                <div style="font-size:14px;font-weight:bold;color:#991b1b;margin-top:3px;">{{ $totalCancelled }} pesanan</div>
                <div style="font-size:9px;color:#9ca3af;margin-top:2px;">Nilai: Rp {{ number_format($cancelledMap->sum('cancelled_amount'), 0, ',', '.') }}</div>
            </td>
            @endif
        </tr></table>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Performa per Kasir</div>
        <table>
            <thead><tr>
                <th>Nama Kasir</th>
                <th class="right">Pesanan Aktif</th>
                <th class="right">Total Penjualan</th>
                <th class="right">Rata-rata</th>
                <th class="right">Avg Proses</th>
                <th class="right" style="color:#991b1b;">Dibatalkan</th>
                <th class="right" style="color:#991b1b;">Nilai Batal</th>
            </tr></thead>
            <tbody>
            @forelse($cashiers as $row)
                @php $cancelled = $cancelledMap->get($row->cashier_id); @endphp
                <tr>
                    <td>{{ $row->cashier }}</td>
                    <td class="right">{{ $row->order_count }}</td>
                    <td class="right">Rp {{ number_format($row->total_sales, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row->avg_order_value, 0, ',', '.') }}</td>
                    <td class="right">{{ $row->avg_processing_min ? number_format($row->avg_processing_min, 1) . ' mnt' : '-' }}</td>
                    <td class="right" style="{{ ($cancelled && $cancelled->cancelled_count > 0) ? 'color:#ef4444;font-weight:bold;' : 'color:#d1d5db;' }}">
                        {{ $cancelled ? $cancelled->cancelled_count : '—' }}
                    </td>
                    <td class="right" style="{{ ($cancelled && $cancelled->cancelled_count > 0) ? 'color:#ef4444;' : 'color:#d1d5db;' }}">
                        {{ $cancelled && $cancelled->cancelled_count > 0 ? 'Rp '.number_format($cancelled->cancelled_amount, 0, ',', '.') : '—' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada data</td></tr>
            @endforelse
            </tbody>
            @if($cashiers->count() > 0)
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="right">{{ $cashiers->sum('order_count') }}</td>
                <td class="right">Rp {{ number_format($totalSalesCashier, 0, ',', '.') }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right" style="color:#ef4444;">{{ $totalCancelled ?: '—' }}</td>
                <td class="right" style="color:#ef4444;">
                    {{ $totalCancelled > 0 ? 'Rp '.number_format($cancelledMap->sum('cancelled_amount'), 0, ',', '.') : '—' }}
                </td>
            </tr>
            @endif
        </table>
        @if($totalCancelled > 0)
        <div class="alert alert-red" style="margin-top:8px;">
            ⚠ Total <strong>{{ $totalCancelled }} pesanan dibatalkan</strong> senilai
            Rp {{ number_format($cancelledMap->sum('cancelled_amount'), 0, ',', '.') }} dari semua kasir dalam periode ini.
        </div>
        @endif
    </div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- LAPORAN PEMBAYARAN                                              --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'payments')

    @php
        $paymentList = $payments ?? collect();
        $unpaidList  = $unpaidOrders ?? collect();
        $byStatusPay = $paymentList->groupBy('status')->map(fn($g) => [
            'count' => $g->count(),
            'total' => $g->sum('amount'),
        ]);
    @endphp

    {{-- Summary per status --}}
    <div class="section">
        <div class="section-title">Ringkasan per Status Pembayaran</div>
        <table>
            <thead><tr><th>Status</th><th class="right">Jumlah</th><th class="right">Total (Rp)</th></tr></thead>
            <tbody>
            @php
                $payStatusCfg = [
                    'paid'     => ['label' => 'Lunas',   'color' => '#166534', 'bg' => '#dcfce7'],
                    'refunded' => ['label' => 'Refund',  'color' => '#9a3412', 'bg' => '#ffedd5'],
                    'pending'  => ['label' => 'Pending', 'color' => '#854d0e', 'bg' => '#fef9c3'],
                ];
            @endphp
            @foreach($payStatusCfg as $key => $cfg)
                @php $stat = $byStatusPay->get($key); @endphp
                @if($stat)
                <tr>
                    <td>
                        <span class="badge" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">{{ $cfg['label'] }}</span>
                    </td>
                    <td class="right">{{ $stat['count'] }}</td>
                    <td class="right">Rp {{ number_format($stat['total'], 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
            </tbody>
            <tr class="total-row">
                <td>TOTAL TRANSAKSI</td>
                <td class="right">{{ $paymentList->count() }}</td>
                <td class="right">Rp {{ number_format($paymentList->sum('amount'), 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- Alert refund jika ada --}}
        @php $refundedStat = $byStatusPay->get('refunded'); @endphp
        @if($refundedStat && $refundedStat['count'] > 0)
        <div class="alert alert-orange" style="margin-top:8px;">
            ⚠ {{ $refundedStat['count'] }} transaksi direfund senilai Rp {{ number_format($refundedStat['total'], 0, ',', '.') }}.
        </div>
        @endif
    </div>

    {{-- Pesanan Belum Bayar --}}
    @if($unpaidList->isNotEmpty())
    <div class="section">
        <div class="section-title orange">Pesanan Aktif Belum Dibayar ({{ $unpaidList->count() }})</div>
        <div class="alert alert-orange" style="margin-bottom:8px;">
            Total nilai yang belum dibayar: <strong>Rp {{ number_format($unpaidList->sum('total_amount'), 0, ',', '.') }}</strong>
        </div>
        <table>
            <thead><tr>
                <th>No. Pesanan</th>
                <th>Pelanggan</th>
                <th>Kasir</th>
                <th class="right">Total (Rp)</th>
                <th>Status</th>
                <th>Dibuat</th>
            </tr></thead>
            <tbody>
            @foreach($unpaidList as $o)
            <tr>
                <td>{{ $o->order_number ?? '-' }}</td>
                <td>{{ $o->customer_name ?? '—' }}</td>
                <td>{{ $o->cashier?->name ?? '-' }}</td>
                <td class="right">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                <td>
                    @php
                        $stColors = ['cooking' => '#92400e', 'ready' => '#1d4ed8', 'pending' => '#9a3412'];
                        $stBgs    = ['cooking' => '#fffbeb',  'ready' => '#eff6ff',  'pending' => '#fff7ed'];
                        $stLabels = ['cooking' => 'Dimasak',  'ready' => 'Siap',     'pending' => 'Pending'];
                    @endphp
                    <span class="badge"
                          style="background:{{ $stBgs[$o->status] ?? '#f3f4f6' }};color:{{ $stColors[$o->status] ?? '#374151' }};">
                        {{ $stLabels[$o->status] ?? ucfirst($o->status) }}
                    </span>
                </td>
                <td>{{ $o->created_at->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
            </tbody>
            <tr class="total-row">
                <td colspan="3">TOTAL BELUM BAYAR</td>
                <td class="right">Rp {{ number_format($unpaidList->sum('total_amount'), 0, ',', '.') }}</td>
                <td colspan="2">{{ $unpaidList->count() }} pesanan</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Daftar Pembayaran --}}
    <div class="section">
        <div class="section-title">Daftar Transaksi Pembayaran</div>
        <table>
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Kasir</th>
                    <th>Metode</th>
                    <th class="right">Jumlah</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
            @forelse($paymentList as $p)
                <tr>
                    <td>{{ $p->order?->order_number ?? '-' }}</td>
                    <td>{{ $p->order?->customer_name ?? '—' }}</td>
                    <td>{{ $p->cashier?->name ?? '-' }}</td>
                    <td>{{ $p->methodLabel() }}</td>
                    <td class="right">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $lbl = match($p->status) { 'paid' => 'Lunas', 'refunded' => 'Refund', 'pending' => 'Pending', default => ucfirst($p->status) };
                            $clr = match($p->status) { 'paid' => '#166534', 'refunded' => '#9a3412', 'pending' => '#854d0e', default => '#374151' };
                            $bg  = match($p->status) { 'paid' => '#dcfce7', 'refunded' => '#ffedd5', 'pending' => '#fef9c3', default => '#f3f4f6' };
                        @endphp
                        <span class="badge" style="background:{{ $bg }};color:{{ $clr }};">{{ $lbl }}</span>
                    </td>
                    <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada data pembayaran</td></tr>
            @endforelse
            </tbody>
            @if($paymentList->count() > 0)
            <tr class="total-row">
                <td colspan="4">TOTAL</td>
                <td class="right">Rp {{ number_format($paymentList->sum('amount'), 0, ',', '.') }}</td>
                <td colspan="2">{{ $paymentList->count() }} transaksi</td>
            </tr>
            @endif
        </table>
    </div>
@endif

</div>

<div class="footer">
    DePOS — Laporan digenerate otomatis pada {{ now()->format('d M Y H:i:s') }}
</div>
</body>
</html>