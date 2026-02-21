#!/bin/bash
# =============================================================================
# DePOS PSR-4 Fix Script
# Jalankan dari root project Laravel: bash fix-psr4.sh
# =============================================================================
set -e

APP=app
echo "🔧 Memulai fix PSR-4 DePOS..."

# ── STEP 1: Hapus file lama yang berisi banyak class ─────────────────────────
echo ""
echo "📦 STEP 1: Hapus file multi-class lama..."

rm -f $APP/Http/Requests/AllRequests.php
rm -f $APP/Http/Requests/Category/CategoryRequests.php
rm -f $APP/Http/Requests/User/UserRequests.php
rm -f $APP/Policies/Policies.php
rm -f $APP/Events/PaymentAndStockEvents.php
rm -f $APP/Listeners/EventListeners.php
rm -f $APP/Providers/AppServiceProvider.php

echo "   ✅ File lama dihapus"

# ── STEP 2: Buat direktori yang dibutuhkan ────────────────────────────────────
echo ""
echo "📁 STEP 2: Buat direktori..."

mkdir -p $APP/Http/Requests/{User,Category,Product,Order,Payment,Table,Reservation,Report}
mkdir -p $APP/{Policies,Events,Listeners,Providers}

echo "   ✅ Direktori siap"

# ── STEP 3: Tulis semua file baru ─────────────────────────────────────────────
echo ""
echo "📝 STEP 3: Menulis file PSR-4 compliant..."

# ─── Requests/User ────────────────────────────────────────────────────────────
cat > $APP/Http/Requests/User/StoreUserRequest.php << 'PHP'
<?php
namespace App\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'role_id'  => ['required', 'exists:roles,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['sometimes', 'in:active,inactive'],
        ];
    }
    public function messages(): array
    {
        return ['email.unique' => 'Email sudah digunakan.', 'role_id.exists' => 'Role tidak valid.', 'store_id.exists' => 'Store tidak valid.'];
    }
}
PHP

cat > $APP/Http/Requests/User/UpdateUserRequest.php << 'PHP'
<?php
namespace App\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'role_id'  => ['required', 'exists:roles,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', 'in:active,inactive'],
        ];
    }
}
PHP

# ─── Requests/Category ────────────────────────────────────────────────────────
cat > $APP/Http/Requests/Category/StoreCategoryRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        $storeId = $this->user()->store_id;
        return [
            'name'        => ['required', 'string', 'max:100', "unique:categories,name,NULL,id,store_id,{$storeId},deleted_at,NULL"],
            'description' => ['nullable', 'string', 'max:500'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
    public function messages(): array { return ['name.unique' => 'Nama kategori sudah digunakan di toko ini.']; }
}
PHP

cat > $APP/Http/Requests/Category/UpdateCategoryRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        $storeId = $this->user()->store_id;
        $id      = $this->route('category');
        return [
            'name'        => ['required', 'string', 'max:100', "unique:categories,name,{$id},id,store_id,{$storeId},deleted_at,NULL"],
            'description' => ['nullable', 'string', 'max:500'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
PHP

# ─── Requests/Product ─────────────────────────────────────────────────────────
cat > $APP/Http/Requests/Product/StoreProductRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return [
            'category_id'     => ['required', 'exists:categories,id'],
            'name'            => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'price'           => ['required', 'numeric', 'min:0'],
            'stock'           => ['required', 'integer', 'min:0'],
            'low_stock_alert' => ['nullable', 'integer', 'min:0'],
            'is_available'    => ['boolean'],
            'track_stock'     => ['boolean'],
        ];
    }
    public function messages(): array { return ['price.min' => 'Harga tidak boleh negatif.', 'stock.min' => 'Stok tidak boleh negatif.']; }
}
PHP

cat > $APP/Http/Requests/Product/UpdateProductRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Product;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'];
        return $rules;
    }
}
PHP

cat > $APP/Http/Requests/Product/AdjustStockRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Product;
use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return ['quantity' => ['required', 'integer', 'min:0'], 'notes' => ['nullable', 'string', 'max:255']];
    }
}
PHP

# ─── Requests/Order ───────────────────────────────────────────────────────────
cat > $APP/Http/Requests/Order/StoreOrderRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array
    {
        return [
            'order_type'            => ['required', 'in:dine_in,takeaway'],
            'table_id'              => ['required_if:order_type,dine_in', 'nullable', 'exists:tables,id'],
            'notes'                 => ['nullable', 'string', 'max:500'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.special_notes' => ['nullable', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        return ['table_id.required_if' => 'Nomor meja wajib diisi untuk pesanan Dine-In.', 'items.min' => 'Pesanan harus mengandung minimal 1 item.'];
    }
}
PHP

cat > $APP/Http/Requests/Order/UpdateOrderRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Order;

class UpdateOrderRequest extends StoreOrderRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['order_type'], $rules['table_id']);
        return $rules;
    }
}
PHP

cat > $APP/Http/Requests/Order/CancelOrderRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Order;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array { return ['cancel_reason' => ['required', 'string', 'max:500']]; }
}
PHP

# ─── Requests/Payment ─────────────────────────────────────────────────────────
cat > $APP/Http/Requests/Payment/ProcessPaymentRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array
    {
        return [
            'payment_method'   => ['required', 'in:cash,card,ewallet'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'amount_received'  => ['required_if:payment_method,cash', 'nullable', 'numeric'],
            'card_type'        => ['required_if:payment_method,card', 'nullable', 'in:Visa,Mastercard'],
            'card_last_four'   => ['required_if:payment_method,card', 'nullable', 'digits:4'],
            'approval_code'    => ['required_if:payment_method,card', 'nullable', 'string', 'max:50'],
            'ewallet_type'     => ['required_if:payment_method,ewallet', 'nullable', 'in:GoPay,OVO,Dana,ShopeePay'],
            'reference_number' => ['required_if:payment_method,ewallet', 'nullable', 'string', 'max:100'],
        ];
    }
}
PHP

cat > $APP/Http/Requests/Payment/RefundPaymentRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return ['refund_amount' => ['required', 'numeric', 'min:0.01'], 'refund_reason' => ['required', 'string', 'max:500']];
    }
}
PHP

# ─── Requests/Table ───────────────────────────────────────────────────────────
cat > $APP/Http/Requests/Table/StoreTableRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Table;
use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        $storeId = $this->user()->store_id;
        return [
            'number'   => ['required', 'string', 'max:20', "unique:tables,number,NULL,id,store_id,{$storeId}"],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'section'  => ['nullable', 'string', 'max:50'],
        ];
    }
    public function messages(): array { return ['number.unique' => 'Nomor meja sudah digunakan.']; }
}
PHP

cat > $APP/Http/Requests/Table/TransferTableRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Table;
use Illuminate\Foundation\Http\FormRequest;

class TransferTableRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array { return ['table_id' => ['required', 'exists:tables,id']]; }
}
PHP

# ─── Requests/Reservation ─────────────────────────────────────────────────────
cat > $APP/Http/Requests/Reservation/StoreReservationRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager', 'cashier']); }
    public function rules(): array
    {
        return [
            'table_id'       => ['required', 'exists:tables,id'],
            'customer_name'  => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'reserved_at'    => ['required', 'date', 'after:now'],
            'guest_count'    => ['nullable', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }
    public function messages(): array { return ['reserved_at.after' => 'Waktu reservasi harus di masa depan.']; }
}
PHP

# ─── Requests/Report ──────────────────────────────────────────────────────────
cat > $APP/Http/Requests/Report/ReportFilterRequest.php << 'PHP'
<?php
namespace App\Http\Requests\Report;
use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return ['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'period' => ['nullable', 'in:daily,weekly,monthly,yearly']];
    }
}
PHP

# ─── Policies ─────────────────────────────────────────────────────────────────
cat > $APP/Policies/OrderPolicy.php << 'PHP'
<?php
namespace App\Policies;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function view(User $user, Order $order): bool { return $this->sameStore($user, $order->store_id); }
    public function create(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function update(User $user, Order $order): bool
    {
        if (!$order->isPending()) return false;
        if (in_array($user->role->slug, ['admin', 'manager'])) return $this->sameStore($user, $order->store_id);
        return $user->role->slug === 'cashier' && $order->cashier_id === $user->id && $this->sameStore($user, $order->store_id);
    }
    public function cancel(User $user, Order $order): bool
    {
        if ($order->isCompleted() || $order->isCancelled()) return false;
        if (in_array($user->role->slug, ['admin', 'manager'])) return $this->sameStore($user, $order->store_id);
        return $user->role->slug === 'cashier' && $order->cashier_id === $user->id && $order->isPending();
    }
    public function updateStatus(User $user, Order $order): bool
    {
        return in_array($user->role->slug, ['kitchen_staff', 'manager', 'admin']) && $this->sameStore($user, $order->store_id);
    }
    private function sameStore(User $user, int $storeId): bool { return $user->role->slug === 'admin' || $user->store_id === $storeId; }
}
PHP

cat > $APP/Policies/ProductPolicy.php << 'PHP'
<?php
namespace App\Policies;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Product $product): bool { return true; }
    public function create(User $user): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function update(User $user, Product $product): bool
    {
        return in_array($user->role->slug, ['admin', 'manager']) && ($user->role->slug === 'admin' || $user->store_id === $product->store_id);
    }
    public function delete(User $user, Product $product): bool { return $this->update($user, $product); }
    public function adjustStock(User $user, Product $product): bool { return $this->update($user, $product); }
}
PHP

cat > $APP/Policies/UserPolicy.php << 'PHP'
<?php
namespace App\Policies;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool { return $user->role->slug === 'admin'; }
    public function view(User $user, User $model): bool { return $user->role->slug === 'admin' || $user->id === $model->id; }
    public function create(User $user): bool { return $user->role->slug === 'admin'; }
    public function update(User $user, User $model): bool { return $user->role->slug === 'admin'; }
    public function delete(User $user, User $model): bool { return $user->role->slug === 'admin' && $user->id !== $model->id; }
    public function deactivate(User $user, User $model): bool { return $user->role->slug === 'admin' && $user->id !== $model->id; }
}
PHP

cat > $APP/Policies/PaymentPolicy.php << 'PHP'
<?php
namespace App\Policies;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function create(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function refund(User $user, Payment $payment): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function viewHistory(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
}
PHP

cat > $APP/Policies/TablePolicy.php << 'PHP'
<?php
namespace App\Policies;
use App\Models\User;

class TablePolicy
{
    public function viewAny(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function manage(User $user): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function transfer(User $user): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function reserve(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
}
PHP

# ─── Events ───────────────────────────────────────────────────────────────────
cat > $APP/Events/PaymentProcessed.php << 'PHP'
<?php
namespace App\Events;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessed
{
    use Dispatchable, SerializesModels;
    public function __construct(public Payment $payment) {}
}
PHP

cat > $APP/Events/LowStockAlert.php << 'PHP'
<?php
namespace App\Events;
use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, SerializesModels;
    public function __construct(public Product $product) {}
}
PHP

# ─── Listeners ────────────────────────────────────────────────────────────────
cat > $APP/Listeners/LogOrderCreated.php << 'PHP'
<?php
namespace App\Listeners;
use App\Events\OrderCreated;
use App\Services\ActivityLogService;

class LogOrderCreated
{
    public function handle(OrderCreated $event): void
    {
        ActivityLogService::logCreated($event->order, ['order_number' => $event->order->order_number]);
    }
}
PHP

cat > $APP/Listeners/NotifyKitchenOnOrderReady.php << 'PHP'
<?php
namespace App\Listeners;
use App\Events\OrderStatusChanged;
use App\Models\User;
use App\Notifications\OrderReadyNotification;
use Illuminate\Support\Facades\Notification;

class NotifyKitchenOnOrderReady
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->newStatus !== 'ready') return;
        $cashiers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['cashier', 'manager']))
            ->where('store_id', $event->order->store_id)->where('status', 'active')->get();
        Notification::send($cashiers, new OrderReadyNotification($event->order));
    }
}
PHP

cat > $APP/Listeners/LogPaymentProcessed.php << 'PHP'
<?php
namespace App\Listeners;
use App\Events\PaymentProcessed;
use App\Services\ActivityLogService;

class LogPaymentProcessed
{
    public function handle(PaymentProcessed $event): void
    {
        ActivityLogService::log('payment_processed', $event->payment, null,
            ['amount' => $event->payment->amount, 'method' => $event->payment->payment_method],
            "Payment #{$event->payment->id} for order #{$event->payment->order->order_number}");
    }
}
PHP

cat > $APP/Listeners/NotifyManagersLowStock.php << 'PHP'
<?php
namespace App\Listeners;
use App\Events\LowStockAlert;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class NotifyManagersLowStock
{
    public function handle(LowStockAlert $event): void
    {
        $managers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'manager']))
            ->where('store_id', $event->product->store_id)->where('status', 'active')->get();
        Notification::send($managers, new LowStockNotification($event->product));
    }
}
PHP

# ─── AppServiceProvider ───────────────────────────────────────────────────────
cat > $APP/Providers/AppServiceProvider.php << 'PHP'
<?php
namespace App\Providers;

use App\Events\LowStockAlert;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\PaymentProcessed;
use App\Listeners\LogOrderCreated;
use App\Listeners\LogPaymentProcessed;
use App\Listeners\NotifyKitchenOnOrderReady;
use App\Listeners\NotifyManagersLowStock;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TablePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Order::class,   OrderPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Table::class,   TablePolicy::class);
        Gate::policy(User::class,    UserPolicy::class);

        Gate::before(function (User $user, string $ability) {
            if ($user->role->slug === 'admin') return true;
        });

        Event::listen(OrderCreated::class,       LogOrderCreated::class);
        Event::listen(OrderStatusChanged::class, NotifyKitchenOnOrderReady::class);
        Event::listen(PaymentProcessed::class,   LogPaymentProcessed::class);
        Event::listen(LowStockAlert::class,      NotifyManagersLowStock::class);

        Blade::directive('active', function (string $pattern) {
            return "<?php echo request()->is({$pattern}) ? 'active' : ''; ?>";
        });
    }
}
PHP

# ── STEP 4: Dump autoload ─────────────────────────────────────────────────────
echo ""
echo "⚙️  STEP 4: composer dump-autoload..."
composer dump-autoload -q

echo ""
echo "✅ SELESAI! Semua PSR-4 error sudah diperbaiki."
echo ""
echo "Lanjutkan dengan:"
echo "  php artisan breeze:install blade"