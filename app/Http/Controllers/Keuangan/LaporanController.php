<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use App\Models\BukuBesar;
use App\Models\KasBankAccount;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Services\EwsService;
use App\Services\LaporanRingkasanService;
use App\Services\SesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function __construct(
        private readonly EwsService $ewsService,
        private readonly SesService $sesService,
        private readonly LaporanRingkasanService $ringkasanService,
    ) {}

    /**
     * F-RPT-03: Dashboard status EWS seluruh unit dan kategori.
     */
    public function dashboardEws(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);

        $anggarans = Anggaran::query()
            ->with('unit:id,nama')
            ->where('jenis', 'pengeluaran')
            ->where('status', 'disetujui')
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->get()
            ->map(fn (Anggaran $a) => [
                'id' => $a->id,
                'unit' => $a->unit->nama,
                'unit_id' => $a->unit_id,
                'kategori' => $a->kategori,
                'pagu' => (float) $a->pagu,
                'realisasi' => $a->realisasi(),
                'rasio_serapan' => round($this->ewsService->hitungRasioSerapan($a), 2),
                'status_ews' => $a->status_ews,
                'status_label' => EwsService::label($a->status_ews),
            ]);

        return Inertia::render('keuangan/laporan/dashboard-ews', [
            'anggarans' => $anggarans->values(),
            'ringkasan' => $anggarans->countBy('status_ews'),
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'filterUnitId' => $unitId,
        ]);
    }

    /**
     * F-RPT-01: Laporan keuangan per unit (pemasukan, pengeluaran, buku besar).
     */
    public function perUnit(Request $request, Unit $unit): Response
    {
        $this->authorizeUnit($request, $unit->id);

        $periodeMulai = $request->date('dari') ?? now()->startOfYear();
        $periodeSelesai = $request->date('sampai') ?? now();

        $transaksiQuery = Transaksi::query()
            ->where('unit_id', $unit->id)
            ->whereBetween('tanggal', [$periodeMulai, $periodeSelesai]);

        $totalPemasukan = (clone $transaksiQuery)->where('jenis', 'masuk')->sum('nominal');
        $totalPengeluaran = (clone $transaksiQuery)->where('jenis', 'keluar')->sum('nominal');

        $perKategori = (clone $transaksiQuery)
            ->selectRaw('jenis, kategori, SUM(nominal) as total')
            ->groupBy('jenis', 'kategori')
            ->orderByDesc('total')
            ->get();

        $bukuBesar = BukuBesar::query()
            ->where('unit_id', $unit->id)
            ->whereBetween('tanggal', [$periodeMulai, $periodeSelesai])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        return Inertia::render('keuangan/laporan/per-unit', [
            'unit' => $unit,
            'totalPemasukan' => (float) $totalPemasukan,
            'totalPengeluaran' => (float) $totalPengeluaran,
            'saldoBersih' => (float) $totalPemasukan - (float) $totalPengeluaran,
            'perKategori' => $perKategori,
            'bukuBesar' => $bukuBesar,
            'saldoKasBank' => KasBankAccount::query()->where('unit_id', $unit->id)->get(['id', 'nama', 'jenis', 'saldo']),
            'periode' => ['dari' => $periodeMulai->format('Y-m-d'), 'sampai' => $periodeSelesai->format('Y-m-d')],
        ]);
    }

    /**
     * F-RPT-02: Laporan konsolidasi lintas unit untuk pimpinan yayasan.
     */
    public function konsolidasi(Request $request): Response
    {
        $periodeMulai = $request->date('dari') ?? now()->startOfYear();
        $periodeSelesai = $request->date('sampai') ?? now();

        $perUnit = $this->ringkasanService->perUnit($periodeMulai, $periodeSelesai);

        return Inertia::render('keuangan/laporan/konsolidasi', [
            'perUnit' => $perUnit,
            'totalPemasukan' => $perUnit->sum('pemasukan'),
            'totalPengeluaran' => $perUnit->sum('pengeluaran'),
            'totalSaldoKasBank' => $perUnit->sum('saldo_kas_bank'),
            'periode' => ['dari' => $periodeMulai->format('Y-m-d'), 'sampai' => $periodeSelesai->format('Y-m-d')],
        ]);
    }

    /**
     * Proyeksi Anggaran (SES): tampilkan deret historis & hasil proyeksi untuk unit + kategori.
     */
    public function proyeksiSes(Request $request, Unit $unit): Response
    {
        $this->authorizeUnit($request, $unit->id);

        $kategori = $request->string('kategori')->toString();

        $kategoriTersedia = Transaksi::query()
            ->where('unit_id', $unit->id)
            ->where('jenis', 'keluar')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $kategori = $kategori ?: $kategoriTersedia->first();

        $deretHistoris = $kategori ? $this->sesService->deretHistoris($unit->id, $kategori) : [];
        $hasil = $kategori ? $this->sesService->proyeksikan($unit->id, $kategori) : null;

        return Inertia::render('keuangan/laporan/proyeksi-ses', [
            'unit' => $unit,
            'kategoriTersedia' => $kategoriTersedia,
            'kategori' => $kategori,
            'deretHistoris' => $deretHistoris,
            'hasil' => $hasil,
            'cukupData' => count($deretHistoris) >= SesService::MIN_BULAN_HISTORIS,
            'minBulan' => SesService::MIN_BULAN_HISTORIS,
        ]);
    }

    public function simpanProyeksiSes(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorizeUnit($request, $unit->id);

        $data = $request->validate(['kategori' => ['required', 'string']]);

        $this->sesService->proyeksikanDanSimpan($unit->id, $data['kategori']);
        $this->toastSuccess('Proyeksi SES berhasil disimpan.');

        return back();
    }
}
