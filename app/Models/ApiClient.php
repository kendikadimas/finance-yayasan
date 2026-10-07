<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

/**
 * Mewakili sistem eksternal (paket lain dalam Sistem Terintegrasi Yayasan Pendidikan,
 * atau pihak ketiga lain) yang diberi akses baca ke REST API sistem keuangan ini lewat
 * token Sanctum. Terpisah dari App\Models\User: ini bukan akun manusia, tidak bisa login
 * ke antarmuka web, cuma punya token untuk otentikasi ke endpoint /api/v1/*.
 */
class ApiClient extends Model
{
    use HasApiTokens;

    protected $fillable = ['nama', 'deskripsi', 'is_aktif', 'abilities'];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'last_used_at' => 'datetime',
            'abilities' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
