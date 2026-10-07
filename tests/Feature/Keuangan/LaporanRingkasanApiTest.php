<?php

namespace Tests\Feature\Keuangan;

use App\Models\ApiClient;
use App\Models\KasBankAccount;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanRingkasanApiTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->client = ApiClient::query()->create([
            'nama' => 'Executive Dashboard',
            'is_aktif' => true,
            'abilities' => ['read:laporan-ringkasan'],
        ]);
    }

    public function test_token_tanpa_ability_yang_benar_ditolak(): void
    {
        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.laporan.ringkasan'))
            ->assertForbidden();
    }

    public function test_klien_aktif_dengan_ability_benar_bisa_mengambil_ringkasan(): void
    {
        $bank = KasBankAccount::query()->create(['unit_id' => $this->unit->id, 'nama' => 'Bank', 'jenis' => 'bank', 'saldo' => 0]);
        $pegawai = User::factory()->create();

        Transaksi::query()->create([
            'unit_id' => $this->unit->id,
            'kas_bank_account_id' => $bank->id,
            'created_by' => $pegawai->id,
            'jenis' => 'masuk',
            'kategori' => 'SPP',
            'nominal' => 2_000_000,
            'tanggal' => '2026-10-05',
        ]);

        $token = $this->client->createToken('default', ['read:laporan-ringkasan'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.laporan.ringkasan', ['dari' => '2026-01-01', 'sampai' => '2026-12-31']));

        $response->assertOk();
        $response->assertJsonPath('data.0.kode_unit', 'U1');
        $response->assertJsonPath('data.0.pemasukan', 2_000_000);
        $response->assertJsonPath('meta.total_pemasukan', 2_000_000);
    }
}
