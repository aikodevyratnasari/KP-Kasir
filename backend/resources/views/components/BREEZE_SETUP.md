# DePOS — Setup Laravel Breeze

## Apa yang Berubah dengan Breeze?

| Sebelum (Manual Auth) | Sesudah (Breeze) |
|----------------------|------------------|
| `AuthController.php` | `AuthenticatedSessionController.php` |
| View login manual | View `auth/login.blade.php` (Breeze-style) |
| Tidak ada profile page | `profile/edit.blade.php` |
| Tidak ada password update | `PasswordController.php` |
| Layout manual | `layouts/guest.blade.php` + `layouts/app.blade.php` |
| Tidak ada Blade components | `x-guest-layout`, `x-text-input`, dll. |
| Tidak ada Vite config | `vite.config.js` + `tailwind.config.js` |
| Tidak ada Alpine.js | `app.js` dengan Alpine.js |

---

## Langkah Setup

### 1. Install Laravel Breeze
```bash
composer require laravel/breeze --dev

# Install Breeze dengan stack Blade (bukan React/Vue)
php artisan breeze:install blade

# Install Node dependencies & build assets
npm install
npm run build   # production
# atau
npm run dev     # development (hot reload)
```

### 2. Hapus File Auth Bawaan Breeze yang Tidak Dipakai
Breeze akan generate banyak file. Hapus yang tidak digunakan DePOS:

```bash
# Hapus controller bawaan Breeze yang tidak dipakai
rm app/Http/Controllers/Auth/RegisteredUserController.php
rm app/Http/Controllers/Auth/EmailVerificationNotificationController.php
rm app/Http/Controllers/Auth/EmailVerificationPromptController.php
rm app/Http/Controllers/Auth/VerifyEmailController.php
rm app/Http/Controllers/Auth/NewPasswordController.php
rm app/Http/Controllers/Auth/PasswordResetLinkController.php
rm app/Http/Controllers/Auth/ConfirmablePasswordController.php

# Hapus view auth yang tidak dipakai
rm resources/views/auth/register.blade.php
rm resources/views/auth/forgot-password.blade.php
rm resources/views/auth/reset-password.blade.php
rm resources/views/auth/verify-email.blade.php
rm resources/views/auth/confirm-password.blade.php

# Hapus routes auth bawaan Breeze
rm routes/auth.php
```

### 3. Salin File DePOS ke Posisi yang Benar

#### Controllers Auth (timpa bawaan Breeze):
```
app/Http/Controllers/Auth/
├── AuthenticatedSessionController.php  ← TIMPA dengan file DePOS
├── PasswordController.php              ← TIMPA dengan file DePOS
└── ProfileController.php              ← TIMPA dengan file DePOS
```

#### Views (timpa + tambah):
```
resources/views/
├── auth/
│   └── login.blade.php               ← TIMPA dengan file DePOS
├── profile/
│   └── edit.blade.php                ← TIMPA dengan file DePOS
├── layouts/
│   ├── app.blade.php                 ← TIMPA dengan file DePOS
│   └── guest.blade.php              ← TIMPA dengan file DePOS
└── components/
    ├── guest-layout.blade.php        ← TIMPA dengan file DePOS
    ├── input-error.blade.php         ← biarkan bawaan Breeze
    ├── input-label.blade.php         ← TIMPA dengan file DePOS
    ├── primary-button.blade.php      ← TIMPA dengan file DePOS
    └── text-input.blade.php          ← TIMPA dengan file DePOS
```

#### CSS & JS:
```
resources/css/app.css    ← TIMPA dengan file DePOS (app.css)
resources/js/app.js      ← TIMPA dengan file DePOS (app.js)
```

#### Config:
```
tailwind.config.js  ← TIMPA dengan file DePOS
vite.config.js      ← TIMPA dengan file DePOS
```

#### Routes:
```
routes/web.php      ← TIMPA dengan routes_web.php dari DePOS
```

#### Bootstrap:
```
bootstrap/app.php   ← TIMPA dengan bootstrap_app.php dari DePOS
```

### 4. Requests
```
app/Http/Requests/Auth/LoginRequest.php  ← TIMPA dengan LoginRequest.php dari DePOS
```

### 5. Jalankan Database
```bash
# Hapus default migration user Laravel yang bentrok
rm database/migrations/0001_01_01_000000_create_users_table.php
rm database/migrations/0001_01_01_000001_create_cache_table.php
rm database/migrations/0001_01_01_000002_create_jobs_table.php

# Fresh migrate + seed
php artisan migrate:fresh --seed
```

### 6. Tambah Blade Directive @active di AppServiceProvider
Buka `app/Providers/AppServiceProvider.php`, tambahkan di method `boot()`:

```php
use Illuminate\Support\Facades\Blade;

// Di dalam boot():
Blade::directive('active', function (string $pattern) {
    return "<?php echo request()->is({$pattern}) ? 'active' : ''; ?>";
});
```

### 7. Build Assets & Jalankan
```bash
npm run build   # atau npm run dev untuk development

php artisan serve
```

---

## Alur Navigasi Setelah Login

```
POST /login  →  AuthenticatedSessionController@store
                   ↓
           Auth::attempt() berhasil?
           ├─ Ya → user->dashboardRoute()
           │        ├─ admin         → /admin/dashboard
           │        ├─ manager       → /manager/dashboard
           │        ├─ cashier       → /cashier/orders
           │        └─ kitchen_staff → /kitchen/display
           └─ Tidak → back() + error message
```

---

## Ringkasan Perbedaan Penting

1. **Tidak ada register** — user hanya bisa dibuat oleh Admin via `/admin/users`
2. **Tidak ada forgot password** — password direset oleh Admin
3. **Profile page** ada tapi email tidak bisa diubah (sesuai SRS)
4. **Login throttle** dihandle custom (5x gagal = kunci 15 menit), bukan Laravel's built-in RateLimiter
5. **Redirect setelah login** dinamis berdasarkan role, bukan `/dashboard` default Breeze
