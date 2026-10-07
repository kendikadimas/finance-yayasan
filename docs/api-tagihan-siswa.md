# API Status Tagihan Siswa

Endpoint baca-saja untuk sistem lain yang perlu tahu status tagihan siswa — bukan
cuma SPP, tapi semua jenis tagihan yang dikelola sistem ini (SPP, uang pangkal,
boarding fee, tagihan kegiatan). Lihat [`api-integrasi.md`](./api-integrasi.md) untuk
daftar endpoint lain dan cara kerja ability.

**Dipakai oleh (contoh):**

- **Parent App** — orang tua cek tagihan anaknya & status lunas/belum.
- **PPDB/Admission** — verifikasi uang pangkal sudah lunas sebelum konfirmasi daftar
  ulang.
- **Boarding Management** — cek status boarding fee santri.

## Mendapatkan Akses

Admin membuat klien di **Keuangan > Klien API** dengan ability **"Status Tagihan
Siswa"** (`read:tagihan-siswa`) dicentang. Lihat
[`api-integrasi.md`](./api-integrasi.md#model-akses-klien--ability) untuk alur
lengkapnya.

## Autentikasi

```
Authorization: Bearer <token>
```

Token wajib punya ability `read:tagihan-siswa`.

## Endpoint

```
GET /api/v1/tagihan-siswa
```

### Parameter Query (semua opsional)

| Parameter       | Tipe    | Keterangan                                              |
| --------------- | ------- | ------------------------------------------------------- |
| `kode_unit`     | string  | Filter berdasarkan kode unit sekolah                    |
| `kode_siswa`    | string  | Filter berdasarkan kode siswa                           |
| `jenis_tagihan` | string  | `spp`, `uang_pangkal`, `boarding_fee`, `kegiatan`, dst. |
| `periode`       | string  | Filter berdasarkan periode tagihan                      |
| `status`        | string  | `belum_bayar`, `sebagian`, atau `lunas`                 |
| `per_page`      | integer | Jumlah data per halaman (1-100, default 25)             |

### Contoh Request

```bash
curl -H "Authorization: Bearer <token>" \
  "https://finance.yayasan.test/api/v1/tagihan-siswa?kode_siswa=S-0001&jenis_tagihan=uang_pangkal"
```

### Contoh Response

```json
{
    "data": [
        {
            "id": 10,
            "unit": { "kode_unit": "U1", "nama": "Unit Satu" },
            "kode_siswa": "S-0001",
            "nama_siswa": "Ahmad Fajar",
            "jenis_tagihan": "uang_pangkal",
            "periode": "2026-07",
            "nominal": 3000000,
            "sisa_tagihan": 3000000,
            "status": "belum_bayar",
            "jatuh_tempo": "2026-07-31"
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
    "meta": { "current_page": 1, "last_page": 1, "per_page": 25, "total": 1 }
}
```

## Batas & Error

- Rate limit: 60 request/menit per klien.
- `401 Unauthorized` — token tidak ada atau tidak valid.
- `403 Forbidden` — token tidak punya ability `read:tagihan-siswa`, atau klien
  dinonaktifkan.
- `422 Unprocessable Entity` — parameter query tidak valid.
