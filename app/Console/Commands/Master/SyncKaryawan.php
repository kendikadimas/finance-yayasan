<?php

namespace App\Console\Commands\Master;

use App\Models\Karyawan;
use App\Services\MasterDataClient;
use Illuminate\Console\Command;

class SyncKaryawan extends Command
{
    protected $signature = 'master:sync-karyawan';

    protected $description = 'Sinkronkan data Karyawan/Pegawai dari REST API Master Data Yayasan (/api/v1/karyawan)';

    public function handle(MasterDataClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('YAYASAN_MASTER_API_TOKEN / YAYASAN_MASTER_API_URL belum diatur di .env.');

            return self::FAILURE;
        }

        $this->info('Mengambil data karyawan dari API Master Data Yayasan...');

        $karyawan = $client->getAllPages('karyawan');

        $jumlah = 0;

        foreach ($karyawan as $k) {
            Karyawan::query()->updateOrCreate(
                ['kode_pegawai' => $k['nip']],
                [
                    'nama_pegawai' => $k['nama'],
                    'kode_unit' => $k['unit']['kode_unit'] ?? null,
                    'jabatan' => $k['role'] ?? null,
                    'status_aktif' => (bool) ($k['is_aktif'] ?? true),
                ],
            );

            $jumlah++;
        }

        $this->info("Selesai. {$jumlah} karyawan disinkronkan.");

        return self::SUCCESS;
    }
}
