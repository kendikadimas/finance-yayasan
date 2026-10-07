<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien untuk REST API Master Data Yayasan (modul Manajemen Akses & API Token berbasis
 * Sanctum milik proyek Sistem Terintegrasi Yayasan Pendidikan). Menyediakan data Unit,
 * Siswa, Karyawan & Guru, Orang Tua/Wali, Tahun Ajaran, Rombel/Kelas, Bangunan Gedung,
 * Ruangan Fasilitas, Mata Pelajaran, dan Rekanan & Vendor lewat endpoint /api/v1/*.
 *
 * Nama endpoint dikonfirmasi langsung terhadap API live (bukan tebakan dari PRD):
 * units, siswa, karyawan, orang-tua, tahun-ajaran, kelas, bangunan, ruang,
 * mata-pelajaran, vendor.
 *
 * Lihat PRD bagian "Kebutuhan Data dari Yayasan". Token disimpan di .env
 * (YAYASAN_MASTER_API_TOKEN), tidak pernah di kode atau dokumen.
 */
class MasterDataClient
{
    private readonly ?string $baseUrl;

    private readonly ?string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.yayasan_master.base_url');
        $this->token = $token ?? config('services.yayasan_master.token');
    }

    public function isConfigured(): bool
    {
        return filled($this->baseUrl) && filled($this->token);
    }

    /**
     * Ambil satu halaman data mentah dari suatu resource master data.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     *
     * @throws RuntimeException jika token belum dikonfigurasi
     * @throws RequestException jika API mengembalikan error
     */
    public function get(string $resource, array $query = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'YAYASAN_MASTER_API_TOKEN belum dikonfigurasi di .env. Lihat PRD bagian Kebutuhan Data dari Yayasan.'
            );
        }

        return $this->client()
            ->get("/{$resource}", $query)
            ->throw()
            ->json();
    }

    /**
     * Ambil SELURUH halaman suatu resource (mengikuti meta.last_page), digabung jadi satu array.
     *
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    public function getAllPages(string $resource, array $query = []): array
    {
        $page = 1;
        $items = [];

        do {
            $response = $this->get($resource, [...$query, 'page' => $page]);
            array_push($items, ...($response['data'] ?? []));
            $lastPage = $response['meta']['last_page'] ?? 1;
            $page++;
        } while ($page <= $lastPage);

        return $items;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function units(array $query = []): array
    {
        return $this->get('units', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function siswa(array $query = []): array
    {
        return $this->get('siswa', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function karyawan(array $query = []): array
    {
        return $this->get('karyawan', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function orangTua(array $query = []): array
    {
        return $this->get('orang-tua', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function tahunAjaran(array $query = []): array
    {
        return $this->get('tahun-ajaran', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function kelas(array $query = []): array
    {
        return $this->get('kelas', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function bangunan(array $query = []): array
    {
        return $this->get('bangunan', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function ruang(array $query = []): array
    {
        return $this->get('ruang', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function mataPelajaran(array $query = []): array
    {
        return $this->get('mata-pelajaran', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function vendor(array $query = []): array
    {
        return $this->get('vendor', $query);
    }

    private function client(): PendingRequest
    {
        $client = Http::baseUrl($this->baseUrl)
            ->withToken($this->token)
            ->acceptJson();

        // Workaround untuk lingkungan lokal Windows/Laragon yang kerap punya
        // curl.cainfo di php.ini menunjuk ke file CA bundle milik project lain yang
        // sudah tidak ada. Alih-alih mengubah php.ini (konfigurasi global, di luar
        // project ini), arahkan verifikasi SSL ke bundle CA resmi yang disertakan.
        if ($caBundle = config('services.yayasan_master.ca_bundle')) {
            $client = $client->withOptions(['verify' => $caBundle]);
        }

        return $client;
    }
}
