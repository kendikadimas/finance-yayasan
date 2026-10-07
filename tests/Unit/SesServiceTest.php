<?php

namespace Tests\Unit;

use App\Services\SesService;
use Tests\TestCase;

class SesServiceTest extends TestCase
{
    private SesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SesService;
    }

    public function test_forecast_dimulai_dari_aktual_pertama(): void
    {
        $forecasts = $this->service->hitungForecast([100.0, 120.0, 90.0], 0.5);

        $this->assertSame(100.0, $forecasts[0]);
    }

    public function test_forecast_mengikuti_rumus_single_exponential_smoothing(): void
    {
        // F_{t+1} = alpha * X_t + (1 - alpha) * F_t
        $actuals = [100.0, 120.0, 90.0];
        $alpha = 0.5;

        $forecasts = $this->service->hitungForecast($actuals, $alpha);

        // F2 = 0.5*100 + 0.5*100 = 100
        $this->assertEqualsWithDelta(100.0, $forecasts[1], 0.001);
        // F3 = 0.5*120 + 0.5*100 = 110
        $this->assertEqualsWithDelta(110.0, $forecasts[2], 0.001);
        // Proyeksi periode berikutnya (index 3) = 0.5*90 + 0.5*110 = 100
        $this->assertEqualsWithDelta(100.0, $forecasts[3], 0.001);
    }

    public function test_mape_nol_ketika_forecast_sempurna(): void
    {
        $actuals = [100.0, 100.0, 100.0];
        $forecasts = [100.0, 100.0, 100.0, 100.0];

        $this->assertSame(0.0, $this->service->hitungMape($actuals, $forecasts));
    }

    public function test_mape_menghitung_persentase_kesalahan_rata_rata(): void
    {
        // actual[1]=120 vs forecast[1]=100 -> error 16.67%; actual[2]=80 vs forecast[2]=110 -> error 37.5%
        $actuals = [100.0, 120.0, 80.0];
        $forecasts = [100.0, 100.0, 110.0];

        $mape = $this->service->hitungMape($actuals, $forecasts);

        $this->assertEqualsWithDelta((16.666667 + 37.5) / 2, $mape, 0.01);
    }
}
