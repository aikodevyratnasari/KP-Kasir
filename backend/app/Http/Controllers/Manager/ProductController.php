<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AdjustStockRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request): View
    {
        $storeId  = $request->get('_store_id');
        $products = Product::forStore($storeId)
            ->with('category')
            ->when($request->search,     fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->category,   fn($q, $c) => $q->where('category_id', $c))
            ->when($request->sort === 'price', fn($q) => $q->orderBy('price', $request->dir ?? 'asc'))
            ->when($request->sort === 'name',  fn($q) => $q->orderBy('name',  $request->dir ?? 'asc'))
            ->when(! $request->sort,           fn($q) => $q->latest())
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('store_id', $storeId)->get();

        return view('manager.products.index', compact('products', 'categories'));
    }

    public function create(Request $request): View
    {
        $categories = Category::where('store_id', $request->get('_store_id'))->get();
        return view('manager.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = array_merge($request->validated(), ['store_id' => $request->get('_store_id')]);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        $product = Product::create($data);
        ActivityLogService::logCreated($product);
        return redirect()->route('manager.products.index')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('store_id', $product->store_id)->get();
        return view('manager.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $old  = $product->toArray();
        $data = $request->validated();
        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        $product->update($data);
        ActivityLogService::logUpdated($product, $old, $product->toArray());
        return redirect()->route('manager.products.index')->with('success', "Produk diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        ActivityLogService::logDeleted($product);
        $product->delete();
        return redirect()->route('manager.products.index')->with('success', "Produk dihapus.");
    }

    public function adjustStock(AdjustStockRequest $request, Product $product): RedirectResponse
    {
        $log = $this->stock->adjust($product, $request->quantity, $request->notes ?? '');
        ActivityLogService::log('stock_adjusted', $product, ['stock' => $log->quantity_before], ['stock' => $log->quantity_after]);
        return back()->with('success', "Stok berhasil diperbarui menjadi {$request->quantity}.");
    }

    public function trashed(Request $request): View
    {
        $products = Product::onlyTrashed()->forStore($request->get('_store_id'))->latest('deleted_at')->paginate(20);
        return view('manager.products.trashed', compact('products'));
    }
}