# API Ringkasan Laporan Keuangan

Endpoint baca-saja berisi ringkasan pemasukan, pengeluaran, dan saldo kas/bank per
unit untuk satu periode. Lihat [`api-integrasi.md`](./api-integrasi.md) untuk daftar
endpoint lain dan cara kerja ability.

**Dipakai oleh (contoh):** modul **Data Integration & Analytics** (data warehouse /
BI) dan **Executive Dashboard** tingkat yayasan, untuk KPI "Keuangan".

## Mendapatkan Akses

Admin membuat klien di **Keuangan > Klien API** dengan ability **"Ringkasan Laporan
Keuangan per Unit"** (`read:laporan-ringkasan`) dicentang. Lihat
[`api-integrasi.md`](./api-integrasi.md#model-akses-klien--ability) untuk alur
lengkapnya.

## Autentikasi

```
Authorization: Bearer <token>
```

Token wajib punya ability `read:laporan-ringkasan`.

## Endpoint

```
GET /api/v1/laporan/ringkasan
```

Endpoint ini tidak dipaginasi — jumlah unit yayasan kecil, seluruh unit dikembalikan
sekaligus dalam satu response.

### Parameter Query (semua opsional)

| Parameter | Tipe | Keterangan                                            |
| --------- | ---- | ----------------------------------------------------- |
| `dari`    | date | Awal periode (`Y-m-d`). Default: awal tahun berjalan. |
| `sampai`  | date | Akhir periode (`Y-m-d`). Default: hari ini.           |

### Contoh Request

```bash
curl -H "Authorization: Bearer <token>" \
  "https://finance.yayasan.test/api/v1/laporan/ringkasan?dari=2026-01-01&sampai=2026-12-31"
```

### Contoh Response

```json
{
    "data": [
        {
            "unit_id": 1,
            "kode_unit": "U1",
            "unit": "Unit Satu",
            "pemasukan": 500000000,
            "pengeluaran": 420000000,
            "saldo_bersih": 80000000,
            "saldo_kas_bank": 95000000
        }
    ],
    "meta": {
        "total_pemasukan": 500000000,
        "total_pengeluaran": 420000000,
        "total_saldo_kas_bank": 95000000,
        "periode": { "dari": "2026-01-01", "sampai": "2026-12-31" }
    }
}
```

## Batas & Error

- Rate limit: 60 request/menit per klien.
- `401 Unauthorized` — token tidak ada atau tidak valid.
- `403 Forbidden` — token tidak punya ability `read:laporan-ringkasan`, atau klien
  dinonaktifkan.
- `422 Unprocessable Entity` — parameter query tidak valid.
