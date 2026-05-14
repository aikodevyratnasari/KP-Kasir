<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan kolom table_id pada tabel reservations nullable agar meja
     * yang sudah punya riwayat reservasi tetap bisa dihapus tanpa
     * melanggar foreign key constraint reservations_table_id_foreign.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Lepas FK dulu sebelum mengubah kolom (PostgreSQL memerlukannya)
            $table->dropForeign('reservations_table_id_foreign');

            // Jadikan nullable
            $table->foreignId('table_id')
                ->nullable()
                ->change();

            // Pasang kembali FK dengan onDelete SET NULL
            $table->foreign('table_id')
                ->references('id')
                ->on('tables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['table_id']);

            // Kembalikan ke NOT NULL (pastikan tidak ada NULL sebelum rollback)
            $table->foreignId('table_id')
                ->nullable(false)
                ->change();

            $table->foreign('table_id')
                ->references('id')
                ->on('tables')
                ->restrictOnDelete();
        });
    }
};