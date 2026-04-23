@extends('layouts.app')
@section('title', 'Proses Pembayaran')
@section('page-title', 'Proses Pembayaran')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="paymentForm({{ $order->remainingBalance() }})">

    {{-- Ringkasan Order --}}
    <div class="card" style="background:#eef2ff; border:1px solid #c7d2fe;">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm font-semibold" style="color:#4338ca;">{{ $order->order_number }}</p>
                <p class="text-xs" style="color:#6366f1;">{{ $order->items->count() }} item
                    @if($order->customer_name) · {{ $order->customer_name }} @endif
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs" style="color:#6366f1;">Sisa Tagihan</p>
                <p class="text-2xl font-bold" style="color:#4338ca;">Rp {{ number_format($order->remainingBalance(), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Form Pembayaran --}}
    <div class="card">
        <form method="POST" action="{{ route('cashier.payments.store', $order) }}" id="payment-form">
            @csrf

            {{-- ── Pilih Metode ── --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Metode Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">

                    {{-- Tunai --}}
                    <label class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition-all"
                           :class="method === 'cash' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="cash" x-model="method" class="sr-only">
                        <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Tunai</p>
                            <p class="text-xs text-gray-400">Hitung kembalian otomatis</p>
                        </div>
                    </label>

                    {{-- Kartu (EDC) --}}
                    <label class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition-all"
                           :class="method === 'card' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="card" x-model="method" class="sr-only">
                        <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Kartu</p>
                            <p class="text-xs text-gray-400">Visa / Mastercard via EDC</p>
                        </div>
                    </label>

                    {{-- QRIS --}}
                    <label class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition-all"
                           :class="method === 'qris' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="qris" x-model="method" class="sr-only">
                        <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h.01M14 17h3M17 14v3M14 21h7M21 14v7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">QRIS</p>
                            <p class="text-xs text-gray-400">Scan QR dari app apapun</p>
                        </div>
                    </label>

                    {{-- E-Wallet --}}
                    <label class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition-all"
                           :class="method === 'ewallet' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="ewallet" x-model="method" class="sr-only">
                        <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">E-Wallet</p>
                            <p class="text-xs text-gray-400">GoPay, OVO, Dana, ShopeePay</p>
                        </div>
                    </label>

                    {{-- Transfer Bank --}}
                    <label class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition-all col-span-2"
                           :class="method === 'bank_transfer' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200'">
                        <input type="radio" name="payment_method" value="bank_transfer" x-model="method" class="sr-only">
                        <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Transfer Bank</p>
                            <p class="text-xs text-gray-400">Virtual Account BCA, BNI, BRI, Mandiri, Permata</p>
                        </div>
                    </label>
                </div>
                @error('payment_method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- ── CASH: jumlah diterima ── --}}
            <div x-show="method === 'cash'" class="space-y-3 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran</label>
                    <input type="number" name="amount" x-model="amount" min="0.01" step="0.01"
                           class="form-input text-lg font-semibold @error('amount') form-input-error @enderror">
                    @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima</label>
                    <input type="number" name="amount_received" x-model="received" min="0" step="1000" class="form-input">
                    <div x-show="Number(received) > 0" class="mt-2 p-3 rounded-lg" style="background:#f0fdf4;border:1px solid #86efac;">
                        <p class="text-sm" style="color:#166534;">Kembalian: <span class="font-bold text-base" x-text="'Rp ' + formatRp(Math.max(0, Number(received) - Number(amount)))"></span></p>
                    </div>
                    @error('amount_received')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ── KARTU (EDC) ── --}}
            <div x-show="method === 'card'" class="space-y-3 mb-4">
                <div class="p-3 rounded-lg text-xs" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Proses pembayaran di mesin EDC terlebih dahulu, lalu isi data konfirmasi di bawah setelah EDC approve.</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran</label>
                    <input type="number" name="amount" x-model="amount" min="0.01" step="0.01" class="form-input text-lg font-semibold">
                </div>
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
                        <input type="text" name="card_last_four" maxlength="4" placeholder="1234"
                               class="form-input @error('card_last_four') form-input-error @enderror">
                        @error('card_last_four')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode Persetujuan (dari EDC)</label>
                    <input type="text" name="approval_code" placeholder="Contoh: 123456"
                           class="form-input @error('approval_code') form-input-error @enderror">
                    @error('approval_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ── QRIS ── --}}
            <div x-show="method === 'qris'" class="mb-4">
                <div class="p-3 rounded-lg text-xs mb-3" style="background:#eff6ff;border:1px solid #93c5fd;color:#1d4ed8;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>QR akan dibuat via Midtrans. Pelanggan scan dengan app bank atau e-wallet manapun yang support QRIS.</span>
                    </div>
                </div>

                @if(config('app.env') !== 'production')
                <div class="p-3 rounded-lg text-xs mb-3" style="background:#fefce8;border:1px solid #fde047;color:#713f12;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
                        <span><strong>Sandbox:</strong> Setelah QR tampil, buka <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" rel="noopener" style="text-decoration:underline">simulator Midtrans</a> dan masukkan order ID untuk simulasi pembayaran.</span>
                    </div>
                </div>
                @endif

                <div id="qris-container" style="display:none; text-align:center; padding:20px 16px 16px; border:2px dashed #c7d2fe; border-radius:12px;">
                    <div id="qris-canvas" style="display:inline-block; line-height:0;"></div>
                    <p class="text-xs text-gray-400 mt-3">Scan QR di atas dengan app bank atau e-wallet yang support QRIS</p>
                    <div id="qris-status" class="mt-2 text-xs font-semibold" style="color:#6366f1;"></div>
                </div>

                <div id="qris-btn-container" class="mt-1">
                    <button type="button" id="qris-initiate-btn"
                            onclick="initiateGateway('qris')"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-xl transition-all"
                            style="color:#4338ca; background:#eef2ff; border:1.5px solid #c7d2fe;"
                            onmouseover="this.style.background='#e0e7ff';"
                            onmouseout="this.style.background='#eef2ff';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h.01M14 17h3M17 14v3M14 21h7M21 14v7"/></svg>
                        Buat &amp; Tampilkan QR Code
                    </button>
                </div>
            </div>

            {{-- ── E-WALLET ── --}}
            <div x-show="method === 'ewallet'" class="mb-4 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Platform E-Wallet</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['GoPay', 'OVO', 'Dana', 'ShopeePay'] as $ew)
                        <label class="flex flex-col items-center justify-center border-2 rounded-xl p-2.5 cursor-pointer transition-all text-xs font-medium text-gray-700"
                               :class="ewalletType === '{{ $ew }}' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 hover:border-indigo-200'">
                            <input type="radio" name="ewallet_type_select" value="{{ $ew }}" x-model="ewalletType" class="sr-only">
                            {{ $ew }}
                        </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="ewallet_type" x-bind:value="ewalletType">
                </div>

                <div class="p-3 rounded-lg text-xs" style="background:#eff6ff;border:1px solid #93c5fd;color:#1d4ed8;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Klik tombol di bawah untuk membuka pop-up pembayaran. Pelanggan menyelesaikan pembayaran di pop-up tersebut.</span>
                    </div>
                </div>

                @if(config('app.env') !== 'production')
                <div class="p-3 rounded-lg text-xs" style="background:#fefce8;border:1px solid #fde047;color:#713f12;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
                        <span><strong>Sandbox:</strong> Di popup Snap, pilih e-wallet lalu klik <em>"Simulate Payment"</em> atau tombol sejenisnya. GoPay &amp; ShopeePay paling stabil di sandbox. OVO/Dana mungkin perlu nomor HP terdaftar.</span>
                    </div>
                </div>
                @endif

                <button type="button" id="ewallet-initiate-btn"
                        onclick="initiateGateway('ewallet')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-xl transition-all"
                        style="color:#4338ca; background:#eef2ff; border:1.5px solid #c7d2fe;"
                        onmouseover="this.style.background='#e0e7ff';"
                        onmouseout="this.style.background='#eef2ff';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    Buka Pembayaran E-Wallet
                </button>

                <div id="ewallet-status" class="text-xs font-semibold text-center" style="color:#6366f1;display:none;"></div>
            </div>

            {{-- ── TRANSFER BANK ── --}}
            <div x-show="method === 'bank_transfer'" class="mb-4 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Bank</label>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach(['bca' => 'BCA', 'bni' => 'BNI', 'bri' => 'BRI', 'mandiri' => 'Mandiri', 'permata' => 'Permata'] as $val => $label)
                        <label class="flex flex-col items-center justify-center border-2 rounded-xl p-2.5 cursor-pointer transition-all text-xs font-medium text-gray-700"
                               :class="bankType === '{{ $val }}' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 hover:border-indigo-200'">
                            <input type="radio" name="bank_type_select" value="{{ $val }}" x-model="bankType" class="sr-only">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="bank_type" x-bind:value="bankType">
                </div>

                <div class="p-3 rounded-lg text-xs" style="background:#eff6ff;border:1px solid #93c5fd;color:#1d4ed8;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Nomor Virtual Account akan dibuat. Tampilkan ke pelanggan agar bisa transfer via ATM / mobile banking.</span>
                    </div>
                </div>

                @if(config('app.env') !== 'production')
                <div class="p-3 rounded-lg text-xs" style="background:#fefce8;border:1px solid #fde047;color:#713f12;">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
                        <span><strong>Sandbox:</strong> Setelah VA muncul, buka <a href="https://simulator.sandbox.midtrans.com/bca/va/index" target="_blank" rel="noopener" style="text-decoration:underline">Midtrans Simulator</a> (ganti bank di URL). Masukkan VA number untuk simulasi transfer.</span>
                    </div>
                </div>
                @endif

                {{-- Container VA number (muncul setelah initiate) --}}
                <div id="va-container" style="display:none; padding:16px; border:2px solid #c7d2fe; border-radius:12px; background:#eef2ff; text-align:center;">
                    <p class="text-xs text-gray-500 mb-1">Nomor Virtual Account</p>
                    <p id="va-bank-label" class="text-sm font-semibold text-indigo-700 mb-1"></p>
                    <p id="va-number-display" class="text-2xl font-bold tracking-wider" style="color:#4338ca; letter-spacing:0.1em;"></p>
                    <p class="text-xs text-gray-400 mt-2">Salin dan berikan ke pelanggan untuk transfer via ATM / mobile banking</p>
                    <button type="button" onclick="copyVaNumber()" id="copy-va-btn"
                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                            style="background:#6366f1;color:white;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Salin Nomor VA
                    </button>
                    <div id="va-status" class="mt-2 text-xs font-semibold" style="color:#6366f1;"></div>
                </div>

                <button type="button" id="bank-transfer-initiate-btn"
                        onclick="initiateGateway('bank_transfer')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-xl transition-all"
                        style="color:#4338ca; background:#eef2ff; border:1.5px solid #c7d2fe;"
                        onmouseover="this.style.background='#e0e7ff';"
                        onmouseout="this.style.background='#eef2ff';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
                    Buat Nomor Virtual Account
                </button>
            </div>

            {{-- ── Tombol Konfirmasi (Cash & Kartu) ── --}}
            <div x-show="method === 'cash' || method === 'card'">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 text-base font-semibold rounded-xl transition-all"
                        style="color:white; background-color:#1c8b59;"
                        onmouseover="this.style.backgroundColor='#0e663e';"
                        onmouseout="this.style.backgroundColor='#1c8b59';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Konfirmasi Pembayaran
                </button>
            </div>

        </form>
    </div>
</div>

{{-- Tombol Kembali --}}
<div class="max-w-xl mx-auto mt-2 mb-4">
    <a href="{{ route('cashier.orders.show', $order) }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
       style="border:1.5px solid #dcdcdc; background-color:white; color:#374151;"
       onmouseover="this.style.backgroundColor='#f3f4f6';"
       onmouseout="this.style.backgroundColor='white';">
        Kembali
    </a>
</div>

{{-- Snap.js dari Midtrans --}}
<script src="https://app{{ config('services.midtrans.production') ? '' : '.sandbox' }}.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>

{{-- QRCode.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

@push('scripts')
<script>
function paymentForm(remaining) {
    return {
        method:      'cash',
        amount:      remaining,
        received:    0,
        ewalletType: 'GoPay',
        bankType:    'bca',
        remaining,
        formatRp(v) { return new Intl.NumberFormat('id-ID').format(Math.round(v)); }
    }
}

const INITIATE_URL = '{{ route('cashier.payments.initiate', $order) }}';
const POLL_BASE    = '{{ url('/cashier/payments') }}/';
const ORDER_URL    = '{{ route('cashier.orders.show', $order) }}';
const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]').content;

const BANK_LABELS = {
    bca: 'BCA', bni: 'BNI', bri: 'BRI', mandiri: 'Mandiri', permata: 'Permata',
};

let activePaymentId = null;
let pollInterval    = null;
let currentVaNumber = null;

async function initiateGateway(method) {
    const ewalletType = method === 'bank_transfer'
        ? document.querySelector('input[name="bank_type_select"]:checked')?.value
        : document.querySelector('input[name="ewallet_type_select"]:checked')?.value;

    const btnId = { qris: 'qris-initiate-btn', ewallet: 'ewallet-initiate-btn', bank_transfer: 'bank-transfer-initiate-btn' }[method];
    const btn   = document.getElementById(btnId);
    if (btn) { btn.disabled = true; btn.textContent = 'Menghubungi gateway...'; }

    try {
        const res  = await fetch(INITIATE_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body:    JSON.stringify({ method, ewallet_type: ewalletType }),
        });
        const data = await res.json();

        if (!data.success) {
            alert('Gagal: ' + (data.message || 'Silakan coba lagi.'));
            if (btn) { btn.disabled = false; btn.textContent = getBtnLabel(method); }
            return;
        }

        activePaymentId = data.payment_id;

        // ── QRIS ────────────────────────────────────────────────────────
        if (method === 'qris' && data.qr_string) {
            const canvas = document.getElementById('qris-canvas');
            canvas.innerHTML = '';
            new QRCode(canvas, {
                text: data.qr_string, width: 220, height: 220,
                colorDark: '#111827', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M,
            });
            setTimeout(() => {
                const cvs = canvas.querySelector('canvas');
                const img = canvas.querySelector('img');
                if (cvs) cvs.style.display = 'none';
                if (img) { img.style.display = 'block'; img.style.borderRadius = '8px'; img.style.margin = '0 auto'; }
            }, 100);
            document.getElementById('qris-container').style.display    = 'block';
            document.getElementById('qris-btn-container').style.display = 'none';
            startPolling('qris');

        // ── QRIS via Snap (fallback) ─────────────────────────────────────
        } else if (method === 'qris' && data.snap_token) {
            openSnap(data.snap_token, 'qris', btn);

        // ── E-WALLET via Snap ────────────────────────────────────────────
        } else if (method === 'ewallet' && data.snap_token) {
            openSnap(data.snap_token, 'ewallet', btn);

        // ── TRANSFER BANK via Core API ───────────────────────────────────
        } else if (method === 'bank_transfer' && data.va_number) {
            currentVaNumber = data.va_number;
            const bankLabel = BANK_LABELS[data.bank] || (data.bank || '').toUpperCase();
            document.getElementById('va-bank-label').textContent    = 'Bank ' + bankLabel;
            document.getElementById('va-number-display').textContent = data.va_number;
            document.getElementById('va-container').style.display   = 'block';
            if (btn) { btn.disabled = false; btn.textContent = 'Perbarui VA'; }
            startPolling('bank_transfer');

        } else {
            alert('Response tidak dikenali dari gateway.');
            if (btn) { btn.disabled = false; btn.textContent = getBtnLabel(method); }
        }

    } catch (e) {
        alert('Error: ' + e.message);
        if (btn) { btn.disabled = false; btn.textContent = getBtnLabel(method); }
    }
}

function openSnap(token, method, btn) {
    const statusElId = method === 'qris' ? 'qris-status' : 'ewallet-status';
    window.snap.pay(token, {
        onSuccess: () => { window.location.href = ORDER_URL + '?success=payment'; },
        onPending: () => {
            const el = document.getElementById(statusElId);
            el.style.display = 'block';
            el.textContent   = 'Menunggu konfirmasi pembayaran...';
            startPolling(method);
        },
        onError: () => {
            const el = document.getElementById(statusElId);
            el.style.display = 'block';
            el.style.color   = '#dc2626';
            el.textContent   = 'Pembayaran gagal atau dibatalkan.';
            if (btn) { btn.disabled = false; btn.textContent = getBtnLabel(method); }
        },
        onClose: () => { startPolling(method); },
    });
}

function copyVaNumber() {
    if (!currentVaNumber) return;
    navigator.clipboard.writeText(currentVaNumber).then(() => {
        const btn = document.getElementById('copy-va-btn');
        const orig = btn.innerHTML;
        btn.textContent = 'Tersalin ✓';
        btn.style.background = '#22c55e';
        setTimeout(() => { btn.innerHTML = orig; btn.style.background = '#6366f1'; }, 2000);
    }).catch(() => {
        prompt('Salin nomor VA berikut:', currentVaNumber);
    });
}

function startPolling(method) {
    if (pollInterval) clearInterval(pollInterval);
    let attempts = 0;
    const maxAttempts = 200; // ~10 menit untuk transfer bank (lebih lama dari QRIS)

    pollInterval = setInterval(async () => {
        attempts++;
        if (attempts > maxAttempts || !activePaymentId) {
            clearInterval(pollInterval);
            return;
        }
        try {
            const res  = await fetch(POLL_BASE + activePaymentId + '/poll', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();

            if (data.is_paid) {
                clearInterval(pollInterval);
                window.location.href = ORDER_URL + '?success=payment';
                return;
            }

            const statusElId = { qris: 'qris-status', ewallet: 'ewallet-status', bank_transfer: 'va-status' }[method];
            const statusEl   = document.getElementById(statusElId);
            if (statusEl) {
                statusEl.style.display = 'block';
                statusEl.textContent   = 'Menunggu pembayaran...';
            }
        } catch (e) { /* diam */ }
    }, 3000);
}

function getBtnLabel(method) {
    return { qris: 'Buat & Tampilkan QR Code', ewallet: 'Buka Pembayaran E-Wallet', bank_transfer: 'Buat Nomor Virtual Account' }[method] || 'Coba Lagi';
}
</script>
@endpush
@endsection