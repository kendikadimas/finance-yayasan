<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BukuBesar extends Model
{
    protected $fillable = ['transaksi_id', 'unit_id', 'tanggal', 'akun', 'debit', 'kredit'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'debit' => 'decimal:2',
            'kredit' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Transaksi, $this> */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
