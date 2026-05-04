@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">

    {{-- Filter --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input w-auto">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input w-auto">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f'"
                onmouseout="this.style.backgroundColor='#2D54BF'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <circle cx="16" cy="6" r="2"/>
                    <line x1="4" y1="12" x2="20" y2="12"/>
                    <circle cx="8" cy="12" r="2"/>
                    <line x1="4" y1="18" x2="20" y2="18"/>
                    <circle cx="14" cy="18" r="2"/>
                </svg>
                Filter
            </button>
            <a href="{{ route('manager.reports.sales') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                    <path d="M3 3v6h6"/>
                </svg>
                Reset
            </a>
            <div class="ml-auto">
                <x-manager.reports.actions type="sales" :from="$from->format('Y-m-d')" :to="$to->format('Y-m-d')" />
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Penjualan (Lunas)</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Dari pembayaran lunas</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Pesanan Aktif</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $totalOrders ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk dibatalkan</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Rata-rata / Transaksi</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Rekap Status Pesanan --}}
    @php
        $byStatus       = $byStatus ?? collect();
        $statusConfig = [
    'completed' => ['label' => 'Selesai',    'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534'],
    'ready'     => ['label' => 'Siap',        'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8'],
    'cooking'   => ['label' => 'Dimasak',     'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#92400e'],
    'pending'   => ['label' => 'Pending',     'bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#9a3412'],
    'cancelled' => ['label' => 'Dibatalkan',  'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#991b1b'],
];
        $totalAllOrders = collect($byStatus)->sum('count');
        $statusMap      = collect($byStatus)->keyBy('status');
        $cancelledRow   = $statusMap->get('cancelled');
    @endphp

    <div class="card">
        <div class="flex items-center justify-between mb-4 border-b border-gray-200 pb-3">
            <div>
                <h2 class="font-semibold text-gray-800 text-sm">Rekap Status Pesanan</h2>
                <p class="text-xs text-gray-400 mt-0.5">Total semua pesanan: <strong class="text-gray-600">{{ $totalAllOrders }}</strong></p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach(['completed', 'ready', 'cooking', 'pending', 'cancelled'] as $statusKey)
                @php $s = $statusMap->get($statusKey); $cfg = $statusConfig[$statusKey]; @endphp
                <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] }};color:{{ $cfg['text'] }};border-radius:10px;padding:14px;">
                    <div style="margin-bottom:6px;">
    <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">{{ $cfg['label'] }}</p>
</div>
                    <p style="font-size:26px;font-weight:700;line-height:1;">{{ $s ? $s->count : 0 }}</p>
                    <p style="font-size:11px;margin-top:4px;opacity:0.7;">Rp {{ number_format($s ? $s->total : 0, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
        @if($cancelledRow && $cancelledRow->count > 0)
<div class="mt-4 flex items-center gap-1.5 text-xs text-red-600">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    <span><strong>{{ $cancelledRow->count }} pesanan dibatalkan</strong> senilai Rp {{ number_format($cancelledRow->total, 0, ',', '.') }} tidak dihitung dalam total penjualan</span>
</div>
@endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Per Metode Pembayaran --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-1 ">
                <h2 class="font-semibold text-gray-800 text-sm">Per Metode Pembayaran</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4 border-b border-gray-200 pb-3">Hanya transaksi <span class="text-green-600 font-medium">Lunas</span></p>
            @forelse($byPaymentMethod ?? [] as $m)
                <div class="flex justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <span class="text-sm capitalize text-gray-700">{{ $m->payment_method }}</span>
                    <div class="text-right">
                        <span class="text-sm font-semibold block">Rp {{ number_format($m->total, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">{{ $m->count }} transaksi</span>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-gray-400 text-sm">Tidak ada data</div>
            @endforelse
        </div>

        {{-- Per Tipe Pesanan --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-1">
                <h2 class="font-semibold text-gray-800 text-sm">Per Tipe Pesanan</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4 border-b border-gray-200 pb-3">Tidak termasuk pesanan <span class="text-red-500 font-medium">dibatalkan</span></p>
            @forelse($byOrderType ?? [] as $t)
                <div class="flex justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <span class="text-sm capitalize text-gray-700">{{ str_replace('_', ' ', $t->order_type) }}</span>
                    <div class="text-right">
                        <span class="text-sm font-semibold block">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">{{ $t->count }} pesanan</span>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-gray-400 text-sm">Tidak ada data</div>
            @endforelse
        </div>
    </div>

    {{-- Tren Harian --}}
    <div class="card">
        <div class="flex items-center gap-2 mb-1">
            <h2 class="font-semibold text-gray-800 text-sm">Tren Harian</h2>
        </div>
        <p class="text-xs text-gray-400 mb-4 border-b border-gray-200 pb-3">Berdasarkan pembayaran lunas per hari</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
    <th class="py-2 text-left text-gray-500 font-medium text-xs uppercase" style="width:30%">Tanggal</th>
    <th class="py-2 text-right text-gray-500 font-medium text-xs uppercase" style="width:40%">Total Penjualan</th>
    <th class="py-2 text-right text-gray-500 font-medium text-xs uppercase" style="width:30%">Transaksi</th>
</tr>
                </thead>
                <tbody>
                @forelse($dailyTrend ?? [] as $d)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
    <td class="py-2 text-gray-700" style="width:30%">{{ \Carbon\Carbon::parse($d->date)->format('d M Y') }}</td>
    <td class="py-2 font-semibold text-right" style="width:40%">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
    <td class="py-2 text-right text-gray-600" style="width:30%">{{ $d->count }}</td>
</tr>
                @empty
                    <tr><td colspan="3" class="py-8 text-center text-gray-400 text-sm">Tidak ada data untuk periode ini</td></tr>
                @endforelse
                </tbody>
                @if(($dailyTrend ?? collect())->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
    <td class="py-2 font-semibold text-gray-700 text-xs uppercase" style="width:30%">Total</td>
    <td class="py-2 font-bold text-gray-900 text-right" style="width:40%">Rp {{ number_format(collect($dailyTrend)->sum('total'), 0, ',', '.') }}</td>
    <td class="py-2 font-semibold text-gray-700 text-right" style="width:30%">{{ collect($dailyTrend)->sum('count') }}</td>
</tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <a href="{{ route('manager.reports.index') }}"
        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
        style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
        onmouseover="this.style.backgroundColor='#f3f4f6';"
        onmouseout="this.style.backgroundColor='white';">
        Kembali
    </a>
</div>
@endsection