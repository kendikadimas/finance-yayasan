<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RabMenungguPersetujuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnggaranController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function index(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);

        $anggarans = Anggaran::query()
            ->with('unit:id,nama')
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->when($request->string('jenis')->toString(), fn ($q, $jenis) => $q->where('jenis', $jenis))
            ->orderByDesc('periode_mulai')
            ->get()
            ->map(fn (Anggaran $a) => [
                ...$a->toArray(),
                'realisasi' => $a->realisasi(),
            ]);

        return Inertia::render('keuangan/anggaran/index', [
            'anggarans' => $anggarans,
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'filterUnitId' => $unitId,
            'filterJenis' => $request->string('jenis')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Anggaran::class);

        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'jenis' => ['required', 'in:pemasukan,pengeluaran'],
            'kategori' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after:periode_mulai'],
            'catatan' => ['nullable', 'string'],
        ]);

        $this->authorizeUnit($request, (int) $data['unit_id']);

        Anggaran::query()->create([...$data, 'status' => 'draft']);
        $this->toastSuccess('RAB berhasil ditambahkan sebagai draft.');

        return to_route('keuangan.anggaran.index');
    }

    public function update(Request $request, Anggaran $anggaran): RedirectResponse
    {
        $this->authorize('update', $anggaran);

        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after:periode_mulai'],
            'catatan' => ['nullable', 'string'],
        ]);

        $anggaran->update($data);
        $this->toastSuccess('RAB berhasil diperbarui.');

        return to_route('keuangan.anggaran.index');
    }

    /**
     * Ajukan RAB draft ke Pimpinan Yayasan untuk disetujui.
     */
    public function ajukan(Request $request, Anggaran $anggaran): RedirectResponse
    {
        $this->authorize('ajukan', $anggaran);

        $anggaran->update(['status' => 'diajukan']);

        User::query()->where('role', User::ROLE_PIMPINAN_YAYASAN)->get()
            ->each(fn (User $pimpinan) => $pimpinan->notify(new RabMenungguPersetujuan($anggaran)));
        $this->toastSuccess('RAB berhasil diajukan untuk persetujuan.');

        return to_route('keuangan.anggaran.index');
    }

    public function approve(Request $request, Anggaran $anggaran): RedirectResponse
    {
        $this->authorize('approve', $anggaran);

        $anggaran->update([
            'status' => 'disetujui',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        $this->toastSuccess('RAB berhasil disetujui.');

        return to_route('keuangan.anggaran.index');
    }

    public function reject(Request $request, Anggaran $anggaran): RedirectResponse
    {
        $this->authorize('approve', $anggaran);

        $anggaran->update([
            'status' => 'ditolak',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        $this->toastSuccess('RAB ditolak.');

        return to_route('keuangan.anggaran.index');
    }

    public function destroy(Anggaran $anggaran): RedirectResponse
    {
        $this->authorize('delete', $anggaran);

        $anggaran->delete();
        $this->toastSuccess('RAB berhasil dihapus.');

        return to_route('keuangan.anggaran.index');
    }
}
