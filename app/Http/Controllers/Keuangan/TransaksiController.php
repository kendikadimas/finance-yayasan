<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use App\Models\KasBankAccount;
use App\Models\MetodePembayaran;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransaksiController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function index(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);
        $jenis = $request->string('jenis')->toString();

        $transaksis = Transaksi::query()
            ->with(['unit:id,nama', 'kasBankAccount:id,nama', 'vendor:id,nama', 'anggaran:id,kategori'])
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return Inertia::render('keuangan/transaksi/index', [
            'transaksis' => $transaksis,
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'vendors' => Vendor::query()->orderBy('nama')->get(['id', 'nama']),
            'kasBankAccounts' => KasBankAccount::query()->when($unitId, fn ($q) => $q->where('unit_id', $unitId))->get(['id', 'nama', 'unit_id']),
            'metodePembayarans' => MetodePembayaran::query()->where('aktif', true)->get(['id', 'nama']),
            'anggarans' => Anggaran::query()
                ->where('status', 'disetujui')
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->get(['id', 'kategori', 'jenis', 'unit_id']),
            'filterUnitId' => $unitId,
            'filterJenis' => $jenis,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'anggaran_id' => ['nullable', 'exists:anggarans,id'],
            'kas_bank_account_id' => ['nullable', 'exists:kas_bank_accounts,id'],
            'metode_pembayaran_id' => ['nullable', 'exists:metode_pembayarans,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'jenis' => ['required', 'in:masuk,keluar'],
            'kategori' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $this->authorizeUnit($request, (int) $data['unit_id']);

        Transaksi::query()->create([...$data, 'created_by' => $request->user()->id]);
        $this->toastSuccess('Transaksi berhasil dicatat.');

        return to_route('keuangan.transaksi.index');
    }

    public function destroy(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->authorizeUnit($request, $transaksi->unit_id);

        $transaksi->delete();
        $this->toastSuccess('Transaksi berhasil dihapus.');

        return to_route('keuangan.transaksi.index');
    }
}
