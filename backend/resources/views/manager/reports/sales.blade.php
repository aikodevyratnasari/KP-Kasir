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
                <x-manager.reports.actions
                    type="sales"
                    :from="$from->format('Y-m-d')"
                    :to="$to->format('Y-m-d')"
                />
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Penjualan (Lunas)</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Berdasarkan pembayaran lunas</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Pesanan Aktif</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalOrders ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk dibatalkan</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Rata-rata per Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Rekap Status Pesanan (semua status termasuk cancelled & pending) --}}
    @php
        $byStatus = $byStatus ?? collect();

        $statusConfig = [
            'completed' => ['label' => 'Selesai',   'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534', 'icon' => '✓'],
            'ready'     => ['label' => 'Siap',       'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8', 'icon' => '🔔'],
            'cooking'   => ['label' => 'Dimasak',    'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#92400e', 'icon' => '🍳'],
            'pending'   => ['label' => 'Pending',    'bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#9a3412', 'icon' => '⏳'],
            'cancelled' => ['label' => 'Dibatalkan', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#991b1b', 'icon' => '✕'],
        ];
        $totalAllOrders = collect($byStatus)->sum('count');
    @endphp

    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-semibold text-gray-800">Rekap Status Pesanan</h2>
                <p class="text-xs text-gray-400 mt-0.5">Semua pesanan dalam periode ini: <strong class="text-gray-600">{{ $totalAllOrders }}</strong></p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                // Urutkan: completed → ready → cooking → pending → cancelled
                $orderedStatuses = ['completed', 'ready', 'cooking', 'pending', 'cancelled'];
                $statusMap = collect($byStatus)->keyBy('status');
            @endphp
            @foreach($orderedStatuses as $statusKey)
                @php
                    $s   = $statusMap->get($statusKey);
                    $cfg = $statusConfig[$statusKey];
                @endphp
                <div style="background:{{ $cfg['bg'] }}; border:1px solid {{ $cfg['border'] }}; color:{{ $cfg['text'] }}; border-radius:10px; padding:14px;">
                    <p style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; opacity:0.8;">
                        {{ $cfg['icon'] }} {{ $cfg['label'] }}
                    </p>
                    <p style="font-size:26px; font-weight:700; margin-top:4px; line-height:1;">
                        {{ $s ? $s->count : 0 }}
                    </p>
                    <p style="font-size:11px; margin-top:4px; opacity:0.7;">
                        Rp {{ number_format($s ? $s->total : 0, 0, ',', '.') }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Catatan untuk cancelled --}}
        @php $cancelledRow = $statusMap->get('cancelled'); @endphp
        @if($cancelledRow && $cancelledRow->count > 0)
            <div class="mt-4 px-3 py-2 rounded-lg text-xs" style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b;">
                ⚠ <strong>{{ $cancelledRow->count }} pesanan dibatalkan</strong> senilai
                Rp {{ number_format($cancelledRow->total, 0, ',', '.') }} tidak dihitung dalam total penjualan.
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- By Payment Method --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-1">Per Metode Pembayaran</h2>
            <p class="text-xs text-gray-400 mb-4">Hanya transaksi berstatus <span class="font-medium text-green-600">Lunas</span></p>
            @forelse($byPaymentMethod ?? [] as $m)
                <div class="flex justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm capitalize text-gray-700">{{ $m->payment_method }}</span>
                    <div class="text-right">
                        <span class="text-sm font-semibold block">Rp {{ number_format($m->total, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">{{ $m->count }} transaksi</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>

        {{-- By Order Type --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-1">Per Tipe Pesanan</h2>
            <p class="text-xs text-gray-400 mb-4">Tidak termasuk pesanan <span class="font-medium text-red-500">dibatalkan</span></p>
            @forelse($byOrderType ?? [] as $t)
                <div class="flex justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm capitalize text-gray-700">{{ str_replace('_', ' ', $t->order_type) }}</span>
                    <div class="text-right">
                        <span class="text-sm font-semibold block">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">{{ $t->count }} pesanan</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    {{-- Daily Trend --}}
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-1">Tren Harian</h2>
        <p class="text-xs text-gray-400 mb-4">Berdasarkan pembayaran lunas per hari</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium">Tanggal</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Total Penjualan</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Jumlah Transaksi</th>
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
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-400">Tidak ada data untuk periode ini</td>
                    </tr>
                @endforelse
                </tbody>
                @if(($dailyTrend ?? collect())->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="py-2 font-semibold text-gray-700">Total</td>
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