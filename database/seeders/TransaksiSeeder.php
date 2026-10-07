<?php

namespace Database\Seeders;

use App\Models\Anggaran;
use App\Models\KasBankAccount;
use App\Models\TagihanSiswa;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransaksiSeeder extends Seeder
{
    /**
     * Variasi bulanan (relatif terhadap basis) agar deret historis tidak konstan,
     * supaya pengujian MAPE pada beberapa nilai alpha bermakna.
     *
     * @var list<float>
     */
    private const VARIASI = [1.0, 1.05, 0.95, 1.1, 1.0, 0.9, 1.15];

    public function run(): void
    {
        Unit::query()->get()->each(function (Unit $unit) {
            // UserSeeder selalu membuat satu Bendahara Unit per unit sebelum seeder ini berjalan.
            $bendahara = User::query()->where('role', User::ROLE_BENDAHARA_UNIT)->where('unit_id', $unit->id)->firstOrFail();
            $bank = KasBankAccount::query()->where('unit_id', $unit->id)->where('jenis', 'bank')->first();
            $kas = KasBankAccount::query()->where('unit_id', $unit->id)->where('jenis', 'kas')->first();

            $anggaranOperasional = Anggaran::query()->where('unit_id', $unit->id)->where('kategori', 'Operasional')->first();
            $anggaranGaji = Anggaran::query()->where('unit_id', $unit->id)->where('kategori', 'Gaji')->first();
            $anggaranSpp = Anggaran::query()->where('unit_id', $unit->id)->where('kategori', 'SPP')->first();

            $bulanMulai = Carbon::create(2026, 4, 1);

            foreach (self::VARIASI as $index => $faktor) {
                $bulan = $bulanMulai->copy()->addMonths($index);

                if ($bulan->greaterThan(now())) {
                    break;
                }

                Transaksi::query()->create([
                    'unit_id' => $unit->id,
                    'anggaran_id' => $anggaranOperasional?->id,
                    'kas_bank_account_id' => $bank?->id,
                    'created_by' => $bendahara->id,
                    'jenis' => 'keluar',
                    'kategori' => 'Operasional',
                    'nominal' => round(18_000_000 * $faktor),
                    'tanggal' => $bulan->copy()->day(10),
                    'keterangan' => 'Belanja operasional bulan '.$bulan->translatedFormat('F Y'),
                ]);

                Transaksi::query()->create([
                    'unit_id' => $unit->id,
                    'anggaran_id' => $anggaranGaji?->id,
                    'kas_bank_account_id' => $bank?->id,
                    'created_by' => $bendahara->id,
                    'jenis' => 'keluar',
                    'kategori' => 'Gaji',
                    'nominal' => round(38_000_000 * $faktor),
                    'tanggal' => $bulan->copy()->day(28),
                    'keterangan' => 'Penggajian bulan '.$bulan->translatedFormat('F Y'),
                ]);

                Transaksi::query()->create([
                    'unit_id' => $unit->id,
                    'anggaran_id' => $anggaranSpp?->id,
                    'kas_bank_account_id' => $bank?->id,
                    'created_by' => $bendahara->id,
                    'jenis' => 'masuk',
                    'kategori' => 'SPP',
                    'nominal' => round(45_000_000 * $faktor),
                    'tanggal' => $bulan->copy()->day(5),
                    'keterangan' => 'Rekap pembayaran SPP bulan '.$bulan->translatedFormat('F Y'),
                ]);
            }

            // Beberapa tagihan siswa individual: sebagian lunas, sebagian masih piutang.
            $siswa = [
                ['kode' => 'S-0001', 'nama' => 'Ahmad Fajar'],
                ['kode' => 'S-0002', 'nama' => 'Siti Rahma'],
                ['kode' => 'S-0003', 'nama' => 'Budi Santoso'],
            ];

            foreach ($siswa as $i => $data) {
                $tagihan = TagihanSiswa::query()->create([
                    'unit_id' => $unit->id,
                    'kode_siswa' => $data['kode'],
                    'nama_siswa' => $data['nama'],
                    'jenis_tagihan' => 'spp',
                    'periode' => now()->format('Y-m'),
                    'nominal' => 500_000,
                    'sisa_tagihan' => 500_000,
                    'status' => 'belum_bayar',
                    'jatuh_tempo' => now()->endOfMonth(),
                ]);

                if ($i < 2) {
                    Transaksi::query()->create([
                        'unit_id' => $unit->id,
                        'tagihan_siswa_id' => $tagihan->id,
                        'kas_bank_account_id' => $kas?->id,
                        'created_by' => $bendahara->id,
                        'jenis' => 'masuk',
                        'kategori' => 'Tagihan Siswa - Spp',
                        'nominal' => 500_000,
                        'tanggal' => now(),
                        'keterangan' => "Pembayaran SPP - {$data['nama']} ({$data['kode']})",
                    ]);
                }
            }
        });
    }
}
