<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\KasBankAccount;
use App\Models\MetodePembayaran;
use App\Models\TagihanSiswa;
use App\Models\Transaksi;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagihanSiswaController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function index(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);

        return Inertia::render('keuangan/tagihan-siswa/index', [
            'tagihans' => TagihanSiswa::query()
                ->with('unit:id,nama')
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->orderByDesc('jatuh_tempo')
                ->get(),
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'kasBankAccounts' => KasBankAccount::query()->when($unitId, fn ($q) => $q->where('unit_id', $unitId))->get(['id', 'nama', 'unit_id']),
            'metodePembayarans' => MetodePembayaran::query()->where('aktif', true)->get(['id', 'nama']),
            'filterUnitId' => $unitId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'kode_siswa' => ['required', 'string', 'max:50'],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'jenis_tagihan' => ['required', 'in:spp,uang_pangkal,boarding_fee,kegiatan'],
            'periode' => ['required', 'string', 'max:50'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'jatuh_tempo' => ['required', 'date'],
        ]);

        $this->authorizeUnit($request, (int) $data['unit_id']);

        TagihanSiswa::query()->create([
            ...$data,
            'sisa_tagihan' => $data['nominal'],
            'status' => 'belum_bayar',
        ]);
        $this->toastSuccess('Tagihan siswa berhasil ditambahkan.');

        return to_route('keuangan.tagihan-siswa.index');
    }

    /**
     * Field deskriptif selalu bisa diubah. Nominal hanya bisa diubah selama belum ada
     * pembayaran (status masih belum_bayar) agar tidak merusak sisa_tagihan yang sudah berjalan.
     */
    public function update(Request $request, TagihanSiswa $tagihanSiswa): RedirectResponse
    {
        $this->authorizeUnit($request, $tagihanSiswa->unit_id);

        $data = $request->validate([
            'kode_siswa' => ['required', 'string', 'max:50'],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'jenis_tagihan' => ['required', 'in:spp,uang_pangkal,boarding_fee,kegiatan'],
            'periode' => ['required', 'string', 'max:50'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'jatuh_tempo' => ['required', 'date'],
        ]);

        if ($tagihanSiswa->status === 'belum_bayar') {
            $data['sisa_tagihan'] = $data['nominal'];
        } else {
            unset($data['nominal']);
        }

        $tagihanSiswa->update($data);
        $this->toastSuccess('Tagihan siswa berhasil diperbarui.');

        return to_route('keuangan.tagihan-siswa.index');
    }

    /**
     * Catat pembayaran tagihan siswa: membuat transaksi pemasukan dan mengurangi sisa tagihan
     * (ditangani otomatis oleh TransaksiObserver).
     */
    public function bayar(Request $request, TagihanSiswa $tagihanSiswa): RedirectResponse
    {
        $this->authorizeUnit($request, $tagihanSiswa->unit_id);

        $data = $request->validate([
            'nominal' => ['required', 'numeric', 'min:0.01', 'max:'.$tagihanSiswa->sisa_tagihan],
            'kas_bank_account_id' => ['required', 'exists:kas_bank_accounts,id'],
            'metode_pembayaran_id' => ['nullable', 'exists:metode_pembayarans,id'],
            'tanggal' => ['required', 'date'],
        ]);

        Transaksi::query()->create([
            'unit_id' => $tagihanSiswa->unit_id,
            'tagihan_siswa_id' => $tagihanSiswa->id,
            'kas_bank_account_id' => $data['kas_bank_account_id'],
            'metode_pembayaran_id' => $data['metode_pembayaran_id'] ?? null,
            'created_by' => $request->user()->id,
            'jenis' => 'masuk',
            'kategori' => 'Tagihan Siswa - '.str($tagihanSiswa->jenis_tagihan)->replace('_', ' ')->title(),
            'nominal' => $data['nominal'],
            'tanggal' => $data['tanggal'],
            'keterangan' => "Pembayaran {$tagihanSiswa->jenis_tagihan} - {$tagihanSiswa->nama_siswa} ({$tagihanSiswa->kode_siswa})",
        ]);
        $this->toastSuccess('Pembayaran berhasil dicatat.');

        return to_route('keuangan.tagihan-siswa.index');
    }

    public function destroy(Request $request, TagihanSiswa $tagihanSiswa): RedirectResponse
    {
        $this->authorizeUnit($request, $tagihanSiswa->unit_id);

        $tagihanSiswa->delete();
        $this->toastSuccess('Tagihan siswa berhasil dihapus.');

        return to_route('keuangan.tagihan-siswa.index');
    }
}
