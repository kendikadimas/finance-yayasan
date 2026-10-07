<?php

namespace Database\Seeders;

use App\Models\MetodePembayaran;
use Illuminate\Database\Seeder;

class MetodePembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $metodes = [
            ['nama' => 'Tunai', 'tipe' => 'manual', 'aktif' => true],
            ['nama' => 'Transfer Bank', 'tipe' => 'manual', 'aktif' => true],
            ['nama' => 'Virtual Account (Payment Gateway)', 'tipe' => 'payment_gateway', 'aktif' => true],
        ];

        foreach ($metodes as $data) {
            MetodePembayaran::query()->create($data);
        }
    }
}
