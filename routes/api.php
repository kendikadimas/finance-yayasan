<?php

use App\Http\Controllers\Api\V1\HutangVendorController;
use App\Http\Controllers\Api\V1\LaporanRingkasanController;
use App\Http\Controllers\Api\V1\PembayaranSppController;
use App\Http\Controllers\Api\V1\PenggajianController;
use App\Http\Controllers\Api\V1\TagihanSiswaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', 'api_client.active', 'throttle:api'])
    ->group(function () {
        Route::get('pembayaran-spp', [PembayaranSppController::class, 'index'])->name('pembayaran-spp.index');
        Route::get('tagihan-siswa', [TagihanSiswaController::class, 'index'])->name('tagihan-siswa.index');
        Route::get('hutang-vendor', [HutangVendorController::class, 'index'])->name('hutang-vendor.index');
        Route::get('penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
        Route::get('laporan/ringkasan', [LaporanRingkasanController::class, 'index'])->name('laporan.ringkasan');
    });
