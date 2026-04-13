@extends('layouts.app')
@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
<div class="max-w-3xl space-y-5 mx-auto" x-data="createProduct()">

    {{-- ══ TAB: INFO ══ --}}
    <div>
        <form method="POST" action="{{ route('manager.products.store') }}" enctype="multipart/form-data" id="create-form">
            @csrf
            <div class="card space-y-4">
                <h3 class="font-semibold text-gray-800 border-b border-gray-200 pb-3">Informasi Produk</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-input @error('name') border-red-400 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" class="form-input">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" step="500"
                               class="form-input @error('price') border-red-400 @enderror">
                        @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-input">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card space-y-4 mt-4">
            {{-- Foto Produk --}}
                <!-- <div class="border border-gray-200 rounded-xl p-4 space-y-3"> -->
                    <!-- <label class="block text-sm font-medium text-gray-700">Foto Produk</label> -->
                     <h3 class="font-semibold text-gray-800">Foto Produk</h3>
                    <div class="flex items-center gap-4">
                        <div id="img-box" class="w-28 h-28 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <img id="img-preview" src="" class="w-full h-full object-cover hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" id="img-placeholder" class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                        <div class="flex flex-col gap-1.5 w-fit">
                            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp"
                                   onchange="previewImage(this); document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'"
                                   class="hidden">
                            <button type="button"
                                onclick="document.getElementById('imageInput').click()"
                                class="w-fit inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors mt-4"
                                style="border: 1px solid #2D54BF; background-color: #2D54BF; color: white;"
                                onmouseover="this.style.backgroundColor='#1e3d8f'"
                                onmouseout="this.style.backgroundColor='#2D54BF'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12V4m0 0L8 8m4-4l4 4"/>
                                </svg>
                                Upload
                            </button>
                            <span id="fileName" class="text-xs text-gray-500">Belum ada file dipilih</span>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP, maks. 2MB</span>
                        </div>
                    </div>
                    @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <!-- </div> -->

                {{-- Stok & Ketersediaan --}}
                <div class="space-y-4">
                    <!-- <label class="block text-sm font-medium text-gray-700">Stok & Ketersediaan</label> -->
                     <h3 class="font-semibold text-gray-800">Stok & Ketersediaan</h3>
                    <div class="flex flex-wrap gap-5" style="margin-top: 8px;">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" id="is_available" value="1"
                                   {{ old('is_available', '1') ? 'checked' : '' }}
                                   class="rounded border-gray-300 accent-[#2D54BF] focus:ring-0 focus:outline-none"
                                   style="outline: none !important; box-shadow: none !important;">
                            <label for="is_available" class="text-sm text-gray-700">Tersedia untuk dipesan</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="track_stock" value="0">
                            <input type="checkbox" name="track_stock" id="track-stock-cb" value="1"
                                   {{ old('track_stock') ? 'checked' : '' }}
                                   onchange="document.getElementById('stock-fields').style.display = this.checked ? '' : 'none'"
                                   class="rounded border-gray-300 accent-[#2D54BF] focus:ring-0 focus:outline-none"
                                   style="outline: none !important; box-shadow: none !important;">
                            <label for="track-stock-cb" class="text-sm text-gray-700">Lacak stok</label>
                        </div>
                    </div>
                    {{-- Hidden default saat track_stock tidak dicentang --}}
                    <input type="hidden" name="stock" value="0">
                    <input type="hidden" name="low_stock_alert" value="5">

                    <div id="stock-fields" class="grid grid-cols-2 gap-4" style="{{ old('track_stock') ? '' : 'display:none' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Stok Rendah</label>
                            <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', 5) }}" min="0" class="form-input">
                        </div>
                    </div>
                </div>                
                </div>
           </div>


             {{-- Tombol --}}
                <div class="flex gap-3 mt-3 justify-end">
                    <a href="{{ route('manager.products.index') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
                        onmouseover="this.style.backgroundColor='#f3f4f6';"
                        onmouseout="this.style.backgroundColor='white';">
                        Kembali
                    </a>
                    <button type="submit"
                        class="ml-auto inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">
                        Simpan Produk
                    </button>
               </div>
            </form>
    </div>


    {{-- ══ TAB: VARIASI ══ --}}
    <div x-show="tab === 'variants'">
        <div class="card space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">Variasi Produk</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Contoh: ukuran porsi, level pedas, pilihan topping</p>
                </div>
                <button type="button" @click="addVariant()" class="btn-primary text-xs">+ Tambah Variasi</button>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Simpan produk terlebih dahulu di tab <strong>Info</strong>, kemudian variasi dapat ditambahkan dari halaman <strong>Edit Produk</strong>.
            </div>
            <div x-show="variants.length === 0" class="text-center py-8 text-gray-400 text-sm">
                Belum ada variasi. Simpan produk dulu, lalu tambahkan variasi dari halaman Edit.
            </div>
        </div>
    </div>

    {{-- ══ TAB: DISKON ══ --}}
    <div x-show="tab === 'discounts'">
        <div class="card">
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Simpan produk terlebih dahulu di tab <strong>Info</strong>, kemudian diskon dapat ditambahkan dari halaman <strong>Edit Produk</strong>.
            </div>
            <div class="mt-4 text-center py-8 text-gray-400 text-sm">
                Belum ada diskon. Simpan produk dulu, lalu tambahkan diskon dari halaman Edit.
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('createProduct', () => ({
        tab: 'info',
        variants: [],
        addVariant() {
            this.variants.push({ name: '', type: 'ukuran', price_adjustment: 0, stock: 0, is_available: true });
        },
        removeVariant(i) { this.variants.splice(i, 1); }
    }));
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('img-preview');
            const placeholder = document.getElementById('img-placeholder');
            const box = document.getElementById('img-box');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.style.display = 'none';
            if (box) box.style.border = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection