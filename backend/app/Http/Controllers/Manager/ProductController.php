<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AdjustStockRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\BundlePackage;
use App\Models\BundlePackageItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductVariant;
use App\Services\ActivityLogService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request): View
    {
        $storeId  = $request->get('_store_id');
        $products = Product::forStore($storeId)
            ->with('category', 'discounts')
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
        return redirect()->route('manager.products.index')
            ->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

public function edit(Product $product): View
{
    $categories = Category::where('store_id', $product->store_id)->get();

    $product->load('variants', 'discounts');

    $variants = $product->variants->map(function ($v) {
        return [
            'name' => $v->name,
            'type' => $v->type,
            'price_adjustment' => $v->price_adjustment,
            'stock' => $v->stock,
            'is_available' => $v->is_available,
        ];
    });

    return view('manager.products.edit', compact('product', 'categories', 'variants'));
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
        return redirect()->route('manager.products.index')
            ->with('success', "Produk diperbarui.");
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

    // ── VARIANTS ────────────────────────────────────────────────────────────

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'variants'                    => ['required', 'array', 'min:1'],
            'variants.*.name'             => ['required', 'string', 'max:100'],
            'variants.*.type'             => ['required', 'string', 'max:50'],
            'variants.*.price_adjustment' => ['required', 'numeric'],
            'variants.*.stock'            => ['required', 'integer', 'min:0'],
            'variants.*.is_available'     => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($product, $data) {
            // Hapus semua variant lama lalu insert ulang
            $product->variants()->delete();
            foreach ($data['variants'] as $i => $v) {
                $product->variants()->create([
                    'name'             => $v['name'],
                    'type'             => $v['type'],
                    'price_adjustment' => $v['price_adjustment'],
                    'stock'            => $v['stock'],
                    'is_available'     => isset($v['is_available']) ? 1 : 0,
                    'sort_order'       => $i,
                ]);
            }
        });

        return back()->with('success', 'Variasi produk disimpan.');
    }

    public function destroyVariant(Product $product, ProductVariant $variant): RedirectResponse
    {
        $variant->delete();
        return back()->with('success', 'Variasi dihapus.');
    }

    // ── DISCOUNTS ────────────────────────────────────────────────────────────

    public function storeDiscount(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'type'             => ['required', 'in:percentage,fixed'],
            'value'            => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at'        => ['nullable', 'date'],
            'ends_at'          => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $data['is_active']        = isset($data['is_active']) ? 1 : 0;
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;

        $product->discounts()->create($data);

        return back()->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function destroyDiscount(Product $product, ProductDiscount $discount): RedirectResponse
    {
        $discount->delete();
        return back()->with('success', 'Diskon dihapus.');
    }

    public function toggleDiscount(Product $product, ProductDiscount $discount): RedirectResponse
    {
        $discount->update(['is_active' => ! $discount->is_active]);
        return back()->with('success', 'Status diskon diperbarui.');
    }

    // ── BUNDLES ──────────────────────────────────────────────────────────────

    public function bundles(Request $request): View
    {
        $storeId  = $request->get('_store_id');
        $bundles  = BundlePackage::forStore($storeId)->with('items.product', 'items.variant')->latest()->paginate(20);
        $products = Product::forStore($storeId)->with('variants')->get();
        return view('manager.bundles.index', compact('bundles', 'products'));
    }

    public function storeBundle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string'],
            'bundle_price' => ['required', 'numeric', 'min:0'],
            'starts_at'    => ['nullable', 'date'],
            'ends_at'      => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'    => ['nullable', 'boolean'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.product_id'        => ['required', 'exists:products,id'],
            'items.*.product_variant_id'=> ['nullable', 'exists:product_variants,id'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $storeId = $request->get('_store_id');
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('bundles', 'public');
            }
            $data['store_id']  = $storeId;
            $data['is_active'] = isset($data['is_active']) ? 1 : 0;

            $items = $data['items'];
            unset($data['items']);

            $bundle = BundlePackage::create($data);
            foreach ($items as $item) {
                $bundle->items()->create($item);
            }
        });

        return redirect()->route('manager.bundles.index')
            ->with('success', 'Paket bundling berhasil dibuat.');
    }

    public function editBundle(BundlePackage $bundle): View
    {
        $bundle->load('items.product', 'items.variant');
        $products = Product::forStore($bundle->store_id)->with('variants')->get();
        return view('manager.bundles.edit', compact('bundle', 'products'));
    }

    public function updateBundle(Request $request, BundlePackage $bundle): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string'],
            'bundle_price' => ['required', 'numeric', 'min:0'],
            'starts_at'    => ['nullable', 'date'],
            'ends_at'      => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'    => ['nullable', 'boolean'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.product_id'         => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $data, $bundle) {
            if ($request->hasFile('image')) {
                if ($bundle->image) Storage::disk('public')->delete($bundle->image);
                $data['image'] = $request->file('image')->store('bundles', 'public');
            }
            $data['is_active'] = isset($data['is_active']) ? 1 : 0;
            $items = $data['items'];
            unset($data['items']);

            $bundle->update($data);
            $bundle->items()->delete();
            foreach ($items as $item) {
                $bundle->items()->create($item);
            }
        });

        return redirect()->route('manager.bundles.index')
            ->with('success', 'Paket bundling diperbarui.');
    }

    public function destroyBundle(BundlePackage $bundle): RedirectResponse
    {
        if ($bundle->image) Storage::disk('public')->delete($bundle->image);
        $bundle->delete();
        return redirect()->route('manager.bundles.index')
            ->with('success', 'Paket bundling dihapus.');
    }
}