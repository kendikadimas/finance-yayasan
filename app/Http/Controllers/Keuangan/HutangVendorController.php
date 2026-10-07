<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\HutangVendor;
use App\Models\KasBankAccount;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HutangVendorController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function index(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);

        return Inertia::render('keuangan/hutang-vendor/index', [
            'hutangs' => HutangVendor::query()
                ->with(['unit:id,nama', 'vendor:id,nama'])
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->orderByDesc('jatuh_tempo')
                ->get(),
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'vendors' => Vendor::query()->orderBy('nama')->get(['id', 'nama']),
            'kasBankAccounts' => KasBankAccount::query()->when($unitId, fn ($q) => $q->where('unit_id', $unitId))->get(['id', 'nama', 'unit_id']),
            'filterUnitId' => $unitId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'nomor_invoice' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['required', 'string'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'jatuh_tempo' => ['required', 'date'],
        ]);

        $this->authorizeUnit($request, (int) $data['unit_id']);

        HutangVendor::query()->create([...$data, 'status' => 'belum_lunas']);
        $this->toastSuccess('Hutang vendor berhasil ditambahkan.');

        return to_route('keuangan.hutang-vendor.index');
    }

    /**
     * Hanya bisa diubah selama belum lunas.
     */
    public function update(Request $request, HutangVendor $hutangVendor): RedirectResponse
    {
        $this->authorizeUnit($request, $hutangVendor->unit_id);
        abort_if($hutangVendor->status !== 'belum_lunas', 403, 'Hutang yang sudah lunas tidak bisa diubah.');

        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'nomor_invoice' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['required', 'string'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'jatuh_tempo' => ['required', 'date'],
        ]);

        $hutangVendor->update($data);
        $this->toastSuccess('Hutang vendor berhasil diperbarui.');

        return to_route('keuangan.hutang-vendor.index');
    }

    public function lunas(Request $request, HutangVendor $hutangVendor): RedirectResponse
    {
        $this->authorizeUnit($request, $hutangVendor->unit_id);

        $data = $request->validate([
            'kas_bank_account_id' => ['required', 'exists:kas_bank_accounts,id'],
            'tanggal_lunas' => ['required', 'date'],
        ]);

        $transaksi = Transaksi::query()->create([
            'unit_id' => $hutangVendor->unit_id,
            'vendor_id' => $hutangVendor->vendor_id,
            'kas_bank_account_id' => $data['kas_bank_account_id'],
            'created_by' => $request->user()->id,
            'jenis' => 'keluar',
            'kategori' => 'Pembayaran Hutang Vendor',
            'nominal' => $hutangVendor->nominal,
            'tanggal' => $data['tanggal_lunas'],
            'keterangan' => "Pelunasan hutang {$hutangVendor->vendor->nama}: {$hutangVendor->deskripsi}",
        ]);

        $hutangVendor->update([
            'transaksi_id' => $transaksi->id,
            'status' => 'lunas',
            'tanggal_lunas' => $data['tanggal_lunas'],
        ]);
        $this->toastSuccess('Hutang vendor berhasil dilunasi.');

        return to_route('keuangan.hutang-vendor.index');
    }

    public function destroy(Request $request, HutangVendor $hutangVendor): RedirectResponse
    {
        $this->authorizeUnit($request, $hutangVendor->unit_id);

        $hutangVendor->delete();
        $this->toastSuccess('Hutang vendor berhasil dihapus.');

        return to_route('keuangan.hutang-vendor.index');
    }
}
