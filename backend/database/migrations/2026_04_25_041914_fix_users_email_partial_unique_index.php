<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus unique constraint lama yang tidak aware soft delete
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });

        // 2. Buat partial unique index — hanya enforce uniqueness untuk
        //    row yang belum di-soft delete (deleted_at IS NULL).
        //    Row yang sudah dihapus boleh punya email yang sama.
        DB::statement(
            'CREATE UNIQUE INDEX users_email_unique
             ON users (email)
             WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        // Balik ke constraint biasa
        DB::statement('DROP INDEX IF EXISTS users_email_unique');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};