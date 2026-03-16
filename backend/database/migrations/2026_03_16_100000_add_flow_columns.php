<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah sent_to_kitchen_at ke orders jika belum ada
        if (! Schema::hasColumn('orders', 'sent_to_kitchen_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('sent_to_kitchen_at')->nullable()->after('notes');
            });
        }

        // PostgreSQL: ubah enum kitchen_orders.status agar bisa nilai 'waiting_payment'
        // Cek apakah kolom status menggunakan enum atau varchar
        DB::statement("
            ALTER TABLE kitchen_orders
            ALTER COLUMN status TYPE VARCHAR(30)
        ");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumnIfExists('sent_to_kitchen_at');
        });
    }
};