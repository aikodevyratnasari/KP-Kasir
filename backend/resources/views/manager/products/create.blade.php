@extends('layouts.app')
@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
<div class="max-w-3xl space-y-5" x-data="createProduct()">

    <a href="{{ route('manager.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali</a>

    {{-- TABS --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        <button type="button" @click="tab = 'info'"
                :class="tab === 'info' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/>
            </svg>
            Info
        </button>
        <button type="button" @click="tab = 'variants'"
                :class="tab === 'variants' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/>
            </svg>
            Variasi
        </button>
        <button type="button" @click="tab = 'discounts'"
                :class="tab === 'discounts' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Diskon
        </button>
    </div>

    {{-- ══ TAB: INFO ══ --}}
    <div x-show="tab === 'info'">
        {{--
            Form create: simpan dulu produk, baru bisa tambah variasi/diskon.
            Note ke user: variasi & diskon bisa ditambah setelah produk tersimpan,
            atau langsung dari tab di bawah (akan disimpan bersama produk jika JS mengirimnya).
        --}}
        <form method="POST" action="{{ route('manager.products.store') }}" enctype="multipart/form-data" id="create-form">
            @csrf
            <div class="space-y-4">
                <div class="card space-y-4">
                    <h3 class="font-semibold text-gray-800">Informasi Produk</h3>
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

                <div class="card space-y-3">
                    <h3 class="font-semibold text-gray-800">Foto Produk</h3>
                    <div class="flex items-start gap-4">
                        <div class="w-28 h-28 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <img id="img-preview" src="" class="w-full h-full object-cover hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" id="img-placeholder" class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                   onchange="previewImage(this)"
                                   class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, WebP — maks. 2MB</p>
                            @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="card space-y-4">
                    <h3 class="font-semibold text-gray-800">Stok & Ketersediaan</h3>
                    <div class="flex flex-wrap gap-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" value="1"
                                   {{ old('is_available', '1') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="text-sm text-gray-700">Tersedia untuk dipesan</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="track_stock" value="0">
                            <input type="checkbox" name="track_stock" value="1" id="track-stock-cb"
                                   {{ old('track_stock') ? 'checked' : '' }}
                                   onchange="document.getElementById('stock-fields').style.display = this.checked ? '' : 'none'"
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="text-sm text-gray-700">Lacak stok</span>
                        </label>
                    </div>
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

            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn-primary">Simpan Produk</button>
                <a href="{{ route('manager.products.index') }}" class="btn-secondary">Batal</a>
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

            {{-- Catatan: variasi hanya bisa disimpan setelah produk tersimpan --}}
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Simpan produk terlebih dahulu di tab <strong>Info</strong>, kemudian variasi dapat ditambahkan dari halaman <strong>Edit Produk</strong>.
            </div>

            <div class="space-y-3" x-show="variants.length > 0">
                <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-500 uppercase px-1">
                    <div class="col-span-3">Nama</div><div class="col-span-2">Tipe</div>
                    <div class="col-span-2">±Harga (Rp)</div><div class="col-span-2">Stok</div>
                    <div class="col-span-2">Tersedia</div><div class="col-span-1"></div>
                </div>
                <template x-for="(v, i) in variants" :key="i">
                    <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-lg p-2">
                        <div class="col-span-3"><input type="text" x-model="v.name" placeholder="cth: Porsi Kecil" class="form-input text-sm" disabled></div>
                        <div class="col-span-2">
                            <select x-model="v.type" class="form-input text-sm" disabled>
                                <option value="ukuran">Ukuran</option><option value="level">Level</option>
                                <option value="topping">Topping</option><option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-span-2"><input type="number" x-model="v.price_adjustment" step="500" class="form-input text-sm" disabled></div>
                        <div class="col-span-2"><input type="number" x-model="v.stock" min="0" class="form-input text-sm" disabled></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" x-model="v.is_available" value="1" class="rounded border-gray-300 text-indigo-600" disabled></div>
                        <div class="col-span-1 flex justify-center">
                            <button type="button" @click="removeVariant(i)" class="text-red-400 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
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
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection