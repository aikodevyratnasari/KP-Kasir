@extends('layouts.app')
@section('title', 'Edit Pesanan')

@section('content')
<div class="space-y-6" x-data="orderForm({{ json_encode($order->items->map(fn($i) => ['product_id'=>$i->product_id,'name'=>$i->product_name,'price'=>$i->unit_price,'quantity'=>$i->quantity])) }})">
    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.orders.show', $order) }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="page-title">Edit Pesanan #{{ $order->order_number }}</h1>
    </div>

    <form method="POST" action="{{ route('cashier.orders.update', $order) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="card">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan</label>
                        <textarea name="notes" rows="2" class="form-input">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>

                @foreach($categories as $category)
                    @if($category->products->count() > 0)
                    <div class="card">
                        <h3 class="font-semibold text-gray-800 mb-3">{{ $category->name }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($category->products->where('is_available', true) as $product)
                                <div class="border border-gray-100 rounded-lg p-3 hover:border-indigo-200 hover:bg-indigo-50 cursor-pointer transition-colors"
                                     @click="addItem({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                        <span class="text-sm font-semibold text-indigo-600 ml-2">Rp {{ number_format($product->price,0,',','.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <h3 class="font-semibold text-gray-800 mb-4">🧺 Keranjang</h3>
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
                                        <button type="button" @click="item.quantity = Math.max(1, item.quantity - 1)" class="w-6 h-6 rounded-full bg-gray-100 text-xs">−</button>
                                        <span class="text-sm font-semibold w-6 text-center" x-text="item.quantity"></span>
                                        <button type="button" @click="item.quantity++" class="w-6 h-6 rounded-full bg-gray-100 text-xs">+</button>
                                    </div>
                                    <span class="text-sm font-semibold text-indigo-600" x-text="'Rp ' + formatRp(item.price * item.quantity)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-semibold text-gray-700">Total</span>
                            <span class="font-bold text-lg" x-text="'Rp ' + formatRp(total)"></span>
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
function orderForm(initial = []) {
    return {
        items: initial,
        get total() { return this.items.reduce((s,i) => s + (i.price*i.quantity), 0); },
        addItem(id, name, price) {
            const ex = this.items.find(i => i.product_id === id);
            if (ex) { ex.quantity++; return; }
            this.items.push({ product_id: id, name, price, quantity: 1 });
        },
        removeItem(idx) { this.items.splice(idx, 1); },
        formatRp(v) { return new Intl.NumberFormat('id-ID').format(v); }
    }
}
</script>
@endpush
@endsection