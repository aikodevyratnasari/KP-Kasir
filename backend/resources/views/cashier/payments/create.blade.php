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
                    @foreach(['cash' => '💵 Tunai', 'card' => '💳 Kartu', 'ewallet' => '📱 E-Wallet'] as $val => $label)
                        <label class="flex flex-col items-center justify-center border-2 rounded-lg p-3 cursor-pointer transition-colors"
                               :class="method === '{{ $val }}' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                            <input type="radio" name="payment_method" value="{{ $val }}" x-model="method" class="sr-only">
                            <span class="text-lg">{{ explode(' ', $label)[0] }}</span>
                            <span class="text-xs font-medium mt-1">{{ explode(' ', $label)[1] }}</span>
                        </label>
                    @endforeach
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
                <div x-show="received > 0" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-700">Kembalian: <span class="font-bold text-base" x-text="'Rp ' + formatRp(Math.max(0, received - amount))"></span></p>
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

            <button type="submit" class="btn-primary w-full justify-center text-base">
                ✓ Proses Pembayaran
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