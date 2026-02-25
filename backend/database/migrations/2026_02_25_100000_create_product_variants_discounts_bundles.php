<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Variasi Produk ────────────────────────────────────────────────
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name', 100);           // "Porsi Kecil", "Pedas Sedang"
            $table->string('type', 50);            // "ukuran", "level", "topping"
            $table->decimal('price_adjustment', 12, 2)->default(0); // +/- dari harga dasar
            $table->integer('stock')->default(0);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Diskon Produk ─────────────────────────────────────────────────
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name', 100);                       // "Promo Ramadan"
            $table->enum('type', ['percentage', 'fixed']);     // % atau nominal Rp
            $table->decimal('value', 12, 2);                   // 20 (%) atau 5000 (Rp)
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Paket Bundling ────────────────────────────────────────────────
        Schema::create('bundle_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->decimal('bundle_price', 12, 2);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Item dalam Bundle ─────────────────────────────────────────────
        Schema::create('bundle_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_package_id')->constrained('bundle_packages')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_package_items');
        Schema::dropIfExists('bundle_packages');
        Schema::dropIfExists('product_discounts');
        Schema::dropIfExists('product_variants');
    }
};