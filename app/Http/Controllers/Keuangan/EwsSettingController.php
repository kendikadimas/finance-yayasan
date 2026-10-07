<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Concerns\FlashesToast;
use App\Http\Controllers\Controller;
use App\Models\EwsSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EwsSettingController extends Controller
{
    use FlashesToast;

    public function edit(): Response
    {
        return Inertia::render('keuangan/ews-setting/edit', [
            'setting' => EwsSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'batas_waspada' => ['required', 'numeric', 'min:0.01'],
            'batas_kritis' => ['required', 'numeric', 'gt:batas_waspada'],
            'batas_melebihi' => ['required', 'numeric', 'gt:batas_kritis'],
        ]);

        EwsSetting::current()->update([...$data, 'updated_by' => $request->user()->id]);
        $this->toastSuccess('Konfigurasi EWS berhasil disimpan.');

        return to_route('keuangan.ews-setting.edit');
    }
}
