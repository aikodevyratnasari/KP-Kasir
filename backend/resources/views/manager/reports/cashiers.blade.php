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
            <a href="{{ route('manager.reports.cashiers') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                    <path d="M3 3v6h6"/>
                </svg>
                Reset
            </a>
            <div class="ml-auto">
                <x-manager.reports.actions type="cashiers" :from="$from->format('Y-m-d')" :to="$to->format('Y-m-d')" />
            </div>
        </form>
    </div>

    @php
        $cashiers            = $data['cashiers']              ?? collect();
        $totalSales          = $data['total_sales']           ?? 0;
        $best                = $data['best']                  ?? null;
        $cancelledPerCashier = $data['cancelled_per_cashier'] ?? collect();
        $pendingPerCashier   = $data['pending_per_cashier']   ?? collect();
        $totalCancelled      = $cancelledPerCashier->sum('cancelled_count');
        $totalPending        = $pendingPerCashier->sum('pending_count');
    @endphp

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Penjualan</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk dibatalkan</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Kasir Aktif</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $cashiers->count() }}</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Dibatalkan</p>
            </div>
            <p class="text-2xl font-bold {{ $totalCancelled > 0 ? 'text-red-500' : 'text-gray-300' }}">{{ $totalCancelled }}</p>
            <p class="text-xs text-gray-400 mt-1">Pesanan dibatalkan</p>
        </div>
        <div class="card" style="{{ $totalPending > 0 ? 'border-left:3px solid #fb923c;' : '' }}">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Pending</p>
            </div>
            <p class="text-2xl font-bold {{ $totalPending > 0 ? 'text-orange-500' : 'text-gray-300' }}">{{ $totalPending }}</p>
            <p class="text-xs text-gray-400 mt-1">Belum diproses</p>
        </div>
    </div>

    {{-- Kasir Terbaik --}}
    @if($best)
    <div class="card">
        <div class="flex items-center gap-3">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-0.5">Kasir Terbaik Periode Ini</p>
                <p class="text-lg font-bold text-gray-900">{{ $best->cashier }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Rp {{ number_format($best->total_sales, 0, ',', '.') }}
                    &nbsp;·&nbsp; {{ $best->order_count }} pesanan
                    &nbsp;·&nbsp; Avg {{ $best->avg_processing_min ? number_format($best->avg_processing_min, 1) . ' mnt' : '-' }}
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel --}}
    <div class="card">
        <div class="mb-3">
            <h2 class="font-semibold text-gray-800 text-sm">Performa per Kasir</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                Kolom
                <span class="font-medium" style="color:#ef4444;">Dibatalkan</span> dan
                <span class="font-medium" style="color:#f97316;">Pending</span>
                ditampilkan untuk transparansi penuh.
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:780px;">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="py-2.5 px-3 text-left text-xs font-semibold text-gray-500 uppercase">Kasir</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold text-gray-500 uppercase">Pesanan</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Penjualan</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold text-gray-500 uppercase">Rata-rata</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold text-gray-500 uppercase">Avg Proses</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold uppercase text-gray-500">Dibatalkan</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold uppercase text-gray-500">Pending</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cashiers as $row)
                    @php
                        $cancelled = $cancelledPerCashier->get($row->cashier_id);
                        $pending   = $pendingPerCashier->get($row->cashier_id);
                    @endphp
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2.5 px-3 font-medium text-gray-900">{{ $row->cashier ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold">Rp {{ number_format($row->total_sales ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-gray-600">Rp {{ number_format($row->avg_order_value ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-gray-500">
                            {{ $row->avg_processing_min ? number_format($row->avg_processing_min, 1) . ' mnt' : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            @if($cancelled && $cancelled->cancelled_count > 0)
                                <span style="color:#ef4444;font-weight:600;">{{ $cancelled->cancelled_count }}</span>
                                <span class="text-xs text-gray-400 block">Rp {{ number_format($cancelled->cancelled_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            @if($pending && $pending->pending_count > 0)
                                <span style="color:#f97316;font-weight:600;">{{ $pending->pending_count }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400 text-sm">Tidak ada data untuk periode ini</td>
                    </tr>
                @endforelse
                </tbody>
                @if($cashiers->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="py-2.5 px-3 font-semibold text-gray-700 text-xs uppercase">Total</td>
                        <td class="py-2.5 px-3 text-right font-semibold">{{ $cashiers->sum('order_count') }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3"></td>
                        <td class="py-2.5 px-3"></td>
                        <td class="py-2.5 px-3 text-right font-semibold" style="color:#ef4444;">{{ $totalCancelled ?: '—' }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold" style="color:#f97316;">{{ $totalPending ?: '—' }}</td>
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