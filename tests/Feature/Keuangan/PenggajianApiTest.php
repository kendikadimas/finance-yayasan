<?php

namespace Tests\Feature\Keuangan;

use App\Models\ApiClient;
use App\Models\Penggajian;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggajianApiTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->client = ApiClient::query()->create([
            'nama' => 'Sistem Employee App',
            'is_aktif' => true,
            'abilities' => ['read:penggajian'],
        ]);
    }

    public function test_token_tanpa_ability_yang_benar_ditolak(): void
    {
        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.penggajian.index'))
            ->assertForbidden();
    }

    public function test_klien_aktif_dengan_ability_benar_bisa_mengambil_status_gaji(): void
    {
        Penggajian::query()->create([
            'unit_id' => $this->unit->id,
            'kode_pegawai' => 'P-001',
            'nama_pegawai' => 'Budi',
            'periode' => '2026-10',
            'gaji_pokok' => 5_000_000,
            'tunjangan' => 500_000,
            'potongan' => 100_000,
            'total_gaji' => 5_400_000,
            'status' => 'dibayar',
            'tanggal_bayar' => '2026-10-28',
        ]);

        $token = $this->client->createToken('default', ['read:penggajian'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.penggajian.index', ['kode_pegawai' => 'P-001']));

        $response->assertOk();
        $response->assertJsonPath('data.0.nama_pegawai', 'Budi');
        $response->assertJsonPath('data.0.total_gaji', 5_400_000);
        $response->assertJsonPath('data.0.status', 'dibayar');
    }
}
