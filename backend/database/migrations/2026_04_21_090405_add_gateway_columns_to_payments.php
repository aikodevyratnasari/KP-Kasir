<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambahkan kolom gateway untuk integrasi Midtrans (QRIS & E-Wallet).
 *
 * Kolom baru:
 *  - gateway          : nama gateway ('midtrans', null = manual/offline)
 *  - gateway_trx_id   : transaction_id dari Midtrans — kunci cocok webhook
 *  - gateway_status   : raw status dari Midtrans (settlement, capture, dll.)
 *  - snap_token       : token Snap untuk pop-up widget Midtrans
 *  - payment_url      : URL redirect jika tidak pakai Snap JS
 *  - qr_string        : base64 QR image atau string untuk QRIS
 *  - gateway_response : JSON respons lengkap dari Midtrans (untuk debug/audit)
 *  - settled_at       : waktu konfirmasi settlement dari webhook
 *
 * Perubahan enum:
 *  - payment_method   : tambah 'qris'
 *  - status           : tambah 'pending' (sebelum gateway konfirmasi)
 *
 * Catatan: Cash & Kartu manual (via EDC) TIDAK menggunakan gateway.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Perbaiki enum payment_method: tambah 'qris' ───────────────
        // PostgreSQL tidak bisa ALTER ENUM langsung, harus via constraint check.
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');
        DB::statement("ALTER TABLE payments ALTER COLUMN payment_method TYPE VARCHAR(20) USING payment_method::text");
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_payment_method_check
            CHECK (payment_method IN ('cash', 'card', 'ewallet', 'qris'))
        ");

        // ── 2. Perbaiki enum status: tambah 'pending' ────────────────────
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check');
        DB::statement("ALTER TABLE payments ALTER COLUMN status TYPE VARCHAR(20) USING status::text");
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_status_check
            CHECK (status IN ('pending', 'paid', 'refunded', 'partial'))
        ");

        // ── 3. Tambah kolom gateway ───────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            // Kolom gateway — null berarti transaksi manual (cash/kartu via EDC)
            $table->string('gateway', 20)->nullable()->after('reference_number')
                  ->comment('null=manual, midtrans=via Midtrans');
            $table->string('gateway_trx_id', 100)->nullable()->after('gateway')
                  ->comment('transaction_id dari Midtrans — kunci untuk webhook matching');
            $table->string('gateway_status', 50)->nullable()->after('gateway_trx_id')
                  ->comment('raw status dari gateway: settlement, capture, pending, deny, expire, cancel');
            $table->string('snap_token', 255)->nullable()->after('gateway_status')
                  ->comment('Snap token untuk pop-up Midtrans JS');
            $table->string('payment_url', 500)->nullable()->after('snap_token')
                  ->comment('URL redirect alternatif jika tidak pakai Snap JS');
            $table->text('qr_string')->nullable()->after('payment_url')
                  ->comment('Base64 QR image atau raw QRIS string');
            $table->jsonb('gateway_response')->nullable()->after('qr_string')
                  ->comment('Raw JSON dari gateway untuk audit/debug');
            $table->timestamp('settled_at')->nullable()->after('refunded_by')
                  ->comment('Waktu webhook settlement/capture diterima');

            // Index untuk pencarian via gateway_trx_id (dipakai di webhook handler)
            $table->index('gateway_trx_id');
            $table->index(['gateway', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway_trx_id']);
            $table->dropIndex(['gateway', 'status']);
            $table->dropColumn([
                'gateway', 'gateway_trx_id', 'gateway_status',
                'snap_token', 'payment_url', 'qr_string',
                'gateway_response', 'settled_at',
            ]);
        });

        // Kembalikan enum ke semula
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_payment_method_check
            CHECK (payment_method IN ('cash', 'card', 'ewallet'))
        ");

        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check');
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_status_check
            CHECK (status IN ('paid', 'refunded', 'partial'))
        ");
    }
};