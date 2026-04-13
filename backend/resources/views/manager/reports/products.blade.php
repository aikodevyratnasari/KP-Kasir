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

            {{-- Tombol Download & Kirim --}}
            <div class="ml-auto">
                <x-manager.reports.actions
                    type="products"
                    :from="$from->format('Y-m-d')"
                    :to="$to->format('Y-m-d')"
                />
            </div>
        </form>
    </div>

    {{-- Summary --}}
    @php
        // productReport() mengembalikan array dengan key: top_by_qty, top_by_revenue, least_sold, by_category, total_items
        // Controller melakukan: return view('...', array_merge($data, compact('from','to')))
        // Sehingga semua key langsung tersedia sebagai variabel
        $topByQty     = $top_by_qty     ?? collect();
        $topByRevenue = $top_by_revenue ?? collect();
        $leastSold    = $least_sold     ?? collect();
        $byCategory   = $by_category    ?? collect();
        $totalItems   = $total_items    ?? 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Jumlah Produk Aktif</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalItems }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Produk Terlaris</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $topByQty->first()->product_name ?? '-' }}</p>
            @if($topByQty->first())
                <p class="text-xs text-gray-400 mt-0.5">{{ $topByQty->first()->total_qty }} terjual</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top by Qty --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">🔥 Top Produk (Qty)</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium">Produk</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Terjual</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Pendapatan</th>
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
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top by Revenue --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">💰 Top Produk (Revenue)</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium">Produk</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Pendapatan</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Qty</th>
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
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Per Kategori --}}
    @if($byCategory->count())
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-4">Per Kategori</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium">Kategori</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Total Qty</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Total Revenue</th>
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