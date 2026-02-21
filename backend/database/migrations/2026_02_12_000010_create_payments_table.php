<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'ewallet']);
            $table->string('ewallet_type', 30)->nullable(); // GoPay, OVO, Dana, ShopeePay
            $table->string('card_type', 30)->nullable();    // Visa, Mastercard
            $table->string('card_last_four', 4)->nullable();
            $table->string('approval_code', 50)->nullable();
            $table->string('reference_number', 100)->nullable(); // ewallet ref
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_received', 12, 2)->nullable(); // for cash
            $table->decimal('change_amount', 12, 2)->nullable();   // for cash
            $table->enum('status', ['paid', 'refunded', 'partial'])->default('paid');
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
