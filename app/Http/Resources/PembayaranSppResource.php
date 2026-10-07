<?php

namespace App\Http\Resources;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaksi
 */
class PembayaranSppResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit' => [
                'kode_unit' => $this->unit->kode_unit,
                'nama' => $this->unit->nama,
            ],
            'siswa' => [
                'kode_siswa' => $this->tagihanSiswa?->kode_siswa,
                'nama_siswa' => $this->tagihanSiswa?->nama_siswa,
            ],
            'jenis_tagihan' => $this->tagihanSiswa?->jenis_tagihan,
            'periode_tagihan' => $this->tagihanSiswa?->periode,
            'nominal_dibayar' => (float) $this->nominal,
            'tanggal_bayar' => $this->tanggal->format('Y-m-d'),
            'metode_pembayaran' => $this->metodePembayaran?->nama,
            'status_tagihan' => $this->tagihanSiswa?->status,
            'sisa_tagihan' => $this->tagihanSiswa ? (float) $this->tagihanSiswa->sisa_tagihan : null,
            'keterangan' => $this->keterangan,
        ];
    }
}
