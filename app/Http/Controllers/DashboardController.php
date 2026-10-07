<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\KasBankAccount;
use App\Models\TagihanSiswa;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('dashboard', [
            'summary' => $user->canViewAllUnits() ? $this->summaryYayasan($user) : $this->summaryUnit($user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryYayasan(User $user): array
    {
        $bulanIni = now()->startOfMonth();

        return [
            'scope' => 'yayasan',
            'totalUnit' => Unit::query()->count(),
            'totalPemasukanBulanIni' => (float) Transaksi::query()->where('jenis', 'masuk')->where('tanggal', '>=', $bulanIni)->sum('nominal'),
            'totalPengeluaranBulanIni' => (float) Transaksi::query()->where('jenis', 'keluar')->where('tanggal', '>=', $bulanIni)->sum('nominal'),
            'totalSaldoKasBank' => (float) KasBankAccount::query()->sum('saldo'),
            'rabMenungguPersetujuan' => Anggaran::query()->where('status', 'diajukan')->count(),
            'unitKritisAtauMelebihi' => Anggaran::query()
                ->where('jenis', 'pengeluaran')
                ->where('status', 'disetujui')
                ->whereIn('status_ews', ['kritis', 'melebihi_anggaran'])
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryUnit(User $user): array
    {
        $bulanIni = now()->startOfMonth();
        $unitId = $user->unit_id;

        return [
            'scope' => 'unit',
            'unit' => $unitId ? Unit::query()->find($unitId)?->nama : null,
            'totalPemasukanBulanIni' => (float) Transaksi::query()->where('unit_id', $unitId)->where('jenis', 'masuk')->where('tanggal', '>=', $bulanIni)->sum('nominal'),
            'totalPengeluaranBulanIni' => (float) Transaksi::query()->where('unit_id', $unitId)->where('jenis', 'keluar')->where('tanggal', '>=', $bulanIni)->sum('nominal'),
            'totalSaldoKasBank' => (float) KasBankAccount::query()->where('unit_id', $unitId)->sum('saldo'),
            'tagihanBelumLunas' => TagihanSiswa::query()->where('unit_id', $unitId)->where('status', '!=', 'lunas')->count(),
            'rabDraft' => Anggaran::query()->where('unit_id', $unitId)->where('status', 'draft')->count(),
            'unitKritisAtauMelebihi' => Anggaran::query()
                ->where('unit_id', $unitId)
                ->where('jenis', 'pengeluaran')
                ->where('status', 'disetujui')
                ->whereIn('status_ews', ['kritis', 'melebihi_anggaran'])
                ->count(),
        ];
    }
}
