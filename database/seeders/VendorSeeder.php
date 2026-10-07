<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            ['nama' => 'CV Sumber Makmur', 'kontak' => '081234567001'],
            ['nama' => 'Toko ATK Sejahtera', 'kontak' => '081234567002'],
            ['nama' => 'PT Catering Nusantara', 'kontak' => '081234567003'],
        ];

        foreach ($vendors as $data) {
            Vendor::query()->create($data);
        }
    }
}
