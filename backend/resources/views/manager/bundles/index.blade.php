@extends('layouts.app')
@section('title', 'Paket Bundling')
@section('page-title', 'Paket Bundling')

@section('content')
<div class="space-y-5" x-data="{
    showForm: false,
    items: [{ product_id: '', product_variant_id: '', quantity: 1 }],
    addItem()  { this.items.push({ product_id: '', product_variant_id: '', quantity: 1 }); },
    removeItem(i) { this.items.splice(i, 1); },
    variants: @json($products->mapWithKeys(fn($p) => [$p->id => $p->variants])),
    getVariants(pid) { return this.variants[pid] ?? []; }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $bundles->total() }} paket bundling</p>
        <button @click="showForm = !showForm" class="btn-primary text-sm">
            <span x-text="showForm ? '✕ Tutup Form' : '+ Buat Paket Bundling'"></span>
        </button>
    </div>

    {{-- Form Buat Bundle --}}
    <div x-show="showForm" x-transition class="card space-y-5">
        <h3 class="font-semibold text-gray-800 text-base">Buat Paket Bundling Baru</h3>

        <form method="POST" action="{{ route('manager.bundles.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="cth: Paket Hemat A"
                           class="form-input @error('name') border-red-400 @enderror" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="form-input">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Paket (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="bundle_price" min="0" step="500"
                           class="form-input @error('bundle_price') border-red-400 @enderror" value="{{ old('bundle_price') }}">
                    @error('bundle_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Paket</label>
                    <input type="file" name="image" accept="image/jpeg,image/png"
                           class="block w-full text-sm text-gray-500
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Mulai</label>
                    <input type="datetime-local" name="starts_at" class="form-input" value="{{ old('starts_at') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                    <input type="datetime-local" name="ends_at" class="form-input" value="{{ old('ends_at') }}">
                    <p class="mt-1 text-xs text-gray-400">Kosongkan = berlaku selamanya</p>
                </div>
                <div class="col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">Langsung aktif</span>
                    </label>
                </div>
            </div>

            {{-- Item Bundle --}}
            <div class="border-t border-gray-100 pt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-700">Item dalam Paket</h4>
                    <button type="button" @click="addItem()" class="text-xs text-indigo-600 hover:underline">+ Tambah Item</button>
                </div>

                <template x-for="(item, i) in items" :key="i">
                    <div class="grid grid-cols-12 gap-2 items-end bg-gray-50 rounded-lg p-3">
                        <div class="col-span-5">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Produk</label>
                            <select :name="`items[${i}][product_id]`" x-model="item.product_id"
                                    @change="item.product_variant_id = ''"
                                    class="form-input text-sm">
                                <option value="">Pilih produk...</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Variasi (opsional)</label>
                            <select :name="`items[${i}][product_variant_id]`" x-model="item.product_variant_id"
                                    class="form-input text-sm">
                                <option value="">Tanpa variasi</option>
                                <template x-for="v in getVariants(item.product_id)" :key="v.id">
                                    <option :value="v.id" x-text="v.name + (v.price_adjustment != 0 ? ' (' + (v.price_adjustment > 0 ? '+' : '') + 'Rp' + Number(v.price_adjustment).toLocaleString('id-ID') + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                            <input type="number" :name="`items[${i}][quantity]`" x-model="item.quantity"
                                   min="1" class="form-input text-sm">
                        </div>
                        <div class="col-span-1 flex justify-center pb-1">
                            <button type="button" @click="removeItem(i)"
                                    x-show="items.length > 1"
                                    class="text-red-400 hover:text-red-600 text-xl leading-none">×</button>
                        </div>
                    </div>
                </template>

                @error('items')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="btn-primary">Simpan Paket</button>
            </div>
        </form>
    </div>

    {{-- Daftar Bundle --}}
    <div class="space-y-4">
        @forelse($bundles as $bundle)
        <div class="card">
            <div class="flex items-start gap-4">
                {{-- Foto --}}
                @if($bundle->image)
                <img src="{{ Storage::url($bundle->image) }}"
                     class="w-20 h-20 rounded-xl object-cover flex-shrink-0">
                @else
                <div class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center text-3xl flex-shrink-0">🎁</div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $bundle->name }}</p>
                            @if($bundle->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $bundle->description }}</p>
                            @endif
                        </div>
                        {{-- Status badge --}}
                        @if($bundle->isCurrentlyActive())
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 flex-shrink-0">✓ Aktif</span>
                        @elseif(!$bundle->is_active)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 flex-shrink-0">Nonaktif</span>
                        @elseif($bundle->starts_at && now()->lt($bundle->starts_at))
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600 flex-shrink-0">⏳ Belum mulai</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600 flex-shrink-0">Kadaluarsa</span>
                        @endif
                    </div>

                    {{-- Harga & Hemat --}}
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-lg font-bold text-indigo-700">
                            Rp {{ number_format($bundle->bundle_price, 0, ',', '.') }}
                        </span>
                        @php $savings = $bundle->savings(); @endphp
                        @if($savings > 0)
                        <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                            Hemat Rp {{ number_format($savings, 0, ',', '.') }}
                        </span>
                        @endif
                    </div>

                    {{-- Periode --}}
                    @if($bundle->starts_at || $bundle->ends_at)
                    <p class="text-xs text-gray-400 mt-1">
                        📅 {{ $bundle->starts_at?->format('d M Y') ?? '—' }}
                        → {{ $bundle->ends_at?->format('d M Y') ?? 'selamanya' }}
                    </p>
                    @endif

                    {{-- Item list --}}
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($bundle->items as $item)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">
                            {{ $item->quantity }}× {{ $item->product->name }}
                            @if($item->variant) ({{ $item->variant->name }}) @endif
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="flex flex-col gap-2 flex-shrink-0">
                    <a href="{{ route('manager.bundles.edit', $bundle) }}" class="btn-secondary text-xs text-center">Edit</a>
                    <form method="POST" action="{{ route('manager.bundles.destroy', $bundle) }}"
                          onsubmit="return confirm('Hapus paket {{ addslashes($bundle->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger text-xs w-full">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="card text-center py-12">
            <p class="text-4xl mb-2">🎁</p>
            <p class="text-gray-500 font-medium">Belum ada paket bundling</p>
            <p class="text-gray-400 text-sm mt-1">Buat paket bundling untuk menarik lebih banyak pelanggan</p>
            <button @click="showForm = true" class="btn-primary mt-4 text-sm">+ Buat Paket Pertama</button>
        </div>
        @endforelse
    </div>

    <div>{{ $bundles->links() }}</div>
</div>
@endsection