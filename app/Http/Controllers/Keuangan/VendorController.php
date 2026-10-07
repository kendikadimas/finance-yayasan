<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    use FlashesToast;

    public function index(): Response
    {
        return Inertia::render('keuangan/vendors/index', [
            'vendors' => Vendor::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:255'],
        ]);

        Vendor::query()->create($data);
        $this->toastSuccess('Vendor berhasil ditambahkan.');

        return to_route('keuangan.vendors.index');
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:255'],
        ]);

        $vendor->update($data);
        $this->toastSuccess('Vendor berhasil diperbarui.');

        return to_route('keuangan.vendors.index');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $vendor->delete();
        $this->toastSuccess('Vendor berhasil dihapus.');

        return to_route('keuangan.vendors.index');
    }
}
