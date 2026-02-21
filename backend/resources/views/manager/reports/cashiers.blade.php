@extends('layouts.app')
@section('title', 'Laporan Kasir')

@section('content')
<div class="space-y-6">
    <h1 class="page-title">Laporan Performa Kasir</h1>

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input w-auto"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input w-auto"></div>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
    </div>

    <div class="card">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-100">
                <th class="py-2 text-left text-gray-500">Nama Kasir</th>
                <th class="py-2 text-right text-gray-500">Total Pesanan</th>
                <th class="py-2 text-right text-gray-500">Total Penjualan</th>
                <th class="py-2 text-right text-gray-500">Rata-rata/Pesanan</th>
            </tr></thead>
            <tbody>
            @forelse($data ?? [] as $row)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-2 font-medium text-gray-900">{{ $row->cashier_name ?? '-' }}</td>
                    <td class="py-2 text-right">{{ $row->total_orders ?? 0 }}</td>
                    <td class="py-2 text-right font-semibold">Rp {{ number_format($row->total_sales ?? 0, 0, ',', '.') }}</td>
                    <td class="py-2 text-right">Rp {{ number_format($row->avg_order ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-6 text-center text-gray-400">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection