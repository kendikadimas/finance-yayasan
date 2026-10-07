<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\KasBankAccount;
use App\Models\Penggajian;
use App\Models\Transaksi;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PenggajianController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function index(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);

        return Inertia::render('keuangan/penggajian/index', [
            'penggajians' => Penggajian::query()
                ->with('unit:id,nama')
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->orderByDesc('periode')
                ->get(),
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'kasBankAccounts' => KasBankAccount::query()->when($unitId, fn ($q) => $q->where('unit_id', $unitId))->get(['id', 'nama', 'unit_id']),
            'filterUnitId' => $unitId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'kode_pegawai' => ['nullable', 'string', 'max:50'],
            'nama_pegawai' => ['required', 'string', 'max:255'],
            'periode' => ['required', 'string', 'max:20'],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'tunjangan' => ['required', 'numeric', 'min:0'],
            'potongan' => ['required', 'numeric', 'min:0'],
        ]);

        $this->authorizeUnit($request, (int) $data['unit_id']);

        Penggajian::query()->create([
            ...$data,
            'total_gaji' => $data['gaji_pokok'] + $data['tunjangan'] - $data['potongan'],
            'status' => 'draft',
        ]);
        $this->toastSuccess('Data penggajian berhasil ditambahkan.');

        return to_route('keuangan.penggajian.index');
    }

    /**
     * Hanya bisa diubah selama berstatus draft (belum dibayar).
     */
    public function update(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizeUnit($request, $penggajian->unit_id);
        abort_if($penggajian->status !== 'draft', 403, 'Data gaji yang sudah dibayar tidak bisa diubah.');

        $data = $request->validate([
            'kode_pegawai' => ['nullable', 'string', 'max:50'],
            'nama_pegawai' => ['required', 'string', 'max:255'],
            'periode' => ['required', 'string', 'max:20'],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'tunjangan' => ['required', 'numeric', 'min:0'],
            'potongan' => ['required', 'numeric', 'min:0'],
        ]);

        $penggajian->update([
            ...$data,
            'total_gaji' => $data['gaji_pokok'] + $data['tunjangan'] - $data['potongan'],
        ]);
        $this->toastSuccess('Data penggajian berhasil diperbarui.');

        return to_route('keuangan.penggajian.index');
    }

    /**
     * Bayar gaji: membuat transaksi pengeluaran dan menandai penggajian sebagai dibayar.
     */
    public function bayar(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizeUnit($request, $penggajian->unit_id);

        $data = $request->validate([
            'kas_bank_account_id' => ['required', 'exists:kas_bank_accounts,id'],
            'tanggal_bayar' => ['required', 'date'],
        ]);

        $transaksi = Transaksi::query()->create([
            'unit_id' => $penggajian->unit_id,
            'kas_bank_account_id' => $data['kas_bank_account_id'],
            'created_by' => $request->user()->id,
            'jenis' => 'keluar',
            'kategori' => 'Penggajian',
            'nominal' => $penggajian->total_gaji,
            'tanggal' => $data['tanggal_bayar'],
            'keterangan' => "Gaji {$penggajian->nama_pegawai} periode {$penggajian->periode}",
        ]);

        $penggajian->update([
            'transaksi_id' => $transaksi->id,
            'status' => 'dibayar',
            'tanggal_bayar' => $data['tanggal_bayar'],
        ]);
        $this->toastSuccess('Gaji berhasil dibayarkan.');

        return to_route('keuangan.penggajian.index');
    }

    public function destroy(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizeUnit($request, $penggajian->unit_id);

        $penggajian->delete();
        $this->toastSuccess('Data penggajian berhasil dihapus.');

        return to_route('keuangan.penggajian.index');
    }
}
