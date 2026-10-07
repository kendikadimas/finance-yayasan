<?php

namespace Database\Seeders;

use App\Models\KasBankAccount;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['kode_unit' => 'SD-01', 'nama' => 'SD Harapan Bangsa', 'jenjang' => 'SD', 'jumlah_siswa' => 320],
            ['kode_unit' => 'SMP-01', 'nama' => 'SMP Harapan Bangsa', 'jenjang' => 'SMP', 'jumlah_siswa' => 260],
            ['kode_unit' => 'SMA-01', 'nama' => 'SMA Harapan Bangsa', 'jenjang' => 'SMA', 'jumlah_siswa' => 210],
        ];

        foreach ($units as $data) {
            $unit = Unit::query()->create($data);

            KasBankAccount::query()->create([
                'unit_id' => $unit->id,
                'nama' => 'Kas Tunai',
                'jenis' => 'kas',
                'saldo' => 5_000_000,
            ]);

            KasBankAccount::query()->create([
                'unit_id' => $unit->id,
                'nama' => 'Bank BCA',
                'jenis' => 'bank',
                'nomor_rekening' => '1234-'.$unit->id.'-5678',
                'saldo' => 300_000_000,
            ]);
        }
    }
}
