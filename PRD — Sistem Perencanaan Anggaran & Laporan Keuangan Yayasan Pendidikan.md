# PRD — Sistem Perencanaan Anggaran & Laporan Keuangan Yayasan Pendidikan

Oct 5, 2026 · @Alya

## Ringkasan Produk

Sistem Perencanaan Anggaran dan Laporan Keuangan berbasis web untuk yayasan pendidikan yang menaungi banyak unit sekolah, mengintegrasikan modul pemasukan, modul pengeluaran, dan fitur peringatan dini (_Early Warning System_) berbasis metode _Single Exponential Smoothing_ (SES).

| Atribut     | Keterangan                                                                                                                                                |
| ----------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Nama Proyek | Pengembangan Sistem Perencanaan Anggaran dan Laporan Keuangan pada Yayasan Pendidikan Berbasis Web                                                        |
| Pemilik     | Dimas Kendika Fazrulfalah (H1D023083) — S1 Informatika, Universitas Jenderal Soedirman                                                                    |
| Status      | Tahap Proposal — menunggu konfirmasi data dari yayasan mitra                                                                                              |
| Bagian dari | Proyek kolaboratif Sistem Terintegrasi Yayasan Pendidikan (paket: core platform, manajemen siswa, ekosistem pembelajaran, SDM, keuangan, layanan digital) |

## Latar Belakang & Tujuan

Yayasan pendidikan multi-unit menghadapi kesulitan mengonsolidasikan kondisi keuangan seluruh unit karena setiap unit memiliki siklus pemasukan, pengeluaran, dan pelaporan yang berbeda, sementara pencatatan manual rentan terhadap kesalahan dan kurang transparan. Penelitian sejenis sudah banyak membangun sistem pencatatan keuangan berbasis web, namun belum ada yang menggabungkan pelaporan lintas unit dengan mekanisme peringatan dini atas potensi pembengkakan anggaran.

Tujuan sistem ini:

1. Mengintegrasikan modul pemasukan dan pengeluaran keuangan dalam satu platform pada yayasan pendidikan multi-unit.
2. Menghasilkan laporan keuangan yang terintegrasi dari lintas unit sehingga pimpinan yayasan memperoleh gambaran kondisi keuangan secara terpadu.
3. Memberi peringatan dini atas potensi pembengkakan anggaran melalui klasifikasi status serapan anggaran dan proyeksi realisasi menggunakan metode SES.
4. Memastikan keluaran laporan anggaran dan keuangan benar dan akurat sesuai data yang dimasukkan.

## Target Pengguna & Peran

| Peran             | Deskripsi                                   | Kewenangan Utama                                                          |
| ----------------- | ------------------------------------------- | ------------------------------------------------------------------------- |
| Bendahara Unit    | Mengelola keuangan harian satu unit sekolah | Input transaksi pemasukan/pengeluaran unit, susun RAB unit                |
| Bendahara Yayasan | Mengelola keuangan tingkat yayasan          | Konsolidasi laporan lintas unit, pantau status EWS seluruh unit           |
| Pimpinan Yayasan  | Pengambil keputusan strategis               | Lihat laporan konsolidasi, terima notifikasi peringatan dini, setujui RAB |
| Admin Sistem      | Mengelola akses dan konfigurasi sistem      | Kelola pengguna, atur ambang batas (_threshold_) status EWS               |

## User Stories & Use Cases

**Bendahara Unit**

- Sebagai Bendahara Unit, saya ingin menyusun RAB unit saya, sehingga anggaran pemasukan dan pengeluaran sudah terencana sebelum periode berjalan. (F-IN-01, F-OUT-01)
- Sebagai Bendahara Unit, saya ingin mencatat transaksi pemasukan dan pengeluaran harian, sehingga pembukuan unit selalu mutakhir. (F-IN-05, F-OUT-04)
- Sebagai Bendahara Unit, saya ingin melihat status EWS unit saya, sehingga saya tahu kategori anggaran mana yang mendekati batas pagu.

**Bendahara Yayasan**

- Sebagai Bendahara Yayasan, saya ingin melihat laporan konsolidasi seluruh unit, sehingga saya memahami kondisi keuangan yayasan secara keseluruhan. (F-RPT-02)
- Sebagai Bendahara Yayasan, saya ingin memantau dashboard status EWS semua unit, sehingga saya bisa menindaklanjuti unit berisiko lebih dulu. (F-RPT-03)

**Pimpinan Yayasan**

- Sebagai Pimpinan Yayasan, saya ingin menerima notifikasi saat status EWS suatu unit naik ke level Kritis atau Melebihi Anggaran, sehingga saya bisa mengambil keputusan dengan cepat.
- Sebagai Pimpinan Yayasan, saya ingin menyetujui RAB yang diajukan tiap unit, sehingga anggaran yang berjalan sudah melalui persetujuan resmi.

**Admin Sistem**

- Sebagai Admin Sistem, saya ingin mengatur ambang batas (threshold) status EWS, sehingga klasifikasi status sesuai kondisi riil yayasan tanpa mengubah kode program. (NF-04)
- Sebagai Admin Sistem, saya ingin mengelola hak akses tiap peran pengguna, sehingga setiap peran hanya dapat mengakses fitur sesuai kewenangannya.

## Ruang Lingkup

**Termasuk (in-scope):**

- Modul Pemasukan: perencanaan anggaran pemasukan, tagihan siswa (SPP, uang pangkal, boarding fee, tagihan kegiatan), pencatatan piutang, konfigurasi metode pembayaran (termasuk opsi payment gateway), pencatatan transaksi pemasukan.
- Modul Pengeluaran: perencanaan anggaran pengeluaran, penggajian guru dan karyawan, pencatatan hutang vendor, pencatatan transaksi pengeluaran beserta buku besar, pengelolaan kas dan bank.
- Fitur Early Warning System: klasifikasi status serapan anggaran, proyeksi realisasi anggaran dengan SES.
- Laporan keuangan per unit dan konsolidasi tingkat yayasan.

**Tidak termasuk (out-of-scope):**

- Manajemen akademik, kepegawaian, dan presensi (tanggung jawab paket lain dalam proyek sistem terintegrasi).
- Integrasi langsung dengan payment gateway pihak ketiga (hanya disediakan sebagai opsi konfigurasi).

## Proses Bisnis Saat Ini

_(Bagian ini akan dilengkapi setelah sesi penjelasan proses bisnis bersama pihak yayasan.)_

Pertanyaan terbuka yang perlu dikonfirmasi:

- [ ] Alur penyusunan RAB saat ini (top-down vs bottom-up, siapa yang approve)
- [ ] Mekanisme pembayaran SPP/tagihan siswa saat ini
- [ ] Alur persetujuan pengeluaran sebelum transaksi dilakukan
- [ ] Format dan frekuensi laporan keuangan yang sudah berjalan
- [ ] Sistem/tools pencatatan keuangan yang dipakai saat ini (Excel, aplikasi, manual)

## Kebutuhan Fungsional — Modul Pemasukan

| ID      | Fitur                          | Deskripsi                                                        |
| ------- | ------------------------------ | ---------------------------------------------------------------- |
| F-IN-01 | Perencanaan Anggaran Pemasukan | Susun target SPP, infaq, dan BOS per unit dan tingkat yayasan    |
| F-IN-02 | Pengelolaan Tagihan Siswa      | Kelola tagihan SPP, uang pangkal, boarding fee, tagihan kegiatan |
| F-IN-03 | Pencatatan Piutang             | Catat dan pantau tunggakan siswa                                 |
| F-IN-04 | Konfigurasi Metode Pembayaran  | Atur metode pembayaran termasuk opsi payment gateway             |
| F-IN-05 | Pencatatan Transaksi Pemasukan | Catat seluruh transaksi pemasukan per unit                       |

## Kebutuhan Fungsional — Modul Pengeluaran

| ID       | Fitur                                  | Deskripsi                                                         |
| -------- | -------------------------------------- | ----------------------------------------------------------------- |
| F-OUT-01 | Perencanaan Anggaran Pengeluaran (RAB) | Susun rencana belanja per unit dan tingkat yayasan                |
| F-OUT-02 | Penggajian                             | Hitung gaji guru dan karyawan                                     |
| F-OUT-03 | Pencatatan Hutang Vendor               | Catat dan pantau hutang kepada vendor                             |
| F-OUT-04 | Pencatatan Transaksi Pengeluaran       | Catat transaksi pengeluaran beserta buku besar (_general ledger_) |
| F-OUT-05 | Pengelolaan Kas & Bank                 | Kelola saldo kas dan rekening bank per unit                       |

## Kebutuhan Fungsional — Early Warning System

Sistem mengklasifikasikan status serapan anggaran per kategori dan unit berdasarkan rasio serapan aktual terhadap pace waktu periode anggaran:

```latex
\text{Expected Pace} = \frac{\text{Waktu Berjalan}}{\text{Total Durasi Periode Anggaran}} \times 100\%
```

```latex
\text{Rasio Serapan} = \frac{\text{Persentase Serapan}}{\text{Expected Pace}}
```

| Rasio Serapan               | Status            |
| --------------------------- | ----------------- |
| ≤ 1.0                       | Aman              |
| 1.0 – 1.2                   | Waspada           |
| 1.2 – 1.5                   | Kritis            |
| > 1.5 atau realisasi > pagu | Melebihi Anggaran |

Ambang batas (_threshold_) dapat dikonfigurasi oleh Admin Sistem. Notifikasi dikirim hanya saat status naik level (mis. Waspada ke Kritis), dipicu melalui Observer/Event Listener setiap transaksi pengeluaran disimpan, untuk menghindari notifikasi berlebihan pada transaksi kecil.

## Kebutuhan Fungsional — Proyeksi Anggaran (SES)

Proyeksi realisasi anggaran periode berikutnya dihitung dengan _Single Exponential Smoothing_:

```latex
F_{t+1} = \alpha X_t + (1-\alpha)F_t
```

dengan Ft+1 = proyeksi periode berikutnya, Xt = realisasi aktual periode berjalan, Ft = proyeksi periode berjalan, dan α = parameter pemulusan (0–1).

Akurasi diuji dengan _Mean Absolute Percentage Error_ (MAPE) terhadap beberapa nilai α untuk memperoleh nilai dengan kesalahan terkecil. Data historis minimal 6 bulan transaksi pengeluaran dipakai sebagai basis pengujian.

## Kebutuhan Fungsional — Laporan Keuangan Terintegrasi

| ID       | Fitur                       | Deskripsi                                                     |
| -------- | --------------------------- | ------------------------------------------------------------- |
| F-RPT-01 | Laporan per Unit            | Laporan keuangan tiap unit sekolah                            |
| F-RPT-02 | Laporan Konsolidasi Yayasan | Gabungan kondisi keuangan seluruh unit untuk pimpinan yayasan |
| F-RPT-03 | Dashboard Status EWS        | Ringkasan status serapan anggaran seluruh unit dan kategori   |

## Kebutuhan Non-Fungsional

| ID    | Kebutuhan                   | Keterangan                                                                         |
| ----- | --------------------------- | ---------------------------------------------------------------------------------- |
| NF-01 | Akurasi Data                | Keluaran laporan harus sesuai dengan data yang dimasukkan                          |
| NF-02 | Keamanan Data Keuangan      | Data transaksi riil dari yayasan dianonimkan sebelum diproses                      |
| NF-03 | Kompatibilitas Kontrak Data | Struktur data tetap kompatibel dengan kontrak data antar modul proyek terintegrasi |
| NF-04 | Kemudahan Konfigurasi       | Ambang batas status EWS dapat diatur tanpa mengubah kode program                   |

## Entitas & Model Data

| Entitas        | Atribut Kunci                                              | Relasi                                               |
| -------------- | ---------------------------------------------------------- | ---------------------------------------------------- |
| Unit           | kode\_unit, nama, jenjang                                  | Satu Unit memiliki banyak Anggaran & Transaksi       |
| Anggaran (RAB) | kategori, pagu, periode, unit\_id                          | Terhubung ke Unit dan Transaksi                      |
| Transaksi      | jenis (masuk/keluar), nominal, tanggal, kategori, unit\_id | Terhubung ke Anggaran dan Buku Besar                 |
| Buku Besar     | akun, debit, kredit, saldo                                 | Dihasilkan dari Transaksi                            |
| Pengguna       | nama, peran, unit\_id                                      | Terhubung ke Unit (kecuali Admin & Pimpinan Yayasan) |
| Vendor         | nama, status\_hutang                                       | Terhubung ke Transaksi Pengeluaran                   |

## Arsitektur & Stack Teknis

| Komponen              | Teknologi                  | Peran                                                                                                                                                                       |
| --------------------- | -------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Backend               | Laravel                    | API dan logika bisnis tiap _increment_                                                                                                                                      |
| Penghubung            | Inertia.js                 | Menghubungkan Laravel dengan frontend tanpa REST API terpisah                                                                                                               |
| Frontend              | React                      | Antarmuka pengguna                                                                                                                                                          |
| Basis Data            | MySQL                      | Penyimpanan data transaksi, anggaran, payroll                                                                                                                               |
| Pengujian API         | Postman                    | Pengujian dan dokumentasi _endpoint_                                                                                                                                        |
| Integrasi Master Data | REST API Yayasan (Sanctum) | Konsumsi data Unit, Siswa, Karyawan & Guru, Orang Tua/Wali, Tahun Ajaran, Rombel/Kelas, Bangunan Gedung, Ruangan Fasilitas, Mata Pelajaran, Rekanan & Vendor via /api/v1/\* |

## Metodologi Pengembangan

Sistem dibangun dengan Model Incremental melalui tahapan berikut:

1. Analisis kebutuhan berdasarkan dokumen spesifikasi sistem terintegrasi yayasan pendidikan.
2. Perancangan arsitektur menyeluruh: Entity Relationship Diagram dan High Level Design.
3. Increment 1 — perancangan, pengembangan, dan pengujian Modul Pemasukan.
4. Increment 2 — perancangan, pengembangan, dan pengujian Modul Pengeluaran, termasuk fitur EWS dan proyeksi SES, terhubung ke hasil Increment 1.
5. Pengujian integrasi menyeluruh antara kedua increment.
6. Perbaikan atas temuan pengujian dan penyusunan laporan akhir.

## Rencana Pengujian

Pengujian fungsional memakai _black box testing_ dengan teknik _equivalence partitioning_, skenario data simulasi minimal 3 unit sekolah dalam satu yayasan, dilakukan per _increment_ sebelum pengujian integrasi menyeluruh.

| Area Pengujian    | Skenario Utama                                                  |
| ----------------- | --------------------------------------------------------------- |
| Modul Pemasukan   | Input tagihan, pencatatan transaksi, validasi piutang           |
| Modul Pengeluaran | Input RAB, transaksi pengeluaran, update buku besar             |
| EWS               | Perubahan status saat transaksi mendekati/melebihi pagu         |
| Proyeksi SES      | Akurasi proyeksi dengan MAPE pada beberapa nilai α              |
| Laporan           | Kesesuaian laporan per unit dan konsolidasi dengan data masukan |

## Kebutuhan Data dari Yayasan

| Kategori              | Data yang Diminta                                                              |
| --------------------- | ------------------------------------------------------------------------------ |
| Struktur Organisasi   | Daftar unit, jenjang, jumlah siswa per unit                                    |
| Perencanaan Anggaran  | RAB tahun berjalan per unit dan kategori                                       |
| Transaksi Pemasukan   | Rekap pembayaran SPP/tagihan per bulan                                         |
| Transaksi Pengeluaran | Rekap pengeluaran per kategori, minimal 6 bulan terakhir (basis pengujian SES) |
| Laporan Existing      | Contoh format laporan keuangan manual yang sudah ada                           |
| Vendor & Hutang       | Daftar vendor aktif dan status hutang (jika ada)                               |

Sebagian kategori data di atas sudah tersedia melalui **REST API Master Data** yayasan (modul Manajemen Akses & API Token berbasis Sanctum, endpoint `/api/v1/*` dengan header `Authorization: Bearer <secret key>`), sehingga tidak perlu diminta/diinput ulang secara manual — cukup diintegrasikan langsung:

- Unit Pendidikan
- Data Siswa
- Karyawan & Guru
- Orang Tua / Wali
- Tahun Ajaran
- Rombel / Kelas
- Bangunan Gedung
- Ruangan Fasilitas
- Mata Pelajaran
- Rekanan & Vendor

Contoh pemanggilan endpoint master data (token aktual disimpan di `.env`, bukan di dokumen ini):

```bash
curl -H "Authorization: Bearer <SECRET_KEY>" \
  https://gevano.my.id/api/v1/units
```

Seluruh data dianonimkan (nama siswa/wali murid dihilangkan atau dikodekan) sebelum diproses.

## Jadwal & Milestone

&#91;embedded content: jadwal penelitian · 6 tahapan, Okt 2026–Jan 2027\]

Pengujian integrasi baru dimulai setelah Increment 1 dan Increment 2 selesai dikembangkan dan diuji secara terpisah.

## Risiko & Asumsi

| Risiko/Asumsi                                  | Dampak                                                 | Mitigasi                                                         |
| ---------------------------------------------- | ------------------------------------------------------ | ---------------------------------------------------------------- |
| Data riil dari yayasan terlambat/tidak lengkap | Pengujian SES & EWS tertunda                           | Ajukan permohonan data di awal jadwal (lihat Jadwal & Milestone) |
| Kontrak data antar modul proyek berubah        | Perlu penyesuaian struktur data                        | Koordinasi rutin dengan tim pengembang paket lain                |
| Threshold status EWS bersifat subjektif        | Klasifikasi kurang relevan dengan kondisi riil yayasan | Threshold dibuat _configurable_, divalidasi bersama yayasan      |
| Riwayat data historis kurang dari 6 bulan      | Akurasi SES menurun                                    | Gunakan periode data tersedia, laporkan keterbatasan di evaluasi |
