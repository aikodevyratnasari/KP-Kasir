<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambahkan support Transfer Bank ke tabel payments.
 *
 * Perubahan:
 *  - payment_method enum: tambah 'bank_transfer'
 *  - kolom va_number    : nomor virtual account dari Midtrans
 *
 * Catatan: payment_url sudah ada sejak migrasi Midtrans sebelumnya.
 * Untuk Mandiri, payment_url dipakai untuk menyimpan "biller_code:bill_key"
 * sehingga tidak perlu kolom baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Perbaiki enum payment_method: tambah 'bank_transfer' ─────
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');
        DB::statement("ALTER TABLE payments ALTER COLUMN payment_method TYPE VARCHAR(20) USING payment_method::text");
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_payment_method_check
            CHECK (payment_method IN ('cash', 'card', 'ewallet', 'qris', 'bank_transfer'))
        ");

        // ── 2. Tambah kolom va_number ────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            $table->string('va_number', 50)->nullable()->after('qr_string')
                  ->comment('Nomor Virtual Account dari Midtrans (BCA, BNI, BRI, Mandiri bill_key, Permata)');
            $table->string('bank', 20)->nullable()->after('va_number')
                  ->comment('Nama bank untuk transfer: bca, bni, bri, mandiri, permata');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['va_number', 'bank']);
        });

        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_payment_method_check
            CHECK (payment_method IN ('cash', 'card', 'ewallet', 'qris'))
        ");
    }
};