<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        User::query()->create([
            'name' => 'Admin Sistem',
            'email' => 'admin@yayasan.test',
            'password' => $password,
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Pimpinan Yayasan',
            'email' => 'pimpinan@yayasan.test',
            'password' => $password,
            'role' => User::ROLE_PIMPINAN_YAYASAN,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Bendahara Yayasan',
            'email' => 'bendahara.yayasan@yayasan.test',
            'password' => $password,
            'role' => User::ROLE_BENDAHARA_YAYASAN,
            'email_verified_at' => now(),
        ]);

        Unit::query()->get()->each(function (Unit $unit) use ($password) {
            User::query()->create([
                'name' => "Bendahara {$unit->nama}",
                'email' => 'bendahara.'.str($unit->kode_unit)->lower()->toString().'@yayasan.test',
                'password' => $password,
                'role' => User::ROLE_BENDAHARA_UNIT,
                'unit_id' => $unit->id,
                'email_verified_at' => now(),
            ]);
        });
    }
}
