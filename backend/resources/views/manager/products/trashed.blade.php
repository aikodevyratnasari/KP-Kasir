@extends('layouts.app')
@section('title', 'Produk Terhapus')
@section('page-title', 'Produk Terhapus')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('manager.products.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali ke Menu</a>
    </div>

    <div class="card p-0">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Dihapus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($products as $product)
                <tr class="hover:bg-gray-50 opacity-70">
                    <td class="py-3 px-4 font-medium text-gray-600">{{ $product->name }}</td>
                    <td class="py-3 px-4 text-gray-500">{{ $product->category?->name ?? '–' }}</td>
                    <td class="py-3 px-4 text-right">Rp {{ number_format($product->price,0,',','.') }}</td>
                    <td class="py-3 px-4 text-gray-400 text-xs">{{ $product->deleted_at->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-8 text-center text-gray-400">Tidak ada produk yang dihapus</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
@endsection