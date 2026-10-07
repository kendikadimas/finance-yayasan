<?php

namespace Tests\Feature\Keuangan;

use App\Models\Anggaran;
use App\Models\HutangVendor;
use App\Models\KasBankAccount;
use App\Models\Penggajian;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulPengeluaranTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private User $bendahara;

    private User $pimpinan;

    private KasBankAccount $bank;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);
        $this->bendahara = User::factory()->role(User::ROLE_BENDAHARA_UNIT, $this->unit->id)->create();
        $this->pimpinan = User::factory()->role(User::ROLE_PIMPINAN_YAYASAN)->create();
        $this->bank = KasBankAccount::query()->create(['unit_id' => $this->unit->id, 'nama' => 'Bank', 'jenis' => 'bank', 'saldo' => 10_000_000]);
    }

    public function test_alur_persetujuan_rab_dari_draft_sampai_disetujui(): void
    {
        $anggaran = Anggaran::query()->create([
            'unit_id' => $this->unit->id,
            'jenis' => 'pengeluaran',
            'kategori' => 'Operasional',
            'pagu' => 1_000_000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-12-31',
            'status' => 'draft',
        ]);

        $this->actingAs($this->bendahara)->post(route('keuangan.anggaran.ajukan', $anggaran));
        $this->assertSame('diajukan', $anggaran->fresh()->status);

        $this->actingAs($this->pimpinan)->post(route('keuangan.anggaran.approve', $anggaran));
        $anggaran->refresh();
        $this->assertSame('disetujui', $anggaran->status);
        $this->assertSame($this->pimpinan->id, $anggaran->approved_by);
    }

    public function test_pimpinan_yayasan_bisa_melihat_daftar_rab_untuk_disetujui(): void
    {
        Anggaran::query()->create([
            'unit_id' => $this->unit->id,
            'jenis' => 'pengeluaran',
            'kategori' => 'Operasional',
            'pagu' => 1_000_000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-12-31',
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->pimpinan)->get(route('keuangan.anggaran.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('anggarans', 1));
    }

    public function test_bendahara_tidak_bisa_menyetujui_rab_sendiri(): void
    {
        $anggaran = Anggaran::query()->create([
            'unit_id' => $this->unit->id,
            'jenis' => 'pengeluaran',
            'kategori' => 'Operasional',
            'pagu' => 1_000_000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-12-31',
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->bendahara)->post(route('keuangan.anggaran.approve', $anggaran));

        $response->assertForbidden();
        $this->assertSame('diajukan', $anggaran->fresh()->status);
    }

    public function test_pimpinan_tidak_bisa_menyetujui_rab_yang_belum_diajukan(): void
    {
        $anggaran = Anggaran::query()->create([
            'unit_id' => $this->unit->id,
            'jenis' => 'pengeluaran',
            'kategori' => 'Operasional',
            'pagu' => 1_000_000,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-12-31',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->pimpinan)->post(route('keuangan.anggaran.approve', $anggaran));

        $response->assertForbidden();
    }

    public function test_transaksi_pengeluaran_mengurangi_saldo_dan_mencatat_buku_besar_debit_beban(): void
    {
        $this->actingAs($this->bendahara)->post(route('keuangan.transaksi.store'), [
            'unit_id' => $this->unit->id,
            'kas_bank_account_id' => $this->bank->id,
            'jenis' => 'keluar',
            'kategori' => 'Operasional',
            'nominal' => 2_000_000,
            'tanggal' => '2026-10-10',
        ]);

        $this->assertSame('8000000.00', $this->bank->fresh()->saldo);
        $this->assertDatabaseHas('buku_besars', ['akun' => 'Beban - Operasional', 'debit' => 2_000_000]);
        $this->assertDatabaseHas('buku_besars', ['akun' => 'Bank', 'kredit' => 2_000_000]);
    }

    public function test_bayar_penggajian_membuat_transaksi_pengeluaran_dan_menandai_dibayar(): void
    {
        $penggajian = Penggajian::query()->create([
            'unit_id' => $this->unit->id,
            'nama_pegawai' => 'Budi',
            'periode' => '2026-10',
            'gaji_pokok' => 5_000_000,
            'tunjangan' => 500_000,
            'potongan' => 100_000,
            'total_gaji' => 5_400_000,
            'status' => 'draft',
        ]);

        $this->actingAs($this->bendahara)->post(route('keuangan.penggajian.bayar', $penggajian), [
            'kas_bank_account_id' => $this->bank->id,
            'tanggal_bayar' => '2026-10-28',
        ]);

        $penggajian->refresh();
        $this->assertSame('dibayar', $penggajian->status);
        $this->assertNotNull($penggajian->transaksi_id);
        $this->assertSame('4600000.00', $this->bank->fresh()->saldo);
    }

    public function test_penggajian_yang_sudah_dibayar_tidak_bisa_diubah(): void
    {
        $penggajian = Penggajian::query()->create([
            'unit_id' => $this->unit->id,
            'nama_pegawai' => 'Budi',
            'periode' => '2026-10',
            'gaji_pokok' => 5_000_000,
            'tunjangan' => 0,
            'potongan' => 0,
            'total_gaji' => 5_000_000,
            'status' => 'dibayar',
        ]);

        $response = $this->actingAs($this->bendahara)->put(route('keuangan.penggajian.update', $penggajian), [
            'nama_pegawai' => 'Budi Diubah',
            'periode' => '2026-10',
            'gaji_pokok' => 6_000_000,
            'tunjangan' => 0,
            'potongan' => 0,
        ]);

        $response->assertForbidden();
    }

    public function test_pelunasan_hutang_vendor_membuat_transaksi_dan_menandai_lunas(): void
    {
        $vendor = Vendor::query()->create(['nama' => 'CV Sumber Makmur']);
        $hutang = HutangVendor::query()->create([
            'vendor_id' => $vendor->id,
            'unit_id' => $this->unit->id,
            'deskripsi' => 'Pembelian ATK',
            'nominal' => 1_500_000,
            'jatuh_tempo' => '2026-10-31',
            'status' => 'belum_lunas',
        ]);

        $this->actingAs($this->bendahara)->post(route('keuangan.hutang-vendor.lunas', $hutang), [
            'kas_bank_account_id' => $this->bank->id,
            'tanggal_lunas' => '2026-10-15',
        ]);

        $hutang->refresh();
        $this->assertSame('lunas', $hutang->status);
        $this->assertSame('8500000.00', $this->bank->fresh()->saldo);
    }
}
