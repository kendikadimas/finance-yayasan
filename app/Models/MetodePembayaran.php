<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodePembayaran extends Model
{
    protected $fillable = ['nama', 'tipe', 'aktif', 'konfigurasi'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'konfigurasi' => 'array',
        ];
    }

    /** @return HasMany<Transaksi, $this> */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }
}
