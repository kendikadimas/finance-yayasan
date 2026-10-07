<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\PenggajianResource;
use App\Models\Penggajian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint baca status penggajian untuk sistem lain — mis. Teacher/Employee App
 * yang menampilkan slip gaji & status pembayaran ke guru/pegawai.
 * Ability: "read:penggajian". Lihat docs/api-penggajian.md.
 */
class PenggajianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user('sanctum')?->tokenCan(ApiAbility::Penggajian->value), 403, 'Token tidak memiliki akses ke data ini.');

        $request->validate([
            'kode_unit' => ['nullable', 'string'],
            'kode_pegawai' => ['nullable', 'string'],
            'periode' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,dibayar'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $penggajian = Penggajian::query()
            ->with('unit:id,kode_unit,nama')
            ->when($request->string('kode_unit')->toString(), fn ($q, $kode) => $q->whereHas('unit', fn ($q) => $q->where('kode_unit', $kode)))
            ->when($request->string('kode_pegawai')->toString(), fn ($q, $kode) => $q->where('kode_pegawai', $kode))
            ->when($request->string('periode')->toString(), fn ($q, $periode) => $q->where('periode', $periode))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page') ?: 25);

        return PenggajianResource::collection($penggajian)->response();
    }
}
