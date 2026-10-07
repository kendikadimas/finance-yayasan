<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hutang_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaksi_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nomor_invoice')->nullable();
            $table->text('deskripsi');
            $table->decimal('nominal', 15, 2);
            $table->date('jatuh_tempo');
            $table->string('status')->default('belum_lunas'); // belum_lunas | lunas
            $table->date('tanggal_lunas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang_vendors');
    }
};
