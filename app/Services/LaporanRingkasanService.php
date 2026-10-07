<?php

namespace App\Services;

use App\Models\KasBankAccount;
use App\Models\Transaksi;
use App\Models\Unit;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Ringkasan pemasukan/pengeluaran/saldo per unit untuk satu periode. Dipakai oleh
 * laporan konsolidasi (web) dan endpoint API /api/v1/laporan/ringkasan.
 */
class LaporanRingkasanService
{
    /**
     * @return Collection<int, array{unit_id: int<0, max>, kode_unit: string, unit: string, pemasukan: float, pengeluaran: float, saldo_bersih: float, saldo_kas_bank: float}>
     */
    public function perUnit(CarbonInterface $periodeMulai, CarbonInterface $periodeSelesai): Collection
    {
        return Unit::query()
            ->get()
            ->map(function (Unit $unit) use ($periodeMulai, $periodeSelesai) {
                $query = Transaksi::query()
                    ->where('unit_id', $unit->id)
                    ->whereBetween('tanggal', [$periodeMulai, $periodeSelesai]);

                $pemasukan = (clone $query)->where('jenis', 'masuk')->sum('nominal');
                $pengeluaran = (clone $query)->where('jenis', 'keluar')->sum('nominal');

                return [
                    'unit_id' => $unit->id,
                    'kode_unit' => $unit->kode_unit,
                    'unit' => $unit->nama,
                    'pemasukan' => (float) $pemasukan,
                    'pengeluaran' => (float) $pengeluaran,
                    'saldo_bersih' => (float) $pemasukan - (float) $pengeluaran,
                    'saldo_kas_bank' => (float) KasBankAccount::query()->where('unit_id', $unit->id)->sum('saldo'),
                ];
            });
    }
}
