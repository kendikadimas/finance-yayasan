<?php

namespace App\Enums;

/**
 * Daftar data yang boleh ditarik sistem eksternal lewat /api/v1/*. Setiap klien API
 * (App\Models\ApiClient) diberi subset dari ability ini oleh admin saat dibuat —
 * token Sanctum-nya hanya bisa mengakses endpoint yang sesuai abilitynya.
 */
enum ApiAbility: string
{
    case PembayaranSpp = 'read:pembayaran-spp';
    case TagihanSiswa = 'read:tagihan-siswa';
    case HutangVendor = 'read:hutang-vendor';
    case Penggajian = 'read:penggajian';
    case LaporanRingkasan = 'read:laporan-ringkasan';

    public function label(): string
    {
        return match ($this) {
            self::PembayaranSpp => 'Riwayat Pembayaran SPP',
            self::TagihanSiswa => 'Status Tagihan Siswa (SPP, Uang Pangkal, Boarding Fee, Kegiatan)',
            self::HutangVendor => 'Status Hutang Vendor',
            self::Penggajian => 'Status Penggajian',
            self::LaporanRingkasan => 'Ringkasan Laporan Keuangan per Unit',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $ability) => $ability->value, self::cases());
    }

    /** @return list<array{value: string, label: string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $ability) => ['value' => $ability->value, 'label' => $ability->label()],
            self::cases(),
        );
    }
}
