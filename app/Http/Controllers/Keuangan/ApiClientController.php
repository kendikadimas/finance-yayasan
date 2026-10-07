<?php

namespace App\Http\Controllers\Keuangan;

use App\Enums\ApiAbility;
use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ApiClientController extends Controller
{
    use FlashesToast;

    public function index(): Response
    {
        return Inertia::render('keuangan/api-clients/index', [
            'clients' => ApiClient::query()->orderByDesc('created_at')->get(),
            'abilityOptions' => ApiAbility::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateClient($request);

        $client = ApiClient::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        $token = $client->createToken('default', $client->abilities);

        Inertia::flash('apiToken', $token->plainTextToken);
        $this->toastSuccess('Klien API berhasil dibuat. Salin token sekarang, token tidak akan ditampilkan lagi.');

        return to_route('keuangan.api-clients.index');
    }

    /**
     * Ubah nama/deskripsi/ability klien. Token yang sudah terbit tidak otomatis ikut
     * berubah abilitynya — admin perlu klik "Terbitkan Ulang" setelahnya kalau ability
     * berubah, karena ability token Sanctum terkunci saat token itu dibuat.
     */
    public function update(Request $request, ApiClient $apiClient): RedirectResponse
    {
        $apiClient->update($this->validateClient($request));
        $this->toastSuccess('Klien API berhasil diperbarui. Terbitkan ulang token agar perubahan ability berlaku.');

        return to_route('keuangan.api-clients.index');
    }

    /**
     * Cabut token lama, terbitkan token baru dengan ability klien saat ini (mis. token
     * lama bocor, atau ability baru saja diubah lewat update()).
     */
    public function regenerateToken(ApiClient $apiClient): RedirectResponse
    {
        $apiClient->tokens()->delete();
        $token = $apiClient->createToken('default', $apiClient->abilities);

        Inertia::flash('apiToken', $token->plainTextToken);
        $this->toastSuccess('Token baru berhasil diterbitkan. Token lama sudah tidak berlaku.');

        return to_route('keuangan.api-clients.index');
    }

    /** @return array{nama: string, deskripsi: string|null, abilities: list<string>} */
    private function validateClient(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => [Rule::in(ApiAbility::values())],
        ]);
    }

    public function toggleActive(ApiClient $apiClient): RedirectResponse
    {
        $apiClient->update(['is_aktif' => ! $apiClient->is_aktif]);
        $this->toastSuccess($apiClient->is_aktif ? 'Klien API diaktifkan.' : 'Klien API dinonaktifkan.');

        return to_route('keuangan.api-clients.index');
    }

    public function destroy(ApiClient $apiClient): RedirectResponse
    {
        $apiClient->tokens()->delete();
        $apiClient->delete();
        $this->toastSuccess('Klien API berhasil dihapus.');

        return to_route('keuangan.api-clients.index');
    }
}
