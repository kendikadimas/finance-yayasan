<?php

namespace App\Console\Commands\Master;

use App\Models\Vendor;
use App\Services\MasterDataClient;
use Illuminate\Console\Command;

class SyncVendors extends Command
{
    protected $signature = 'master:sync-vendors';

    protected $description = 'Sinkronkan data Vendor dari REST API Master Data Yayasan (/api/v1/vendor)';

    public function handle(MasterDataClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('YAYASAN_MASTER_API_TOKEN / YAYASAN_MASTER_API_URL belum diatur di .env.');

            return self::FAILURE;
        }

        $this->info('Mengambil data vendor dari API Master Data Yayasan...');

        $vendors = $client->getAllPages('vendor');

        $jumlah = 0;

        foreach ($vendors as $v) {
            Vendor::query()->updateOrCreate(
                ['kode_vendor' => $v['kode_vendor']],
                [
                    'nama' => $v['nama_vendor'],
                    'kontak' => $v['telepon'] ?? null,
                ],
            );

            $jumlah++;
        }

        $this->info("Selesai. {$jumlah} vendor disinkronkan.");

        return self::SUCCESS;
    }
}
