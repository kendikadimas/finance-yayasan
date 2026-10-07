<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ews_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('batas_waspada', 4, 2)->default(1.0);
            $table->decimal('batas_kritis', 4, 2)->default(1.2);
            $table->decimal('batas_melebihi', 4, 2)->default(1.5);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ews_settings');
    }
};
