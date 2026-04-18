@extends('layouts.app')
@section('title', 'Laporan Kasir')
@section('page-title', 'Laporan Kasir')

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
            <a href="{{ route('manager.reports.cashiers') }}" class="btn-secondary">Reset</a>

            <div class="ml-auto">
                <x-manager.reports.actions
                    type="cashiers"
                    :from="$from->format('Y-m-d')"
                    :to="$to->format('Y-m-d')"
                />
            </div>
        </form>
    </div>

    {{-- Summary --}}
    @php
        $cashiers            = $data['cashiers']              ?? collect();
        $totalSales          = $data['total_sales']           ?? 0;
        $best                = $data['best']                  ?? null;
        $cancelledPerCashier = $data['cancelled_per_cashier'] ?? collect();
        $pendingPerCashier   = $data['pending_per_cashier']   ?? collect();

        $totalCancelled = $cancelledPerCashier->sum('cancelled_count');
        $totalPending   = $pendingPerCashier->sum('pending_count');
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Penjualan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk dibatalkan</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Jumlah Kasir Aktif</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $cashiers->count() }}</p>
        </div>
        <div class="card" style="border-left:3px solid #f87171;">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Dibatalkan</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $totalCancelled }}</p>
            <p class="text-xs text-gray-400 mt-1">Pesanan yang dibatalkan</p>
        </div>
        <div class="card" style="border-left:3px solid #fb923c;">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Pending</p>
            <p class="text-2xl font-bold text-orange-500 mt-1">{{ $totalPending }}</p>
            <p class="text-xs text-gray-400 mt-1">Belum diproses</p>
        </div>
    </div>

    @if($best)
    <div class="card" style="border-left:3px solid #6366f1;">
        <p class="text-xs text-gray-500 uppercase font-medium mb-1">🏆 Kasir Terbaik Periode Ini</p>
        <p class="text-xl font-bold text-gray-900">{{ $best->cashier }}</p>
        <p class="text-sm text-gray-500 mt-0.5">
            Rp {{ number_format($best->total_sales, 0, ',', '.') }} &nbsp;·&nbsp;
            {{ $best->order_count }} pesanan &nbsp;·&nbsp;
            Avg {{ $best->avg_processing_min ? number_format($best->avg_processing_min, 1) . ' mnt' : '-' }}
        </p>
    </div>
    @endif

    {{-- Tabel Performa --}}
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-1">Performa per Kasir</h2>
        <p class="text-xs text-gray-400 mb-4">Kolom <span class="text-red-500 font-medium">Dibatalkan</span> dan <span class="text-orange-500 font-medium">Pending</span> ditampilkan untuk transparansi penuh.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:750px;">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium">Nama Kasir</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Pesanan Aktif</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Total Penjualan</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Rata-rata/Pesanan</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Avg Proses (mnt)</th>
                        <th class="py-2 text-right font-medium" style="color:#ef4444;">Dibatalkan</th>
                        <th class="py-2 text-right font-medium" style="color:#f97316;">Pending</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cashiers as $row)
                    @php
                        $cancelled = $cancelledPerCashier->get($row->cashier_id);
                        $pending   = $pendingPerCashier->get($row->cashier_id);
                    @endphp
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 font-medium text-gray-900">{{ $row->cashier ?? '-' }}</td>
                        <td class="py-2 text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($row->total_sales ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($row->avg_order_value ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-gray-500">
                            {{ $row->avg_processing_min ? number_format($row->avg_processing_min, 1) : '-' }}
                        </td>
                        <td class="py-2 text-right">
                            @if($cancelled && $cancelled->cancelled_count > 0)
                                <span style="color:#ef4444; font-weight:600;">{{ $cancelled->cancelled_count }}</span>
                                <span class="text-xs text-gray-400 block">Rp {{ number_format($cancelled->cancelled_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            @if($pending && $pending->pending_count > 0)
                                <span style="color:#f97316; font-weight:600;">{{ $pending->pending_count }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-400">Tidak ada data untuk periode ini</td>
                    </tr>
                @endforelse
                </tbody>
                @if($cashiers->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="py-2 font-semibold text-gray-700">Total</td>
                        <td class="py-2 text-right font-semibold">{{ $cashiers->sum('order_count') }}</td>
                        <td class="py-2 text-right font-bold text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                        <td class="py-2"></td>
                        <td class="py-2"></td>
                        <td class="py-2 text-right font-semibold" style="color:#ef4444;">{{ $totalCancelled ?: '—' }}</td>
                        <td class="py-2 text-right font-semibold" style="color:#f97316;">{{ $totalPending ?: '—' }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection