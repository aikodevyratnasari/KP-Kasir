@extends('layouts.app')
@section('title', 'Buat Pesanan')

@section('content')
<div class="space-y-6" x-data="orderForm()">
    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.orders.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="page-title">Buat Pesanan Baru</h1>
    </div>

    <form method="POST" action="{{ route('cashier.orders.store') }}">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Menu --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Tipe & Meja --}}
                <div class="card">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pesanan <span class="text-red-500">*</span></label>
                            <select name="order_type" x-model="orderType" class="form-input">
                                <option value="dine_in">Dine-In</option>
                                <option value="takeaway">Takeaway</option>
                            </select>
                        </div>
                        <div x-show="orderType === 'dine_in'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Meja <span class="text-red-500">*</span></label>
                            <select name="table_id" class="form-input">
                                <option value="">Pilih Meja</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}">Meja {{ $table->number }} ({{ $table->capacity }} kursi)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Catatan khusus..."></textarea>
                    </div>
                </div>

                {{-- Pilih Menu --}}
                @foreach($categories as $category)
                    @if($category->products->count() > 0)
                    <div class="card">
                        <h3 class="font-semibold text-gray-800 mb-3">{{ $category->name }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($category->products->where('is_available', true) as $product)
                                <div class="border border-gray-100 rounded-lg p-3 hover:border-indigo-200 hover:bg-indigo-50 cursor-pointer transition-colors"
                                     @click="addItem({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                            @if($product->description)
                                                <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($product->description, 40) }}</p>
                                            @endif
                                        </div>
                                        <span class="text-sm font-semibold text-indigo-600 ml-2 whitespace-nowrap">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @if($product->track_stock)
                                        <p class="text-xs text-gray-400 mt-1">Stok: {{ $product->stock }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            {{-- Keranjang --}}
            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <h3 class="font-semibold text-gray-800 mb-4">🧺 Keranjang</h3>

                    <div x-show="items.length === 0" class="text-center py-8 text-gray-400 text-sm">
                        Klik menu untuk menambahkan
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <template x-for="(item, index) in items" :key="item.product_id">
                            <div class="border border-gray-100 rounded-lg p-3">
                                <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">

                                <div class="flex justify-between items-start">
                                    <p class="text-sm font-medium text-gray-900 flex-1" x-text="item.name"></p>
                                    <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 ml-2 text-xs">✕</button>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="item.quantity = Math.max(1, item.quantity - 1)"
                                                class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs hover:bg-gray-200">−</button>
                                        <span class="text-sm font-semibold w-6 text-center" x-text="item.quantity"></span>
                                        <button type="button" @click="item.quantity++"
                                                class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs hover:bg-gray-200">+</button>
                                    </div>
                                    <span class="text-sm font-semibold text-indigo-600" x-text="'Rp ' + formatRp(item.price * item.quantity)"></span>
                                </div>
                                <div class="mt-2">
                                    <input type="text" :name="'items['+index+'][special_notes]'" placeholder="Catatan item..." class="form-input text-xs py-1">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="items.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-semibold text-gray-700">Subtotal</span>
                            <span class="font-bold text-lg text-gray-900" x-text="'Rp ' + formatRp(total)"></span>
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center">
                            Buat Pesanan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        orderType: 'dine_in',
        items: [],
        get total() {
            return this.items.reduce((s, i) => s + (i.price * i.quantity), 0);
        },
        addItem(id, name, price) {
            const existing = this.items.find(i => i.product_id === id);
            if (existing) { existing.quantity++; return; }
            this.items.push({ product_id: id, name, price, quantity: 1 });
        },
        removeItem(index) { this.items.splice(index, 1); },
        formatRp(val) { return new Intl.NumberFormat('id-ID').format(val); }
    }
}
</script>
@endpush
@endsection