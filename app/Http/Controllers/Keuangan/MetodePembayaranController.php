<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MetodePembayaranController extends Controller
{
    use FlashesToast;

    public function index(): Response
    {
        return Inertia::render('keuangan/metode-pembayaran/index', [
            'metodePembayarans' => MetodePembayaran::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'in:manual,payment_gateway'],
            'aktif' => ['boolean'],
        ]);

        MetodePembayaran::query()->create($data);
        $this->toastSuccess('Metode pembayaran berhasil ditambahkan.');

        return to_route('keuangan.metode-pembayaran.index');
    }

    public function update(Request $request, MetodePembayaran $metodePembayaran): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'in:manual,payment_gateway'],
            'aktif' => ['boolean'],
        ]);

        $metodePembayaran->update($data);
        $this->toastSuccess('Metode pembayaran berhasil diperbarui.');

        return to_route('keuangan.metode-pembayaran.index');
    }

    public function destroy(MetodePembayaran $metodePembayaran): RedirectResponse
    {
        $metodePembayaran->delete();
        $this->toastSuccess('Metode pembayaran berhasil dihapus.');

        return to_route('keuangan.metode-pembayaran.index');
    }
}
