<?php

namespace App\Http\Resources;

use App\Models\HutangVendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HutangVendor */
class HutangVendorResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit' => [
                'kode_unit' => $this->unit->kode_unit,
                'nama' => $this->unit->nama,
            ],
            'vendor' => [
                'nama' => $this->vendor->nama,
            ],
            'nomor_invoice' => $this->nomor_invoice,
            'deskripsi' => $this->deskripsi,
            'nominal' => (float) $this->nominal,
            'status' => $this->status,
            'jatuh_tempo' => $this->jatuh_tempo->format('Y-m-d'),
            'tanggal_lunas' => $this->tanggal_lunas?->format('Y-m-d'),
        ];
    }
}
