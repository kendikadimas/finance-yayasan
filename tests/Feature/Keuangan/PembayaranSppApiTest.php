<?php

namespace Tests\Feature\Keuangan;

use App\Models\ApiClient;
use App\Models\TagihanSiswa;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranSppApiTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private ApiClient $client;

    private User $bendahara;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->bendahara = User::factory()->role(User::ROLE_BENDAHARA_UNIT, $this->unit->id)->create();
        $this->client = ApiClient::query()->create([
            'nama' => 'Sistem PPDB',
            'is_aktif' => true,
            'abilities' => ['read:pembayaran-spp'],
        ]);
    }

    private function buatTagihanDanTransaksi(array $overrides = []): Transaksi
    {
        $tagihan = TagihanSiswa::query()->create(array_merge([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-10',
            'nominal' => 500_000,
            'sisa_tagihan' => 500_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-10-31',
        ], $overrides));

        return Transaksi::query()->create([
            'unit_id' => $this->unit->id,
            'tagihan_siswa_id' => $tagihan->id,
            'created_by' => $this->bendahara->id,
            'jenis' => 'masuk',
            'kategori' => 'Tagihan Siswa - Spp',
            'nominal' => 200_000,
            'tanggal' => '2026-10-05',
            'keterangan' => 'Pembayaran spp - Ahmad Fajar (S-0001)',
        ]);
    }

    public function test_request_tanpa_token_ditolak(): void
    {
        $this->buatTagihanDanTransaksi();

        $this->getJson(route('api.v1.pembayaran-spp.index'))->assertUnauthorized();
    }

    public function test_token_tanpa_ability_yang_benar_ditolak(): void
    {
        $this->buatTagihanDanTransaksi();
        $token = $this->client->createToken('default', ['read:lainnya'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.pembayaran-spp.index'))
            ->assertForbidden();
    }

    public function test_klien_nonaktif_ditolak_walau_token_valid(): void
    {
        $this->buatTagihanDanTransaksi();
        $this->client->update(['is_aktif' => false]);
        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.pembayaran-spp.index'))
            ->assertForbidden();
    }

    public function test_klien_aktif_dengan_ability_benar_bisa_mengambil_data(): void
    {
        $this->buatTagihanDanTransaksi();
        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.pembayaran-spp.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.siswa.kode_siswa', 'S-0001');
        $response->assertJsonPath('data.0.nominal_dibayar', 200_000);
        $response->assertJsonPath('data.0.sisa_tagihan', 300_000);
    }

    public function test_filter_kode_unit_dan_periode_bekerja(): void
    {
        $this->buatTagihanDanTransaksi();

        $unitLain = Unit::query()->create(['kode_unit' => 'U2', 'nama' => 'Unit Dua', 'jenjang' => 'SMP']);
        $tagihanLain = TagihanSiswa::query()->create([
            'unit_id' => $unitLain->id,
            'kode_siswa' => 'S-0002',
            'nama_siswa' => 'Budi',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-09',
            'nominal' => 400_000,
            'sisa_tagihan' => 0,
            'status' => 'lunas',
            'jatuh_tempo' => '2026-09-30',
        ]);
        Transaksi::query()->create([
            'unit_id' => $unitLain->id,
            'tagihan_siswa_id' => $tagihanLain->id,
            'created_by' => $this->bendahara->id,
            'jenis' => 'masuk',
            'kategori' => 'Tagihan Siswa - Spp',
            'nominal' => 400_000,
            'tanggal' => '2026-09-10',
            'keterangan' => 'Pembayaran spp - Budi (S-0002)',
        ]);

        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.pembayaran-spp.index', ['kode_unit' => 'U1']));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.unit.kode_unit', 'U1');
    }

    public function test_request_yang_bukan_pembayaran_spp_tidak_disertakan(): void
    {
        $tagihan = TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0003',
            'nama_siswa' => 'Citra',
            'jenis_tagihan' => 'buku',
            'periode' => '2026-10',
            'nominal' => 100_000,
            'sisa_tagihan' => 0,
            'status' => 'lunas',
            'jatuh_tempo' => '2026-10-31',
        ]);
        Transaksi::query()->create([
            'unit_id' => $this->unit->id,
            'tagihan_siswa_id' => $tagihan->id,
            'created_by' => $this->bendahara->id,
            'jenis' => 'masuk',
            'kategori' => 'Tagihan Siswa - Buku',
            'nominal' => 100_000,
            'tanggal' => '2026-10-05',
            'keterangan' => 'Pembayaran buku - Citra (S-0003)',
        ]);

        $token = $this->client->createToken('default', ['read:pembayaran-spp'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.pembayaran-spp.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}
