<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogService;
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
            ->withCount('products')
            ->latest()
            ->paginate(20);

        return view('manager.categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['store_id'] = $request->get('_store_id');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);
        ActivityLogService::logCreated($category);
        return redirect()->route('manager.categories.index')->with('success', "Kategori {$category->name} berhasil dibuat.");
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeStore($category->store_id);
        $old  = $category->toArray();
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);
        ActivityLogService::logUpdated($category, $old, $category->toArray());
        return redirect()->route('manager.categories.index')->with('success', "Kategori diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeStore($category->store_id);
        abort_if($category->hasProducts(), 422, 'Tidak dapat menghapus kategori yang masih memiliki produk.');
        if ($category->image) Storage::disk('public')->delete($category->image);
        ActivityLogService::logDeleted($category);
        $category->delete();
        return redirect()->route('manager.categories.index')->with('success', "Kategori dihapus.");
    }

    private function authorizeStore(int $storeId): void
    {
        $user = auth()->user();
        abort_if($user->role->slug !== 'admin' && $user->store_id !== $storeId, 403);
    }
}