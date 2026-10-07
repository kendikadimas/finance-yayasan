<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HutangVendor extends Model
{
    protected $fillable = [
        'vendor_id', 'unit_id', 'transaksi_id', 'nomor_invoice', 'deskripsi',
        'nominal', 'jatuh_tempo', 'status', 'tanggal_lunas',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'jatuh_tempo' => 'date:Y-m-d',
            'tanggal_lunas' => 'date:Y-m-d',
        ];
    }

    /** @return BelongsTo<Vendor, $this> */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
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
