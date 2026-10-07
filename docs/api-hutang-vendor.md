# API Status Hutang Vendor

Endpoint baca-saja untuk sistem lain yang perlu tahu status pembayaran invoice vendor
dari sistem keuangan ini. Lihat [`api-integrasi.md`](./api-integrasi.md) untuk daftar
endpoint lain dan cara kerja ability.

**Dipakai oleh (contoh):** modul **Procurement** — setelah Purchase Order diterima dan
dicatat sebagai hutang di sistem keuangan ini, Procurement perlu tahu kapan invoice
vendornya sudah dibayar.

## Mendapatkan Akses

Admin membuat klien di **Keuangan > Klien API** dengan ability **"Status Hutang
Vendor"** (`read:hutang-vendor`) dicentang. Lihat
[`api-integrasi.md`](./api-integrasi.md#model-akses-klien--ability) untuk alur
lengkapnya.

## Autentikasi

```
Authorization: Bearer <token>
```

Token wajib punya ability `read:hutang-vendor`.

## Endpoint

```
GET /api/v1/hutang-vendor
```

### Parameter Query (semua opsional)

| Parameter       | Tipe    | Keterangan                                  |
| --------------- | ------- | ------------------------------------------- |
| `kode_unit`     | string  | Filter berdasarkan kode unit sekolah        |
| `nomor_invoice` | string  | Filter berdasarkan nomor invoice            |
| `status`        | string  | `belum_lunas` atau `lunas`                  |
| `per_page`      | integer | Jumlah data per halaman (1-100, default 25) |

### Contoh Request

```bash
curl -H "Authorization: Bearer <token>" \
  "https://finance.yayasan.test/api/v1/hutang-vendor?status=belum_lunas"
```

### Contoh Response

```json
{
    "data": [
        {
            "id": 7,
            "unit": { "kode_unit": "U1", "nama": "Unit Satu" },
            "vendor": { "nama": "CV Sumber Makmur" },
            "nomor_invoice": "INV-001",
            "deskripsi": "Pembelian ATK",
            "nominal": 1500000,
            "status": "belum_lunas",
            "jatuh_tempo": "2026-10-31",
            "tanggal_lunas": null
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
    "meta": { "current_page": 1, "last_page": 1, "per_page": 25, "total": 1 }
}
```

## Batas & Error

- Rate limit: 60 request/menit per klien.
- `401 Unauthorized` — token tidak ada atau tidak valid.
- `403 Forbidden` — token tidak punya ability `read:hutang-vendor`, atau klien
  dinonaktifkan.
- `422 Unprocessable Entity` — parameter query tidak valid.
