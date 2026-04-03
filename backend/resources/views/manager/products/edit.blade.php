@extends('layouts.app')
@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<div class="max-w-3xl space-y-5"
     x-data="{
         tab: '{{ session('tab', 'info') }}',
         trackStock: {{ $product->track_stock ? 'true' : 'false' }},
         imagePreview: null,
         handleImage(e) {
             const file = e.target.files[0];
             if (!file) return;
             const reader = new FileReader();
             reader.onload = ev => this.imagePreview = ev.target.result;
             reader.readAsDataURL(file);
         },
        variants: @json($variants),
         addVariant() {
             this.variants.push({ name:'', type:'ukuran', price_adjustment:0, stock:0, is_available:true });
         },
         removeVariant(i) {
             this.variants.splice(i, 1);
         }
     }">

    <a href="{{ route('manager.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali</a>

    {{-- TABS --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        {{-- Info tab --}}
        <button type="button" @click="tab = 'info'"
                :class="tab === 'info' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/>
            </svg>
            Info
        </button>
        {{-- Variasi tab --}}
        <button type="button" @click="tab = 'variants'"
                :class="tab === 'variants' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/>
            </svg>
            Variasi
        </button>
        {{-- Diskon tab --}}
        <button type="button" @click="tab = 'discounts'"
                :class="tab === 'discounts' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Diskon
        </button>
    </div>

    {{-- ══ TAB: INFO PRODUK ══ --}}
    <div x-show="tab === 'info'">
        <form method="POST" action="{{ route('manager.products.update', $product) }}"
              enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="space-y-4">
                {{-- Info Dasar --}}
                <div class="card space-y-4">
                    <h3 class="font-semibold text-gray-800">Informasi Produk</h3>
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

                {{-- Foto Produk --}}
                <div class="card space-y-3">
                    <h3 class="font-semibold text-gray-800">Foto Produk</h3>
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imagePreview">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'">
                                    @else
                                        {{-- image placeholder --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    @endif
                                </template>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                   @change="handleImage($event)"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100 cursor-pointer">
                            <p class="mt-1.5 text-xs text-gray-400">
                                JPG, PNG, WebP — maks. 2MB
                                @if($product->image) &nbsp;·&nbsp; Kosongkan jika tidak ingin mengganti foto @endif
                            </p>
                            @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Stok & Ketersediaan --}}
                <div class="card space-y-4">
                    <h3 class="font-semibold text-gray-800">Stok & Ketersediaan</h3>
                    <div class="flex flex-wrap gap-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" value="1"
                                   {{ old('is_available', $product->is_available) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Tersedia untuk dipesan</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="track_stock" value="0">
                            <input type="checkbox" name="track_stock" value="1"
                                   {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}
                                   @change="trackStock = $event.target.checked"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Lacak stok</span>
                        </label>
                    </div>
                    <div x-show="trackStock" x-transition class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0"
                                   class="form-input @error('stock') border-red-400 @enderror">
                            @error('stock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Stok Rendah</label>
                            <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', $product->low_stock_alert) }}" min="0"
                                   class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
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

            <form method="POST" action="{{ route('manager.products.variants.store', $product) }}">
                @csrf

                <div class="space-y-3" x-show="variants.length > 0">
                    <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-500 uppercase px-1">
                        <div class="col-span-3">Nama</div>
                        <div class="col-span-2">Tipe</div>
                        <div class="col-span-2">±Harga (Rp)</div>
                        <div class="col-span-2">Stok</div>
                        <div class="col-span-2">Tersedia</div>
                        <div class="col-span-1"></div>
                    </div>

                    <template x-for="(v, i) in variants" :key="i">
                        <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-lg p-2">
                            <div class="col-span-3">
                                <input type="text" :name="`variants[${i}][name]`" x-model="v.name"
                                       placeholder="cth: Porsi Kecil"
                                       class="form-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <select :name="`variants[${i}][type]`" x-model="v.type" class="form-input text-sm">
                                    <option value="ukuran">Ukuran</option>
                                    <option value="level">Level</option>
                                    <option value="topping">Topping</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="number" :name="`variants[${i}][price_adjustment]`"
                                       x-model="v.price_adjustment" step="500"
                                       placeholder="0"
                                       class="form-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <input type="number" :name="`variants[${i}][stock]`"
                                       x-model="v.stock" min="0"
                                       class="form-input text-sm">
                            </div>
                            <div class="col-span-2 flex justify-center">
                                <input type="checkbox" :name="`variants[${i}][is_available]`"
                                       x-model="v.is_available" value="1"
                                       class="rounded border-gray-300 text-indigo-600">
                            </div>
                            <div class="col-span-1 flex justify-center">
                                <button type="button" @click="removeVariant(i)"
                                        class="text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="variants.length === 0" class="text-center py-8 text-gray-400">
                    <div class="flex justify-center mb-2">
                        {{-- shuffle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/>
                        </svg>
                    </div>
                    <p class="text-sm">Belum ada variasi. Klik "+ Tambah Variasi" untuk menambahkan.</p>
                </div>

                <div class="flex justify-end mt-4" x-show="variants.length > 0">
                    <button type="submit" class="btn-primary">Simpan Variasi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ TAB: DISKON ══ --}}
    <div x-show="tab === 'discounts'" class="space-y-4">

        {{-- Form tambah diskon --}}
        <div class="card space-y-4">
            <h3 class="font-semibold text-gray-800">Tambah Diskon Baru</h3>

            <form method="POST" action="{{ route('manager.products.discounts.store', $product) }}"
                  x-data="{ dtype: 'percentage' }">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Promo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="cth: Promo Weekend, Diskon Ramadan"
                               class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon</label>
                        <select name="type" x-model="dtype" class="form-input">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nilai
                            <span x-text="dtype === 'percentage' ? '(%)' : '(Rp)'"></span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="value" min="0"
                               :max="dtype === 'percentage' ? 100 : ''"
                               :placeholder="dtype === 'percentage' ? 'cth: 20' : 'cth: 5000'"
                               class="form-input">
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
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="text-sm text-gray-700">Langsung aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end mt-3">
                    <button type="submit" class="btn-primary text-sm">Tambah Diskon</button>
                </div>
            </form>
        </div>

        {{-- Daftar diskon --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Daftar Diskon ({{ $product->discounts->count() }})</h3>
            </div>
            @forelse($product->discounts as $disc)
            <div class="px-4 py-3 flex items-center gap-4 border-b border-gray-50 last:border-0 hover:bg-gray-50">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 text-sm">{{ $disc->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <span class="font-semibold text-indigo-600">
                            {{ $disc->type === 'percentage' ? $disc->value . '%' : 'Rp ' . number_format($disc->value, 0, ',', '.') }}
                        </span>
                        @if($disc->starts_at || $disc->ends_at)
                            &nbsp;·&nbsp;
                            {{ $disc->starts_at?->format('d M Y H:i') ?? '—' }}
                            →
                            {{ $disc->ends_at?->format('d M Y H:i') ?? 'selamanya' }}
                        @else
                            &nbsp;·&nbsp; Tidak ada batas waktu
                        @endif
                    </p>
                </div>

                {{-- Status badge --}}
                @if($disc->isCurrentlyActive())
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Aktif
                    </span>
                @elseif(!$disc->is_active)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Nonaktif</span>
                @elseif($disc->starts_at && now()->lt($disc->starts_at))
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600 inline-flex items-center gap-1">
                        {{-- hourglass --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/>
                        </svg>
                        Belum mulai
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Kadaluarsa</span>
                @endif

                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('manager.products.discounts.toggle', [$product, $disc]) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs text-indigo-500 hover:underline">
                            {{ $disc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('manager.products.discounts.destroy', [$product, $disc]) }}"
                          onsubmit="return confirm('Hapus diskon ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-gray-400 text-sm">
                <div class="flex justify-center mb-2">
                    {{-- tag --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                Belum ada diskon untuk produk ini
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection