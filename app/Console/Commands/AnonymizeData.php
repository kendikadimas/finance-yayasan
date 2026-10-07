<?php

namespace App\Console\Commands;

use App\Models\Penggajian;
use App\Models\TagihanSiswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * NF-02 (PRD): "Data transaksi riil dari yayasan dianonimkan sebelum diproses."
 *
 * Mengganti nama & kode identitas pribadi (siswa, pegawai) dengan pseudonim yang stabil
 * (urutan & relasi tetap terjaga, hanya identitas aslinya yang hilang), tanpa menyentuh
 * angka keuangan (nominal, tanggal, status). Jalankan ini pada salinan data riil yayasan
 * SEBELUM dipakai untuk pengujian/analisis (mis. pengujian akurasi proyeksi SES),
 * tidak pernah pada database produksi yang sedang melayani pengguna nyata.
 */
class AnonymizeData extends Command
{
    protected $signature = 'data:anonymize {--force : Lewati konfirmasi}';

    protected $description = 'Anonimkan nama & kode siswa/pegawai pada data tagihan siswa dan penggajian';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm(
            'Ini akan MENGGANTI PERMANEN nama & kode siswa/pegawai di database saat ini dengan pseudonim. '
            .'Pastikan ini dijalankan pada SALINAN data, bukan database produksi. Lanjutkan?'
        )) {
            $this->warn('Dibatalkan.');

            return self::FAILURE;
        }

        DB::transaction(function () {
            TagihanSiswa::query()->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $i => $row) {
                    $row->forceFill([
                        'kode_siswa' => 'ANON-S-'.str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                        'nama_siswa' => 'Siswa '.str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                    ])->save();
                }
            });

            Penggajian::query()->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $row->forceFill([
                        'kode_pegawai' => 'ANON-P-'.str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                        'nama_pegawai' => 'Pegawai '.str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                    ])->save();
                }
            });
        });

        $this->info('Selesai: '.TagihanSiswa::count().' tagihan siswa & '.Penggajian::count().' data penggajian dianonimkan.');

        return self::SUCCESS;
    }
}
