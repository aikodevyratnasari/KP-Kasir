@extends('layouts.app')
@section('title', 'Analitik Revenue')

@section('content')
<div class="space-y-6">
    <h1 class="page-title">Analitik Revenue</h1>

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input w-auto"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input w-auto"></div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                <select name="period" class="form-input w-auto">
                    @foreach(['daily'=>'Harian','weekly'=>'Mingguan','monthly'=>'Bulanan','yearly'=>'Tahunan'] as $val=>$label)
                        <option value="{{ $val }}" {{ ($period??'daily')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
        </form>
    </div>

    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-4">Data Revenue</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100">
                    <th class="py-2 text-left text-gray-500">Periode</th>
                    <th class="py-2 text-right text-gray-500">Total Revenue</th>
                    <th class="py-2 text-right text-gray-500">Jumlah Transaksi</th>
                </tr></thead>
                <tbody>
                @forelse($data ?? [] as $row)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 text-gray-700">{{ $row->period ?? $row->date ?? '-' }}</td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($row->total,0,',','.') }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $row->count ?? '-' }}</td>
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