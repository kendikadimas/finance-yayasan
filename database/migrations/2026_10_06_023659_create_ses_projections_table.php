<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ses_projections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('kategori');
            $table->string('periode_proyeksi'); // periode yang diproyeksikan, contoh "2026-11"
            $table->decimal('alpha', 3, 2);
            $table->decimal('proyeksi', 15, 2);
            $table->decimal('mape', 6, 2)->nullable();
            $table->timestamp('computed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ses_projections');
    }
};
