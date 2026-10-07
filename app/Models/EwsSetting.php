<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EwsSetting extends Model
{
    protected $fillable = ['batas_waspada', 'batas_kritis', 'batas_melebihi', 'updated_by'];

    protected function casts(): array
    {
        return [
            'batas_waspada' => 'float',
            'batas_kritis' => 'float',
            'batas_melebihi' => 'float',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ambang batas EWS bersifat tunggal (satu baris konfigurasi global).
     *
     * Nilai default diberikan eksplisit (bukan mengandalkan DEFAULT kolom di migrasi):
     * firstOrCreate() tidak me-refresh model dari DB setelah insert, jadi instance yang baru
     * dibuat tidak akan tahu nilai default kolom kecuali diberikan di sini juga.
     */
    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'batas_waspada' => 1.0,
            'batas_kritis' => 1.2,
            'batas_melebihi' => 1.5,
        ]);
    }
}
