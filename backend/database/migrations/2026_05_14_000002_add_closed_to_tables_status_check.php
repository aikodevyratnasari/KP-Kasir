<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan nilai 'closed' ke check constraint tables_status_check.
     * Constraint lama hanya mengizinkan: available, occupied, reserved.
     * Constraint baru: available, occupied, reserved, closed.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE tables DROP CONSTRAINT tables_status_check');
        DB::statement("ALTER TABLE tables ADD CONSTRAINT tables_status_check CHECK (status IN ('available', 'occupied', 'reserved', 'closed'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tables DROP CONSTRAINT tables_status_check');
        DB::statement("ALTER TABLE tables ADD CONSTRAINT tables_status_check CHECK (status IN ('available', 'occupied', 'reserved'))");
    }
};