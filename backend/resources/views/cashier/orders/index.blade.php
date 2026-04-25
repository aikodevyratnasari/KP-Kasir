@extends('layouts.app')
@section('title', 'Daftar Pesanan')
@section('page-title', 'Pesanan')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <div class="card overflow-x-auto">
        <form method="GET" class="flex gap-3 items-end min-w-max">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nomor order / pelanggan..." class="form-input w-48">
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
            <a href="{{ route('cashier.orders.index') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                    <path d="M3 3v6h6"/>
                </svg>
                Reset
            </a>

            <div class="ml-auto flex items-center gap-1.5 text-xs text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span id="poll-status">Live</span>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card p-0 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Meja</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Kasir</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Bayar</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase whitespace-nowrap pr-8">Total</th>
                   <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($orders as $order)
                @php
                    // Eager-loaded dari controller: ->with('cashier', 'table')
                    // Untuk kolom Bayar, kita butuh payments — pastikan controller juga eager-load payments
                    // atau gunakan lazy load di sini. Jika sudah di-eager-load, tidak ada N+1.
                    $payments      = $order->relationLoaded('payments') ? $order->payments : $order->payments;
                    $hasRefund     = $payments->contains('status', 'refunded');
                    $hasPaid       = $payments->contains('status', 'paid');
                    $isFullyPaid   = $order->isFullyPaid();
                @endphp
                <tr id="order-row-{{ $order->id }}" data-order-id="{{ $order->id }}"
                    class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4 whitespace-nowrap">
                        <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $order->created_at->format('d M, H:i') }}</p>
                    </td>
                    <td class="py-3 px-4 text-gray-700 text-xs">
                        {{ $order->customer_name ?? '—' }}
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

                    {{--
                        Prioritas badge Bayar:
                        1. Refund  — ada payment berstatus 'refunded' (pesanan dibatalkan setelah lunas)
                        2. Lunas   — remainingBalance() <= 0 (tidak ada refund)
                        3. Sebagian — ada payment 'paid' tapi belum lunas penuh
                        4. Belum   — belum ada pembayaran sama sekali
                    --}}
                    <td class="py-3 px-4 text-center">
                        <span class="order-paid-badge">
                            @if($hasRefund)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">Refund</span>
                            @elseif($isFullyPaid)
                                <span class="badge badge-completed">Lunas</span>
                            @elseif($hasPaid)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Sebagian</span>
                            @else
                                <span class="badge badge-pending_payment">Belum</span>
                            @endif
                        </span>
                    </td>

                    <td class="py-3 px-4 text-right font-semibold whitespace-nowrap align-middle">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-center align-middle">
                        <a href="{{ route('cashier.orders.show', $order) }}"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-all"
                        style="color: #6b7280; background-color: #f3f4f6; border: 1px solid #e5e7eb;"
                        onmouseover="this.style.backgroundColor='#e5e7eb'; this.style.color='#111827';"
                        onmouseout="this.style.backgroundColor='#f3f4f6'; this.style.color='#6b7280';"
                        onmousedown="this.style.transform='scale(0.95)';"
                        onmouseup="this.style.transform='scale(1)';"
                        title="Detail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="5" cy="12" r="2"/>
                                <circle cx="12" cy="12" r="2"/>
                                <circle cx="19" cy="12" r="2"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-12 text-center text-gray-400">
                        <div class="flex justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6"/><path d="M9 16h4"/>
                            </svg>
                        </div>
                        <p>Belum ada pesanan</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
    <div class="pagination-custom">
        {{ $orders->links() }}
    </div>
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

    function renderPaidBadge(o) {
        if (o.has_refund) {
            return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">Refund</span>';
        }
        if (o.is_paid) {
            return '<span class="badge badge-completed">Lunas</span>';
        }
        if (o.has_partial) {
            return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Sebagian</span>';
        }
        return '<span class="badge badge-pending_payment">Belum</span>';
    }

    async function pollOrders() {
        try {
            const res  = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            data.forEach(o => {
                const row = document.getElementById('order-row-' + o.id);
                if (!row) return;

                const statusBadge = row.querySelector('.order-status-badge');
                if (statusBadge) {
                    statusBadge.classList.remove(...Object.values(statusClass));
                    statusBadge.classList.add(statusClass[o.status] || 'badge-pending');
                    statusBadge.textContent = o.status.charAt(0).toUpperCase() + o.status.slice(1);
                }

                const paidBadge = row.querySelector('.order-paid-badge');
                if (paidBadge) {
                    paidBadge.innerHTML = renderPaidBadge(o);
                }
            });

            document.getElementById('poll-status').textContent =
                'Live · ' + new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

        } catch (e) {
            document.getElementById('poll-status').textContent = 'Offline';
        }
    }

    setInterval(pollOrders, INTERVAL);
})();
</script>
@endpush