<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix: Tambahkan nilai 'waiting_payment' dan 'cancelled' ke constraint
 * kitchen_orders_status_check di PostgreSQL.
 *
 * Mengapa perlu migration ini?
 * - OrderService::create() sudah menyimpan status 'waiting_payment' di KitchenOrder,
 *   namun nilai tersebut tidak ada di enum awal migration.
 * - OrderService::cancel() mencoba update status ke 'cancelled', yang juga tidak
 *   ada di enum awal — inilah penyebab error SQLSTATE[23514].
 *
 * PostgreSQL tidak mendukung ALTER COLUMN ... SET TYPE enum(...) secara langsung
 * untuk menambah nilai. Cara yang aman adalah:
 *   1. DROP constraint lama
 *   2. ALTER COLUMN ke tipe text sementara
 *   3. Buat constraint CHECK baru dengan semua nilai yang dibutuhkan
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus constraint check lama
        DB::statement('ALTER TABLE kitchen_orders DROP CONSTRAINT IF EXISTS kitchen_orders_status_check');

        // 2. Ubah tipe kolom dari enum ke varchar (kalau masih enum native Postgres)
        //    Jika sudah varchar/text, langkah ini aman-aman saja
        DB::statement("ALTER TABLE kitchen_orders ALTER COLUMN status TYPE VARCHAR(30) USING status::text");

        // 3. Buat constraint baru yang mencakup semua status yang digunakan
        DB::statement("
            ALTER TABLE kitchen_orders
            ADD CONSTRAINT kitchen_orders_status_check
            CHECK (status IN ('waiting_payment', 'queued', 'cooking', 'ready', 'cancelled'))
        ");
    }

    public function down(): void
    {
        // Kembalikan ke constraint semula (tanpa waiting_payment & cancelled)
        DB::statement('ALTER TABLE kitchen_orders DROP CONSTRAINT IF EXISTS kitchen_orders_status_check');

        DB::statement("ALTER TABLE kitchen_orders ALTER COLUMN status TYPE VARCHAR(30) USING status::text");

        DB::statement("
            ALTER TABLE kitchen_orders
            ADD CONSTRAINT kitchen_orders_status_check
            CHECK (status IN ('queued', 'cooking', 'ready'))
        ");
    }
};