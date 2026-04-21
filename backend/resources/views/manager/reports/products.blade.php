@extends('layouts.app')
@section('title', 'Laporan Produk')
@section('page-title', 'Laporan Produk')

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
            <a href="{{ route('manager.reports.products') }}" class="btn-secondary">Reset</a>
            <div class="ml-auto">
                <x-manager.reports.actions type="products" :from="$from->format('Y-m-d')" :to="$to->format('Y-m-d')" />
            </div>
        </form>
    </div>

    @php
        $topByQty        = $top_by_qty       ?? collect();
        $topByRevenue    = $top_by_revenue   ?? collect();
        $byCategory      = $by_category      ?? collect();
        $totalItems      = $total_items      ?? 0;
        $cancelledSummary= $cancelled_summary ?? null;
        $cancelledItems  = $cancelled_items  ?? collect();
    @endphp

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Jenis Produk Terjual</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $totalItems }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk pesanan dibatalkan</p>
        </div>

        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fef9c3;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Produk Terlaris</p>
            </div>
            <p class="text-base font-bold text-gray-900 leading-tight">{{ $topByQty->first()->product_name ?? '-' }}</p>
            @if($topByQty->first())
                <p class="text-xs text-gray-400 mt-0.5">{{ $topByQty->first()->total_qty }} terjual</p>
            @endif
        </div>

        <div class="card" style="{{ ($cancelledSummary && $cancelledSummary->count > 0) ? 'border-left:3px solid #f87171;' : '' }}">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Pesanan Dibatalkan</p>
            </div>
            <p class="text-2xl font-bold {{ ($cancelledSummary && $cancelledSummary->count > 0) ? 'text-red-500' : 'text-gray-300' }}">
                {{ $cancelledSummary->count ?? 0 }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Rp {{ number_format($cancelledSummary->total_amount ?? 0, 0, ',', '.') }} potensi hilang</p>
        </div>
    </div>

    {{-- Ringkasan Pembatalan --}}
    @if($cancelledSummary && $cancelledSummary->count > 0)
    <div class="card" style="border-left:3px solid #f87171;">
        <div class="flex items-start gap-3">
            <div style="flex-shrink:0;margin-top:2px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-800 text-sm mb-1">Produk dalam Pesanan Dibatalkan</h3>
                <p class="text-xs text-gray-600 mb-3">
                    <strong class="text-red-600">{{ $cancelledSummary->count }} pesanan dibatalkan</strong>
                    senilai <strong class="text-red-600">Rp {{ number_format($cancelledSummary->total_amount, 0, ',', '.') }}</strong>.
                    Produk berikut terdampak dan tidak dihitung dalam data terjual.
                </p>
                @if($cancelledItems->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:1px solid #fecaca;">
                                <th class="py-1.5 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                                <th class="py-1.5 text-right text-xs font-semibold text-gray-500 uppercase">Qty Batal</th>
                                <th class="py-1.5 text-right text-xs font-semibold text-gray-500 uppercase">Nilai Hilang</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($cancelledItems->take(10) as $ci)
                            <tr style="border-bottom:1px solid #fef2f2;">
                                <td class="py-1.5 text-gray-700">{{ $ci->product_name }}</td>
                                <td class="py-1.5 text-right font-semibold" style="color:#ef4444;">{{ $ci->total_qty }}</td>
                                <td class="py-1.5 text-right font-semibold" style="color:#ef4444;">Rp {{ number_format($ci->total_lost, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top by Qty --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <h2 class="font-semibold text-gray-800 text-sm">Top Produk berdasarkan Qty</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4">Hanya dari pesanan yang tidak dibatalkan</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Terjual</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($topByQty as $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-800">{{ $p->product_name }}</td>
                        <td class="py-2 text-right font-semibold">{{ $p->total_qty }}</td>
                        <td class="py-2 text-right text-gray-600">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400 text-sm">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top by Revenue --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <h2 class="font-semibold text-gray-800 text-sm">Top Produk berdasarkan Revenue</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4">Hanya dari pesanan yang tidak dibatalkan</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Pendapatan</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($topByRevenue as $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-800">{{ $p->product_name }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $p->total_qty }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400 text-sm">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Per Kategori --}}
    @if($byCategory->count())
    <div class="card">
        <div class="flex items-center gap-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <h2 class="font-semibold text-gray-800 text-sm">Per Kategori</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Total Qty</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($byCategory as $category => $stat)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-700">{{ $category }}</td>
                        <td class="py-2 text-right">{{ $stat['qty'] }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($stat['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection