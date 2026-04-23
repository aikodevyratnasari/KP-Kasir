<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambah status 'cancelled' ke constraint payments.status.
 *
 * 'cancelled' dipakai untuk pending gateway yang dibatalkan karena kasir
 * ganti metode pembayaran — berbeda dari 'refunded' (sudah bayar lalu dikembalikan).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check');
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_status_check
            CHECK (status IN ('pending', 'paid', 'refunded', 'partial', 'cancelled'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check');
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_status_check
            CHECK (status IN ('pending', 'paid', 'refunded', 'partial'))
        ");
    }
};