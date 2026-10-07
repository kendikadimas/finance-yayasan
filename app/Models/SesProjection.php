<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesProjection extends Model
{
    protected $fillable = [
        'unit_id', 'kategori', 'periode_proyeksi', 'alpha', 'proyeksi', 'mape', 'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'alpha' => 'float',
            'proyeksi' => 'decimal:2',
            'mape' => 'float',
            'computed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
