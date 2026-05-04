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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('role', 'store')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when($request->role,   fn($q, $r) => $q->whereHas('role', fn($q2) => $q2->where('slug', $r)))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->store,  fn($q, $s) => $q->where('store_id', $s)); // ← filter by store

        $users = $query->latest()->paginate(20)->withQueryString();
        $roles  = Role::all();
        $stores = Store::orderBy('name')->get(); // untuk dropdown filter

        return view('admin.users.index', compact('users', 'roles', 'stores'));
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
        $old          = $user->toArray();
        $emailBefore  = $user->email;
        $data         = $request->validated();
        $emailNew     = $data['email'];
        $emailChanged = $emailBefore !== $emailNew;

        Log::info('UserController@update', [
            'user_id'       => $user->id,
            'email_before'  => $emailBefore,
            'email_new'     => $emailNew,
            'email_changed' => $emailChanged,
            'validated'     => $data,
            'deleted_at_before' => $user->deleted_at,
        ]);

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update(array_merge($data, ['updated_at' => now()]));

        $afterUpdate = DB::table('users')->where('id', $user->id)->first();

        Log::info('UserController@update after', [
            'user_id'    => $user->id,
            'deleted_at' => $afterUpdate?->deleted_at,
            'email'      => $afterUpdate?->email,
        ]);

        $user->refresh();

        ActivityLogService::logUpdated($user, $old, $user->toArray());

        if ($emailChanged) {
            $user->notify(new VerifyEmailNotification());

            return redirect()->route('admin.users.index')
                ->with('success', "User {$user->name} berhasil diperbarui. Email verifikasi dikirim ke {$user->email}.");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');

        $name  = $user->name;
        $email = $user->email;

        ActivityLogService::log('delete_user', $user, description: "User {$email} dihapus oleh admin.");
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} ({$email}) berhasil dihapus.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menonaktifkan akun sendiri.');
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        ActivityLogService::log('toggle_user_status', $user, description: "User {$user->email} status changed to {$user->status}");
        $label = $user->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} telah {$label}.");
    }

    public function resendVerification(User $user): RedirectResponse
    {
        abort_if($user->hasVerifiedEmail(), 422, 'Email user ini sudah diverifikasi.');
        $user->notify(new VerifyEmailNotification());

        return back()->with('success', "Email verifikasi telah dikirim ulang ke {$user->email}.");
    }

    public function showResetPassword(User $user): View
    {
        return view('auth.admin-reset-password', compact('user'));
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'password' => 'Password minimal 8 karakter, mengandung huruf besar, kecil, dan angka.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLogService::log('reset_password', $user, description: "Password {$user->email} direset oleh admin.");

        return redirect()->route('admin.users.index')
            ->with('success', "Password {$user->name} berhasil direset.");
    }
}