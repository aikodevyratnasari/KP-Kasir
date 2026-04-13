@extends('layouts.app')
@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<div class="max-w-3xl space-y-5 mx-auto" x-data="editProduct()">

    <!-- <a href="{{ route('manager.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali</a> -->

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
            @if($product->variants->count() > 0)
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-1.5 py-0.5 rounded-full">
                    {{ $product->variants->count() }}
                </span>
            @endif
        </button>
        <button type="button" @click="tab = 'discounts'"
                :class="tab === 'discounts' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Diskon
            @if($product->discounts->where('is_active', true)->count() > 0)
                <span class="bg-red-100 text-red-600 text-xs font-bold px-1.5 py-0.5 rounded-full">
                    {{ $product->discounts->where('is_active', true)->count() }}
                </span>
            @endif
        </button>
    </div>

    {{-- ══ TAB: INFO ══ --}}
    <div x-show="tab === 'info'">
        <form method="POST" action="{{ route('manager.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div class="card space-y-4">
                    <h3 class="font-semibold text-gray-800 border-b border-gray-200 pb-3">Informasi Produk</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                   class="form-input @error('name') border-red-400 @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select name="category_id" class="form-input">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="500"
                                   class="form-input @error('price') border-red-400 @enderror">
                            @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="3" class="form-input">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card space-y-3">
                    <h3 class="font-semibold text-gray-800">Foto Produk</h3>
<div class="flex items-center gap-4">
    <div id="img-box" class="w-28 h-28 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center flex-shrink-0
    {{ $product->image ? '' : 'border-2 border-dashed border-gray-300' }}">
    @if($product->image)
        <img src="{{ Storage::url($product->image) }}" id="img-preview" class="w-full h-full object-cover">
        <svg xmlns="http://www.w3.org/2000/svg" id="img-placeholder" class="w-10 h-10 text-gray-300 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
        </svg>
    @else
        <img id="img-preview" src="" class="w-full h-full object-cover hidden">
        <svg xmlns="http://www.w3.org/2000/svg" id="img-placeholder" class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
        </svg>
    @endif
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
        <span id="fileName" class="text-xs text-gray-500">
            {{ $product->image ? basename($product->image) : 'Belum ada file dipilih' }}
        </span>
        <span class="text-xs text-gray-400">JPG, PNG, WebP, maks. 2MB</span>
    </div>
</div>
@error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    
                    <div class="space-y-4" style="margin-top: 20px;">
                    <h3 class="font-semibold text-gray-800">Stok & Ketersediaan</h3>
                    <!-- <label class="block text-sm font-medium text-gray-700">Stok & Ketersediaan</label> -->
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
                    <div id="stock-fields" class="grid grid-cols-2 gap-4"
                         style="{{ old('track_stock', $product->track_stock) ? '' : 'display:none' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Stok Rendah</label>
                            <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', $product->low_stock_alert) }}" min="0" class="form-input">
                        </div>
                    </div>
                </div>
                </div>
            </div>
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
                        Simpan Perubahan
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
                    <p class="text-xs text-gray-500 mt-0.5">Contoh: ukuran porsi, level pedas, pilihan rasa</p>
                </div>
                <button type="submit"
                        @click="addVariant()"
                        class="ml-auto inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #d87641; border: 1px solid #d87641;"
                        onmouseover="this.style.backgroundColor='#ba4a0d'"
                        onmouseout="this.style.backgroundColor='#d87641'">
                        + Tambah Variasi
                    </button>
            </div>

            <form method="POST" action="{{ route('manager.products.variants.store', $product) }}">
                @csrf

                <div class="space-y-3" x-show="variants.length > 0">
                    <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-500 uppercase px-3">
                        <div class="col-span-3">Nama</div>
                        <div class="col-span-2">Kategori</div>
                        <div class="col-span-2">±Harga (Rp)</div>
                        <div class="col-span-2">Stok</div>
                        <div class="col-span-2 text-center">Tersedia</div>
                        <div class="col-span-1"></div>
                    </div>

                    <template x-for="(v, i) in variants" :key="i">
                        <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-lg p-2">
                            <input type="hidden" :name="`variants[${i}][id]`" :value="v.id ?? ''">
                            <div class="col-span-3">
                                <input type="text" :name="`variants[${i}][name]`" x-model="v.name"
                                       placeholder="cth: Pedas Sedang" class="form-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <select :name="`variants[${i}][type]`" x-model="v.type" class="form-input text-sm w-30">
                                    <option value="ukuran">Ukuran</option>
                                    <option value="level">Level</option>
                                    <option value="rasa">Rasa</option>
                                    <option value="topping">Topping</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="number" :name="`variants[${i}][price_adjustment]`"
                                       x-model="v.price_adjustment" step="500" placeholder="0" class="form-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <input type="number" :name="`variants[${i}][stock]`"
                                       x-model="v.stock" min="0" class="form-input text-sm">
                            </div>
                            <div class="col-span-2 flex justify-center">
                                <input type="checkbox" :name="`variants[${i}][is_available]`"
                                    x-model="v.is_available" value="1"
                                    class="rounded accent-[#2D54BF] focus:ring-0 focus:outline-none"
                                    style="outline: none !important; box-shadow: none !important;">
                            </div>
                            <div class="col-span-1 flex justify-center">
                                <button type="button" @click="removeVariant(i)" class="text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="variants.length === 0" class="text-center py-8 text-gray-400 text-sm">
                    Belum ada variasi. Klik "+ Tambah Variasi" untuk menambahkan.
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit"
                        class="ml-auto inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">
                        Simpan Variasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ TAB: DISKON ══ --}}
    <div x-show="tab === 'discounts'" class="space-y-4">
        <div class="card space-y-4">
            <h3 class="font-semibold text-gray-800">Tambah Diskon Baru</h3>
            <form method="POST" action="{{ route('manager.products.discounts.store', $product) }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Promo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="cth: Promo Weekend" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon</label>
                        <select name="type" id="discount-type" onchange="updateDiscountLabel(this.value)" class="form-input">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nilai <span id="discount-unit" class="text-gray-400">(%)</span> <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="value" min="0" placeholder="cth: 20" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Mulai</label>
                        <input type="datetime-local" name="starts_at" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                        <input type="datetime-local" name="ends_at" class="form-input">
                        <p class="mt-1 text-xs text-gray-400">Kosongkan = tidak ada batas waktu</p>
                    </div>
                    <div class="col-span-2">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" checked 
               class="rounded border-gray-300 accent-[#2D54BF] focus:ring-0 focus:outline-none"
               style="outline: none !important; box-shadow: none !important;">
        <span class="text-sm text-gray-700">Langsung aktif</span>
    </label>
</div>
                </div>
                <div class="flex justify-end mt-3">
                     <button type="submit"
                        class="ml-auto inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">
                        Tambah Diskon
                    </button>
                </div>
            </form>
        </div>

        <div class="card p-0 overflow-x-auto">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Daftar Diskon ({{ $product->discounts->count() }})</h3>
            </div>
            @forelse($product->discounts as $disc)
            <div class="px-4 py-3 flex items-center justify-between gap-4 border-b border-gray-50 last:border-0 hover:bg-gray-50 min-w-[700px]">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 text-sm whitespace-nowrap">{{ $disc->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 whitespace-nowrap">
                        <span class="font-semibold text-indigo-600">
                            {{ $disc->type === 'percentage' ? $disc->value . '%' : 'Rp ' . number_format($disc->value, 0, ',', '.') }}
                        </span>
                        @if($disc->starts_at || $disc->ends_at)
                            &nbsp;·&nbsp; {{ $disc->starts_at?->format('d M Y H:i') ?? '—' }} → {{ $disc->ends_at?->format('d M Y H:i') ?? 'selamanya' }}
                        @else
                            &nbsp;·&nbsp; Tidak ada batas waktu
                        @endif
                    </p>
                </div>
                @if($disc->isCurrentlyActive())
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700" style="margin-right: 150px; margin-left: 120px;">Aktif</span>
                @elseif(!$disc->is_active)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500" style="margin-right: 150px; margin-left: 120px;">Nonaktif</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600" style="margin-right: 150px; margin-left: 120px;">Kadaluarsa</span>
                @endif
                <div class="flex items-center justify-center gap-2">
    <form method="POST" action="{{ route('manager.products.discounts.toggle', [$product, $disc]) }}">
        @csrf @method('PATCH')
        <button type="submit"
    class="px-3 py-1 rounded-lg text-sm font-medium transition-colors
        {{ $disc->is_active ? 'bg-yellow-400 text-white hover:bg-yellow-500' : 'bg-green-500 text-white hover:bg-green-600' }}">
    {{ $disc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
</button>
    </form>
    <form method="POST" action="{{ route('manager.products.discounts.destroy', [$product, $disc]) }}"
          onsubmit="return confirm('Hapus diskon ini?')">
        @csrf @method('DELETE')
        <button type="submit"
    class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
    style="background-color: #ef4444; color: white;"
    onmouseover="this.style.backgroundColor='#dc2626'"
    onmouseout="this.style.backgroundColor='#ef4444'">
    Hapus
</button>
    </form>
</div>
            </div>
            @empty
            <div class="py-8 text-center text-gray-400 text-sm">Belum ada diskon untuk produk ini</div>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('editProduct', () => ({
        // FIX: baca tab dari session (dikirim saat simpan varian/diskon)
        tab: '{{ session("tab", "info") }}',
        variants: @json($variants),

        addVariant() {
            this.variants.push({ 
                id: null,   // ← tambah ini
                name: '', 
                type: 'ukuran', 
                price_adjustment: 0, 
                stock: 0, 
                is_available: false 
            });
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
            // Hapus border dashed saat foto diupload
            if (box) {
                box.style.border = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateDiscountLabel(type) {
    document.getElementById('discount-unit').textContent = type === 'percentage' ? '(%)' : '(Rp)';
}
// Auto hide flash message
setTimeout(() => {
    document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 3000);
</script>
@endpush
@endsection