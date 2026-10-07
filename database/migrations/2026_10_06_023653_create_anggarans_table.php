<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('jenis'); // pemasukan | pengeluaran
            $table->string('kategori');
            $table->decimal('pagu', 15, 2);
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->string('status')->default('draft'); // draft | diajukan | disetujui | ditolak
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status_ews')->default('aman'); // aman | waspada | kritis | melebihi_anggaran
            $table->timestamp('status_ews_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggarans');
    }
};
