# Alur Fitur Sistem Keuangan Yayasan

---

## 1. Autentikasi & Otorisasi Pengguna

**Login:**
1. User buka `/login` → isi email + password
2. Laravel Sanctum verifikasi credential → buat session
3. Middleware `auth` + `verified` melindungi semua route keuangan
4. Role user (`superadmin`, `admin_yayasan`, `bendahara_unit`, `kepala_unit`) menentukan apa yang bisa diakses di tiap halaman

**Role & akses:**
- `superadmin` / `admin_yayasan` → akses semua unit
- `bendahara_unit` → hanya unit sendiri, bisa input transaksi
- `kepala_unit` → hanya unit sendiri, read-only laporan

---

## 2. Sinkronisasi Master Data (Inbound)

**Trigger:** Dijalankan manual atau via scheduler

```bash
php artisan master:sync-units
php artisan master:sync-vendors
php artisan master:sync-siswa
php artisan master:sync-karyawan
```

**Alur tiap command:**
1. `MasterDataClient::isConfigured()` cek `YAYASAN_MASTER_API_TOKEN` di `.env`
2. `getAllPages()` loop semua halaman API (`/api/v1/{resource}?page=N`) sampai `meta.last_page`
3. Tiap item di-`updateOrCreate` berdasarkan key unik (`kode_unit`, `kode_vendor`, `nis` siswa, `nip` karyawan)
4. Data lama diupdate, data baru ditambah, tidak ada yang dihapus

**Hasil:** Tabel `units`, `vendors`, `siswa`, `karyawan` selalu up-to-date dengan Master Data pusat

---

## 3. Transaksi (Pemasukan & Pengeluaran)

**Input transaksi baru:**
1. Bendahara buka **Keuangan > Transaksi > Tambah**
2. Pilih jenis (pemasukan/pengeluaran), akun kas/bank, tanggal, nominal, keterangan
3. Submit → controller validasi → `Transaksi` disimpan
4. `TransaksiObserver::created()` otomatis:
   - Membuat entry `BukuBesar` (debit/kredit sesuai jenis)
   - Mengupdate `KasBankAccount.saldo`

**Edit/hapus:**
- Edit → observer `updated()` reverse entry lama + buat entry baru
- Hapus → observer `deleted()` reverse entry → saldo balik

---

## 4. Tagihan Siswa

**Buat tagihan:**
1. Bendahara buka **Keuangan > Tagihan Siswa > Tambah**
2. Pilih unit, isi kode siswa (autocomplete dari tabel `siswa` hasil sync), jenis tagihan, periode, nominal, jatuh tempo
3. Status awal otomatis `belum_bayar`, `sisa_tagihan` = `nominal`

**Catat pembayaran:**
1. Bendahara pilih tagihan yang ada → klik **Bayar**
2. Input nominal dibayar → sistem buat `Transaksi` pemasukan
3. `sisa_tagihan` berkurang, status berubah otomatis:
   `belum_bayar` → `sebagian` → `lunas`

---

## 5. Hutang Vendor

**Catat hutang baru:**
1. Bendahara buka **Keuangan > Hutang Vendor > Tambah**
2. Pilih vendor (dari tabel `vendors` hasil sync), isi nomor invoice, deskripsi, nominal, jatuh tempo
3. Status awal `belum_lunas`

**Lunasin hutang:**
1. Pilih hutang yang ada → klik **Tandai Lunas**
2. Sistem buat `Transaksi` pengeluaran
3. `status` berubah ke `lunas`, `tanggal_lunas` diisi hari ini

---

## 6. Penggajian

**Input penggajian:**
1. Bendahara buka **Keuangan > Penggajian > Tambah**
2. Isi kode pegawai (autocomplete dari tabel `karyawan`), periode, gaji pokok, tunjangan, potongan
3. `total_gaji` dihitung otomatis: `gaji_pokok + tunjangan - potongan`
4. Status awal `draft`

**Proses pembayaran:**
1. Pilih penggajian draft → klik **Bayar**
2. Sistem buat `Transaksi` pengeluaran
3. `status` berubah ke `dibayar`, `tanggal_bayar` diisi

---

## 7. Anggaran (RAB)

**Buat anggaran:**
1. Admin buka **Keuangan > Anggaran > Tambah**
2. Pilih unit, tahun anggaran, kategori, nominal target
3. Disimpan sebagai `Anggaran`

**Monitoring:**
- Halaman anggaran tampilkan realisasi vs target berdasarkan transaksi yang sudah masuk di kategori yang sama

---

## 8. Laporan Keuangan

**Laporan per unit:**
1. User buka **Keuangan > Laporan**
2. Pilih unit + periode → controller query `Transaksi` + `BukuBesar`
3. Tampilkan ringkasan pemasukan, pengeluaran, saldo bersih, saldo kas/bank

**Konsolidasi yayasan:**
1. `superadmin`/`admin_yayasan` buka **Keuangan > Laporan > Konsolidasi**
2. `LaporanRingkasanService::perUnit()` aggregasi data semua unit sekaligus
3. Tampilkan tabel per-unit + total yayasan

---

## 9. Early Warning System (EWS)

**Cek kesehatan keuangan:**
1. Dashboard EWS query kondisi tiap unit
2. Sistem beri sinyal merah/kuning/hijau berdasarkan:
   - Rasio pengeluaran vs pemasukan
   - Hutang jatuh tempo yang belum lunas
   - Tagihan siswa yang overdue
3. Admin yayasan bisa lihat unit mana yang perlu perhatian

---

## 10. Manajemen Klien API

**Daftarkan klien baru:**
1. Admin buka **Keuangan > Klien API > Tambah**
2. Isi nama klien, pilih ability yang diizinkan (checkbox dari `ApiAbility` enum)
3. Submit → `ApiClient` dibuat → `createToken()` Sanctum dengan ability yang dipilih
4. Token muncul **sekali** di dialog (Inertia flash) → salin sekarang, tidak bisa dilihat ulang

**Edit ability:**
1. Klik **Edit** di baris klien → inline form muncul
2. Centang/uncentang ability → simpan → `ApiClient` diupdate
3. Token lama **tidak** ikut berubah; klik **Terbitkan Ulang** agar token baru pakai ability baru

**Regenerate token:**
1. Klik **Terbitkan Ulang**
2. Token lama di-revoke, token baru dibuat dengan ability klien saat ini
3. Token baru muncul sekali di dialog

**Nonaktifkan klien:**
1. Toggle `is_aktif` → semua request klien itu langsung ditolak 403 tanpa perlu cabut token

---

## 11. API Outbound (Finance → Sistem Lain)

**Alur request dari sistem eksternal:**

```
Sistem lain
  → GET /api/v1/{endpoint}
  → Header: Authorization: Bearer <token>
  → Middleware: auth:sanctum
      → Sanctum decode token → resolve ApiClient
      → EnsureApiClientActive: cek is_aktif, update last_used_at
  → Controller cek tokenCan('{ability}')
  → Query database dengan filter dari query string
  → Return JSON (paginated atau flat)
```

**5 endpoint yang tersedia:**

| Endpoint | Ability | Untuk |
|---|---|---|
| `GET /api/v1/pembayaran-spp` | `read:pembayaran-spp` | Riwayat bayar SPP |
| `GET /api/v1/tagihan-siswa` | `read:tagihan-siswa` | Status semua tagihan siswa |
| `GET /api/v1/hutang-vendor` | `read:hutang-vendor` | Status hutang vendor |
| `GET /api/v1/penggajian` | `read:penggajian` | Status penggajian pegawai |
| `GET /api/v1/laporan/ringkasan` | `read:laporan-ringkasan` | Ringkasan keuangan per unit |

---

## 12. Dokumentasi API

- **Markdown:** `docs/api-integrasi.md` + 5 file per-endpoint
- **Halaman web:** `https://finance.yayasan.test/docs/api` (static HTML di `public/docs/api.html`, disajikan via route `/docs/api`)
