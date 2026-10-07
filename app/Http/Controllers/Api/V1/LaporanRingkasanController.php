<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiAbility;
use App\Http\Controllers\Controller;
use App\Services\LaporanRingkasanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint baca ringkasan keuangan per unit (pemasukan, pengeluaran, saldo kas/bank)
 * untuk satu periode — dipakai modul Data Integration & Analytics / Executive
 * Dashboard tingkat yayasan. Ability: "read:laporan-ringkasan".
 * Lihat docs/api-laporan-ringkasan.md.
 */
class LaporanRingkasanController extends Controller
{
    public function __construct(private readonly LaporanRingkasanService $ringkasanService) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user('sanctum')?->tokenCan(ApiAbility::LaporanRingkasan->value), 403, 'Token tidak memiliki akses ke data ini.');

        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
        ]);

        $periodeMulai = $request->date('dari') ?? now()->startOfYear();
        $periodeSelesai = $request->date('sampai') ?? now();

        $perUnit = $this->ringkasanService->perUnit($periodeMulai, $periodeSelesai);

        return response()->json([
            'data' => $perUnit->values(),
            'meta' => [
                'total_pemasukan' => $perUnit->sum('pemasukan'),
                'total_pengeluaran' => $perUnit->sum('pengeluaran'),
                'total_saldo_kas_bank' => $perUnit->sum('saldo_kas_bank'),
                'periode' => [
                    'dari' => $periodeMulai->format('Y-m-d'),
                    'sampai' => $periodeSelesai->format('Y-m-d'),
                ],
            ],
        ]);
    }
}
