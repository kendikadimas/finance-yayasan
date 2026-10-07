<?php

namespace Database\Seeders;

use App\Models\Anggaran;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnggaranSeeder extends Seeder
{
    public function run(): void
    {
        $pimpinan = User::query()->where('role', User::ROLE_PIMPINAN_YAYASAN)->first();

        $pemasukan = [
            'SPP' => 600_000_000,
            'BOS' => 150_000_000,
            'Infaq' => 40_000_000,
        ];

        $pengeluaran = [
            'Gaji' => 500_000_000,
            'Operasional' => 240_000_000,
            'Pemeliharaan' => 60_000_000,
        ];

        Unit::query()->get()->each(function (Unit $unit) use ($pemasukan, $pengeluaran, $pimpinan) {
            foreach ($pemasukan as $kategori => $pagu) {
                Anggaran::query()->create([
                    'unit_id' => $unit->id,
                    'jenis' => 'pemasukan',
                    'kategori' => $kategori,
                    'pagu' => $pagu,
                    'periode_mulai' => '2026-01-01',
                    'periode_selesai' => '2026-12-31',
                    'status' => 'disetujui',
                    'approved_by' => $pimpinan?->id,
                    'approved_at' => now(),
                ]);
            }

            foreach ($pengeluaran as $kategori => $pagu) {
                Anggaran::query()->create([
                    'unit_id' => $unit->id,
                    'jenis' => 'pengeluaran',
                    'kategori' => $kategori,
                    'pagu' => $pagu,
                    'periode_mulai' => '2026-01-01',
                    'periode_selesai' => '2026-12-31',
                    'status' => 'disetujui',
                    'approved_by' => $pimpinan?->id,
                    'approved_at' => now(),
                ]);
            }
        });
    }
}
