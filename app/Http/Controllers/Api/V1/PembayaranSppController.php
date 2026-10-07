<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranSppResource;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint baca untuk sistem lain (paket lain dalam Sistem Terintegrasi Yayasan
 * Pendidikan) yang perlu menarik data pembayaran SPP dari sistem keuangan ini.
 * Otentikasi: Bearer token Sanctum milik App\Models\ApiClient, ability
 * "read:pembayaran-spp". Lihat docs/api-pembayaran-spp.md untuk dokumentasi lengkap.
 */
class PembayaranSppController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user('sanctum')?->tokenCan(ApiAbility::PembayaranSpp->value), 403, 'Token tidak memiliki akses ke data ini.');

        $request->validate([
            'kode_unit' => ['nullable', 'string'],
            'kode_siswa' => ['nullable', 'string'],
            'periode' => ['nullable', 'string'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $pembayaran = Transaksi::query()
            ->with(['unit:id,kode_unit,nama', 'tagihanSiswa', 'metodePembayaran:id,nama'])
            ->where('jenis', 'masuk')
            ->whereNotNull('tagihan_siswa_id')
            ->whereHas('tagihanSiswa', fn ($q) => $q->where('jenis_tagihan', 'spp'))
            ->when($request->string('kode_unit')->toString(), fn ($q, $kode) => $q->whereHas('unit', fn ($q) => $q->where('kode_unit', $kode)))
            ->when($request->string('kode_siswa')->toString(), fn ($q, $kode) => $q->whereHas('tagihanSiswa', fn ($q) => $q->where('kode_siswa', $kode)))
            ->when($request->string('periode')->toString(), fn ($q, $periode) => $q->whereHas('tagihanSiswa', fn ($q) => $q->where('periode', $periode)))
            ->when($request->date('tanggal_dari'), fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($request->date('tanggal_sampai'), fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page') ?: 25);

        return PembayaranSppResource::collection($pembayaran)->response();
    }
}
