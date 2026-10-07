<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = ['kode_vendor', 'nama', 'kontak', 'status_hutang'];

    /** @return HasMany<HutangVendor, $this> */
    public function hutangVendors(): HasMany
    {
        return $this->hasMany(HutangVendor::class);
    }

    /** @return HasMany<Transaksi, $this> */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }
}
