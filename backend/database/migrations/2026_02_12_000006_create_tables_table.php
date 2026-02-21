<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('number', 20);
            $table->integer('capacity')->default(4);
            $table->string('section', 50)->nullable(); // e.g. Indoor, Outdoor, VIP
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
            $table->unique(['store_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
