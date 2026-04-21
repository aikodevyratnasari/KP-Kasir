@extends('layouts.app')
@section('title', 'Buat Pesanan')
@section('page-title', 'Buat Pesanan')

@section('content')
@php $taxRate = auth()->user()->store->tax_rate ?? 10; @endphp
<div style="display:flex; gap:0; height:calc(100vh - 64px); overflow:hidden;"
     x-data="orderForm({{ $taxRate }})" x-init="mounted()">

    {{-- ── KIRI: Form + Menu (scrollable) ── --}}
    <div style="flex:1; overflow-y:auto; padding:20px 16px 20px 0;">
        <form method="POST" action="{{ route('cashier.orders.store') }}" id="order-form">
            @csrf
            <div class="card mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pesanan <span class="text-red-500">*</span></label>
                        <select name="order_type" x-model="orderType" class="form-input">
                            <option value="dine_in">Dine-In</option>
                            <option value="takeaway">Takeaway</option>
                        </select>
                    </div>
                    <div x-show="orderType === 'dine_in'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Meja <span class="text-red-500">*</span></label>
                        <select name="table_id" class="form-input">
                            <option value="">Pilih Meja</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}"
                                    {{ (request('table') == $table->id || old('table_id') == $table->id) ? 'selected' : '' }}>
                                    Meja {{ $table->number }} ({{ $table->capacity }} kursi)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Pelanggan <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                        </label>
                        <input type="text" name="customer_name"
                               value="{{ old('customer_name', $prefilledCustomerName ?? '') }}"
                               placeholder="cth: Budi Santoso" class="form-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan</label>
                        <textarea name="notes" rows="2" class="form-input"
                                  placeholder="Catatan khusus untuk seluruh pesanan...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── Pilih Menu ── --}}
            @foreach($categories as $category)
                @if($category->products->where('is_available', true)->count() > 0)
                <div style="margin-bottom:32px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; padding-top:4px;">
                        <span style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.09em; white-space:nowrap;">{{ $category->name }}</span>
                        <div style="flex:1; height:1px; background:#e5e7eb;"></div>
                        <span style="font-size:10px; color:#9ca3af; white-space:nowrap;">{{ $category->products->where('is_available', true)->count() }} item</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($category->products->where('is_available', true) as $product)
                        @php
                            $variants     = $product->variants->where('is_available', true)->values();
                            $hasVariants  = $variants->count() > 0;
                            $activeDisc   = $product->discounts->first(fn($d) => $d->isCurrentlyActive());
                            $displayPrice = $activeDisc ? $activeDisc->discountedPrice((float)$product->price) : (float)$product->price;
                            $hasDiscount  = $activeDisc !== null;
                        @endphp
                        <div style="background:#fff; border:1.5px solid #e5e7eb; border-radius:12px; overflow:hidden; cursor:pointer; transition:border-color 0.15s, box-shadow 0.15s, transform 0.15s; user-select:none;"
                             onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.12)'; this.style.transform='translateY(-2px)';"
                             onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; this.style.transform='none';"
                             onmousedown="this.style.transform='scale(0.97)';"
                             onmouseup="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.12)'; this.style.transform='translateY(-2px)';"
                             @if($hasVariants)
                                 @click="openVariantModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }}, {{ $variants->toJson() }})"
                             @else
                                 @click="addItem({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})"
                             @endif>
                            <div style="height:110px; background:#f8fafc; overflow:hidden; position:relative;">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                         style="width:100%; height:100%; object-fit:cover; display:block;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center; color:#d1d5db;">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:36px;height:36px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                                    </div>
                                @else
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#d1d5db;">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:36px;height:36px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                                    </div>
                                @endif
                                @if($hasVariants)
                                    <div style="position:absolute; top:6px; right:6px; background:#6366f1; color:white; font-size:9px; font-weight:700; padding:2px 7px; border-radius:8px; pointer-events:none;">
                                        {{ $variants->count() }} variasi
                                    </div>
                                @endif
                                @if($hasDiscount)
                                    <div style="position:absolute; top:6px; left:6px; background:#ef4444; color:white; font-size:9px; font-weight:700; padding:2px 7px; border-radius:8px; pointer-events:none;">
                                        {{ $activeDisc->type === 'percentage' ? number_format($activeDisc->value,0).'%' : 'DISKON' }} OFF
                                    </div>
                                @endif
                            </div>
                            <div style="padding:10px 12px 12px;">
                                <p style="font-size:13px; font-weight:600; color:#111827; margin:0 0 3px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ $product->name }}</p>
                                @if($product->description)
                                    <p style="font-size:11px; color:#9ca3af; margin:0 0 6px; line-height:1.3; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;">{{ $product->description }}</p>
                                @else
                                    <div style="margin-bottom:6px;"></div>
                                @endif
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                    <div>
                                        <span style="font-size:13px; font-weight:700; color:#4f46e5;">
                                            @if($hasVariants) Mulai @endif Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                        </span>
                                        @if($hasDiscount)
                                            <span style="font-size:10px; color:#9ca3af; text-decoration:line-through; display:block; margin-top:1px;">
                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($product->track_stock)
                                        <span style="font-size:10px; color:#9ca3af; background:#f3f4f6; padding:2px 6px; border-radius:10px; flex-shrink:0;">Stok {{ $product->stock }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </form>
    </div>

    {{-- ── KANAN: Keranjang ── --}}
    <div id="cart-panel" style="width:320px; flex-shrink:0; display:flex; flex-direction:column; border-left:1px solid #e5e7eb; background:#fff; overflow:hidden;">
        <div style="padding:16px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Keranjang</h3>
                <span x-show="items.length > 0" class="text-xs bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full" x-text="items.length + ' item'"></span>
            </div>
        </div>
        <div style="flex:1; overflow-y:auto; padding:12px;">
            <div x-show="items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 py-12">
    <div style="width:80px; height:80px; border-radius:50%; border:2px solid #e5e7eb; background:#f9fafb; display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:36px;height:36px; color:#d1d5db;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
    </div>
    <p class="text-sm">Klik menu untuk menambahkan</p>
</div>
            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="item.key">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id" form="order-form">
                        <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity" form="order-form">
                        <template x-if="item.variant_id">
                            <input type="hidden" :name="'items['+index+'][variant_id]'" :value="item.variant_id" form="order-form">
                        </template>
                        <div class="flex items-start justify-between mb-1.5">
                            <div class="flex-1 leading-tight">
                                <p class="text-sm font-semibold text-gray-900" x-text="item.name"></p>
                                <p x-show="item.variant_name" class="text-xs text-indigo-500 font-medium mt-0.5" x-text="item.variant_name"></p>
                            </div>
                            <button type="button" @click="removeItem(index)" 
                                class="ml-2 flex-shrink-0 transition-colors"
                                style="width:20px; height:20px; border-radius:50%; background:#f3f4f6; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#9ca3af;"
                                onmouseover="this.style.backgroundColor='#fee2e2'; this.style.color='#ef4444';"
                                onmouseout="this.style.backgroundColor='#f3f4f6'; this.style.color='#9ca3af';">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center" style="border:1.5px solid #d1d5db; border-radius:6px; overflow:hidden;">
    <button type="button" @click="item.quantity > 1 ? item.quantity-- : removeItem(index)"
        style="width:28px; height:28px; background:white; border:none; color:#374151; font-size:16px; font-weight:500; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1; padding:0; margin-bottom: 3px;"
        onmouseover="this.style.backgroundColor='#f3f4f6'"
        onmouseout="this.style.backgroundColor='white'">−</button>
    <span style="width:32px; height:28px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:#111827; border-left:1.5px solid #d1d5db; border-right:1.5px solid #d1d5db;" x-text="item.quantity"></span>
    <button type="button" @click="item.quantity++"
        style="width:28px; height:28px; background:white; border:none; color:#2D54BF; font-size:16px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1; padding:0; margin-bottom: 3px;"
        onmouseover="this.style.backgroundColor='#f3f4f6'"
        onmouseout="this.style.backgroundColor='white'">+</button>
</div>
                            <span class="text-sm font-bold text-indigo-600" x-text="'Rp ' + formatRp(item.price * item.quantity)"></span>
                        </div>
                        <div class="mt-2">
                            <input type="text" :name="'items['+index+'][special_notes]'" form="order-form"
                                   placeholder="Catatan item..." class="form-input text-xs py-1 bg-white">
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div style="padding:14px; border-top:1px solid #e5e7eb; flex-shrink:0; background:#fff;">
            <div x-show="items.length > 0">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm text-gray-700">Subtotal</span>
                    <span class="text-sm text-gray-700" x-text="'Rp ' + formatRp(subtotal)"></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-gray-400">Pajak ({{ $taxRate }}%)</span>
                    <span class="text-xs text-gray-400" x-text="'Rp ' + formatRp(taxAmount)"></span>
                </div>
                <div style="height:1.5px; background:#d1d5db; margin-bottom:8px;"></div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-bold text-gray-700">Total</span>
                    <span class="text-sm font-bold text-gray-900" x-text="'Rp ' + formatRp(grandTotal)"></span>
                </div>
                <button type="submit" form="order-form"
                        class="w-full justify-center py-3 text-base inline-flex items-center gap-2 font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">
                    Buat Pesanan
                </button>
            </div>
            <div x-show="items.length === 0" class="text-center text-xs text-gray-400">Tambahkan item untuk melanjutkan</div>
        </div>
    </div>

    {{-- ── MODAL PILIH VARIAN ── --}}
    <div :style="variantModal.open
                    ? 'display:flex; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;'
                    : 'display:none;'"
         @click.self="variantModal.open = false">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:420px; margin:16px; max-height:calc(100vh - 32px); box-shadow:0 24px 60px rgba(0,0,0,0.25); display:flex; flex-direction:column; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div>
                    <h3 style="font-size:14px; font-weight:700; color:#111827; margin:0;" x-text="variantModal.productName"></h3>
                    <p style="font-size:11px; color:#9ca3af; margin:4px 0 0;">Pilih variasi produk</p>
                </div>
                <button type="button" @click="variantModal.open = false"
                        style="color:#9ca3af; background:none; border:none; cursor:pointer; padding:4px; border-radius:6px; display:flex; align-items:center; justify-content:center;"
                        onmouseover="this.style.color='#374151'; this.style.background='#f3f4f6';"
                        onmouseout="this.style.color='#9ca3af'; this.style.background='none';">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div style="padding:12px 16px 16px; overflow-y:auto; flex:1;">
                <template x-for="v in variantModal.variants" :key="v.id">
                    <div style="border:1.5px solid #e5e7eb; border-radius:10px; padding:12px 14px; margin-bottom:8px; cursor:pointer; background:#fff; transition:border-color 0.12s, background 0.12s;"
                         onmouseover="this.style.borderColor='#6366f1'; this.style.background='#f5f3ff';"
                         onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff';"
                         @click="addItemWithVariant(variantModal.productId, variantModal.productName, variantModal.basePrice, v.id, v.name, Number(v.price_adjustment||0)); variantModal.open = false">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <p style="font-size:13px; font-weight:600; color:#111827; margin:0;" x-text="v.name"></p>
                                <p style="font-size:11px; color:#9ca3af; margin:3px 0 0;"
                                x-text="v.type ? v.type.charAt(0).toUpperCase() + v.type.slice(1) : ''"></p>
                            </div>
                            <div style="text-align:right; flex-shrink:0; margin-left:12px;">
                                <p style="font-size:13px; font-weight:700; color:#4f46e5; margin:0;"
                                   x-text="'Rp ' + formatRp(variantModal.basePrice + Number(v.price_adjustment||0))"></p>
                                <p x-show="Number(v.price_adjustment) !== 0" style="font-size:10px; color:#9ca3af; margin:2px 0 0;"
                                   x-text="(Number(v.price_adjustment)>0?'+':'-')+' Rp '+formatRp(Math.abs(Number(v.price_adjustment)))"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    {{-- Floating Cart Button (mobile) --}}
<button id="cart-toggle"
    @click="
        const panel = document.getElementById('cart-panel');
        const overlay = document.getElementById('cart-overlay');
        panel.classList.add('open');
        overlay.classList.add('active');
        document.getElementById('cart-toggle').style.display = 'none';
    "
    style="position:fixed; bottom:24px; right:24px; z-index:9998; width:56px; height:56px; border-radius:50%; background:#2D54BF; color:white; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 16px rgba(45,84,191,0.4); transition: transform 0.15s, background 0.15s;"
    onmouseover="this.style.backgroundColor='#1e3d8f'; this.style.transform='scale(1.08)';"
    onmouseout="this.style.backgroundColor='#2D54BF'; this.style.transform='scale(1)';"
    onmousedown="this.style.transform='scale(0.95)';"
    onmouseup="this.style.transform='scale(1.08)';">
    <svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
    </svg>
    <span x-show="items.length > 0"
          x-text="items.length"
          style="position:absolute; top:-2px; right:-2px; background:#ef4444; color:white; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid white;">
    </span>
</button>

<style>
/* @media (min-width: 769px) {
    #cart-panel { display: flex !important; position: static !important; width: 320px !important; max-height: unset !important; border-radius: 0 !important; box-shadow: none !important; flex-direction: column !important; }
    #cart-toggle { display: none !important; }
}
@media (max-width: 768px) {
    #cart-panel { display: none; position: fixed !important; bottom: 90px !important; right: 16px !important; width: calc(100vw - 32px) !important; max-height: 70vh !important; border-radius: 16px !important; border: 1px solid #e5e7eb !important; box-shadow: 0 8px 32px rgba(0,0,0,0.15) !important; z-index: 9997 !important; }
    #cart-toggle { display: flex !important; }
} */

    .field-error {
    border: 2px solid #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,0.1) !important;
    border-radius: 8px;
}

 #cart-panel {
    position: fixed !important;
    top: 0;
    right: 0;
    height: 100vh;
    width: 340px;

    background: #fff;
    border-left: 1px solid #e5e7eb;
    box-shadow: -8px 0 32px rgba(0,0,0,0.15);

    display: flex;
    flex-direction: column;

    transform: translateX(100%);
    transition: transform 0.25s ease;

    z-index: 9997;
}

/* kondisi terbuka */
#cart-panel.open {
    transform: translateX(0);
}

#cart-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 9996;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}

#cart-overlay.active {
    opacity: 1;
    pointer-events: all;
}
</style>
</style>
<div id="cart-overlay"></div>
</div>

@push('scripts')
<script>
function orderForm(taxRate) {
    return {
        orderType: '{{ request('table') ? 'dine_in' : old('order_type', request('type', 'dine_in')) }}',
        items: [],
        taxRate: taxRate,
        variantModal: { open: false, productId: null, productName: '', basePrice: 0, variants: [] },
        get subtotal()   { return this.items.reduce((s, i) => s + (i.price * i.quantity), 0); },
        get taxAmount()  { return Math.round(this.subtotal * this.taxRate / 100); },
        get grandTotal() { return this.subtotal + this.taxAmount; },
        openVariantModal(id, name, price, variants) {
            this.variantModal = { open: true, productId: id, productName: name, basePrice: Number(price), variants };
        },
        addItem(id, name, price) {
            const key = 'p_' + id;
            const ex  = this.items.find(i => i.key === key);
            if (ex) { ex.quantity++; return; }
            this.items.push({ key, product_id: id, name, price: Number(price), quantity: 1, variant_id: null, variant_name: null });
        },
        addItemWithVariant(productId, productName, basePrice, variantId, variantName, priceAdjustment) {
            const price = Number(basePrice) + (Number(priceAdjustment) || 0);
            const key   = 'p_' + productId + '_v_' + variantId;
            const ex    = this.items.find(i => i.key === key);
            if (ex) { ex.quantity++; return; }
            this.items.push({ key, product_id: productId, name: productName, price, quantity: 1, variant_id: variantId, variant_name: variantName });
        },
        removeItem(index) { this.items.splice(index, 1); },
        formatRp(val) { return new Intl.NumberFormat('id-ID').format(Math.round(val)); },
        mounted() {
    const panel = document.getElementById('cart-panel');
    const overlay = document.getElementById('cart-overlay');
    const cartBtn = document.getElementById('cart-toggle');

    const closeCart = () => {
        panel.classList.remove('open');
        overlay.classList.remove('active');
        cartBtn.style.display = 'flex';
    };

    panel.classList.remove('open');
    overlay.classList.remove('active');

    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) closeCart();
    });

    if (overlay) {
        overlay.addEventListener('click', closeCart);
    }
}
    }
}
document.getElementById('order-form').addEventListener('submit', function(e) {
    const tableSelect = document.querySelector('select[name="table_id"]');
    const orderType = document.querySelector('select[name="order_type"]').value;

    document.querySelectorAll('.field-error').forEach(el => {
        el.classList.remove('field-error');
    });

    if (orderType === 'dine_in' && tableSelect && !tableSelect.value) {
        tableSelect.classList.add('field-error');
    }
});

document.querySelector('select[name="table_id"]')?.addEventListener('change', function() {
    if (this.value) {
        this.classList.remove('field-error');
    }
});
</script>
@endpush
@endsection