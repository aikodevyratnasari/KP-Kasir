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
