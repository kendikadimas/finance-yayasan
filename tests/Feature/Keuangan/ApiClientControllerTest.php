<?php

namespace Tests\Feature\Keuangan;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiClientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->role(User::ROLE_ADMIN)->create();
    }

    public function test_admin_bisa_membuat_klien_api_dengan_ability_terpilih(): void
    {
        $response = $this->actingAs($this->admin)->post(route('keuangan.api-clients.store'), [
            'nama' => 'Sistem PPDB',
            'deskripsi' => 'Verifikasi uang pangkal saat daftar ulang',
            'abilities' => ['read:tagihan-siswa', 'read:pembayaran-spp'],
        ]);

        $response->assertRedirect();
        $client = ApiClient::query()->where('nama', 'Sistem PPDB')->firstOrFail();
        $this->assertSame(['read:tagihan-siswa', 'read:pembayaran-spp'], $client->abilities);
        $this->assertSame(1, $client->tokens()->count());
        $this->assertEqualsCanonicalizing(['read:tagihan-siswa', 'read:pembayaran-spp'], $client->tokens()->first()->abilities);
    }

    public function test_membuat_klien_tanpa_ability_ditolak_validasi(): void
    {
        $response = $this->actingAs($this->admin)->post(route('keuangan.api-clients.store'), [
            'nama' => 'Sistem Tanpa Ability',
            'abilities' => [],
        ]);

        $response->assertSessionHasErrors('abilities');
        $this->assertDatabaseMissing('api_clients', ['nama' => 'Sistem Tanpa Ability']);
    }

    public function test_regenerate_token_memakai_ability_klien_saat_ini(): void
    {
        $client = ApiClient::query()->create([
            'nama' => 'Sistem Boarding',
            'is_aktif' => true,
            'abilities' => ['read:tagihan-siswa'],
        ]);

        $this->actingAs($this->admin)->put(route('keuangan.api-clients.update', $client), [
            'nama' => 'Sistem Boarding',
            'abilities' => ['read:tagihan-siswa', 'read:hutang-vendor'],
        ])->assertRedirect();

        $client->refresh();
        $this->assertSame(['read:tagihan-siswa', 'read:hutang-vendor'], $client->abilities);

        $this->actingAs($this->admin)->post(route('keuangan.api-clients.regenerate', $client));

        $this->assertEqualsCanonicalizing(
            ['read:tagihan-siswa', 'read:hutang-vendor'],
            $client->tokens()->first()->abilities,
        );
    }
}
