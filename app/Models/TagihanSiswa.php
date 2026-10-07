<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanSiswa extends Model
{
    protected $fillable = [
        'unit_id', 'kode_siswa', 'nama_siswa', 'jenis_tagihan', 'periode',
        'nominal', 'sisa_tagihan', 'status', 'jatuh_tempo',
    ];

    protected function casts(): array
    {
        return [
            'jatuh_tempo' => 'date:Y-m-d',
            'nominal' => 'decimal:2',
            'sisa_tagihan' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return HasMany<Transaksi, $this> */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }
}
