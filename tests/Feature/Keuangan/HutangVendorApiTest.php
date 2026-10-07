<?php

namespace Tests\Feature\Keuangan;

use App\Models\ApiClient;
use App\Models\HutangVendor;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HutangVendorApiTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private Vendor $vendor;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->vendor = Vendor::query()->create(['nama' => 'CV Sumber Makmur']);
        $this->client = ApiClient::query()->create([
            'nama' => 'Sistem Procurement',
            'is_aktif' => true,
            'abilities' => ['read:hutang-vendor'],
        ]);
    }

    public function test_token_tanpa_ability_yang_benar_ditolak(): void
    {
        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.hutang-vendor.index'))
            ->assertForbidden();
    }

    public function test_klien_aktif_dengan_ability_benar_bisa_mengambil_status_hutang(): void
    {
        HutangVendor::query()->create([
            'vendor_id' => $this->vendor->id,
            'unit_id' => $this->unit->id,
            'nomor_invoice' => 'INV-001',
            'deskripsi' => 'Pembelian ATK',
            'nominal' => 1_500_000,
            'jatuh_tempo' => '2026-10-31',
            'status' => 'belum_lunas',
        ]);

        $token = $this->client->createToken('default', ['read:hutang-vendor'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.hutang-vendor.index', ['status' => 'belum_lunas']));

        $response->assertOk();
        $response->assertJsonPath('data.0.nomor_invoice', 'INV-001');
        $response->assertJsonPath('data.0.vendor.nama', 'CV Sumber Makmur');
        $response->assertJsonPath('data.0.nominal', 1_500_000);
    }
}
