<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use FlashesToast;

    public function index(): Response
    {
        return Inertia::render('keuangan/users/index', [
            'users' => User::query()->with('unit:id,nama')->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('nama')->get(['id', 'nama']),
            'roles' => [
                User::ROLE_ADMIN, User::ROLE_PIMPINAN_YAYASAN, User::ROLE_BENDAHARA_YAYASAN, User::ROLE_BENDAHARA_UNIT,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,pimpinan_yayasan,bendahara_yayasan,bendahara_unit'],
            'unit_id' => ['nullable', 'exists:units,id', 'required_if:role,bendahara_unit'],
        ]);

        User::query()->create([
            ...$data,
            'password' => Hash::make($data['password']),
            // Akun dibuat & divouch langsung oleh Admin, bukan lewat pendaftaran publik,
            // sehingga tidak perlu alur verifikasi email terpisah.
            'email_verified_at' => now(),
        ]);
        $this->toastSuccess('Pengguna berhasil ditambahkan.');

        return to_route('keuangan.users.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,pimpinan_yayasan,bendahara_yayasan,bendahara_unit'],
            'unit_id' => ['nullable', 'exists:units,id', 'required_if:role,bendahara_unit'],
        ]);

        $user->update($data);
        $this->toastSuccess('Pengguna berhasil diperbarui.');

        return to_route('keuangan.users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak dapat menghapus akun sendiri.');

        $user->delete();
        $this->toastSuccess('Pengguna berhasil dihapus.');

        return to_route('keuangan.users.index');
    }
}
