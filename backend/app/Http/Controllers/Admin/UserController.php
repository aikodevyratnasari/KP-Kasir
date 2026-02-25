<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('role', 'store')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when($request->role,   fn($q, $r) => $q->whereHas('role', fn($q2) => $q2->where('slug', $r)))
            ->when($request->status, fn($q, $s) => $q->where('status', $s));

        $users = $query->latest()->paginate(20)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles'  => Role::all(),
            'stores' => Store::where('is_active', true)->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());
        ActivityLogService::logCreated($user);

        // Kirim email verifikasi ke user yang baru dibuat
        $user->notify(new VerifyEmailNotification());

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil dibuat. Email verifikasi telah dikirim ke {$user->email}.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user'   => $user->load('role', 'store'),
            'roles'  => Role::all(),
            'stores' => Store::where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $old = $user->toArray();
        $user->update($request->validated());
        ActivityLogService::logUpdated($user, $old, $user->toArray());
        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menonaktifkan akun sendiri.');
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        ActivityLogService::log('toggle_user_status', $user, description: "User {$user->email} status changed to {$user->status}");
        $label = $user->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User {$user->name} telah {$label}.");
    }

    /**
     * Kirim ulang email verifikasi dari halaman admin.
     */
    public function resendVerification(User $user): RedirectResponse
    {
        abort_if($user->hasVerifiedEmail(), 422, 'Email user ini sudah diverifikasi.');

        $user->notify(new VerifyEmailNotification());

        return back()->with('success', "Email verifikasi telah dikirim ulang ke {$user->email}.");
    }
}