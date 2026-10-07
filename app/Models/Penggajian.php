<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggajian extends Model
{
    protected $fillable = [
        'unit_id', 'transaksi_id', 'kode_pegawai', 'nama_pegawai', 'periode',
        'gaji_pokok', 'tunjangan', 'potongan', 'total_gaji', 'status', 'tanggal_bayar',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tunjangan' => 'decimal:2',
            'potongan' => 'decimal:2',
            'total_gaji' => 'decimal:2',
            'tanggal_bayar' => 'date:Y-m-d',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return BelongsTo<Transaksi, $this> */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }
}
