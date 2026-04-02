@extends('layouts.app')
@section('title', 'Buat Pesanan')
@section('page-title', 'Buat Pesanan')

@section('content')
{{-- Full-height layout: sidebar kanan sticky tanpa scroll halaman --}}
<div style="display:flex; gap:0; height:calc(100vh - 64px); overflow:hidden;"
     x-data="orderForm()">

    {{-- ── KIRI: Form + Menu (scrollable) ── --}}
    <div style="flex:1; overflow-y:auto; padding:20px 16px 20px 0;">

        <!-- <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('cashier.orders.index') }}" class="text-gray-400 hover:text-gray-600 text-lg">←</a>
            <h1 class="page-title">Buat Pesanan Baru</h1>
        </div> -->

        <form method="POST" action="{{ route('cashier.orders.store') }}" id="order-form">
            @csrf

            {{-- ── Tipe & Meja ── --}}
            <div class="card mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipe Pesanan <span class="text-red-500">*</span>
                        </label>
                        <select name="order_type" x-model="orderType" class="form-input">
                            <option value="dine_in">Dine-In</option>
                            <option value="takeaway">Takeaway</option>
                        </select>
                    </div>
                    <div x-show="orderType === 'dine_in'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Meja <span class="text-red-500">*</span>
                        </label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan</label>
                        <textarea name="notes" rows="2" class="form-input"
                                  placeholder="Catatan khusus untuk seluruh pesanan..."></textarea>
                    </div>
                </div>
            </div>

            {{-- ── Pilih Menu (card dengan gambar) ── --}}
            @foreach($categories as $category)
                @if($category->products->where('is_available', true)->count() > 0)
                <div style="margin-bottom:32px;">
                    {{-- Header kategori dengan garis pemisah --}}
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
                        <div style="background:#fff; border:1.5px solid #e5e7eb; border-radius:12px; overflow:hidden; cursor:pointer; transition:all 0.15s; user-select:none;"
                             onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.12)'; this.style.transform='translateY(-2px)';"
                             onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; this.style.transform='none';"
                             onmousedown="this.style.transform='scale(0.97)';"
                             onmouseup="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.12)'; this.style.transform='translateY(-2px)';"
                             @click="addItem({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">

                            {{-- Gambar fixed 110px --}}
                            <div style="height:110px; background:#f8fafc; overflow:hidden;">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}"
                                         alt="{{ $product->name }}"
                                         style="width:100%; height:100%; object-fit:cover; display:block;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center; font-size:32px; color:#d1d5db;">🍽️</div>
                                @else
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:32px; color:#d1d5db;">🍽️</div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div style="padding:10px 12px 12px;">
                                <p style="font-size:13px; font-weight:600; color:#111827; margin:0 0 3px; line-height:1.35;
                                          display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                    {{ $product->name }}
                                </p>
                                @if($product->description)
                                    <p style="font-size:11px; color:#9ca3af; margin:0 0 8px; line-height:1.3;
                                              display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;">
                                        {{ $product->description }}
                                    </p>
                                @else
                                    <div style="margin-bottom:8px;"></div>
                                @endif
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <span style="font-size:13px; font-weight:700; color:#4f46e5;">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                    @if($product->track_stock)
                                        <span style="font-size:10px; color:#9ca3af; background:#f3f4f6; padding:2px 6px; border-radius:10px;">
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

    {{-- ── KANAN: Keranjang (fixed height, tidak ikut scroll) ── --}}
    <div style="width:320px; flex-shrink:0; display:flex; flex-direction:column; border-left:1px solid #e5e7eb; background:#fff; overflow:hidden;">

        {{-- Header keranjang --}}
        <div style="padding:16px; border-bottom:1px solid #f1f5f9; flex-shrink:0;">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Keranjang</h3>
                <span x-show="items.length > 0"
                      class="text-xs bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full"
                      x-text="items.length + ' item'"></span>
            </div>
        </div>

        {{-- Daftar item (scrollable) --}}
        <div style="flex:1; overflow-y:auto; padding:12px;">
            {{-- Kosong --}}
            <div x-show="items.length === 0"
                 class="flex flex-col items-center justify-center h-full text-gray-400 py-12">
                <span class="text-4xl mb-2">🛒</span>
                <p class="text-sm">Klik menu untuk menambahkan</p>
            </div>

            {{-- Item list --}}
            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="item.product_id">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id" form="order-form">
                        <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity" form="order-form">

                        <div class="flex items-start justify-between mb-1.5">
                            <p class="text-sm font-semibold text-gray-900 flex-1 leading-tight" x-text="item.name"></p>
                            <button type="button" @click="removeItem(index)"
                                    class="ml-2 text-gray-300 hover:text-red-500 transition-colors text-lg leading-none flex-shrink-0">
                                ×
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            {{-- Qty control --}}
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                        @click="item.quantity > 1 ? item.quantity-- : removeItem(index)"
                                        class="w-7 h-7 rounded-full bg-white border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-100 flex items-center justify-center">
                                    −
                                </button>
                                <span class="text-sm font-bold w-6 text-center" x-text="item.quantity"></span>
                                <button type="button" @click="item.quantity++"
                                        class="w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 flex items-center justify-center">
                                    +
                                </button>
                            </div>
                            <span class="text-sm font-bold text-indigo-600"
                                  x-text="'Rp ' + formatRp(item.price * item.quantity)"></span>
                        </div>

                        {{-- Catatan item --}}
                        <div class="mt-2">
                            <input type="text"
                                   :name="'items['+index+'][special_notes]'"
                                   form="order-form"
                                   placeholder="Catatan item..."
                                   class="form-input text-xs py-1 bg-white">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Footer: Total + Submit --}}
        <div style="padding:14px; border-top:1px solid #f1f5f9; flex-shrink:0; background:#fff;">
            <div x-show="items.length > 0">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-gray-500">Subtotal</span>
                    <span class="text-lg font-bold text-gray-900"
                          x-text="'Rp ' + formatRp(total)"></span>
                </div>
                <button type="submit" form="order-form"
                        class="btn-primary w-full justify-center py-3 text-base">
                    ✓ Buat Pesanan
                </button>
            </div>
            <div x-show="items.length === 0" class="text-center text-xs text-gray-400">
                Tambahkan item untuk melanjutkan
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        orderType: '{{ request('table') ? 'dine_in' : old('order_type', request('type', 'dine_in')) }}',
        items: [],
        get total() {
            return this.items.reduce((s, i) => s + (i.price * i.quantity), 0);
        },
        addItem(id, name, price) {
            const existing = this.items.find(i => i.product_id === id);
            if (existing) {
                existing.quantity++;
                // Animasi visual feedback
                this.$nextTick(() => {
                    const el = document.querySelector(`[data-pid="${id}"]`);
                    if (el) { el.classList.add('scale-105'); setTimeout(() => el.classList.remove('scale-105'), 200); }
                });
                return;
            }
            this.items.push({ product_id: id, name, price, quantity: 1 });
        },
        removeItem(index) { this.items.splice(index, 1); },
        formatRp(val) { return new Intl.NumberFormat('id-ID').format(Math.round(val)); }
    }
}
</script>
@endpush
@endsection