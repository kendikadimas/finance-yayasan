<?php

namespace App\Console\Commands\Master;

use App\Models\Unit;
use App\Services\MasterDataClient;
use Illuminate\Console\Command;

class SyncUnits extends Command
{
    protected $signature = 'master:sync-units';

    protected $description = 'Sinkronkan data Unit Pendidikan dari REST API Master Data Yayasan (/api/v1/units)';

    public function handle(MasterDataClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('YAYASAN_MASTER_API_TOKEN / YAYASAN_MASTER_API_URL belum diatur di .env.');

            return self::FAILURE;
        }

        $this->info('Mengambil data unit dari API Master Data Yayasan...');

        $units = $client->getAllPages('units');

        $jumlah = 0;

        foreach ($units as $unit) {
            Unit::query()->updateOrCreate(
                ['kode_unit' => $unit['kode_unit']],
                [
                    'nama' => $unit['nama_unit'],
                    'jenjang' => $unit['jenjang'] ?? '',
                    'jumlah_siswa' => $unit['total_siswa'] ?? 0,
                ],
            );

            $jumlah++;
        }

        $this->info("Selesai. {$jumlah} unit disinkronkan.");

        return self::SUCCESS;
    }
}
