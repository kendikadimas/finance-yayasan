<?php

namespace App\Services;

use App\Models\SesProjection;
use App\Models\Transaksi;
use Illuminate\Support\Carbon;

class SesService
{
    public const MIN_BULAN_HISTORIS = 6;

    /**
     * Nilai alpha yang diuji untuk mencari kesalahan (MAPE) terkecil.
     *
     * @var list<float>
     */
    private const ALPHA_CANDIDATES = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9];

    /**
     * Ambil deret realisasi pengeluaran bulanan untuk satu unit & kategori, urut kronologis.
     *
     * @return array<string, float> key = "YYYY-MM", value = total realisasi
     */
    public function deretHistoris(int $unitId, string $kategori): array
    {
        return Transaksi::query()
            ->where('unit_id', $unitId)
            ->where('kategori', $kategori)
            ->where('jenis', 'keluar')
            ->get()
            ->groupBy(fn (Transaksi $t) => Carbon::parse($t->tanggal)->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('nominal'))
            ->sortKeys()
            ->all();
    }

    /**
     * F_{t+1} = alpha * X_t + (1 - alpha) * F_t, dengan F_1 = X_1.
     *
     * @param  list<float>  $actuals
     * @return list<float> forecast untuk setiap titik t=1..n, plus proyeksi n+1 di index terakhir
     */
    public function hitungForecast(array $actuals, float $alpha): array
    {
        $forecasts = [];
        $forecasts[0] = $actuals[0];

        foreach ($actuals as $t => $actual) {
            if ($t === 0) {
                continue;
            }
            $forecasts[$t] = $alpha * $actuals[$t - 1] + (1 - $alpha) * $forecasts[$t - 1];
        }

        $n = count($actuals) - 1;
        $forecasts[] = $alpha * $actuals[$n] + (1 - $alpha) * $forecasts[$n];

        return array_values($forecasts);
    }

    /**
     * MAPE dihitung dari t=2..n (titik pertama tidak punya forecast yang berarti karena F_1 = X_1).
     *
     * @param  list<float>  $actuals
     * @param  list<float>  $forecasts  hasil dari hitungForecast() (indeks n+1 = proyeksi, diabaikan)
     */
    public function hitungMape(array $actuals, array $forecasts): float
    {
        $errors = [];

        for ($t = 1; $t < count($actuals); $t++) {
            if ($actuals[$t] == 0.0) {
                continue;
            }
            $errors[] = abs(($actuals[$t] - $forecasts[$t]) / $actuals[$t]);
        }

        if ($errors === []) {
            return 0.0;
        }

        return (array_sum($errors) / count($errors)) * 100;
    }

    /**
     * Uji beberapa nilai alpha, pilih yang menghasilkan MAPE terkecil.
     *
     * @return array{alpha: float, mape: float, proyeksi: float}|null null jika data historis < 6 bulan
     */
    public function proyeksikan(int $unitId, string $kategori): ?array
    {
        $deret = $this->deretHistoris($unitId, $kategori);

        if (count($deret) < self::MIN_BULAN_HISTORIS) {
            return null;
        }

        $actuals = array_values($deret);

        $terbaik = null;

        foreach (self::ALPHA_CANDIDATES as $alpha) {
            $forecasts = $this->hitungForecast($actuals, $alpha);
            $mape = $this->hitungMape($actuals, $forecasts);

            if ($terbaik === null || $mape < $terbaik['mape']) {
                $terbaik = [
                    'alpha' => $alpha,
                    'mape' => $mape,
                    'proyeksi' => $forecasts[count($forecasts) - 1],
                ];
            }
        }

        return $terbaik;
    }

    /**
     * Hitung proyeksi terbaik dan simpan sebagai record SesProjection untuk periode berikutnya.
     */
    public function proyeksikanDanSimpan(int $unitId, string $kategori): ?SesProjection
    {
        $hasil = $this->proyeksikan($unitId, $kategori);

        if ($hasil === null) {
            return null;
        }

        $deret = $this->deretHistoris($unitId, $kategori);
        $periodeTerakhir = Carbon::createFromFormat('Y-m', array_key_last($deret));
        $periodeProyeksi = $periodeTerakhir->copy()->addMonth()->format('Y-m');

        return SesProjection::query()->updateOrCreate(
            ['unit_id' => $unitId, 'kategori' => $kategori, 'periode_proyeksi' => $periodeProyeksi],
            [
                'alpha' => $hasil['alpha'],
                'proyeksi' => $hasil['proyeksi'],
                'mape' => $hasil['mape'],
                'computed_at' => now(),
            ],
        );
    }
}
