<?php

namespace App\Http\Resources;

use App\Models\TagihanSiswa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TagihanSiswa */
class TagihanSiswaResource extends JsonResource
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
            'kode_siswa' => $this->kode_siswa,
            'nama_siswa' => $this->nama_siswa,
            'jenis_tagihan' => $this->jenis_tagihan,
            'periode' => $this->periode,
            'nominal' => (float) $this->nominal,
            'sisa_tagihan' => (float) $this->sisa_tagihan,
            'status' => $this->status,
            'jatuh_tempo' => $this->jatuh_tempo->format('Y-m-d'),
        ];
    }
}
