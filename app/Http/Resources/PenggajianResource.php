<?php

namespace App\Http\Resources;

use App\Models\Penggajian;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Penggajian */
class PenggajianResource extends JsonResource
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
            'kode_pegawai' => $this->kode_pegawai,
            'nama_pegawai' => $this->nama_pegawai,
            'periode' => $this->periode,
            'gaji_pokok' => (float) $this->gaji_pokok,
            'tunjangan' => (float) $this->tunjangan,
            'potongan' => (float) $this->potongan,
            'total_gaji' => (float) $this->total_gaji,
            'status' => $this->status,
            'tanggal_bayar' => $this->tanggal_bayar?->format('Y-m-d'),
        ];
    }
}
