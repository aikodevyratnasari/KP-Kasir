@extends('layouts.app')
@section('title', 'Tambah Toko')
@section('page-title', 'Tambah Toko Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="card">
        <form method="POST" action="{{ route('admin.stores.store') }}"
              id="form-create-store" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 gap-4">

                {{-- Logo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo Toko</label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <img id="logo-preview" src="" alt="Preview"
                            class="w-full h-full object-cover hidden">
                            <svg id="logo-placeholder" xmlns="http://www.w3.org/2000/svg"
                                 class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/png,image/jpg,image/jpeg,image/webp"
       id="logoInput" class="hidden" onchange="previewLogo(event); document.getElementById('logoFileName').textContent = this.files[0] ? this.files[0].name : 'Tidak ada file yang dipilih'">
<button type="button"
    onclick="document.getElementById('logoInput').click()"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
    style="border: 1px solid #2D54BF; background-color: #2D54BF; color: white;"
    onmouseover="this.style.backgroundColor='#1e3d8f'"
    onmouseout="this.style.backgroundColor='#2D54BF'">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12V4m0 0L8 8m4-4l4 4"/>
    </svg>
    Upload
</button>
<span id="logoFileName" class="ml-2 text-xs text-gray-500">Tidak ada file yang dipilih</span>
<p class="mt-1 text-xs text-gray-400">PNG/JPG/WEBP, maks. 1 MB. Opsional.</p>
@error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="Contoh: DePOS Restaurant - Cabang Barat"
                           class="form-input @error('name') form-input-error @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2"
                              placeholder="Jl. Contoh No. 1, Kota, Provinsi"
                              class="form-input @error('address') form-input-error @enderror"
                    >{{ old('address') }}</textarea>
                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Telepon & Email --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="031-XXXXXXXX"
                               class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="cabang@depos.id"
                               class="form-input @error('email') form-input-error @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- NPWP & Tax Rate --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Pajak (NPWP)</label>
                        <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                               placeholder="XX.XXX.XXX.X-XXX.XXX"
                               class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tarif Pajak (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                               value="{{ old('tax_rate', '10.00') }}"
                               class="form-input @error('tax_rate') form-input-error @enderror">
                        @error('tax_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Receipt Footer --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Footer Struk</label>
                    <textarea name="receipt_footer" rows="2"
                              placeholder="Terima kasih telah berkunjung!..."
                              class="form-input"
                    >{{ old('receipt_footer', 'Terima kasih telah berkunjung! Kami berharap dapat melayani Anda kembali.') }}</textarea>
                </div>

                {{-- Opsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opsi</label>
                    <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                        <input type="hidden" name="has_kitchen" value="0">
                        <input type="checkbox" name="has_kitchen" value="1" id="has_kitchen"
                               {{ old('has_kitchen') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <div>
                            <label for="has_kitchen" class="text-sm font-medium text-gray-700 cursor-pointer">
                                Aktifkan Kitchen Display
                            </label>
                            <p class="text-xs text-gray-400">Toko ini memiliki layar dapur untuk manajemen pesanan masak.</p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex justify-between">
        <a href="{{ route('admin.stores.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
           style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
           onmouseover="this.style.backgroundColor='#f3f4f6';"
           onmouseout="this.style.backgroundColor='white';">
            Kembali
        </a>
        <button type="submit" form="form-create-store"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f';"
                onmouseout="this.style.backgroundColor='#2D54BF';"
                onmousedown="this.style.transform='scale(0.98)';"
                onmouseup="this.style.transform='scale(1)';">
            Simpan Toko
        </button>
    </div>
</div>

@push('scripts')
<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const preview     = document.getElementById('logo-preview');
        const placeholder = document.getElementById('logo-placeholder');
        preview.src       = e.target.result;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

// Bersihkan error saat input berubah
document.querySelectorAll('.form-input').forEach(input => {
    ['input', 'change'].forEach(evt => {
        input.addEventListener(evt, function () {
            this.classList.remove('form-input-error');
            const err = this.closest('div')?.querySelector('p.text-red-600');
            if (err) err.remove();
        });
    });
});
</script>
@endpush
@endsection