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
            <a href="{{ route('manager.reports.products') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                    <path d="M3 3v6h6"/>
                </svg>
                Reset
            </a>
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
                <div style="width:36px;height:36px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Jenis Produk Terjual</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $totalItems }}</p>
            <p class="text-xs text-gray-400 mt-1">Tidak termasuk pesanan dibatalkan</p>
        </div>

        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                    <div style="width:36px;height:36px;border-radius:50%;background:#fef9c3;display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="6"/>
                            <path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/>
                        </svg>
                    </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Produk Terlaris</p>
            </div>
            <p class="font-bold text-gray-900 leading-tight" style="font-size:1.1rem; margin-top:20px;">
    {{ $topByQty->first()->product_name ?? '-' }}
</p>
@if($topByQty->first())
    <p class="text-xs text-gray-400" style="margin-top:4px;">
        {{ $topByQty->first()->total_qty }} terjual
    </p>
@endif
        </div>

        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
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
    <div class="card">
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <h3 class="font-semibold text-gray-800 text-sm mb-0.1">Produk dalam Pesanan Dibatalkan</h3>
                <p class="text-xs text-gray-600 mb-3">
                    <strong class="text-red-600">{{ $cancelledSummary->count }} pesanan dibatalkan</strong>
                    senilai <strong class="text-red-600">Rp {{ number_format($cancelledSummary->total_amount, 0, ',', '.') }}</strong>.
                    Produk berikut terdampak dan tidak dihitung dalam data terjual
                </p>
                @if($cancelledItems->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:1px solid #fecaca;">
                                <th class="py-1.5 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                                <th class="py-1.5 text-center text-xs font-semibold text-gray-500 uppercase">Qty Batal</th>
                                <th class="py-1.5 text-right text-xs font-semibold text-gray-500 uppercase">Nilai Hilang</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($cancelledItems->take(10) as $ci)
                            <tr style="border-bottom:1px solid #fef2f2;">
                                <td class="py-1.5 text-gray-700">{{ $ci->product_name }}</td>
                                <td class="py-1.5 text-center font-semibold" style="color:#ef4444;">{{ $ci->total_qty }}</td>
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
                <h2 class="font-semibold text-gray-800 text-sm">Top Produk berdasarkan Qty</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4 border-b border-gray-200 pb-3">Hanya dari pesanan yang tidak dibatalkan</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="py-2 text-center text-xs font-semibold text-gray-500 uppercase">Terjual</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($topByQty as $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-800">{{ $p->product_name }}</td>
                        <td class="py-2 text-center font-semibold">{{ $p->total_qty }}</td>
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
                <h2 class="font-semibold text-gray-800 text-sm">Top Produk berdasarkan Revenue</h2>
            </div>
            <p class="text-xs text-gray-400 mb-4 border-b border-gray-200 pb-3">Hanya dari pesanan yang tidak dibatalkan</p>
            <table class="w-full text-sm">
                <thead>
                    
                        <tr class="border-b border-gray-100">
                        <th class="py-2 text-xs font-semibold text-gray-500 uppercase" style="text-align:left; width:50%;">Produk</th>
                        <th class="py-2 text-xs font-semibold text-gray-500 uppercase" style="text-align:center; width:35%;">Pendapatan</th>
                        <th class="py-2 text-xs font-semibold text-gray-500 uppercase" style="text-align:right; width:15%;">Qty</th>
                    </tr>
                    
                </thead>
                <tbody>
                @forelse($topByRevenue as $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-800" style="width:50%;">{{ $p->product_name }}</td>
                        <td class="py-2 font-semibold" style="text-align:center; width:35%;">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                        <td class="py-2 text-gray-600" style="text-align:right; width:15%;">{{ $p->total_qty }}</td>
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
        <div class="flex items-center gap-2 mb-4 border-b border-gray-200 pb-3">
            <h2 class="font-semibold text-gray-800 text-sm">Per Kategori</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                        <th class="py-2 text-center text-xs font-semibold text-gray-500 uppercase">Total Qty</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-500 uppercase">Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($byCategory as $category => $stat)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-700">{{ $category }}</td>
                        <td class="py-2 text-center">{{ $stat['qty'] }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($stat['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    <a href="{{ route('manager.reports.index') }}"
        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
        style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
        onmouseover="this.style.backgroundColor='#f3f4f6';"
        onmouseout="this.style.backgroundColor='white';">
        Kembali
    </a>
</div>
@endsection