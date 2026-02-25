<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Halaman edit profil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('role', 'store'),
        ]);
    }

    /**
     * Update nama & telepon (email tidak bisa diubah).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('profileInformation', [
            'name'  => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $old = $user->only('name', 'phone');
        $user->update($validated);
        ActivityLogService::logUpdated($user, $old, $user->only('name', 'phone'));

        return back()->with('status', 'profile-updated');
    }

    /**
     * Ganti password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update(['password' => Hash::make($validated['password'])]);
        ActivityLogService::log('password_changed', $user, description: "User {$user->email} changed password.");

        return back()->with('status', 'password-updated');
    }
}