<?php

namespace Tests\Feature\Keuangan;

use App\Models\ApiClient;
use App\Models\TagihanSiswa;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanSiswaApiTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->client = ApiClient::query()->create([
            'nama' => 'Sistem PPDB',
            'is_aktif' => true,
            'abilities' => ['read:tagihan-siswa'],
        ]);
    }

    public function test_token_tanpa_ability_yang_benar_ditolak(): void
    {
        TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'uang_pangkal',
            'periode' => '2026-07',
            'nominal' => 3_000_000,
            'sisa_tagihan' => 3_000_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-07-31',
        ]);

        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.tagihan-siswa.index'))
            ->assertForbidden();
    }

    public function test_klien_aktif_dengan_ability_benar_bisa_mengambil_status_tagihan(): void
    {
        TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'uang_pangkal',
            'periode' => '2026-07',
            'nominal' => 3_000_000,
            'sisa_tagihan' => 3_000_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-07-31',
        ]);

        $token = $this->client->createToken('default', ['read:tagihan-siswa'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.tagihan-siswa.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.jenis_tagihan', 'uang_pangkal');
        $response->assertJsonPath('data.0.status', 'belum_bayar');
        $response->assertJsonPath('data.0.sisa_tagihan', 3_000_000);
    }

    public function test_filter_jenis_tagihan_dan_status_bekerja(): void
    {
        TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-10',
            'nominal' => 500_000,
            'sisa_tagihan' => 0,
            'status' => 'lunas',
            'jatuh_tempo' => '2026-10-31',
        ]);
        TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'boarding_fee',
            'periode' => '2026-10',
            'nominal' => 1_000_000,
            'sisa_tagihan' => 1_000_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-10-31',
        ]);

        $token = $this->client->createToken('default', ['read:tagihan-siswa'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.tagihan-siswa.index', ['jenis_tagihan' => 'boarding_fee']));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.jenis_tagihan', 'boarding_fee');
    }
}
