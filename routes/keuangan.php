<?php

use App\Http\Controllers\Keuangan\AnggaranController;
use App\Http\Controllers\Keuangan\ApiClientController;
use App\Http\Controllers\Keuangan\EwsSettingController;
use App\Http\Controllers\Keuangan\HutangVendorController;
use App\Http\Controllers\Keuangan\KasBankAccountController;
use App\Http\Controllers\Keuangan\LaporanController;
use App\Http\Controllers\Keuangan\MetodePembayaranController;
use App\Http\Controllers\Keuangan\NotificationController;
use App\Http\Controllers\Keuangan\PenggajianController;
use App\Http\Controllers\Keuangan\TagihanSiswaController;
use App\Http\Controllers\Keuangan\TransaksiController;
use App\Http\Controllers\Keuangan\UnitController;
use App\Http\Controllers\Keuangan\UserController;
use App\Http\Controllers\Keuangan\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('keuangan')->name('keuangan.')->group(function () {
    // Admin saja: struktur organisasi & konfigurasi sistem.
    Route::middleware('role:admin')->group(function () {
        Route::post('units', [UnitController::class, 'store'])->name('units.store');
        Route::put('units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::post('metode-pembayaran', [MetodePembayaranController::class, 'store'])->name('metode-pembayaran.store');
        Route::put('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'update'])->name('metode-pembayaran.update');
        Route::delete('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'destroy'])->name('metode-pembayaran.destroy');

        Route::get('ews-setting', [EwsSettingController::class, 'edit'])->name('ews-setting.edit');
        Route::put('ews-setting', [EwsSettingController::class, 'update'])->name('ews-setting.update');

        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Klien API: sistem eksternal yang diberi akses baca ke /api/v1/*.
        Route::get('api-clients', [ApiClientController::class, 'index'])->name('api-clients.index');
        Route::post('api-clients', [ApiClientController::class, 'store'])->name('api-clients.store');
        Route::put('api-clients/{api_client}', [ApiClientController::class, 'update'])->name('api-clients.update');
        Route::post('api-clients/{api_client}/regenerate', [ApiClientController::class, 'regenerateToken'])->name('api-clients.regenerate');
        Route::post('api-clients/{api_client}/toggle', [ApiClientController::class, 'toggleActive'])->name('api-clients.toggle');
        Route::delete('api-clients/{api_client}', [ApiClientController::class, 'destroy'])->name('api-clients.destroy');
    });

    Route::get('units', [UnitController::class, 'index'])->name('units.index');
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    // Vendor & metode pembayaran: dapat dilihat semua, dikelola peran operasional.
    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('metode-pembayaran.index');

    // RAB: Pimpinan Yayasan hanya bisa melihat (untuk menyetujui/menolak), tidak mengelola operasional.
    Route::middleware('role:admin,pimpinan_yayasan,bendahara_yayasan,bendahara_unit')->group(function () {
        Route::get('anggaran', [AnggaranController::class, 'index'])->name('anggaran.index');
    });

    Route::middleware('role:admin,bendahara_yayasan,bendahara_unit')->group(function () {
        Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::put('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');

        // Kas & Bank.
        Route::get('kas-bank', [KasBankAccountController::class, 'index'])->name('kas-bank.index');
        Route::post('kas-bank', [KasBankAccountController::class, 'store'])->name('kas-bank.store');
        Route::put('kas-bank/{kas_bank_account}', [KasBankAccountController::class, 'update'])->name('kas-bank.update');
        Route::delete('kas-bank/{kas_bank_account}', [KasBankAccountController::class, 'destroy'])->name('kas-bank.destroy');

        // Tagihan siswa & piutang.
        Route::get('tagihan-siswa', [TagihanSiswaController::class, 'index'])->name('tagihan-siswa.index');
        Route::post('tagihan-siswa', [TagihanSiswaController::class, 'store'])->name('tagihan-siswa.store');
        Route::put('tagihan-siswa/{tagihan_siswa}', [TagihanSiswaController::class, 'update'])->name('tagihan-siswa.update');
        Route::post('tagihan-siswa/{tagihan_siswa}/bayar', [TagihanSiswaController::class, 'bayar'])->name('tagihan-siswa.bayar');
        Route::delete('tagihan-siswa/{tagihan_siswa}', [TagihanSiswaController::class, 'destroy'])->name('tagihan-siswa.destroy');

        // RAB / Anggaran.
        Route::post('anggaran', [AnggaranController::class, 'store'])->name('anggaran.store');
        Route::put('anggaran/{anggaran}', [AnggaranController::class, 'update'])->name('anggaran.update');
        Route::post('anggaran/{anggaran}/ajukan', [AnggaranController::class, 'ajukan'])->name('anggaran.ajukan');
        Route::delete('anggaran/{anggaran}', [AnggaranController::class, 'destroy'])->name('anggaran.destroy');

        // Transaksi.
        Route::get('transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::post('transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::delete('transaksi/{transaksi}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');

        // Penggajian.
        Route::get('penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
        Route::post('penggajian', [PenggajianController::class, 'store'])->name('penggajian.store');
        Route::put('penggajian/{penggajian}', [PenggajianController::class, 'update'])->name('penggajian.update');
        Route::post('penggajian/{penggajian}/bayar', [PenggajianController::class, 'bayar'])->name('penggajian.bayar');
        Route::delete('penggajian/{penggajian}', [PenggajianController::class, 'destroy'])->name('penggajian.destroy');

        // Hutang vendor.
        Route::get('hutang-vendor', [HutangVendorController::class, 'index'])->name('hutang-vendor.index');
        Route::post('hutang-vendor', [HutangVendorController::class, 'store'])->name('hutang-vendor.store');
        Route::put('hutang-vendor/{hutang_vendor}', [HutangVendorController::class, 'update'])->name('hutang-vendor.update');
        Route::post('hutang-vendor/{hutang_vendor}/lunas', [HutangVendorController::class, 'lunas'])->name('hutang-vendor.lunas');
        Route::delete('hutang-vendor/{hutang_vendor}', [HutangVendorController::class, 'destroy'])->name('hutang-vendor.destroy');

        // Laporan per unit & proyeksi SES.
        Route::get('laporan/unit/{unit}', [LaporanController::class, 'perUnit'])->name('laporan.per-unit');
        Route::get('laporan/unit/{unit}/proyeksi-ses', [LaporanController::class, 'proyeksiSes'])->name('laporan.proyeksi-ses');
        Route::post('laporan/unit/{unit}/proyeksi-ses', [LaporanController::class, 'simpanProyeksiSes'])->name('laporan.proyeksi-ses.store');
    });

    // RAB: hanya Pimpinan Yayasan yang menyetujui/menolak.
    Route::middleware('role:pimpinan_yayasan')->group(function () {
        Route::post('anggaran/{anggaran}/approve', [AnggaranController::class, 'approve'])->name('anggaran.approve');
        Route::post('anggaran/{anggaran}/reject', [AnggaranController::class, 'reject'])->name('anggaran.reject');
    });

    // Laporan konsolidasi & dashboard EWS: tingkat yayasan.
    Route::middleware('role:admin,pimpinan_yayasan,bendahara_yayasan')->group(function () {
        Route::get('laporan/konsolidasi', [LaporanController::class, 'konsolidasi'])->name('laporan.konsolidasi');
    });

    Route::get('laporan/dashboard-ews', [LaporanController::class, 'dashboardEws'])->name('laporan.dashboard-ews');

    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
