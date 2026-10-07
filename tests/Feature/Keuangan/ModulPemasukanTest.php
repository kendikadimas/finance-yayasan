<?php

namespace Tests\Feature\Keuangan;

use App\Models\KasBankAccount;
use App\Models\TagihanSiswa;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulPemasukanTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private User $bendahara;

    private KasBankAccount $kas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->bendahara = User::factory()->role(User::ROLE_BENDAHARA_UNIT, $this->unit->id)->create();
        $this->kas = KasBankAccount::query()->create(['unit_id' => $this->unit->id, 'nama' => 'Kas Tunai', 'jenis' => 'kas', 'saldo' => 0]);
    }

    public function test_bendahara_unit_bisa_menyusun_rab_pemasukan_untuk_unitnya(): void
    {
        $response = $this->actingAs($this->bendahara)->post(route('keuangan.anggaran.store'), [
            'unit_id' => $this->unit->id,
            'jenis' => 'pemasukan',
            'kategori' => 'SPP',
            'pagu' => 100_000_000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-12-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('anggarans', ['unit_id' => $this->unit->id, 'kategori' => 'SPP', 'status' => 'draft']);
    }

    public function test_bendahara_unit_tidak_bisa_menyusun_rab_untuk_unit_lain(): void
    {
        $unitLain = Unit::query()->create(['kode_unit' => 'U2', 'nama' => 'Unit Dua', 'jenjang' => 'SMP']);

        $response = $this->actingAs($this->bendahara)->post(route('keuangan.anggaran.store'), [
            'unit_id' => $unitLain->id,
            'jenis' => 'pemasukan',
            'kategori' => 'SPP',
            'pagu' => 100_000_000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-12-31',
        ]);

        $response->assertForbidden();
    }

    public function test_tagihan_siswa_baru_berstatus_belum_bayar_dengan_sisa_sama_dengan_nominal(): void
    {
        $this->actingAs($this->bendahara)->post(route('keuangan.tagihan-siswa.store'), [
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-10',
            'nominal' => 500_000,
            'jatuh_tempo' => '2026-10-31',
        ]);

        $this->assertDatabaseHas('tagihan_siswas', [
            'kode_siswa' => 'S-0001',
            'nominal' => 500_000,
            'sisa_tagihan' => 500_000,
            'status' => 'belum_bayar',
        ]);
    }

    public function test_pembayaran_sebagian_mengubah_status_menjadi_sebagian_dan_mengurangi_sisa_piutang(): void
    {
        $tagihan = TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-10',
            'nominal' => 500_000,
            'sisa_tagihan' => 500_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-10-31',
        ]);

        $this->actingAs($this->bendahara)->post(route('keuangan.tagihan-siswa.bayar', $tagihan), [
            'nominal' => 200_000,
            'kas_bank_account_id' => $this->kas->id,
            'tanggal' => '2026-10-05',
        ]);

        $tagihan->refresh();
        $this->assertSame('300000.00', $tagihan->sisa_tagihan);
        $this->assertSame('sebagian', $tagihan->status);
        $this->assertSame('200000.00', $this->kas->fresh()->saldo);
    }

    public function test_pelunasan_penuh_mengubah_status_menjadi_lunas(): void
    {
        $tagihan = TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-10',
            'nominal' => 500_000,
            'sisa_tagihan' => 500_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-10-31',
        ]);

        $this->actingAs($this->bendahara)->post(route('keuangan.tagihan-siswa.bayar', $tagihan), [
            'nominal' => 500_000,
            'kas_bank_account_id' => $this->kas->id,
            'tanggal' => '2026-10-05',
        ]);

        $tagihan->refresh();
        $this->assertSame('0.00', $tagihan->sisa_tagihan);
        $this->assertSame('lunas', $tagihan->status);
    }

    public function test_pembayaran_melebihi_sisa_tagihan_ditolak_validasi(): void
    {
        $tagihan = TagihanSiswa::query()->create([
            'unit_id' => $this->unit->id,
            'kode_siswa' => 'S-0001',
            'nama_siswa' => 'Ahmad Fajar',
            'jenis_tagihan' => 'spp',
            'periode' => '2026-10',
            'nominal' => 500_000,
            'sisa_tagihan' => 500_000,
            'status' => 'belum_bayar',
            'jatuh_tempo' => '2026-10-31',
        ]);

        $response = $this->actingAs($this->bendahara)->post(route('keuangan.tagihan-siswa.bayar', $tagihan), [
            'nominal' => 600_000, // melebihi sisa_tagihan
            'kas_bank_account_id' => $this->kas->id,
            'tanggal' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors('nominal');
        $this->assertSame('500000.00', $tagihan->fresh()->sisa_tagihan);
    }
}
