<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20)->nullable();
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->nullable(); // auto cancel after 30 min
            $table->integer('guest_count')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'converted', 'cancelled', 'expired'])->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
