<?php

namespace App\Models;

use App\Observers\TransaksiObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(TransaksiObserver::class)]
class Transaksi extends Model
{
    protected $fillable = [
        'unit_id', 'anggaran_id', 'kas_bank_account_id', 'metode_pembayaran_id',
        'vendor_id', 'tagihan_siswa_id', 'created_by', 'jenis', 'kategori',
        'nominal', 'tanggal', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'nominal' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return BelongsTo<Anggaran, $this> */
    public function anggaran(): BelongsTo
    {
        return $this->belongsTo(Anggaran::class);
    }

    /** @return BelongsTo<KasBankAccount, $this> */
    public function kasBankAccount(): BelongsTo
    {
        return $this->belongsTo(KasBankAccount::class);
    }

    /** @return BelongsTo<MetodePembayaran, $this> */
    public function metodePembayaran(): BelongsTo
    {
        return $this->belongsTo(MetodePembayaran::class);
    }

    /** @return BelongsTo<Vendor, $this> */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** @return BelongsTo<TagihanSiswa, $this> */
    public function tagihanSiswa(): BelongsTo
    {
        return $this->belongsTo(TagihanSiswa::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<BukuBesar, $this> */
    public function bukuBesars(): HasMany
    {
        return $this->hasMany(BukuBesar::class);
    }
}
