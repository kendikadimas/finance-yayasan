<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['kode_unit', 'nama', 'jenjang', 'jumlah_siswa'];

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<Anggaran, $this> */
    public function anggarans(): HasMany
    {
        return $this->hasMany(Anggaran::class);
    }

    /** @return HasMany<Transaksi, $this> */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    /** @return HasMany<KasBankAccount, $this> */
    public function kasBankAccounts(): HasMany
    {
        return $this->hasMany(KasBankAccount::class);
    }

    /** @return HasMany<TagihanSiswa, $this> */
    public function tagihanSiswas(): HasMany
    {
        return $this->hasMany(TagihanSiswa::class);
    }

    /** @return HasMany<Penggajian, $this> */
    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class);
    }

    /** @return HasMany<HutangVendor, $this> */
    public function hutangVendors(): HasMany
    {
        return $this->hasMany(HutangVendor::class);
    }
}
