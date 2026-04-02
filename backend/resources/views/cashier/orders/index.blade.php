@extends('layouts.app')
@section('title', 'Daftar Pesanan')
@section('page-title', 'Pesanan')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nomor order..." class="form-input w-44">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="form-input w-36">
                    <option value="">Semua</option>
                    @foreach(['pending','cooking','ready','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                <select name="order_type" class="form-input w-36">
                    <option value="">Semua</option>
                    <option value="dine_in"  {{ request('order_type') === 'dine_in'  ? 'selected' : '' }}>Dine-In</option>
                    <option value="takeaway" {{ request('order_type') === 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
            </div>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('cashier.orders.index') }}" class="btn-secondary text-sm">Reset</a>

            {{-- Live indicator --}}
            <div class="ml-auto flex items-center gap-1.5 text-xs text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span id="poll-status">Live</span>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Meja</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Kasir</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Bayar</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($orders as $order)
                <tr id="order-row-{{ $order->id }}" data-order-id="{{ $order->id }}"
                    class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4">
                        <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $order->created_at->format('d M, H:i') }}</p>
                    </td>
                    <td class="py-3 px-4 capitalize text-gray-600 text-xs">
                        {{ str_replace('_',' ', $order->order_type) }}
                    </td>
                    <td class="py-3 px-4 text-gray-600">
                        {{ $order->table?->number ?? '—' }}
                    </td>
                    <td class="py-3 px-4 text-gray-600 text-xs">
                        {{ $order->cashier?->name ?? '—' }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="order-status-badge badge badge-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="order-paid-badge">
                            @if($order->isFullyPaid())
                                <span class="badge badge-completed">Lunas</span>
                            @else
                                <span class="badge badge-pending_payment">Belum</span>
                            @endif
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right font-semibold">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-right">
    <a href="{{ route('cashier.orders.show', $order) }}"
       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
       style="color: white; background-color: #16a34a; border: 1px solid #16a34a;"
       onmouseover="this.style.backgroundColor='#15803d'; this.style.borderColor='#15803d';"
       onmouseout="this.style.backgroundColor='#16a34a'; this.style.borderColor='#16a34a';"
       onmousedown="this.style.backgroundColor='#166534'; this.style.transform='scale(0.98)';"
       onmouseup="this.style.backgroundColor='#15803d'; this.style.transform='scale(1)';">
        Detail
    </a>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-400">
                        <p class="text-3xl mb-2">📋</p>
                        <p>Belum ada pesanan</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function() {
    const INTERVAL = 10000;
    const POLL_URL = '{{ route('poll.orders') }}';

    const statusClass = {
        pending:   'badge-pending',
        cooking:   'badge-cooking',
        ready:     'badge-ready',
        completed: 'badge-completed',
        cancelled: 'badge-cancelled',
    };

    async function pollOrders() {
        try {
            const res  = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            data.forEach(o => {
                const row = document.getElementById('order-row-' + o.id);
                if (!row) return;

                // Update status badge
                const statusBadge = row.querySelector('.order-status-badge');
                if (statusBadge) {
                    const allBadge = Object.values(statusClass);
                    statusBadge.classList.remove(...allBadge);
                    statusBadge.classList.add(statusClass[o.status] || 'badge-pending');
                    statusBadge.textContent = o.status.charAt(0).toUpperCase() + o.status.slice(1);
                }

                // Update payment badge
                const paidBadge = row.querySelector('.order-paid-badge');
                if (paidBadge) {
                    paidBadge.innerHTML = o.is_paid
                        ? '<span class="badge badge-completed">Lunas</span>'
                        : '<span class="badge badge-pending_payment">Belum</span>';
                }
            });

            document.getElementById('poll-status').textContent = 'Live · ' + new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'});

        } catch (e) {
            document.getElementById('poll-status').textContent = 'Offline';
        }
    }

    setInterval(pollOrders, INTERVAL);
})();
</script>
@endpush