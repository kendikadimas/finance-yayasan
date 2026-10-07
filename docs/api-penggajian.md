# API Status Penggajian

Endpoint baca-saja untuk sistem lain yang perlu tahu status penggajian guru/pegawai
dari sistem keuangan ini. Lihat [`api-integrasi.md`](./api-integrasi.md) untuk daftar
endpoint lain dan cara kerja ability.

**Dipakai oleh (contoh):** **Teacher/Employee App** — guru/pegawai cek slip gaji dan
status pembayarannya.

Data ini bersifat sensitif (nominal gaji). Hanya berikan ability `read:penggajian`
ke sistem yang benar-benar perlu menampilkannya, dan pastikan sistem pemanggil
memfilter `kode_pegawai` sesuai pegawai yang sedang login di sisi mereka — endpoint
ini sendiri tidak membatasi per-pegawai, filternya opsional.

## Mendapatkan Akses

Admin membuat klien di **Keuangan > Klien API** dengan ability **"Status
Penggajian"** (`read:penggajian`) dicentang. Lihat
[`api-integrasi.md`](./api-integrasi.md#model-akses-klien--ability) untuk alur
lengkapnya.

## Autentikasi

```
Authorization: Bearer <token>
```

Token wajib punya ability `read:penggajian`.

## Endpoint

```
GET /api/v1/penggajian
```

### Parameter Query (semua opsional)

| Parameter      | Tipe    | Keterangan                                  |
| -------------- | ------- | ------------------------------------------- |
| `kode_unit`    | string  | Filter berdasarkan kode unit sekolah        |
| `kode_pegawai` | string  | Filter berdasarkan kode pegawai             |
| `periode`      | string  | Filter berdasarkan periode gaji             |
| `status`       | string  | `draft` atau `dibayar`                      |
| `per_page`     | integer | Jumlah data per halaman (1-100, default 25) |

### Contoh Request

```bash
curl -H "Authorization: Bearer <token>" \
  "https://finance.yayasan.test/api/v1/penggajian?kode_pegawai=P-001&periode=2026-10"
```

### Contoh Response

```json
{
    "data": [
        {
            "id": 3,
            "unit": { "kode_unit": "U1", "nama": "Unit Satu" },
            "kode_pegawai": "P-001",
            "nama_pegawai": "Budi",
            "periode": "2026-10",
            "gaji_pokok": 5000000,
            "tunjangan": 500000,
            "potongan": 100000,
            "total_gaji": 5400000,
            "status": "dibayar",
            "tanggal_bayar": "2026-10-28"
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
    "meta": { "current_page": 1, "last_page": 1, "per_page": 25, "total": 1 }
}
```

## Batas & Error

- Rate limit: 60 request/menit per klien.
- `401 Unauthorized` — token tidak ada atau tidak valid.
- `403 Forbidden` — token tidak punya ability `read:penggajian`, atau klien
  dinonaktifkan.
- `422 Unprocessable Entity` — parameter query tidak valid.
