<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('kode_siswa');
            $table->string('nama_siswa');
            $table->string('jenis_tagihan'); // spp | uang_pangkal | boarding_fee | kegiatan
            $table->string('periode'); // contoh: "2026-10" atau "Semester Ganjil 2026/2027"
            $table->decimal('nominal', 15, 2);
            $table->decimal('sisa_tagihan', 15, 2);
            $table->string('status')->default('belum_bayar'); // belum_bayar | sebagian | lunas
            $table->date('jatuh_tempo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_siswas');
    }
};
