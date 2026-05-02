@extends('layouts.app')
@section('title', 'Pengaturan Toko')
@section('page-title', 'Pengaturan Toko')

@section('content')
<div class="max-w-2xl space-y-5 mx-auto">

    <form method="POST" action="{{ route('manager.settings.update') }}">
        @csrf @method('PATCH')

        {{-- Info Toko --}}
        <div class="card space-y-4 mb-5">
            <h3 class="font-semibold text-gray-800 border-b border-gray-200">Informasi Toko</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}"
                           class="form-input @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="form-input"
                              placeholder="Alamat lengkap toko...">{{ old('address', $store->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $store->phone) }}"
                           placeholder="08xxxxxxxxxx" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $store->email) }}"
                           placeholder="toko@email.com" class="form-input">
                </div>
            </div>
        </div>

        {{-- Pengaturan Pajak --}}
        <div class="card space-y-4 mb-5">
            <div>
                <h3 class="font-semibold text-gray-800">Pengaturan Pajak</h3>
                <p class="text-xs text-gray-500 mt-0.5 ">Pajak diterapkan otomatis saat kasir membuat pesanan</p>
            </div>

            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tarif Pajak (%)</label>
                        <p class="text-xs text-gray-400 mb-1">Isikan 0 jika tidak ada pajak</p>
                    <div class="relative">
                        <input type="number" name="tax_rate"
                               value="{{ old('tax_rate', $store->tax_rate) }}"
                               min="0" max="100" step="0.5"
                               class="form-input pr-8 @error('tax_rate') border-red-400 @enderror">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none">%</span>
                    </div>
                    @error('tax_rate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. NPWP / Tax ID</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $store->tax_number) }}"
                           placeholder="00.000.000.0-000.000" class="form-input">
                </div>
            </div>

            {{-- Preview kalkulasi --}}
            <div class="bg-gray-50 rounded-xl p-4" x-data="taxPreview({{ (float)$store->tax_rate }})">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Preview Kalkulasi</p>
                <div class="flex gap-3 items-center mb-3">
                    <label class="text-sm text-gray-600 whitespace-nowrap">Contoh harga:</label>
                    <input type="number" x-model="price" min="0" step="1000"
                           class="form-input text-sm py-1.5 w-36"
                           placeholder="50000">
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium" x-text="'Rp ' + fmt(Number(price)||0)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs" x-text="`Pajak (${taxRate}%)`"></span>
                        <span class="text-gray-400 text-xs" x-text="'Rp ' + fmt(Math.round((Number(price)||0) * taxRate / 100))"></span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-gray-200">
                        <span class="font-semibold text-gray-700">Total</span>
                        <span class="font-bold text-black-600" x-text="'Rp ' + fmt(Math.round((Number(price)||0) * (1 + taxRate/100)))"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alur Pesanan --}}
        <div class="card space-y-4 mb-5">
            <div>
                <h3 class="font-semibold text-gray-800">Alur Pesanan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Atur apakah status dimasak dan siap ditangani oleh user kitchen atau langsung oleh kasir</p>
            </div>

            <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50 cursor-pointer">
                <input type="hidden" name="has_kitchen" value="0">
                <input type="checkbox" name="has_kitchen" value="1"
                       class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('has_kitchen', $store->has_kitchen ?? true) ? 'checked' : '' }}>
                <span>
                    <span class="block text-sm font-semibold text-gray-800">Gunakan user kitchen</span>
                    <span class="block text-xs text-gray-500 mt-0.5">Jika aktif, pesanan lunas masuk ke display dapur. Jika nonaktif, kasir menangani status Dimasak dan Siap Disajikan dari detail pesanan.</span>
                </span>
            </label>
        </div>

        {{-- Struk --}}
        <div class="card space-y-4 mb-5 ">
            <h3 class="font-semibold text-gray-800 border-b border-gray-200">Pengaturan Struk</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Footer Struk</label>
                <textarea name="receipt_footer" rows="3" class="form-input"
                          placeholder="cth: Terima kasih! Selamat menikmati.">{{ old('receipt_footer', $store->receipt_footer) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Teks yang muncul di bagian bawah struk pembayaran</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                        class="ml-auto inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">
                        Simpan Pengatuan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function taxPreview(initialRate) {
    return {
        price: 50000,
        taxRate: initialRate,
        fmt(n) { return new Intl.NumberFormat('id-ID').format(n); },
        init() {
            // Sync taxRate dari input form secara real-time
            const input = document.querySelector('input[name="tax_rate"]');
            if (input) {
                input.addEventListener('input', () => {
                    this.taxRate = parseFloat(input.value) || 0;
                });
            }
        }
    }
}
setTimeout(() => {
    document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 2000);
</script>
@endpush
@endsection
