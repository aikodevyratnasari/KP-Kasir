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
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manager.reports.sales') }}" class="btn-secondary">Reset</a>
            <div class="ml-auto">
                <x-manager.reports.actions type="sales" :from="$from->format('Y-m-d')" :to="$to->format('Y-m-d')" />
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Penjualan (Lunas)</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Dari pembayaran lunas</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Pesanan Aktif</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $totalOrders ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk dibatalkan</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Rata-rata / Transaksi</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Rekap Status Pesanan --}}
    @php
        $byStatus       = $byStatus ?? collect();
        $statusConfig   = [
            'completed' => ['label' => 'Selesai',   'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'],
            'ready'     => ['label' => 'Siap',       'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>'],
            'cooking'   => ['label' => 'Dimasak',    'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#92400e',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 11h.01M11 15h.01M16 16h.01M10 11.5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M9 3H5a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-4z"/><path d="M3 11v4a6 6 0 0 0 12 0v-4"/><line x1="9" y1="21" x2="15" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>'],
            'pending'   => ['label' => 'Pending',    'bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#9a3412',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'],
            'cancelled' => ['label' => 'Dibatalkan', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#991b1b',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'],
        ];
        $totalAllOrders = collect($byStatus)->sum('count');
        $statusMap      = collect($byStatus)->keyBy('status');
        $cancelledRow   = $statusMap->get('cancelled');
    @endphp

    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-semibold text-gray-800 text-sm">Rekap Status Pesanan</h2>
                <p class="text-xs text-gray-400 mt-0.5">Total semua pesanan: <strong class="text-gray-600">{{ $totalAllOrders }}</strong></p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach(['completed', 'ready', 'cooking', 'pending', 'cancelled'] as $statusKey)
                @php $s = $statusMap->get($statusKey); $cfg = $statusConfig[$statusKey]; @endphp
                <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] }};color:{{ $cfg['text'] }};border-radius:10px;padding:14px;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                        {!! $cfg['icon'] !!}
                        <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">{{ $cfg['label'] }}</p>
                    </div>
                    <p style="font-size:26px;font-weight:700;line-height:1;">{{ $s ? $s->count : 0 }}</p>
                    <p style="font-size:11px;margin-top:4px;opacity:0.7;">Rp {{ number_format($s ? $s->total : 0, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
        @if($cancelledRow && $cancelledRow->count > 0)
        <div class="mt-4 flex items-center gap-2 px-3 py-2 rounded-lg text-xs" style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span><strong>{{ $cancelledRow->count }} pesanan dibatalkan</strong> senilai Rp {{ number_format($cancelledRow->total, 0, ',', '.') }} tidak dihitung dalam total penjualan.</span>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Per Metode Pembayaran --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <h2 class="font-semibold text-gray-800 text-sm">Per Metode Pembayaran</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4">Hanya transaksi <span class="text-green-600 font-medium">Lunas</span></p>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <h2 class="font-semibold text-gray-800 text-sm">Per Tipe Pesanan</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4">Tidak termasuk pesanan <span class="text-red-500 font-medium">dibatalkan</span></p>
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
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <h2 class="font-semibold text-gray-800 text-sm">Tren Harian</h2>
        </div>
        <p class="text-xs text-gray-400 mb-4">Berdasarkan pembayaran lunas per hari</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium text-xs uppercase">Tanggal</th>
                        <th class="py-2 text-right text-gray-500 font-medium text-xs uppercase">Total Penjualan</th>
                        <th class="py-2 text-right text-gray-500 font-medium text-xs uppercase">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($dailyTrend ?? [] as $d)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-700">{{ \Carbon\Carbon::parse($d->date)->format('d M Y') }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $d->count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-8 text-center text-gray-400 text-sm">Tidak ada data untuk periode ini</td></tr>
                @endforelse
                </tbody>
                @if(($dailyTrend ?? collect())->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="py-2 font-semibold text-gray-700 text-xs uppercase">Total</td>
                        <td class="py-2 text-right font-bold text-gray-900">Rp {{ number_format(collect($dailyTrend)->sum('total'), 0, ',', '.') }}</td>
                        <td class="py-2 text-right font-semibold text-gray-700">{{ collect($dailyTrend)->sum('count') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection