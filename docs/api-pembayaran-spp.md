# API Pembayaran SPP

Endpoint baca-saja untuk sistem lain di Sistem Terintegrasi Yayasan Pendidikan yang
perlu menarik riwayat pembayaran SPP dari sistem keuangan ini. Lihat
[`api-integrasi.md`](./api-integrasi.md) untuk daftar lengkap endpoint lain yang
tersedia dan cara kerja sistem ability-nya.

## Mendapatkan Akses

1. Admin membuat klien API baru di **Keuangan > Klien API** (`/keuangan/api-clients`),
   dan mencentang ability **"Riwayat Pembayaran SPP"** (`read:pembayaran-spp`).
2. Saat klien dibuat, token muncul satu kali di layar — salin dan simpan, token tidak
   bisa dilihat lagi setelah itu. Jika hilang, gunakan tombol "Terbitkan Ulang".
3. Admin bisa menonaktifkan klien kapan saja tanpa mencabut tokennya (tombol
   "Nonaktifkan") — klien nonaktif langsung ditolak meski tokennya masih valid.

## Autentikasi

Kirim token sebagai Bearer token Sanctum:

```
Authorization: Bearer <token>
```

Token hanya bisa memanggil endpoint yang sesuai dengan ability yang dicentang admin
saat klien dibuat. Untuk endpoint ini, token wajib punya ability
`read:pembayaran-spp`.

## Endpoint

```
GET /api/v1/pembayaran-spp
```

### Parameter Query (semua opsional)

| Parameter        | Tipe    | Keterangan                                             |
| ---------------- | ------- | ------------------------------------------------------ |
| `kode_unit`      | string  | Filter berdasarkan kode unit sekolah                   |
| `kode_siswa`     | string  | Filter berdasarkan kode siswa                          |
| `periode`        | string  | Filter berdasarkan periode tagihan (contoh: `2026-10`) |
| `tanggal_dari`   | date    | Tanggal pembayaran mulai (`Y-m-d`)                     |
| `tanggal_sampai` | date    | Tanggal pembayaran sampai (`Y-m-d`)                    |
| `per_page`       | integer | Jumlah data per halaman (1-100, default 25)            |

### Contoh Request

```bash
curl -H "Authorization: Bearer <token>" \
  "https://finance.yayasan.test/api/v1/pembayaran-spp?kode_unit=U1&periode=2026-10"
```

### Contoh Response

```json
{
    "data": [
        {
            "id": 42,
            "unit": { "kode_unit": "U1", "nama": "Unit Satu" },
            "siswa": { "kode_siswa": "S-0001", "nama_siswa": "Ahmad Fajar" },
            "jenis_tagihan": "spp",
            "periode_tagihan": "2026-10",
            "nominal_dibayar": 200000,
            "tanggal_bayar": "2026-10-05",
            "metode_pembayaran": "Transfer Bank",
            "status_tagihan": "sebagian",
            "sisa_tagihan": 300000,
            "keterangan": "Pembayaran spp - Ahmad Fajar (S-0001)"
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
    "meta": { "current_page": 1, "last_page": 3, "per_page": 25, "total": 67 }
}
```

## Batas & Error

- Rate limit: 60 request/menit per klien.
- `401 Unauthorized` — token tidak ada atau tidak valid.
- `403 Forbidden` — token tidak punya ability `read:pembayaran-spp`, atau klien
  dinonaktifkan oleh admin.
- `422 Unprocessable Entity` — parameter query tidak valid.
