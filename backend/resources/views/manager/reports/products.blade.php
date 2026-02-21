@extends('layouts.app')
@section('title', 'Laporan Produk')

@section('content')
<div class="space-y-6">
    <h1 class="page-title">Laporan Produk</h1>

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input w-auto"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input w-auto"></div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manager.reports.products') }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">🔥 Top Produk (Qty)</h2>
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100"><th class="py-2 text-left text-gray-500">Produk</th><th class="py-2 text-right text-gray-500">Terjual</th><th class="py-2 text-right text-gray-500">Pendapatan</th></tr></thead>
                <tbody>
                @forelse($topByQty ?? [] as $p)
                    <tr class="border-b border-gray-50"><td class="py-2">{{ $p->product_name }}</td><td class="py-2 text-right font-semibold">{{ $p->total_qty }}</td><td class="py-2 text-right">Rp {{ number_format($p->total_revenue,0,',','.') }}</td></tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">💰 Top Produk (Revenue)</h2>
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100"><th class="py-2 text-left text-gray-500">Produk</th><th class="py-2 text-right text-gray-500">Pendapatan</th><th class="py-2 text-right text-gray-500">Qty</th></tr></thead>
                <tbody>
                @forelse($topByRevenue ?? [] as $p)
                    <tr class="border-b border-gray-50"><td class="py-2">{{ $p->product_name }}</td><td class="py-2 text-right font-semibold">Rp {{ number_format($p->total_revenue,0,',','.') }}</td><td class="py-2 text-right">{{ $p->total_qty }}</td></tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection