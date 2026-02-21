@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="page-title">Laporan Penjualan</h1>
    </div>

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
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Penjualan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Pesanan</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalOrders ?? 0 }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Rata-rata per Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- By Payment Method --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Per Metode Pembayaran</h2>
            @forelse($byPaymentMethod ?? [] as $m)
                <div class="flex justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm capitalize text-gray-700">{{ $m->payment_method }}</span>
                    <div class="text-right">
                        <span class="text-sm font-semibold block">Rp {{ number_format($m->total,0,',','.') }}</span>
                        <span class="text-xs text-gray-400">{{ $m->count }} transaksi</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>

        {{-- By Order Type --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Per Tipe Pesanan</h2>
            @forelse($byOrderType ?? [] as $t)
                <div class="flex justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm capitalize text-gray-700">{{ str_replace('_',' ',$t->order_type) }}</span>
                    <div class="text-right">
                        <span class="text-sm font-semibold block">Rp {{ number_format($t->total,0,',','.') }}</span>
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
        <h2 class="font-semibold text-gray-800 mb-4">Tren Harian</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100">
                    <th class="py-2 text-left text-gray-500 font-medium">Tanggal</th>
                    <th class="py-2 text-right text-gray-500 font-medium">Total Penjualan</th>
                    <th class="py-2 text-right text-gray-500 font-medium">Jumlah Transaksi</th>
                </tr></thead>
                <tbody>
                @forelse($dailyTrend ?? [] as $d)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-700">{{ \Carbon\Carbon::parse($d->date)->format('d M Y') }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($d->total,0,',','.') }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $d->count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Tidak ada data untuk periode ini</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection