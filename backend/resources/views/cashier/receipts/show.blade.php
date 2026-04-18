@extends('layouts.app')
@section('title', 'Struk Pembayaran')

@section('content')
<div class="max-w-sm mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-900">Struk Pembayaran</h1>
        {{-- Tombol cetak — tab baru agar halaman ini tetap aktif --}}
        <a href="{{ route('cashier.receipts.print', $payment) }}"
           target="_blank"
           rel="noopener noreferrer"
           class="btn-secondary text-xs inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Cetak
        </a>
    </div>

    {{-- Flash sukses / error kirim email --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form kirim email --}}
    <div class="card">
        <h2 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-1.5">
            Kirim Struk via Email
        </h2>
        <div class="flex gap-2" id="email-send-wrap">
            <input type="email"
                id="receipt-show-email"
                placeholder="contoh@email.com"
                class="form-input flex-1 text-sm"
                required>
            <button type="submit" 
                id="receipt-show-btn"
                        onclick="sendReceiptFromShow()"
                        class="btn-primary text-sm inline-flex items-center gap-1.5 flex-shrink-0 focus:outline-none"
                        style="color:white; background-color:#e21c1c; outline:none !important; box-shadow:none !important;"
                        onmouseover="this.style.backgroundColor='#cb0d0d';"
                        onmouseout="this.style.backgroundColor='#e21c1c';">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Kirim
            </button>
        </div>
        <div id="receipt-show-feedback" style="display:none;" class="mt-2 text-sm rounded-lg px-3 py-2"></div>
        <p class="text-xs text-gray-400 mt-2">Struk akan dikirim dalam format HTML yang rapi ke email di atas.</p>

        @push('scripts')
        <script>
        function sendReceiptFromShow() {
            const emailInput = document.getElementById('receipt-show-email');
            const btn        = document.getElementById('receipt-show-btn');
            const feedback   = document.getElementById('receipt-show-feedback');
            const email      = emailInput.value.trim();

            if (!email) { emailInput.focus(); return; }

            btn.disabled    = true;
            btn.innerHTML   = 'Mengirim...';
            btn.style.backgroundColor = '#a5b4fc';
            feedback.style.display    = 'none';

            fetch('{{ route('cashier.receipts.send-email', $payment) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email })
            })
            .then(r => r.json())
            .then(data => {
                feedback.style.display = 'block';
                if (data.success) {
                    feedback.className   = 'mt-2 text-sm rounded-lg px-3 py-2 bg-green-50 text-green-700 border border-green-200';
                    feedback.textContent = '✓ Struk berhasil dikirim ke ' + email;
                    btn.innerHTML        = 'Terkirim ✓';
                    btn.style.backgroundColor = '#22c55e';
                } else {
                    feedback.className   = 'mt-2 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
                    feedback.textContent = data.message || 'Gagal mengirim.';
                    btn.disabled         = false;
                    btn.innerHTML        = '<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Kirim';
                    btn.style.backgroundColor = '';
                }
            })
            .catch(() => {
                feedback.style.display = 'block';
                feedback.className     = 'mt-2 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
                feedback.textContent   = 'Gagal mengirim. Periksa koneksi.';
                btn.disabled           = false;
                btn.innerHTML          = 'Kirim';
                btn.style.backgroundColor = '';
            });
        }
        </script>
        @endpush
    </div>

    {{-- Preview struk --}}
    <div class="card font-mono text-sm" id="receipt">
        {{-- Header --}}
        <div class="text-center border-b border-dashed border-gray-300 pb-3 mb-3">
            <p class="text-base font-bold">{{ $payment->order->store->name ?? config('app.name') }}</p>
            <p class="text-xs text-gray-500">{{ $payment->order->store->address ?? '' }}</p>
            <p class="text-xs text-gray-500">{{ $payment->order->store->phone ?? '' }}</p>
        </div>

        {{-- Info --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-300 pb-3 mb-3">
            <div class="flex justify-between">
                <span>No. Pesanan</span><span>{{ $payment->order->order_number }}</span>
            </div>
            @if($payment->order->customer_name)
            <div class="flex justify-between">
                <span>Pelanggan</span><span class="font-medium">{{ $payment->order->customer_name }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span>Kasir</span><span>{{ $payment->cashier?->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu</span><span>{{ $payment->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tipe</span><span class="capitalize">{{ str_replace('_',' ',$payment->order->order_type) }}</span>
            </div>
            @if($payment->order->table)
            <div class="flex justify-between">
                <span>Meja</span><span>{{ $payment->order->table->number }}</span>
            </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="border-b border-dashed border-gray-300 pb-3 mb-3">
            @foreach($payment->order->items as $item)
                <div class="flex justify-between">
                    <span>{{ $item->product_name }}</span>
                </div>
                @if($item->variant_name)
                    <div class="pl-2 text-xs text-indigo-500">↳ {{ $item->variant_name }}</div>
                @endif
                @if(isset($item->discount_label) && $item->discount_label && isset($item->discount_amount) && $item->discount_amount > 0)
                    <div class="pl-2 text-xs text-red-500">🏷 {{ $item->discount_label }}</div>
                @endif
                <div class="flex justify-between text-gray-500 pl-2">
                    <span>{{ $item->quantity }} x Rp {{ number_format($item->unit_price,0,',','.') }}</span>
                    <span>Rp {{ number_format($item->subtotal,0,',','.') }}</span>
                </div>
                @if($item->special_notes)
                <div class="pl-2 text-xs text-orange-600 italic flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                    {{ $item->special_notes }}
                </div>
                @endif
            @endforeach
            @if($payment->order->notes)
            <div class="mt-2 pt-2 border-t border-dashed border-gray-200 text-xs text-gray-500">
                <span class="font-medium">Catatan:</span> {{ $payment->order->notes }}
            </div>
            @endif
        </div>

        {{-- Total --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-300 pb-3 mb-3">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>Rp {{ number_format($payment->order->subtotal,0,',','.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pajak ({{ number_format($payment->order->tax_rate,0) }}%)</span>
                <span>Rp {{ number_format($payment->order->tax_amount,0,',','.') }}</span>
            </div>
            <div class="flex justify-between font-bold text-sm mt-1">
                <span>TOTAL</span>
                <span>Rp {{ number_format($payment->order->total_amount,0,',','.') }}</span>
            </div>
            <div class="flex justify-between mt-1">
                <span class="capitalize">{{ $payment->methodLabel() }}</span>
                <span>Rp {{ number_format($payment->amount,0,',','.') }}</span>
            </div>
            @if($payment->amount_received && $payment->payment_method === 'cash')
            <div class="flex justify-between">
                <span>Uang Diterima</span>
                <span>Rp {{ number_format($payment->amount_received,0,',','.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kembalian</span>
                <span>Rp {{ number_format($payment->change_amount ?? ($payment->amount_received - $payment->amount),0,',','.') }}</span>
            </div>
            @endif
        </div>
        <div class="text-center text-xs text-gray-500">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p class="mt-1">Simpan struk ini sebagai bukti pembayaran</p>
        </div>
    </div>
    <div class="flex justify-start">
        <a href="{{ route('cashier.orders.show', $payment->order) }}"
           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
           style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
           onmouseover="this.style.backgroundColor='#f3f4f6';"
           onmouseout="this.style.backgroundColor='white';">
           Kembali
        </a>
    </div>
</div>
@endsection