@extends('layouts.app')
@section('title', 'Analitik Revenue')
@section('page-title', 'Analitik Revenue')

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
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                <select name="period" class="form-input w-auto">
                    @foreach(['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'] as $val => $label)
                        <option value="{{ $val }}" {{ $period === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
            <a href="{{ route('manager.reports.revenue') }}" class="btn-secondary">Reset</a>

            {{-- Tombol Download & Kirim --}}
            <div class="ml-auto">
                <x-manager.reports.actions
                    type="revenue"
                    :from="$from->format('Y-m-d')"
                    :to="$to->format('Y-m-d')"
                    :period="$period"
                />
            </div>
        </form>
    </div>

    {{-- Summary --}}
    @php
        $rows       = $data['data']        ?? collect();
        $totalRev   = $data['total']       ?? 0;
        $periodType = $data['period_type'] ?? $period;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalRev, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Jumlah Periode</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $rows->count() }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Rata-rata per Periode</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                Rp {{ $rows->count() > 0 ? number_format($totalRev / $rows->count(), 0, ',', '.') : '0' }}
            </p>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-4">
            Data Revenue
            <span class="text-xs font-normal text-gray-400 ml-2">
                ({{ ['daily'=>'Harian','weekly'=>'Mingguan','monthly'=>'Bulanan','yearly'=>'Tahunan'][$periodType] ?? $periodType }})
            </span>
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-2 text-left text-gray-500 font-medium">Periode</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Total Revenue</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-700">{{ $row->period ?? '-' }}</td>
                        {{-- ReportService returns 'revenue' column, not 'total' --}}
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($row->revenue ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $row->count ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-400">Tidak ada data untuk periode ini</td>
                    </tr>
                @endforelse
                </tbody>
                @if($rows->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="py-2 font-semibold text-gray-700">Total</td>
                        <td class="py-2 text-right font-bold text-gray-900">Rp {{ number_format($totalRev, 0, ',', '.') }}</td>
                        <td class="py-2 text-right font-semibold text-gray-700">{{ $rows->sum('count') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection