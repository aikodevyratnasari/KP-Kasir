<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('role', 'store'),
        ]);
    }

    /**
     * Update the user's profile information.
     * Email tidak bisa diubah (sesuai SRS: admin cannot update email).
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
}
