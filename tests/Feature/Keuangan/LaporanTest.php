<?php

namespace Tests\Feature\Keuangan;

use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_per_unit_hanya_menjumlahkan_transaksi_unit_tersebut(): void
    {
        $unitA = Unit::query()->create(['kode_unit' => 'A', 'nama' => 'Unit A', 'jenjang' => 'SD']);
        $unitB = Unit::query()->create(['kode_unit' => 'B', 'nama' => 'Unit B', 'jenjang' => 'SMP']);
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $user = User::factory()->create();

        Transaksi::query()->create(['unit_id' => $unitA->id, 'created_by' => $user->id, 'jenis' => 'masuk', 'kategori' => 'SPP', 'nominal' => 1_000_000, 'tanggal' => now()]);
        Transaksi::query()->create(['unit_id' => $unitA->id, 'created_by' => $user->id, 'jenis' => 'keluar', 'kategori' => 'Operasional', 'nominal' => 300_000, 'tanggal' => now()]);
        Transaksi::query()->create(['unit_id' => $unitB->id, 'created_by' => $user->id, 'jenis' => 'masuk', 'kategori' => 'SPP', 'nominal' => 5_000_000, 'tanggal' => now()]);

        $response = $this->actingAs($admin)->get(route('keuangan.laporan.per-unit', $unitA));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('totalPemasukan', 1_000_000)
            ->where('totalPengeluaran', 300_000)
            ->where('saldoBersih', 700_000)
        );
    }

    public function test_bendahara_unit_tidak_bisa_melihat_laporan_unit_lain(): void
    {
        $unitA = Unit::query()->create(['kode_unit' => 'A', 'nama' => 'Unit A', 'jenjang' => 'SD']);
        $unitB = Unit::query()->create(['kode_unit' => 'B', 'nama' => 'Unit B', 'jenjang' => 'SMP']);
        $bendahara = User::factory()->role(User::ROLE_BENDAHARA_UNIT, $unitA->id)->create();

        $response = $this->actingAs($bendahara)->get(route('keuangan.laporan.per-unit', $unitB));

        $response->assertForbidden();
    }

    public function test_laporan_konsolidasi_menjumlahkan_seluruh_unit(): void
    {
        $unitA = Unit::query()->create(['kode_unit' => 'A', 'nama' => 'Unit A', 'jenjang' => 'SD']);
        $unitB = Unit::query()->create(['kode_unit' => 'B', 'nama' => 'Unit B', 'jenjang' => 'SMP']);
        $bendaharaYayasan = User::factory()->role(User::ROLE_BENDAHARA_YAYASAN)->create();
        $user = User::factory()->create();

        Transaksi::query()->create(['unit_id' => $unitA->id, 'created_by' => $user->id, 'jenis' => 'masuk', 'kategori' => 'SPP', 'nominal' => 1_000_000, 'tanggal' => now()]);
        Transaksi::query()->create(['unit_id' => $unitB->id, 'created_by' => $user->id, 'jenis' => 'masuk', 'kategori' => 'SPP', 'nominal' => 2_000_000, 'tanggal' => now()]);

        $response = $this->actingAs($bendaharaYayasan)->get(route('keuangan.laporan.konsolidasi'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('totalPemasukan', 3_000_000));
    }

    public function test_bendahara_unit_tidak_bisa_mengakses_laporan_konsolidasi(): void
    {
        $unit = Unit::query()->create(['kode_unit' => 'A', 'nama' => 'Unit A', 'jenjang' => 'SD']);
        $bendahara = User::factory()->role(User::ROLE_BENDAHARA_UNIT, $unit->id)->create();

        $response = $this->actingAs($bendahara)->get(route('keuangan.laporan.konsolidasi'));

        $response->assertForbidden();
    }
}
