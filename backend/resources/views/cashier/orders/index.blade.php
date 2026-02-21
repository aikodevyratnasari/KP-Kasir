@extends('layouts.app')
@section('title', 'Daftar Pesanan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="page-title">Pesanan</h1>
        <a href="{{ route('cashier.orders.create') }}" class="btn-primary">+ Pesanan Baru</a>
    </div>

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. pesanan..." class="form-input w-40"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="form-input w-auto">
                    <option value="">Semua Status</option>
                    @foreach(['pending','cooking','ready','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                <select name="order_type" class="form-input w-auto">
                    <option value="">Semua Tipe</option>
                    <option value="dine_in" {{ request('order_type')==='dine_in'?'selected':'' }}>Dine-In</option>
                    <option value="takeaway" {{ request('order_type')==='takeaway'?'selected':'' }}>Takeaway</option>
                </select></div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('cashier.orders.index') }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card p-0">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">No. Pesanan</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Meja</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 font-medium text-indigo-600">
                        <a href="{{ route('cashier.orders.show', $order) }}" class="hover:underline">{{ $order->order_number }}</a>
                    </td>
                    <td class="py-3 px-4 text-gray-600 capitalize">{{ str_replace('_','-',$order->order_type) }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $order->table?->number ?? '-' }}</td>
                    <td class="py-3 px-4"><span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="py-3 px-4 text-right font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</td>
                    <td class="py-3 px-4 text-right text-gray-400 text-xs">{{ $order->created_at->format('H:i') }}</td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('cashier.orders.show', $order) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                            @if($order->status === 'pending')
                                <a href="{{ route('cashier.orders.edit', $order) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                            @endif
                            @if(in_array($order->status, ['pending','cooking','ready']) && !$order->isFullyPaid())
                                <a href="{{ route('cashier.payments.create', $order) }}" class="text-xs text-green-600 hover:underline font-semibold">Bayar</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada pesanan</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>
</div>
@endsection