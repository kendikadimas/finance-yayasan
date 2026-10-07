<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagihanSiswaResource;
use App\Models\TagihanSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint baca status tagihan siswa (SPP, uang pangkal, boarding fee, tagihan
 * kegiatan) untuk sistem lain — mis. Parent App (cek tagihan anak), PPDB/Admission
 * (verifikasi uang pangkal sebelum daftar ulang), Boarding Management (status
 * boarding fee). Ability: "read:tagihan-siswa". Lihat docs/api-tagihan-siswa.md.
 */
class TagihanSiswaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user('sanctum')?->tokenCan(ApiAbility::TagihanSiswa->value), 403, 'Token tidak memiliki akses ke data ini.');

        $request->validate([
            'kode_unit' => ['nullable', 'string'],
            'kode_siswa' => ['nullable', 'string'],
            'jenis_tagihan' => ['nullable', 'string'],
            'periode' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:belum_bayar,sebagian,lunas'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tagihan = TagihanSiswa::query()
            ->with('unit:id,kode_unit,nama')
            ->when($request->string('kode_unit')->toString(), fn ($q, $kode) => $q->whereHas('unit', fn ($q) => $q->where('kode_unit', $kode)))
            ->when($request->string('kode_siswa')->toString(), fn ($q, $kode) => $q->where('kode_siswa', $kode))
            ->when($request->string('jenis_tagihan')->toString(), fn ($q, $jenis) => $q->where('jenis_tagihan', $jenis))
            ->when($request->string('periode')->toString(), fn ($q, $periode) => $q->where('periode', $periode))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('jatuh_tempo')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page') ?: 25);

        return TagihanSiswaResource::collection($tagihan)->response();
    }
}
