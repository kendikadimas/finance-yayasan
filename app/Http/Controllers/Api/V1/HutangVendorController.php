<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\HutangVendorResource;
use App\Models\HutangVendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint baca status hutang vendor untuk sistem lain — mis. modul Procurement
 * yang perlu tahu status pembayaran invoice vendor setelah Purchase Order.
 * Ability: "read:hutang-vendor". Lihat docs/api-hutang-vendor.md.
 */
class HutangVendorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user('sanctum')?->tokenCan(ApiAbility::HutangVendor->value), 403, 'Token tidak memiliki akses ke data ini.');

        $request->validate([
            'kode_unit' => ['nullable', 'string'],
            'nomor_invoice' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:belum_lunas,lunas'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $hutang = HutangVendor::query()
            ->with(['unit:id,kode_unit,nama', 'vendor:id,nama'])
            ->when($request->string('kode_unit')->toString(), fn ($q, $kode) => $q->whereHas('unit', fn ($q) => $q->where('kode_unit', $kode)))
            ->when($request->string('nomor_invoice')->toString(), fn ($q, $nomor) => $q->where('nomor_invoice', $nomor))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('jatuh_tempo')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page') ?: 25);

        return HutangVendorResource::collection($hutang)->response();
    }
}
