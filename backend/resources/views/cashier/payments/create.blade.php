@extends('layouts.app')
@section('title', 'Proses Pembayaran')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="paymentForm({{ $order->remainingBalance() }})">
    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.orders.show', $order) }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="page-title">Proses Pembayaran</h1>
    </div>

    {{-- Ringkasan --}}
    <div class="card bg-indigo-50 border border-indigo-100">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-indigo-700 font-medium">{{ $order->order_number }}</p>
                <p class="text-xs text-indigo-500">{{ $order->items->count() }} item</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-indigo-500">Sisa Tagihan</p>
                <p class="text-2xl font-bold text-indigo-700">Rp {{ number_format($order->remainingBalance(),0,',','.') }}</p>
            </div>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('cashier.payments.store', $order) }}">
            @csrf

            {{-- Metode Pembayaran --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    {{-- Tunai --}}
                    <label class="flex flex-col items-center justify-center border-2 rounded-lg p-3 cursor-pointer transition-colors"
                           :class="method === 'cash' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="cash" x-model="method" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>
                        </svg>
                        <span class="text-xs font-medium">Tunai</span>
                    </label>

                    {{-- Kartu --}}
                    <label class="flex flex-col items-center justify-center border-2 rounded-lg p-3 cursor-pointer transition-colors"
                           :class="method === 'card' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="card" x-model="method" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        <span class="text-xs font-medium">Kartu</span>
                    </label>

                    {{-- E-Wallet --}}
                    <label class="flex flex-col items-center justify-center border-2 rounded-lg p-3 cursor-pointer transition-colors"
                           :class="method === 'ewallet' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="ewallet" x-model="method" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                        <span class="text-xs font-medium">E-Wallet</span>
                    </label>
                </div>
                @error('payment_method') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Jumlah --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran <span class="text-red-500">*</span></label>
                <input type="number" name="amount" x-model="amount" :max="remaining" min="0.01" step="0.01"
                       class="form-input text-lg font-semibold @error('amount') form-input-error @enderror">
                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Tunai: uang diterima --}}
            <div x-show="method === 'cash'" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima</label>
                <input type="number" name="amount_received" x-model="received" min="0" step="1000" class="form-input">
                <div x-show="Number(received) > 0" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-700">Kembalian: <span class="font-bold text-base" x-text="'Rp ' + formatRp(Math.max(0, Number(received) - Number(amount)))"></span></p>
                </div>
                @error('amount_received') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Kartu --}}
            <div x-show="method === 'card'" class="space-y-3 mb-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Kartu</label>
                        <select name="card_type" class="form-input">
                            <option value="Visa">Visa</option>
                            <option value="Mastercard">Mastercard</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">4 Digit Terakhir</label>
                        <input type="text" name="card_last_four" maxlength="4" placeholder="1234" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode Persetujuan</label>
                    <input type="text" name="approval_code" class="form-input">
                </div>
            </div>

            {{-- E-Wallet --}}
            <div x-show="method === 'ewallet'" class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Platform</label>
                    <select name="ewallet_type" class="form-input">
                        @foreach(['GoPay','OVO','Dana','ShopeePay'] as $ew)
                            <option>{{ $ew }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Referensi</label>
                    <input type="text" name="reference_number" class="form-input">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center text-base inline-flex items-center gap-2">
                {{-- check --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Proses Pembayaran
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function paymentForm(remaining) {
    return {
        method: 'cash',
        amount: remaining,
        received: 0,
        remaining,
        formatRp(v) { return new Intl.NumberFormat('id-ID').format(Math.round(v)); }
    }
}
</script>
@endpush
@endsection