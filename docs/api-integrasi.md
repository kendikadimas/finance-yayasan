# API Integrasi — Sistem Keuangan Yayasan

Sistem keuangan ini adalah modul **Finance & Accounting** dalam Sistem Informasi
Terintegrasi Yayasan. Paket lain dalam sistem terintegrasi (Parent App, PPDB/Admission,
Boarding Management, Teacher/Employee App, Procurement, Data Integration & Analytics,
Executive Dashboard, dst.) bisa menarik data dari sini lewat REST API baca-saja di
bawah `/api/v1/*`. Sistem ini **tidak** menerima data masuk dari sistem lain lewat API
ini — hanya menyediakan data yang sudah dicatat bendahara.

## Endpoint yang tersedia

| Endpoint                        | Ability                  | Dipakai oleh (contoh)                                   | Dokumentasi                                            |
| ------------------------------- | ------------------------ | ------------------------------------------------------- | ------------------------------------------------------ |
| `GET /api/v1/pembayaran-spp`    | `read:pembayaran-spp`    | Parent App (riwayat bayar SPP)                          | [api-pembayaran-spp.md](./api-pembayaran-spp.md)       |
| `GET /api/v1/tagihan-siswa`     | `read:tagihan-siswa`     | Parent App, PPDB/Admission (cek uang pangkal), Boarding | [api-tagihan-siswa.md](./api-tagihan-siswa.md)         |
| `GET /api/v1/hutang-vendor`     | `read:hutang-vendor`     | Procurement (status pembayaran invoice vendor)          | [api-hutang-vendor.md](./api-hutang-vendor.md)         |
| `GET /api/v1/penggajian`        | `read:penggajian`        | Teacher/Employee App (slip gaji)                        | [api-penggajian.md](./api-penggajian.md)               |
| `GET /api/v1/laporan/ringkasan` | `read:laporan-ringkasan` | Data Integration & Analytics, Executive Dashboard       | [api-laporan-ringkasan.md](./api-laporan-ringkasan.md) |

## Model akses: klien & ability

Sistem eksternal **tidak** login sebagai pengguna manusia. Setiap sistem didaftarkan
sebagai satu **Klien API** (`App\Models\ApiClient`) oleh admin di
**Keuangan > Klien API** (`/keuangan/api-clients`):

1. Admin memberi nama klien (mis. "PPDB" atau "Parent App") dan mencentang ability
   mana saja yang boleh diakses klien itu — hanya yang dibutuhkan, tidak semua.
2. Sistem menerbitkan satu token Sanctum yang dikunci ke ability yang dicentang.
   Token ditampilkan **satu kali** saat dibuat — salin segera, token tidak bisa dilihat
   ulang.
3. Kalau ability klien diubah belakangan, token yang sudah beredar **tidak** otomatis
   ikut berubah (ability terkunci saat token dibuat) — admin perlu klik
   **"Terbitkan Ulang"** supaya token baru memakai ability yang terbaru. Token lama
   langsung tidak berlaku begitu token baru diterbitkan.
4. Admin bisa menonaktifkan klien kapan saja (tombol "Nonaktifkan") tanpa mencabut
   tokennya — request dari klien nonaktif langsung ditolak (403) meski tokennya valid.

Setiap endpoint, selain dicek oleh middleware (token valid + klien aktif), juga
mengecek sendiri apakah token punya ability yang sesuai — token dengan ability yang
salah ditolak (403) walau dia valid dan klien-nya aktif.

## Pola umum tiap endpoint

- **Autentikasi**: header `Authorization: Bearer <token>`.
- **Format**: JSON, dipaginasi (`data`, `links`, `meta`), parameter `per_page`
  (1–100, default 25) kecuali disebutkan lain.
- **Rate limit**: 60 request/menit per klien.
- **Error**:
    - `401 Unauthorized` — token tidak ada / tidak valid.
    - `403 Forbidden` — token tidak punya ability yang sesuai, atau klien dinonaktifkan.
    - `422 Unprocessable Entity` — parameter query tidak valid.

## Yang di luar scope API ini

Ini murni jalur **baca**. Tidak ada endpoint untuk sistem lain membuat tagihan,
menandai pembayaran, atau memproses uang lewat API ini — sesuai scope PRD yang
menyebut integrasi payment gateway pihak ketiga hanya opsi konfigurasi, bukan
kewajiban. Kalau PPDB/Boarding nantinya perlu **membuat** tagihan uang pangkal /
boarding fee lewat API (bukan cuma baca statusnya), itu endpoint tambahan yang belum
dibangun dan perlu keputusan terpisah.
