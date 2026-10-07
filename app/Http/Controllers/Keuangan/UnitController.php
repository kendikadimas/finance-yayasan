<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    use FlashesToast;

    public function index(): Response
    {
        return Inertia::render('keuangan/units/index', [
            'units' => Unit::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_unit' => ['required', 'string', 'max:50', 'unique:units,kode_unit'],
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'string', 'max:100'],
            'jumlah_siswa' => ['required', 'integer', 'min:0'],
        ]);

        Unit::query()->create($data);
        $this->toastSuccess('Unit berhasil ditambahkan.');

        return to_route('keuangan.units.index');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'kode_unit' => ['required', 'string', 'max:50', 'unique:units,kode_unit,'.$unit->id],
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'string', 'max:100'],
            'jumlah_siswa' => ['required', 'integer', 'min:0'],
        ]);

        $unit->update($data);
        $this->toastSuccess('Unit berhasil diperbarui.');

        return to_route('keuangan.units.index');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();
        $this->toastSuccess('Unit berhasil dihapus.');

        return to_route('keuangan.units.index');
    }
}
