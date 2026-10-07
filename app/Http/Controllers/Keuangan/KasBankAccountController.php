<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Concerns\ScopesToUnit;
use App\Http\Controllers\Controller;
use App\Models\KasBankAccount;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KasBankAccountController extends Controller
{
    use FlashesToast, ScopesToUnit;

    public function index(Request $request): Response
    {
        $unitId = $this->resolveUnitId($request);

        return Inertia::render('keuangan/kas-bank/index', [
            'accounts' => KasBankAccount::query()
                ->with('unit:id,nama')
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->orderBy('nama')
                ->get(),
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'filterUnitId' => $unitId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:kas,bank'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'saldo' => ['required', 'numeric', 'min:0'],
        ]);

        $this->authorizeUnit($request, (int) $data['unit_id']);

        KasBankAccount::query()->create($data);
        $this->toastSuccess('Akun kas/bank berhasil ditambahkan.');

        return to_route('keuangan.kas-bank.index');
    }

    public function update(Request $request, KasBankAccount $kasBankAccount): RedirectResponse
    {
        $this->authorizeUnit($request, $kasBankAccount->unit_id);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:kas,bank'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
        ]);

        $kasBankAccount->update($data);
        $this->toastSuccess('Akun kas/bank berhasil diperbarui.');

        return to_route('keuangan.kas-bank.index');
    }

    public function destroy(Request $request, KasBankAccount $kasBankAccount): RedirectResponse
    {
        $this->authorizeUnit($request, $kasBankAccount->unit_id);

        $kasBankAccount->delete();
        $this->toastSuccess('Akun kas/bank berhasil dihapus.');

        return to_route('keuangan.kas-bank.index');
    }
}
