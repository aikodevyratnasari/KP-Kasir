<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus unique constraint lama
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'name']);
        });

        // Buat partial unique index — hanya berlaku untuk record yang BELUM dihapus
        // PostgreSQL mendukung WHERE clause pada index
        DB::statement('
            CREATE UNIQUE INDEX categories_store_id_name_active_unique
            ON categories (store_id, name)
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        // Hapus partial index
        DB::statement('DROP INDEX IF EXISTS categories_store_id_name_active_unique');

        // Kembalikan unique constraint lama
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['store_id', 'name']);
        });
    }
};