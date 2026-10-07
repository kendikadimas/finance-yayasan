<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggaran extends Model
{
    protected $fillable = [
        'unit_id', 'jenis', 'kategori', 'pagu', 'periode_mulai', 'periode_selesai',
        'status', 'approved_by', 'approved_at', 'catatan', 'status_ews', 'status_ews_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date:Y-m-d',
            'periode_selesai' => 'date:Y-m-d',
            'approved_at' => 'datetime',
            'status_ews_updated_at' => 'datetime',
            'pagu' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<Transaksi, $this> */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    /**
     * Total realisasi (serapan aktual) dari transaksi yang terhubung ke RAB ini.
     */
    public function realisasi(): float
    {
        return (float) $this->transaksis()->sum('nominal');
    }
}
