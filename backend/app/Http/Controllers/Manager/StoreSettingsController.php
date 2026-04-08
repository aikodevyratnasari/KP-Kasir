<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        return view('manager.settings.index', compact('store'));
    }

    public function update(Request $request): RedirectResponse
    {
        $store = $request->user()->store;

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'address'         => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:100'],
            'tax_rate'        => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_number'      => ['nullable', 'string', 'max:50'],
            'receipt_footer'  => ['nullable', 'string', 'max:500'],
        ]);

        $store->update($data);

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}