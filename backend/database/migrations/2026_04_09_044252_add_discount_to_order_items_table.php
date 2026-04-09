<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->nullable()->after('unit_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_price');
            $table->string('discount_label', 100)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_amount', 'discount_label']);
        });
    }
};