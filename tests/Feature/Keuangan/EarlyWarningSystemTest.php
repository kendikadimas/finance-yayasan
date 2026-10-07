<?php

namespace Tests\Feature\Keuangan;

use App\Models\Anggaran;
use App\Models\BukuBesar;
use App\Models\EwsSetting;
use App\Models\KasBankAccount;
use App\Models\Transaksi;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\AnggaranStatusMelebihi;
use App\Services\EwsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EarlyWarningSystemTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unit;

    private Anggaran $anggaran;

    private Carbon $periodeMulai;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::query()->create(['kode_unit' => 'U1', 'nama' => 'Unit Satu', 'jenjang' => 'SD']);

        $this->periodeMulai = Carbon::parse('2026-01-01');

        // Periode 100 hari, ditengah periode (hari ke-50) expected pace tepat 50%.
        $this->anggaran = Anggaran::query()->create([
            'unit_id' => $this->unit->id,
            'jenis' => 'pengeluaran',
            'kategori' => 'Operasional',
            'pagu' => 1_000_000,
            'periode_mulai' => $this->periodeMulai,
            'periode_selesai' => $this->periodeMulai->copy()->addDays(100),
            'status' => 'disetujui',
        ]);

        Carbon::setTestNow($this->periodeMulai->copy()->addDays(50));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function catatTransaksi(float $nominal): void
    {
        Transaksi::query()->create([
            'unit_id' => $this->unit->id,
            'anggaran_id' => $this->anggaran->id,
            'created_by' => User::factory()->create()->id,
            'jenis' => 'keluar',
            'kategori' => 'Operasional',
            'nominal' => $nominal,
            'tanggal' => now(),
        ]);
    }

    public function test_status_aman_saat_rasio_serapan_di_bawah_satu(): void
    {
        $this->catatTransaksi(400_000); // rasio = (0.4/0.5) = 0.8

        $this->assertSame(EwsService::STATUS_AMAN, $this->anggaran->fresh()->status_ews);
    }

    public function test_status_waspada_saat_rasio_serapan_melewati_satu(): void
    {
        $this->catatTransaksi(550_000); // rasio = (0.55/0.5) = 1.1

        $this->assertSame(EwsService::STATUS_WASPADA, $this->anggaran->fresh()->status_ews);
    }

    public function test_status_kritis_saat_rasio_serapan_melewati_satu_koma_dua(): void
    {
        $this->catatTransaksi(700_000); // rasio = (0.7/0.5) = 1.4

        $this->assertSame(EwsService::STATUS_KRITIS, $this->anggaran->fresh()->status_ews);
    }

    public function test_status_melebihi_anggaran_saat_rasio_serapan_melewati_satu_koma_lima(): void
    {
        $this->catatTransaksi(800_000); // rasio = (0.8/0.5) = 1.6

        $this->assertSame(EwsService::STATUS_MELEBIHI, $this->anggaran->fresh()->status_ews);
    }

    public function test_status_melebihi_anggaran_dipaksa_ketika_realisasi_lewati_pagu_meski_rasio_kecil(): void
    {
        // Periode hampir selesai (hari ke-99 dari 100) -> expected pace ~99%, tapi realisasi > pagu.
        Carbon::setTestNow($this->periodeMulai->copy()->addDays(99));

        $this->catatTransaksi(1_100_000);

        $this->assertSame(EwsService::STATUS_MELEBIHI, $this->anggaran->fresh()->status_ews);
    }

    public function test_threshold_bisa_dikonfigurasi_tanpa_ubah_kode(): void
    {
        EwsSetting::current()->update(['batas_waspada' => 2.0, 'batas_kritis' => 3.0, 'batas_melebihi' => 4.0]);

        // Dengan threshold longgar, rasio 1.4 (yang tadinya "kritis") sekarang masih "aman".
        $this->catatTransaksi(700_000);

        $this->assertSame(EwsService::STATUS_AMAN, $this->anggaran->fresh()->status_ews);
    }

    public function test_notifikasi_hanya_dikirim_ke_pimpinan_yayasan_saat_status_naik_level(): void
    {
        $pimpinan = User::factory()->role(User::ROLE_PIMPINAN_YAYASAN)->create();
        $bendahara = User::factory()->role(User::ROLE_BENDAHARA_UNIT, $this->unit->id)->create();

        $this->catatTransaksi(700_000); // naik ke Kritis -> harus notifikasi

        $this->assertCount(1, DatabaseNotification::where('notifiable_id', $pimpinan->id)->get());
        $this->assertCount(0, DatabaseNotification::where('notifiable_id', $bendahara->id)->get());

        $notification = DatabaseNotification::where('notifiable_id', $pimpinan->id)->first();
        $this->assertSame(AnggaranStatusMelebihi::class, $notification->type);
    }

    public function test_notifikasi_tidak_dikirim_ulang_saat_status_tetap_sama(): void
    {
        $pimpinan = User::factory()->role(User::ROLE_PIMPINAN_YAYASAN)->create();

        $this->catatTransaksi(700_000); // naik ke Kritis
        $this->catatTransaksi(1); // nominal kecil, status tetap Kritis

        $this->assertCount(1, DatabaseNotification::where('notifiable_id', $pimpinan->id)->get());
    }

    public function test_saldo_kas_bank_berkurang_dan_buku_besar_tercatat_saat_transaksi_pengeluaran(): void
    {
        $akun = KasBankAccount::query()->create([
            'unit_id' => $this->unit->id,
            'nama' => 'Kas Tunai',
            'jenis' => 'kas',
            'saldo' => 1_000_000,
        ]);

        Transaksi::query()->create([
            'unit_id' => $this->unit->id,
            'anggaran_id' => $this->anggaran->id,
            'kas_bank_account_id' => $akun->id,
            'created_by' => User::factory()->create()->id,
            'jenis' => 'keluar',
            'kategori' => 'Operasional',
            'nominal' => 200_000,
            'tanggal' => now(),
        ]);

        $this->assertSame('800000.00', $akun->fresh()->saldo);
        $this->assertSame(2, BukuBesar::count());
    }
}
