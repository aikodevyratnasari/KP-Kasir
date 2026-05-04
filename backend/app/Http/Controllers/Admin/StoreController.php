<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\Store;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::withCount(['users', 'users as active_users_count' => function ($q) {
            $q->where('status', 'active');
        }])->latest()->get();

        return view('admin.stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('admin.stores.create');
    }

    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['has_kitchen'] = $request->boolean('has_kitchen');
        $data['is_active']   = true;

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        unset($data['logo']);

        $store = Store::create($data);

        // Jika belum ada HQ, jadikan toko pertama sebagai HQ otomatis
        if (!Store::where('is_headquarters', true)->exists()) {
            $store->update(['is_headquarters' => true]);
        }

        ActivityLogService::logCreated($store);

        return redirect()->route('admin.stores.index')
            ->with('success', "Toko \"{$store->name}\" berhasil ditambahkan.");
    }

    public function edit(Store $store): View
    {
        $store->loadCount(['users', 'users as active_users_count' => function ($q) {
            $q->where('status', 'active');
        }]);

        return view('admin.stores.edit', compact('store'));
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $old  = $store->toArray();
        $data = $request->validated();
        $data['has_kitchen'] = $request->boolean('has_kitchen');
        $data['is_active']   = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            if ($store->logo_path) {
                \Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        unset($data['logo']);

        $store->update($data);
        ActivityLogService::logUpdated($store, $old, $store->toArray());

        return redirect()->route('admin.stores.index')
            ->with('success', "Toko \"{$store->name}\" berhasil diperbarui.");
    }

    public function toggleStatus(Store $store): RedirectResponse
    {
        if ($store->is_active) {
            $activeUserCount = $store->users()->where('status', 'active')->count();
            if ($activeUserCount > 0) {
                return back()->with('error',
                    "Tidak dapat menonaktifkan \"{$store->name}\" — " .
                    "masih ada {$activeUserCount} user aktif. " .
                    "Nonaktifkan semua user toko ini terlebih dahulu."
                );
            }
        }

        $store->update(['is_active' => !$store->is_active]);

        ActivityLogService::log(
            'toggle_store_status',
            $store,
            description: "Store {$store->name} diubah menjadi " . ($store->is_active ? 'aktif' : 'nonaktif')
        );

        $label = $store->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Toko \"{$store->name}\" berhasil {$label}.");
    }

    /**
     * Tetapkan toko ini sebagai cabang pusat (HQ).
     * Hanya satu toko yang bisa menjadi HQ — toko lain otomatis dicabut statusnya.
     */
    public function setHeadquarters(Store $store): RedirectResponse
    {
        if ($store->is_headquarters) {
            return back()->with('success', "\"{$store->name}\" sudah menjadi cabang pusat.");
        }

        $store->setAsHeadquarters();

        ActivityLogService::log(
            'set_headquarters',
            $store,
            description: "Store {$store->name} ditetapkan sebagai cabang pusat."
        );

        return back()->with('success', "\"{$store->name}\" ditetapkan sebagai cabang pusat.");
    }
}