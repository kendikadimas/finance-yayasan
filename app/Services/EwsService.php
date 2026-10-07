<?php

namespace App\Services;

use App\Models\Anggaran;
use App\Models\EwsSetting;
use App\Models\User;
use App\Notifications\AnggaranStatusMelebihi;
use Illuminate\Support\Carbon;

class EwsService
{
    public const STATUS_AMAN = 'aman';

    public const STATUS_WASPADA = 'waspada';

    public const STATUS_KRITIS = 'kritis';

    public const STATUS_MELEBIHI = 'melebihi_anggaran';

    /**
     * Urutan level status dari paling aman ke paling parah, untuk mendeteksi kenaikan level.
     */
    private const LEVELS = [
        self::STATUS_AMAN => 0,
        self::STATUS_WASPADA => 1,
        self::STATUS_KRITIS => 2,
        self::STATUS_MELEBIHI => 3,
    ];

    /**
     * Rasio serapan = persentase serapan aktual terhadap pace waktu periode anggaran.
     * Expected Pace = waktu berjalan / total durasi periode.
     */
    public function hitungRasioSerapan(Anggaran $anggaran): float
    {
        $mulai = Carbon::parse($anggaran->periode_mulai)->startOfDay();
        $selesai = Carbon::parse($anggaran->periode_selesai)->startOfDay();
        $today = Carbon::today();

        $totalDurasi = max(1, $mulai->diffInDays($selesai));
        $waktuBerjalan = min($totalDurasi, max(0, $mulai->diffInDays($today->greaterThan($selesai) ? $selesai : $today)));

        $expectedPace = $waktuBerjalan / $totalDurasi;
        $persentaseSerapan = $anggaran->pagu > 0 ? $anggaran->realisasi() / (float) $anggaran->pagu : 0;

        if ($expectedPace <= 0) {
            return $persentaseSerapan > 0 ? PHP_FLOAT_MAX : 0.0;
        }

        return $persentaseSerapan / $expectedPace;
    }

    public function klasifikasikan(float $rasio, bool $melebihiPagu, ?EwsSetting $setting = null): string
    {
        $setting ??= EwsSetting::current();

        if ($melebihiPagu || $rasio > $setting->batas_melebihi) {
            return self::STATUS_MELEBIHI;
        }

        if ($rasio > $setting->batas_kritis) {
            return self::STATUS_KRITIS;
        }

        if ($rasio > $setting->batas_waspada) {
            return self::STATUS_WASPADA;
        }

        return self::STATUS_AMAN;
    }

    /**
     * Evaluasi ulang status EWS sebuah RAB, simpan jika berubah, dan kirim notifikasi
     * hanya saat status naik level (mis. Waspada ke Kritis) untuk menghindari spam.
     */
    public function evaluasi(Anggaran $anggaran): string
    {
        $setting = EwsSetting::current();
        $realisasi = $anggaran->realisasi();
        $rasio = $this->hitungRasioSerapan($anggaran);
        $statusBaru = $this->klasifikasikan($rasio, $realisasi > (float) $anggaran->pagu, $setting);

        $statusLama = $anggaran->status_ews;

        if ($statusBaru !== $statusLama) {
            $anggaran->forceFill([
                'status_ews' => $statusBaru,
                'status_ews_updated_at' => now(),
            ])->save();

            if ((self::LEVELS[$statusBaru] ?? 0) > (self::LEVELS[$statusLama] ?? 0)) {
                $this->notifikasiKenaikanLevel($anggaran, $statusBaru);
            }
        }

        return $statusBaru;
    }

    private function notifikasiKenaikanLevel(Anggaran $anggaran, string $statusBaru): void
    {
        if (! in_array($statusBaru, [self::STATUS_KRITIS, self::STATUS_MELEBIHI], true)) {
            return;
        }

        User::query()
            ->where('role', User::ROLE_PIMPINAN_YAYASAN)
            ->get()
            ->each(fn (User $pimpinan) => $pimpinan->notify(new AnggaranStatusMelebihi($anggaran, $statusBaru)));
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::STATUS_AMAN => 'Aman',
            self::STATUS_WASPADA => 'Waspada',
            self::STATUS_KRITIS => 'Kritis',
            self::STATUS_MELEBIHI => 'Melebihi Anggaran',
            default => $status,
        };
    }
}
