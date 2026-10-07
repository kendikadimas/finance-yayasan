<?php

namespace App\Observers;

use App\Models\BukuBesar;
use App\Models\Transaksi;
use App\Services\EwsService;

class TransaksiObserver
{
    public function __construct(private readonly EwsService $ewsService) {}

    /**
     * Setiap transaksi disimpan: update saldo kas/bank, catat jurnal buku besar (double entry),
     * perbarui sisa tagihan siswa, dan evaluasi ulang status EWS RAB terkait.
     */
    public function created(Transaksi $transaksi): void
    {
        $this->perbaruiSaldoKasBank($transaksi, arah: 1);
        $this->catatBukuBesar($transaksi);
        $this->perbaruiTagihanSiswa($transaksi);

        if ($transaksi->anggaran_id && $transaksi->jenis === 'keluar') {
            $this->ewsService->evaluasi($transaksi->anggaran);
        }
    }

    public function deleted(Transaksi $transaksi): void
    {
        $this->perbaruiSaldoKasBank($transaksi, arah: -1);
        $transaksi->bukuBesars()->delete();

        if ($transaksi->anggaran_id && $transaksi->jenis === 'keluar') {
            $this->ewsService->evaluasi($transaksi->anggaran);
        }
    }

    private function perbaruiSaldoKasBank(Transaksi $transaksi, int $arah): void
    {
        if (! $transaksi->kas_bank_account_id) {
            return;
        }

        $pengaruh = $transaksi->jenis === 'masuk' ? $transaksi->nominal : -$transaksi->nominal;

        $transaksi->kasBankAccount()->increment('saldo', $arah * $pengaruh);
    }

    private function catatBukuBesar(Transaksi $transaksi): void
    {
        $akunKasBank = $transaksi->kasBankAccount?->nama ?? 'Kas/Bank';

        if ($transaksi->jenis === 'masuk') {
            $debitAkun = $akunKasBank;
            $kreditAkun = "Pendapatan - {$transaksi->kategori}";
        } else {
            $debitAkun = "Beban - {$transaksi->kategori}";
            $kreditAkun = $akunKasBank;
        }

        BukuBesar::query()->create([
            'transaksi_id' => $transaksi->id,
            'unit_id' => $transaksi->unit_id,
            'tanggal' => $transaksi->tanggal,
            'akun' => $debitAkun,
            'debit' => $transaksi->nominal,
            'kredit' => 0,
        ]);

        BukuBesar::query()->create([
            'transaksi_id' => $transaksi->id,
            'unit_id' => $transaksi->unit_id,
            'tanggal' => $transaksi->tanggal,
            'akun' => $kreditAkun,
            'debit' => 0,
            'kredit' => $transaksi->nominal,
        ]);
    }

    private function perbaruiTagihanSiswa(Transaksi $transaksi): void
    {
        if (! $transaksi->tagihan_siswa_id || $transaksi->jenis !== 'masuk') {
            return;
        }

        $tagihan = $transaksi->tagihanSiswa;
        $sisa = max(0, (float) $tagihan->sisa_tagihan - (float) $transaksi->nominal);

        $tagihan->forceFill([
            'sisa_tagihan' => $sisa,
            'status' => $sisa <= 0 ? 'lunas' : 'sebagian',
        ])->save();
    }
}
