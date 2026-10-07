<?php

namespace App\Console\Commands\Master;

use App\Models\Siswa;
use App\Services\MasterDataClient;
use Illuminate\Console\Command;

class SyncSiswa extends Command
{
    protected $signature = 'master:sync-siswa';

    protected $description = 'Sinkronkan data Siswa dari REST API Master Data Yayasan (/api/v1/siswa)';

    public function handle(MasterDataClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('YAYASAN_MASTER_API_TOKEN / YAYASAN_MASTER_API_URL belum diatur di .env.');

            return self::FAILURE;
        }

        $this->info('Mengambil data siswa dari API Master Data Yayasan...');

        $siswa = $client->getAllPages('siswa');

        $jumlah = 0;

        foreach ($siswa as $s) {
            Siswa::query()->updateOrCreate(
                ['kode_siswa' => $s['nis']],
                [
                    'nama_siswa' => $s['nama_lengkap'],
                    'kode_unit' => $s['unit']['kode_unit'] ?? null,
                    'status_aktif' => ($s['status'] ?? 'aktif') === 'aktif',
                ],
            );

            $jumlah++;
        }

        $this->info("Selesai. {$jumlah} siswa disinkronkan.");

        return self::SUCCESS;
    }
}
