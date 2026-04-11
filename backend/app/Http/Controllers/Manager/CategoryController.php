<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $storeId    = $request->get('_store_id');
        $categories = Category::where('store_id', $storeId)
            ->withCount([
                'products as products_count' => fn($q) => $q->whereNull('products.deleted_at')
            ])
            ->latest()
            ->paginate(20);

        return view('manager.categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data             = $request->validated();
        $data['store_id'] = (int) $request->get('_store_id');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        try {
            $category = Category::create($data);
        } catch (UniqueConstraintViolationException) {
            // Safety net: jika validasi lolos tapi DB menolak (race condition / type mismatch)
            return back()
                ->withInput()
                ->withErrors(['name' => 'Nama kategori sudah digunakan di toko ini.']);
        }

        ActivityLogService::logCreated($category);

        return redirect()
            ->route('manager.categories.index')
            ->with('success', "Kategori {$category->name} berhasil dibuat");
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeStore($category->store_id);

        $old  = $category->toArray();
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        try {
            $category->update($data);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Nama kategori sudah digunakan di toko ini.']);
        }

        ActivityLogService::logUpdated($category, $old, $category->toArray());

        return redirect()
            ->route('manager.categories.index')
            ->with('success', 'Kategori diperbarui');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeStore($category->store_id);

        // Set category_id produk menjadi null (uncategorized)
        \DB::table('products')
            ->where('category_id', $category->id)
            ->whereNull('deleted_at')
            ->update(['category_id' => null]);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        ActivityLogService::logDeleted($category);
        $category->delete();

        return redirect()
            ->route('manager.categories.index')
            ->with('success', "Kategori {$category->name} dihapus. Produk dipindah ke Tanpa Kategori.");
    }

    private function authorizeStore(int $storeId): void
    {
        $user = auth()->user();
        abort_if($user->role->slug !== 'admin' && $user->store_id !== $storeId, 403);
    }
}