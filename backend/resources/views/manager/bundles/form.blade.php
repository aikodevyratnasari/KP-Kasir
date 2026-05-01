@extends('layouts.app')
@section('title', isset($bundle) ? 'Edit Paket' : 'Tambah Paket Bundling')
@section('page-title', isset($bundle) ? 'Edit Paket' : 'Tambah Paket Bundling')

@section('content')
<div class="max-w-3xl mx-auto space-y-5"
     x-data="bundleForm({{ $products->toJson() }}, {{ isset($bundle) ? $bundle->items->toJson() : '[]' }}, {{ isset($bundle) ? $bundle->bundle_price : 'null' }})">

    <form method="POST"
          action="{{ isset($bundle) ? route('manager.bundles.update', $bundle) : route('manager.bundles.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($bundle)) @method('PUT') @endif

        {{-- INFO PAKET --}}
        <div class="card space-y-4 border-b border-gray-200 pb-4">
            <h3 class="font-semibold text-gray-800 border-b border-gray-200 pb-1">Informasi Paket</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="name"
                           value="{{ old('name', $bundle->name ?? '') }}"
                           placeholder="cth: Paket Hemat Makan Siang"
                           class="form-input @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                    <textarea name="description" rows="2" class="form-input"
                              placeholder="Ceritakan keunggulan paket ini">{{ old('description', $bundle->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Mulai <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', isset($bundle->starts_at) ? $bundle->starts_at?->format('Y-m-d\TH:i') : '') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', isset($bundle->ends_at) ? $bundle->ends_at?->format('Y-m-d\TH:i') : '') }}"
                           class="form-input">
                    <p class="mt-1 text-xs text-gray-400">Kosongkan = tidak ada batas waktu</p>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', ($bundle->is_active ?? true) ? '1' : '0') == '1' ? 'checked' : '' }}
                           class="rounded border-gray-300 accent-[#2D54BF] focus:ring-0"
                           style="outline:none !important; box-shadow:none !important;">
                    <label for="is_active" class="text-sm text-gray-700">Langsung aktif</label>
                </div>
            </div>
        </div>

        {{-- FOTO PAKET --}}
        <div class="card space-y-3 mt-4">
            <h3 class="font-semibold text-gray-800">Foto Paket <span class="text-gray-400 font-normal text-xs">(opsional)</span></h3>
            <div class="flex items-center gap-4">
                <div id="img-box" class="w-24 h-24 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center flex-shrink-0"
                     style="{{ isset($bundle) && $bundle->image ? '' : 'border:2px dashed #d1d5db;' }}">
                    @if(isset($bundle) && $bundle->image)
                        <img src="{{ Storage::url($bundle->image) }}" id="img-preview" class="w-full h-full object-cover">
                        <svg id="img-placeholder" class="hidden w-8 h-8 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    @else
                        <img id="img-preview" src="" class="w-full h-full object-cover hidden">
                        <svg id="img-placeholder" class="w-8 h-8 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    @endif
                </div>
                <div class="flex flex-col gap-1.5 w-fit">
                            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp"
                                   onchange="previewBundleImage(this); document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'"
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
        </div>

        {{-- PILIH PRODUK --}}
        <div class="card mt-4 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">Produk dalam Paket <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Minimal 1 produk. Tambahkan produk yang akan dibundel.</p>
                </div>
                <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-product-picker'))"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        style="border: 1.5px solid #c4b5fd; background-color: #ede9fe; color: #7c3aed;"
                            onmouseover="this.style.backgroundColor='#7c3aed'; this.style.color='white'; this.style.borderColor='#7c3aed';"
                            onmouseout="this.style.backgroundColor='#ede9fe'; this.style.color='#7c3aed'; this.style.borderColor='#c4b5fd';">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                    Tambah Produk
                </button>
            </div>

            <div x-show="selectedItems.length > 0" class="space-y-2">
                <div class="gap-2 text-xs font-semibold text-gray-500 uppercase px-3"
                    style="display:grid; grid-template-columns: 2.5fr 2.5fr 90px 80px 24px;">
                    <div>Produk</div>
                    <div>Varian</div>
                    <div style="text-align:center;">Qty</div>
                    <div style="text-align:left; padding-left:20px;">Harga</div>
                    <div></div>
                </div>
                <template x-for="(item, idx) in selectedItems" :key="item.key">
                    <div class="items-center rounded-xl px-3 py-2.5"
                        style="display:grid; grid-template-columns: 2.5fr 2.5fr 90px 80px 24px; gap:8px; background:#f9fafb; border:1px solid #e5e7eb;">
                        <input type="hidden" :name="`items[${idx}][product_id]`"         :value="item.product_id">
                        <input type="hidden" :name="`items[${idx}][product_variant_id]`"  :value="item.variant_id ?? ''">
                        <input type="hidden" :name="`items[${idx}][quantity]`"            :value="item.quantity">
                        <div>
                            <p class="text-sm font-medium text-gray-900 leading-tight" x-text="item.product_name"></p>
                    </div>
                <div x-data="{ open: false }" style="position:relative; width:85%; min-width:0;" @click.outside="open = false">
    <input type="hidden" :name="`items[${idx}][product_variant_id]`" :value="item.variant_id ?? ''">
    <button type="button"
        @click="open = !open"
        x-text="(() => {
            const v = item.variants.find(v => String(v.id) === String(item.variant_id));
            if (!v) return 'Tidak ada';
            const adj = Number(v.price_adjustment);
            return v.name.replace(/^\+\s*/, '') + (adj !== 0 ? ' (' + (adj > 0 ? '+' : '') + 'Rp' + formatRp(adj) + ')' : '');
        })()"
            style="width:100%; padding:5px 28px 5px 8px; font-size:12px; border-radius:8px; border:1px solid #d1d5db; background:white; color:#111827; cursor:pointer; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; position:relative;">
    </button>
    <span style="position:absolute; right:9px; top:50%; transform:translateY(-50%); pointer-events:none;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;color:#6b7280;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
    </span>
    <div x-show="open" x-cloak
         style="position:absolute; top:calc(85% + 4px); left:0; right:0; background:white; border:1px solid #d1d5db; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:50; overflow:hidden; max-height:180px; overflow-y:auto;">
        <div @click="item.variant_id = null; onVariantChange(idx); open = false;"
     class="varian-option"
     :class="!item.variant_id ? 'active' : ''">
    Tidak ada
</div>
        <template x-for="v in item.variants" :key="v.id">
            <div @click="item.variant_id = v.id; onVariantChange(idx); open = false;"
     class="varian-option"
     :class="String(v.id) === String(item.variant_id) ? 'active' : ''"
     x-text="v.name.replace(/^\+\s*/, '') + (Number(v.price_adjustment) !== 0 ? ' (' + (Number(v.price_adjustment) > 0 ? '+' : '') + 'Rp' + formatRp(v.price_adjustment) + ')' : '')">
</div>
        </template>
    </div>
</div>
            <div style="display:flex; align-items:center; justify-content:center; gap:6px;">
                <button type="button"
                    @click="item.quantity > 1 ? item.quantity-- : null; recalcNormal()"
                    style="width:22px;height:22px;border-radius:5px;background:#e5e7eb;border:0.5px solid #d1d5db;cursor:pointer;font-size:15px;line-height:1;display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                    onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
                    <span style="position:relative; top:-1px; line-height:1;">−</span>
                </button>

                <span class="text-sm font-semibold text-gray-800" style="min-width:16px; text-align:center;" x-text="item.quantity"></span>

                <button type="button"
                    @click="item.quantity++; recalcNormal()"
                    style="width:22px;height:22px;border-radius:5px;background:#e5e7eb;border:0.5px solid #d1d5db;cursor:pointer;font-size:15px;line-height:1;display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                    onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
                    <span style="position:relative; top:-1px; line-height:1;">+</span>
                </button>
            </div>
            <div style="text-align:left; padding-left:20px;">
                <span class="text-xs font-semibold text-gray-700" style="white-space:nowrap;" x-text="'Rp '+formatRp(item.effectivePrice * item.quantity)"></span>
            </div>
            <div style="display:flex; justify-content:center;">
                <button type="button" @click="removeItem(idx)"
                        style="color:#9ca3af;background:none;border:none;cursor:pointer;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;"
                        onmouseover="this.style.background='#fee2e2';this.style.color='#ef4444';"
                        onmouseout="this.style.background='none';this.style.color='#9ca3af';">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </template>
</div>
        </div>

        {{-- HARGA BUNDLE --}}
        <div class="card mt-4 space-y-4">
            <h3 class="font-semibold text-gray-800 border-b border-gray-200 pb-1">Harga Paket</h3>
            <div x-show="selectedItems.length > 0"
                 style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:14px 16px;">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Total harga normal produk</span>
                    <span class="text-sm font-semibold text-gray-800" x-text="'Rp ' + formatRp(normalTotal)"></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Harga paket (yang dibayar)</span>
                    <span class="text-sm font-bold text-green-600" x-text="'Rp ' + formatRp(Number(bundlePrice) || 0)"></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                    <span class="text-xs text-gray-500">Pelanggan hemat</span>
                    <span class="text-xs font-bold"
                          :style="savings > 0 ? 'color:#16a34a' : 'color:#9ca3af'"
                          x-text="savings > 0 ? 'Rp ' + formatRp(savings) + ' (' + savingsPct + '%)' : 'Tidak ada potongan'"></span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Paket (Rp) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="bundle_price" id="bundle_price_input"
                           x-model="bundlePrice"
                           @input="bundlePrice = $event.target.value"
                           min="0" step="500"
                           placeholder="Masukkan harga paket..."
                           class="form-input @error('bundle_price') border-red-400 @enderror pr-32">
                    <button type="button"
                            @click="bundlePrice = normalTotal"
                            x-show="selectedItems.length > 0"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-xs px-2 py-1 rounded-full transition-colors"
                            style="background:#ede9fe; color:#7c3aed; border:1.5px solid #c4b5fd;"
                            onmouseover="this.style.background='#7c3aed'; this.style.color='white'; this.style.borderColor='#7c3aed';"
                            onmouseout="this.style.background='#ede9fe'; this.style.color='#7c3aed'; this.style.borderColor='#c4b5fd';">
                        Pakai total normal
                    </button>
                </div>
                @error('bundle_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-xs text-gray-400">
                    Isi dengan harga yang lebih rendah dari total normal agar pelanggan mendapat keuntungan.
                </p>
            </div>
        </div>

        {{-- TOMBOL SIMPAN --}}
        <div class="flex justify-between mt-4">
            <a href="{{ route('manager.products.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg"
               style="border:1.5px solid #dcdcdc; background:white; color:#374151;"
               onmouseover="this.style.backgroundColor='#f3f4f6';"
               onmouseout="this.style.backgroundColor='white';">
                Kembali
            </a>
            <button type="submit"
                    :disabled="selectedItems.length === 0"
                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    style="background-color:#2D54BF; border:1px solid #2D54BF;"
                    onmouseover="if(!this.disabled)this.style.backgroundColor='#1e3d8f';"
                    onmouseout="this.style.backgroundColor='#2D54BF';">
                {{ isset($bundle) ? 'Simpan Perubahan' : 'Buat Paket' }}
            </button>
        </div>
    </form>

</div>{{-- end x-data bundleForm --}}

{{-- ══ MODAL PILIH PRODUK ══ --}}
<div x-data="{
        open:    false,
        search:  '',
        products: {{ $products->toJson() }},
        get filtered() {
            const q = this.search.toLowerCase();
            return q ? this.products.filter(p => p.name.toLowerCase().includes(q)) : this.products;
        }
     }"
     x-init="
         window.addEventListener('open-product-picker', () => {
             open   = true;
             search = '';
             $nextTick(() => $refs.pickerSearch && $refs.pickerSearch.focus());
         });
     "
     x-show="open"
     x-cloak
     class="fixed inset-0 flex items-center justify-center z-50 px-4"
     style="background:rgba(0,0,0,0.5);"
     @click.self="open = false"
     @keydown.escape.window="open = false">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col" style="max-height:80vh;">
        <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div>
                <h3 style="font-size:14px; font-weight:700; color:#111827; margin:0;">Pilih Produk</h3>
                <p style="font-size:11px; color:#9ca3af; margin:3px 0 0;">Klik produk untuk menambahkan ke paket</p>
            </div>
            <button type="button" @click="open = false"
                    style="color:#9ca3af; background:none; border:none; cursor:pointer; width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center;"
                    onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='none';">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="padding:12px 16px; border-bottom:1px solid #f1f5f9; flex-shrink:0;">
            <input type="text" x-ref="pickerSearch" x-model="search"
                   placeholder="Cari nama produk..." class="form-input text-sm py-2">
        </div>
        <div style="overflow-y:auto; flex:1; padding:8px 12px;">
            <template x-for="p in filtered" :key="p.id">
                <div class="picker-row"
                     @click="window.dispatchEvent(new CustomEvent('product-picked', { detail: p })); open = false; search = '';">
                    <div>
                        <p style="font-size:13px; font-weight:600; color:#111827; margin:0;" x-text="p.name"></p>
                        <p style="font-size:11px; color:#9ca3af; margin:2px 0 0;" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(p.price)"></p>
                    </div>
                    <div style="flex-shrink:0;">
                        <span class="picker-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:10px;height:10px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Tambah
                        </span>
                    </div>
                </div>
            </template>
            <div x-show="filtered.length === 0" class="text-center py-8 text-sm text-gray-400">
                Produk tidak ditemukan
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
[x-cloak] { display: none !important; }
.picker-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 12px; border-radius: 10px; cursor: pointer;
    margin-bottom: 4px; border: 1.5px solid transparent;
    transition: background 0.15s, border-color 0.15s, transform 0.1s, box-shadow 0.15s;
    user-select: none;
}
.picker-row:hover { background:#f5f3ff; border-color:#c4b5fd; box-shadow:0 2px 8px rgba(139,92,246,0.10); transform:translateX(2px); }
.picker-row:active { transform:scale(0.98); background:#ede9fe; }
.picker-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: #ede9fe; color: #7c3aed; font-size: 10px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px; border: 1.5px solid #c4b5fd;
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.1s;
    white-space: nowrap;
}
.picker-row:hover .picker-badge { background:#7c3aed; color:#fff; border-color:#7c3aed; transform:scale(1.05); }
.picker-row:active .picker-badge { transform:scale(0.97); }
select {
    border-radius: 8px !important;
}
.varian-option {
    padding: 8px 12px;
    font-size: 12px;
    cursor: pointer;
    color: #111827;
    transition: background 0.1s;
}
.varian-option:hover {
    background: #f3f4f6 !important;
}
.varian-option.active {
    background: #ede9fe !important;
    color: #7c3aed !important;
    font-weight: 500;
}
@endpush

@push('scripts')
<script>
function bundleForm(allProducts, existingItems, existingBundlePrice) {
    return {
        allProducts:   allProducts,
        selectedItems: [],
        normalTotal:   0,
        // FIX: default null → akan diset ke normalTotal saat produk pertama ditambah
        bundlePrice:   existingBundlePrice !== null ? existingBundlePrice : null,
        _bundlePriceManuallySet: existingBundlePrice !== null,

        get savings() {
            const s = this.normalTotal - Number(this.bundlePrice);
            return s > 0 ? s : 0;
        },
        get savingsPct() {
            if (this.normalTotal <= 0) return 0;
            return Math.round(this.savings / this.normalTotal * 100);
        },

        init() {
            window.addEventListener('product-picked', (e) => {
                this.addProduct(e.detail);
            });

            if (existingItems && existingItems.length > 0) {
                existingItems.forEach(ei => {
                    const product = this.allProducts.find(p => p.id === ei.product_id);
                    if (!product) return;
                    const variantId = ei.product_variant_id ?? null;
                    const variant   = product.variants?.find(v => v.id === variantId) ?? null;
                    const adj       = variant ? Number(variant.price_adjustment) : 0;
                    this.selectedItems.push({
                        key:            `p_${product.id}_${variantId ?? 0}_${Date.now()}`,
                        product_id:     product.id,
                        product_name:   product.name,
                        base_price:     Number(product.price),
                        effectivePrice: Number(product.price) + adj,
                        variant_id:     variantId,
                        variants:       product.variants ?? [],
                        quantity:       ei.quantity ?? 1,
                    });
                });
                this.recalcNormal();
            }
        },

        addProduct(product) {
            this.selectedItems.push({
                key:            `p_${product.id}_0_${Date.now()}`,
                product_id:     product.id,
                product_name:   product.name,
                base_price:     Number(product.price),
                effectivePrice: Number(product.price),
                variant_id:     null,
                variants:       product.variants ?? [],
                quantity:       1,
            });
            this.recalcNormal();
        },

        removeItem(idx) {
            this.selectedItems.splice(idx, 1);
            this.recalcNormal();
        },

        onVariantChange(idx) {
            const item    = this.selectedItems[idx];
            const variant = item.variants.find(v => String(v.id) === String(item.variant_id));
            const adj     = variant ? Number(variant.price_adjustment) : 0;
            item.effectivePrice = item.base_price + adj;
            this.recalcNormal();
        },

        recalcNormal() {
            this.normalTotal = this.selectedItems.reduce(
                (sum, i) => sum + (i.effectivePrice * i.quantity), 0
            );
            // FIX: Selalu default ke total normal kecuali sudah diisi manual oleh user
            // (untuk create baru: selalu ikuti total; untuk edit: pakai nilai tersimpan)
            if (!this._bundlePriceManuallySet) {
                this.bundlePrice = this.normalTotal;
            }
        },

        formatRp(val) {
            return new Intl.NumberFormat('id-ID').format(Math.round(Number(val) || 0));
        },
    };
}

function previewBundleImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview     = document.getElementById('img-preview');
            const placeholder = document.getElementById('img-placeholder');
            const box         = document.getElementById('img-box');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
            if (box) box.style.border = 'none';
            document.getElementById('fileName').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Tandai bundlePrice sudah diubah manual oleh user (bukan auto-recalc)
document.addEventListener('DOMContentLoaded', () => {
    const priceInput = document.getElementById('bundle_price_input');
    if (priceInput) {
        priceInput.addEventListener('input', () => {
            // Set flag di Alpine component
            const el = priceInput.closest('[x-data]');
            if (el && el._x_dataStack) {
                el._x_dataStack[0]._bundlePriceManuallySet = true;
            }
        });
    }
});
</script>
@endpush
@endsection