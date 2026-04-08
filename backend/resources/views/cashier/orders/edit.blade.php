@extends('layouts.app')
@section('title', 'Edit Pesanan')
@section('page-title', 'Edit Pesanan')

@section('content')
<div style="display:flex; gap:0; height:calc(100vh - 64px); overflow:hidden;"
     x-data="orderForm({{ json_encode($order->items->map(fn($i) => [
         'key'           => 'p_' . $i->product_id . ($i->variant_id ? '_v_' . $i->variant_id : ''),
         'product_id'    => $i->product_id,
         'name'          => $i->product_name,
         'price'         => (float) $i->unit_price,
         'quantity'      => $i->quantity,
         'variant_id'    => $i->variant_id ?? null,
         'variant_name'  => ($i->variant_id && $i->variant) ? $i->variant->name : null,
         'discount_label'=> null,
     ])) }})">

    {{-- ── KIRI: Catatan + Menu (scrollable) ── --}}
    <div style="flex:1; overflow-y:auto; padding:20px 16px 20px 0;">

        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('cashier.orders.show', $order) }}" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
            </a>
            <h1 class="page-title">Edit Pesanan #{{ $order->order_number }}</h1>
        </div>

        <form method="POST" action="{{ route('cashier.orders.update', $order) }}" id="order-form">
            @csrf @method('PUT')
            <div class="card mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan</label>
                <textarea name="notes" rows="2" class="form-input"
                          placeholder="Catatan khusus untuk seluruh pesanan...">{{ old('notes', $order->notes) }}</textarea>
            </div>

            {{-- Pilih Menu — identik dengan create --}}
            @foreach($categories as $category)
                @if($category->products->where('is_available', true)->count() > 0)
                <div style="margin-bottom:32px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; padding-top:4px;">
                        <span style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.09em; white-space:nowrap;">
                            {{ $category->name }}
                        </span>
                        <div style="flex:1; height:1px; background:#e5e7eb;"></div>
                        <span style="font-size:10px; color:#9ca3af; white-space:nowrap;">
                            {{ $category->products->where('is_available', true)->count() }} item
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($category->products->where('is_available', true) as $product)
                        @php
                            $variants      = $product->variants->where('is_available', true)->values();
                            $hasVariants   = $variants->count() > 0;
                            $activeDisc    = $product->discounts->first(fn($d) => $d->isCurrentlyActive());
                            $displayPrice  = $activeDisc ? $activeDisc->discountedPrice((float)$product->price) : (float)$product->price;
                            $hasDiscount   = $activeDisc !== null;
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
                                        {{ $activeDisc->type === 'percentage' ? $activeDisc->value.'%' : 'DISKON' }} OFF
                                    </div>
                                @endif
                            </div>

                            <div style="padding:10px 12px 12px;">
                                <p style="font-size:13px; font-weight:600; color:#111827; margin:0 0 3px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                    {{ $product->name }}
                                </p>
                                @if($product->description)
                                    <p style="font-size:11px; color:#9ca3af; margin:0 0 6px; line-height:1.3; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;">
                                        {{ $product->description }}
                                    </p>
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
                                        <span style="font-size:10px; color:#9ca3af; background:#f3f4f6; padding:2px 6px; border-radius:10px; flex-shrink:0;">
                                            Stok {{ $product->stock }}
                                        </span>
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
    <div style="width:320px; flex-shrink:0; display:flex; flex-direction:column; border-left:1px solid #e5e7eb; background:#fff; overflow:hidden;">
        <div style="padding:16px; border-bottom:1px solid #f1f5f9; flex-shrink:0;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Keranjang</h3>
                <span x-show="items.length > 0" class="text-xs bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full" x-text="items.length + ' item'"></span>
            </div>
        </div>
        <div style="flex:1; overflow-y:auto; padding:12px;">
            <div x-show="items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
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
                                <p x-show="item.discount_label" class="text-xs text-red-500 font-medium mt-0.5" x-text="item.discount_label"></p>
                            </div>
                            <button type="button" @click="removeItem(index)" class="ml-2 text-gray-300 hover:text-red-500 transition-colors flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="item.quantity > 1 ? item.quantity-- : removeItem(index)"
                                        class="w-7 h-7 rounded-full bg-white border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-100 flex items-center justify-center">−</button>
                                <span class="text-sm font-bold w-6 text-center" x-text="item.quantity"></span>
                                <button type="button" @click="item.quantity++"
                                        class="w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 flex items-center justify-center">+</button>
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
        <div style="padding:14px; border-top:1px solid #f1f5f9; flex-shrink:0; background:#fff;">
            <div x-show="items.length > 0">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-gray-500">Subtotal</span>
                    <span class="text-lg font-bold text-gray-900" x-text="'Rp ' + formatRp(total)"></span>
                </div>
                <button type="submit" form="order-form"
                        class="btn-primary w-full justify-center py-3 text-base inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan Perubahan
                </button>
            </div>
            <div x-show="items.length === 0" class="text-center text-xs text-gray-400">Tambahkan item untuk melanjutkan</div>
        </div>
    </div>

    {{-- ── MODAL PILIH VARIAN — identik dengan create ── --}}
    <div :style="variantModal.open
                    ? 'display:flex; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;'
                    : 'display:none;'"
         @click.self="variantModal.open = false">

        <div style="background:#fff; border-radius:16px; width:100%; max-width:420px;
                    margin:16px; max-height:calc(100vh - 32px);
                    box-shadow:0 24px 60px rgba(0,0,0,0.25);
                    display:flex; flex-direction:column; overflow:hidden;">

            <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9;
                        display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div>
                    <h3 style="font-size:14px; font-weight:700; color:#111827; margin:0;" x-text="variantModal.productName"></h3>
                    <p style="font-size:11px; color:#9ca3af; margin:4px 0 0;">Pilih variasi produk</p>
                </div>
                <button type="button" @click="variantModal.open = false"
                        style="color:#9ca3af; background:none; border:none; cursor:pointer; padding:4px; border-radius:6px; display:flex; align-items:center; justify-content:center;"
                        onmouseover="this.style.color='#374151'; this.style.background='#f3f4f6';"
                        onmouseout="this.style.color='#9ca3af'; this.style.background='none';">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div style="padding:12px 16px 16px; overflow-y:auto; flex:1;">
                <div style="border:1.5px solid #e5e7eb; border-radius:10px; padding:12px 14px; margin-bottom:8px; cursor:pointer; background:#fff; transition:border-color 0.12s, background 0.12s;"
                     onmouseover="this.style.borderColor='#6366f1'; this.style.background='#f5f3ff';"
                     onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff';"
                     @click="addItemWithVariant(variantModal.productId, variantModal.productName, variantModal.basePrice, null, null, 0, variantModal.discountLabel); variantModal.open = false">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <p style="font-size:13px; font-weight:600; color:#111827; margin:0;">Standar</p>
                            <p style="font-size:11px; color:#9ca3af; margin:3px 0 0;">Tanpa variasi</p>
                        </div>
                        <span style="font-size:13px; font-weight:700; color:#4f46e5;" x-text="'Rp ' + formatRp(variantModal.basePrice)"></span>
                    </div>
                </div>
                <template x-for="v in variantModal.variants" :key="v.id">
                    <div style="border:1.5px solid #e5e7eb; border-radius:10px; padding:12px 14px; margin-bottom:8px; cursor:pointer; background:#fff; transition:border-color 0.12s, background 0.12s;"
                         onmouseover="this.style.borderColor='#6366f1'; this.style.background='#f5f3ff';"
                         onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff';"
                         @click="addItemWithVariant(variantModal.productId, variantModal.productName, variantModal.basePrice, v.id, v.name, Number(v.price_adjustment||0), variantModal.discountLabel); variantModal.open = false">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <p style="font-size:13px; font-weight:600; color:#111827; margin:0;" x-text="v.name"></p>
                                <p style="font-size:11px; color:#9ca3af; margin:3px 0 0;" x-text="v.type"></p>
                            </div>
                            <div style="text-align:right; flex-shrink:0; margin-left:12px;">
                                <p style="font-size:13px; font-weight:700; color:#4f46e5; margin:0;"
                                   x-text="'Rp ' + formatRp(variantModal.basePrice + Number(v.price_adjustment||0))"></p>
                                <p x-show="Number(v.price_adjustment) !== 0" style="font-size:10px; color:#9ca3af; margin:2px 0 0;"
                                   x-text="(Number(v.price_adjustment)>0?'+':'')+' Rp '+formatRp(Math.abs(Number(v.price_adjustment)))"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function orderForm(initial = []) {
    return {
        items: initial,
        variantModal: { open: false, productId: null, productName: '', basePrice: 0, variants: [], discountLabel: null },
        get total() { return this.items.reduce((s, i) => s + (i.price * i.quantity), 0); },
        openVariantModal(id, name, price, variants, discountLabel) {
            this.variantModal = { open: true, productId: id, productName: name, basePrice: Number(price), variants, discountLabel: discountLabel || null };
        },
        addItem(id, name, price, discountLabel) {
            const key = 'p_' + id;
            const ex = this.items.find(i => i.key === key);
            if (ex) { ex.quantity++; return; }
            this.items.push({ key, product_id: id, name, price: Number(price), quantity: 1, variant_id: null, variant_name: null, discount_label: discountLabel || null });
        },
        addItemWithVariant(productId, productName, basePrice, variantId, variantName, priceAdjustment, discountLabel) {
            const price = Number(basePrice) + (Number(priceAdjustment) || 0);
            const key   = variantId ? ('p_' + productId + '_v_' + variantId) : ('p_' + productId);
            const ex = this.items.find(i => i.key === key);
            if (ex) { ex.quantity++; return; }
            this.items.push({ key, product_id: productId, name: productName, price, quantity: 1, variant_id: variantId, variant_name: variantName, discount_label: discountLabel || null });
        },
        removeItem(index) { this.items.splice(index, 1); },
        formatRp(val) { return new Intl.NumberFormat('id-ID').format(Math.round(val)); }
    }
}
</script>
@endpush
@endsection