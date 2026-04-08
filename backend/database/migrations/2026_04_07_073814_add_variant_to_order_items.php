<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Referensi ke varian yang dipilih (nullable — tidak semua item pakai varian)
            $table->foreignId('variant_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('product_variants')
                  ->nullOnDelete();

            // Snapshot nama varian saat order dibuat (agar tidak berubah jika varian diedit/dihapus)
            $table->string('variant_name')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn(['variant_id', 'variant_name']);
        });
    }
};