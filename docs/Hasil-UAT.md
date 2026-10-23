# Laporan Hasil User Acceptance Testing (UAT)

**XenoRent — Sistem Rental Kapal Alien**  
Versi 2.0 · 23 Oktober 2026  
Disusun oleh: Muhammad Fazly Radithya, Stevy Reuben Januardi, Muhammad Nadhif Aqila

## 1. Ringkasan Hasil

XenoRent **lolos UAT**. Seluruh 66 skenario uji end-to-end di browser lulus (66 lulus, 0 gagal), mencakup 33 kriteria penerimaan PRD, fitur pendukung, validasi field, kondisi tampilan, tujuan bisnis, kebutuhan non-fungsional, dan integritas data. Lima temuan dari putaran uji sebelumnya telah diperbaiki dan diverifikasi ulang (bagian 4).

| Kelompok skenario                                           | Jumlah | Lulus  | Gagal |
|-------------------------------------------------------------|--------|--------|-------|
| A. Kriteria Penerimaan (KP-01 s.d. KP-33)                   | 34     | 34     | 0     |
| B. Fitur pendukung (login, logout, profil, fitur Must lain) | 6      | 6      | 0     |
| C. Fitur Should / Could                                     | 3      | 3      | 0     |
| D. Validasi field per form (F-14, NFR-03)                   | 8      | 8      | 0     |
| E. Kondisi tampilan (PRD bagian 8)                          | 6      | 6      | 0     |
| F. Tujuan bisnis (BRD bagian 3)                             | 1      | 1      | 0     |
| G. Kebutuhan non-fungsional (BRD bagian 6)                  | 5      | 5      | 0     |
| H. Integritas data (Flowchart & ERD bagian 9)               | 3      | 3      | 0     |
| **Total**                                                   | **66** | **66** | **0** |

Selain uji end-to-end, 56 tes otomatis backend (PHPUnit) juga lulus seluruhnya (Lampiran B).

## 2. Informasi Pengujian

Pengujian dijalankan secara otomatis di browser dengan Playwright. Setiap skenario meniru tindakan pengguna (mengisi form, menekan tombol, membuka halaman), memeriksa hasilnya terhadap PRD, lalu menyimpan screenshot halaman penuh sebagai bukti.

| Item                | Keterangan                                                                        |
|---------------------|-----------------------------------------------------------------------------------|
| Aplikasi            | XenoRent, repositori `mfzlyy/XenoRent` branch `main`                              |
| Tanggal uji         | 23 Oktober 2026 (selesai 2026-10-23 00:13:25 UTC)                                 |
| Tester              | Muhammad Fazly Radithya, Stevy Reuben Januardi, Muhammad Nadhif Aqila             |
| Alat uji            | Playwright 1.64.1 (end-to-end), PHPUnit 12.5.40 (backend)                         |
| Browser             | Chromium (Playwright), resolusi 1366 × 768; uji tampilan HP pada lebar 390 piksel |
| Aplikasi yang diuji | Laravel 13.38.0, PHP 8.3                                                          |
| Database uji        | SQLite terpisah (`database/uat.sqlite`), di-seed ulang sebelum setiap skenario    |
| Data uji            | Data seed dari `php artisan migrate:fresh --seed` (PRD bagian 9)                  |
| Tarif denda         | Rp10.000 per hari, durasi sewa 5 hari                                             |

**Akun uji**

| Akun                  | Email                 | Password   | Kondisi awal                                            |
|-----------------------|-----------------------|------------|---------------------------------------------------------|
| Admin                 | `admin@xenorent.test` | `password` | —                                                       |
| Rina Kosmos (anggota) | `rina@xenorent.test`  | `password` | 2 sewa aktif (kuota penuh), satu di antaranya terlambat |
| Bima Nebula (anggota) | `bima@xenorent.test`  | `password` | 0 sewa aktif, 1 riwayat dengan denda Rp20.000           |

**Cara menjalankan ulang**

``` bash
npm run uat           # menjalankan seluruh skenario end-to-end
npm run uat:report    # membuka laporan HTML Playwright
php artisan test      # tes otomatis backend (PHPUnit)
```

## 3. Rekap Hasil per Kelompok

Kolom Bukti merujuk ke screenshot di Lampiran A. Kode KP, F, S, VAL, TB, NFR, dan INT mengacu ke BRD, Flowchart & ERD, dan PRD.

**A. Kriteria Penerimaan (KP-01 s.d. KP-33)**

| Kode   | Skenario                                                                               | Fitur | Hasil | Bukti            |
|--------|----------------------------------------------------------------------------------------|-------|-------|------------------|
| KP-01  | Registrasi anggota dengan data valid                                                   | F-01  | Lulus | Gambar A.1–A.2   |
| KP-02  | Registrasi dengan email yang sudah dipakai                                             | F-01  | Lulus | Gambar A.3       |
| KP-03  | Login dengan email dan password benar                                                  | F-02  | Lulus | Gambar A.4–A.6   |
| KP-04  | Login dengan password salah                                                            | F-02  | Lulus | Gambar A.7       |
| KP-05  | Tamu membuka halaman selain login/registrasi                                           | F-02  | Lulus | Gambar A.8–A.9   |
| KP-06  | Anggota membuka halaman Admin                                                          | F-02  | Lulus | Gambar A.10      |
| KP-07  | Pengguna mengubah telepon dan alamat profil sendiri                                    | F-03  | Lulus | Gambar A.11–A.12 |
| KP-07b | Profil yang diubah adalah milik akun yang sedang login (ID dari sesi, bukan dari form) | F-03  | Lulus | Gambar A.13      |
| KP-08  | Daftar unit menampilkan kode, nama, kategori, dan status                               | F-04  | Lulus | Gambar A.14      |
| KP-09  | Tiga unit bernama “Zeta Saucer” tampil terpisah dengan kode berbeda                    | F-04  | Lulus | Gambar A.15      |
| KP-10  | Pencarian “zeta” menemukan unit tanpa membedakan huruf besar-kecil                     | F-05  | Lulus | Gambar A.16–A.17 |
| KP-11  | Pencarian tanpa hasil menampilkan pesan “Unit tidak ditemukan”                         | F-05  | Lulus | Gambar A.18      |
| KP-12  | Admin menambah unit dengan kode baru dan 2 kategori                                    | F-06  | Lulus | Gambar A.19–A.20 |
| KP-13  | Admin menambah unit dengan kode yang sudah ada                                         | F-06  | Lulus | Gambar A.21      |
| KP-14  | Admin menyimpan unit tanpa memilih kategori                                            | F-06  | Lulus | Gambar A.22      |
| KP-15  | Admin menghapus unit yang sedang disewa                                                | F-06  | Lulus | Gambar A.23–A.24 |
| KP-16  | Admin menghapus unit yang tidak sedang disewa; riwayat sewa lama tetap ada             | F-06  | Lulus | Gambar A.25–A.27 |
| KP-17  | Admin menambah kategori dengan nama yang sudah ada                                     | F-07  | Lulus | Gambar A.28      |
| KP-18  | Admin menghapus kategori yang dipakai beberapa unit                                    | F-07  | Lulus | Gambar A.29–A.31 |
| KP-19  | Admin menambah anggota dengan data lengkap                                             | F-08  | Lulus | Gambar A.32–A.34 |
| KP-20  | Admin mengubah data anggota dengan password dikosongkan                                | F-08  | Lulus | Gambar A.35–A.37 |
| KP-21  | Admin menghapus anggota yang masih punya sewa aktif                                    | F-08  | Lulus | Gambar A.38–A.39 |
| KP-22  | Anggota dengan 0 atau 1 sewa aktif menyewa unit tersedia                               | F-09  | Lulus | Gambar A.40–A.42 |
| KP-23  | Anggota dengan 2 sewa aktif mencoba menyewa lagi                                       | F-09  | Lulus | Gambar A.43      |
| KP-24  | Anggota mencoba menyewa unit berstatus disewa                                          | F-09  | Lulus | Gambar A.44–A.45 |
| KP-25  | Admin membuka detail unit: tombol Sewa tidak tampil dan server menolak sewa dari Admin | F-09  | Lulus | Gambar A.46–A.47 |
| KP-26  | Sewa Saya hanya menampilkan sewa milik sendiri                                         | F-10  | Lulus | Gambar A.48–A.49 |
| KP-27  | Admin membuka Sewa Aktif: semua sewa berstatus disewa dari semua anggota               | F-11  | Lulus | Gambar A.50      |
| KP-28  | Admin memproses pengembalian sebelum atau tepat di jatuh tempo                         | F-12  | Lulus | Gambar A.51–A.54 |
| KP-29  | Admin memproses pengembalian 2 hari setelah jatuh tempo                                | F-12  | Lulus | Gambar A.55–A.57 |
| KP-30  | Admin memproses sewa yang sudah dikembalikan                                           | F-12  | Lulus | Gambar A.58–A.59 |
| KP-31  | Anggota mencoba memproses pengembalian lewat URL                                       | F-12  | Lulus | Gambar A.60      |
| KP-32  | Admin membuka riwayat sewa seorang anggota                                             | F-13  | Lulus | Gambar A.61–A.62 |
| KP-33  | Admin menekan Cetak: tampilan cetak tanpa menu navigasi dan siap disimpan sebagai PDF  | F-13  | Lulus | Gambar A.63–A.65 |

**B. Fitur pendukung (login, logout, profil, fitur Must lain)**

| Kode  | Skenario                                                                                          | Fitur | Hasil | Bukti            |
|-------|---------------------------------------------------------------------------------------------------|-------|-------|------------------|
| F-02b | Logout mengakhiri sesi                                                                            | F-02  | Lulus | Gambar A.66      |
| F-06b | Admin mengubah data unit dan kategorinya                                                          | F-06  | Lulus | Gambar A.67–A.68 |
| F-06c | Admin menyimpan perubahan unit dengan kode milik unit lain                                        | F-06  | Lulus | Gambar A.69      |
| F-07b | Admin menambah dan mengubah kategori                                                              | F-07  | Lulus | Gambar A.70–A.71 |
| F-08b | Admin menghapus anggota tanpa sewa aktif; riwayat sewanya tetap utuh                              | F-08  | Lulus | Gambar A.72–A.73 |
| F-16  | README memuat nama kelompok, nama tim, nama project, fitur, gambar skema database, dan video demo | F-16  | Lulus | Gambar A.74      |

**C. Fitur Should / Could**

| Kode | Skenario                                                               | Fitur      | Hasil | Bukti            |
|------|------------------------------------------------------------------------|------------|-------|------------------|
| S-02 | Dialog konfirmasi sebelum menghapus                                    | S-02       | Lulus | Gambar A.75–A.77 |
| S-03 | Paginasi daftar                                                        | S-03       | Lulus | Gambar A.78–A.79 |
| S-05 | Dashboard menampilkan sisa kuota (Anggota) dan angka ringkasan (Admin) | S-05, C-03 | Lulus | Gambar A.80–A.81 |

**D. Validasi field per form (F-14, NFR-03)**

| Kode    | Skenario                                                | Fitur      | Hasil | Bukti            |
|---------|---------------------------------------------------------|------------|-------|------------------|
| VAL-01  | Validasi form Registrasi                                | F-14, F-01 | Lulus | Gambar A.82–A.85 |
| VAL-02  | Validasi form Login                                     | F-14, F-02 | Lulus | Gambar A.86–A.87 |
| VAL-03  | Validasi form Profil                                    | F-14, F-03 | Lulus | Gambar A.88–A.89 |
| VAL-04  | Validasi form Unit                                      | F-14, F-06 | Lulus | Gambar A.90–A.92 |
| VAL-05  | Validasi form Kategori                                  | F-14, F-07 | Lulus | Gambar A.93–A.94 |
| VAL-06  | Validasi form Anggota (Admin)                           | F-14, F-08 | Lulus | Gambar A.95–A.96 |
| VAL-06b | Validasi ubah anggota: email milik anggota lain ditolak | F-14, F-08 | Lulus | Gambar A.97      |
| VAL-07  | Validasi kata kunci pencarian maksimal 100 karakter     | F-14, F-05 | Lulus | Gambar A.98      |

**E. Kondisi tampilan (PRD bagian 8)**

| Kode  | Skenario                                                                                     | Fitur          | Hasil | Bukti              |
|-------|----------------------------------------------------------------------------------------------|----------------|-------|--------------------|
| KT-01 | Sewa Saya kosong untuk anggota yang belum pernah menyewa                                     | F-10           | Lulus | Gambar A.99–A.100  |
| KT-02 | Sewa Aktif kosong setelah semua unit dikembalikan                                            | F-11           | Lulus | Gambar A.101       |
| KT-03 | Riwayat anggota kosong: tombol Cetak nonaktif                                                | F-13           | Lulus | Gambar A.102       |
| KT-04 | Daftar unit kosong                                                                           | F-04           | Lulus | Gambar A.103–A.104 |
| KT-05 | Halaman 404 untuk data yang tidak ada                                                        | PRD halaman 14 | Lulus | Gambar A.105–A.106 |
| KT-06 | Tombol kirim nonaktif dan berlabel “Memproses…” sehingga klik ganda tidak membuat sewa ganda | S-01           | Lulus | Gambar A.107–A.108 |

**F. Tujuan bisnis (BRD bagian 3)**

| Kode  | Skenario                                                                 | Fitur | Hasil | Bukti              |
|-------|--------------------------------------------------------------------------|-------|-------|--------------------|
| TB-03 | Denda sistem sama dengan hitungan manual di semua skenario keterlambatan | F-12  | Lulus | Gambar A.109–A.112 |

**G. Kebutuhan non-fungsional (BRD bagian 6)**

| Kode   | Skenario                                                                          | Fitur        | Hasil | Bukti              |
|--------|-----------------------------------------------------------------------------------|--------------|-------|--------------------|
| NFR-01 | Password disimpan sebagai hash; halaman dilindungi login dan peran di server      | NFR-01       | Lulus | Gambar A.113       |
| NFR-02 | Permintaan sewa beruntun tidak bisa melewati kuota 2 unit                         | NFR-02       | Lulus | Gambar A.114–A.115 |
| NFR-04 | Tampilan dapat dipakai di layar HP (lebar 390 px) tanpa scroll horizontal halaman | NFR-04, S-04 | Lulus | Gambar A.116–A.122 |
| NFR-07 | Tarif denda dan durasi sewa dibaca dari konfigurasi, bukan ditulis di kode        | NFR-07       | Lulus | Gambar A.123       |
| NFR-08 | migrate:fresh –seed membangun ulang database dengan data seed sesuai PRD          | NFR-08, F-15 | Lulus | Gambar A.124       |

**H. Integritas data (Flowchart & ERD bagian 9)**

| Kode   | Skenario                                                                                 | Fitur          | Hasil | Bukti              |
|--------|------------------------------------------------------------------------------------------|----------------|-------|--------------------|
| INT-01 | Kode unit dan email unik di level database, termasuk bekas data terhapus                 | INT-01         | Lulus | Gambar A.125–A.126 |
| INT-02 | Satu akun satu profil dan pasangan unit-kategori tidak ganda                             | INT-02, INT-03 | Lulus | Gambar A.127       |
| INT-03 | Foreign key rentals bersifat restrict: akun/unit dengan sewa tidak bisa dihapus permanen | INT-07, INT-08 | Lulus | Gambar A.128       |

## 4. Riwayat Temuan & Tindak Lanjut

Putaran uji sebelumnya (22 Oktober 2026) menemukan lima masalah. Seluruhnya telah diperbaiki pada 23 Oktober 2026 dan diverifikasi ulang dengan tes otomatis; tidak ada temuan terbuka.

| Kode      | Temuan                                                                                               | Tingkat | Perbaikan                                                                                                      | Verifikasi                               | Status     |
|-----------|------------------------------------------------------------------------------------------------------|---------|----------------------------------------------------------------------------------------------------------------|------------------------------------------|------------|
| TEMUAN-01 | Admin dapat menghapus atau mengubah akun Admin lewat endpoint kelola anggota (`/admin/members/{id}`) | Sedang  | Endpoint ubah, hapus, riwayat, dan cetak riwayat anggota mengembalikan 404 jika akun yang dituju bukan anggota | 5 tes PHPUnit                            | Diperbaiki |
| TEMUAN-02 | Simpan kategori tanpa field deskripsi menghasilkan error 500                                         | Rendah  | Deskripsi kosong disimpan sebagai `null`                                                                       | Tes PHPUnit kategori tanpa deskripsi     | Diperbaiki |
| TEMUAN-03 | Kata kunci pencarian lebih dari 100 karakter tetap diterima                                          | Rendah  | Validasi maksimal 100 karakter di server dengan pesan Bahasa Indonesia                                         | VAL-07 dan 2 tes PHPUnit                 | Diperbaiki |
| TEMUAN-04 | Teks pesan KP-02, KP-14, KP-17 (serta KP-21, KP-24, KP-30) berbeda dari PRD                          | Rendah  | Pesan disesuaikan dengan teks PRD                                                                              | KP-02, KP-14, KP-17, KP-21, KP-24, KP-30 | Diperbaiki |
| TEMUAN-05 | README belum memuat identitas tim dan video demo                                                     | Rendah  | README dilengkapi identitas tim dan bagian video demo                                                          | F-16                                     | Diperbaiki |

## 5. Persetujuan UAT

Dengan menandatangani tabel ini, tim menyatakan XenoRent diterima sesuai hasil pengujian di atas.

| Nama                    | Peran                | Keputusan | Tanggal         | Tanda tangan |
|-------------------------|----------------------|-----------|-----------------|--------------|
| Muhammad Fazly Radithya | Tester / Dokumentasi | Diterima  | 23 Oktober 2026 |              |
| Stevy Reuben Januardi   | Tester / Backend     | Diterima  | 23 Oktober 2026 |              |
| Muhammad Nadhif Aqila   | Tester / Frontend    | Diterima  | 23 Oktober 2026 |              |

## 6. Ringkasan Keputusan

Lima keputusan berikut menjadi acuan untuk Manual Book dan Dokumentasi Project.

1.  **XenoRent diterima:** 66 dari 66 skenario end-to-end dan 56 dari 56 tes backend lulus.
2.  **Seluruh temuan sudah ditutup:** TEMUAN-01 s.d. TEMUAN-05 diperbaiki dan diverifikasi ulang.
3.  **Bukti UAT adalah screenshot otomatis Playwright** di Lampiran A, tersimpan di `docs/uat/screenshots/`.
4.  **Kode skenario mengikuti BRD, Flowchart & ERD, dan PRD**, sehingga setiap hasil dapat dilacak ke kebutuhannya.
5.  **Setiap perubahan kode** wajib diikuti `npm run uat` dan `php artisan test` ulang sebelum dirilis.

## Lampiran A — Bukti Screenshot

Lampiran ini memuat 128 screenshot bukti dari 66 skenario, diambil otomatis oleh Playwright. Langkah uji dan hasil yang diharapkan untuk setiap skenario mengacu ke PRD dan laporan Playwright (`docs/uat/Hasil-UAT.md`); file asli tersimpan di `docs/uat/screenshots/`.

### A. Kriteria Penerimaan (KP-01 s.d. KP-33)

**KP-01 — Registrasi anggota dengan data valid**

![Gambar A.1. KP-01 — Form registrasi terisi data valid](uat/screenshots/KP-01_01_form-registrasi-terisi-data-valid.png)

*Gambar A.1. KP-01 — Form registrasi terisi data valid*

![Gambar A.2. KP-01 — Dashboard anggota setelah registrasi, sisa kuota 2 dari 2](uat/screenshots/KP-01_02_dashboard-anggota-setelah-registrasi-sisa-kuota-2-dari-2.png)

*Gambar A.2. KP-01 — Dashboard anggota setelah registrasi, sisa kuota 2 dari 2*

**KP-02 — Registrasi dengan email yang sudah dipakai**

![Gambar A.3. KP-02 — Registrasi ditolak karena email sudah dipakai, isian lain tetap terisi](uat/screenshots/KP-02_01_registrasi-ditolak-karena-email-sudah-dipakai-isian-lain-tetap-terisi.png)

*Gambar A.3. KP-02 — Registrasi ditolak karena email sudah dipakai, isian lain tetap terisi*

**KP-03 — Login dengan email dan password benar**

![Gambar A.4. KP-03 — Form login diisi akun Admin](uat/screenshots/KP-03_01_form-login-diisi-akun-admin.png)

*Gambar A.4. KP-03 — Form login diisi akun Admin*

![Gambar A.5. KP-03 — Dashboard Admin setelah login](uat/screenshots/KP-03_02_dashboard-admin-setelah-login.png)

*Gambar A.5. KP-03 — Dashboard Admin setelah login*

![Gambar A.6. KP-03 — Dashboard Anggota (Rina) setelah login](uat/screenshots/KP-03_03_dashboard-anggota-rina-setelah-login.png)

*Gambar A.6. KP-03 — Dashboard Anggota (Rina) setelah login*

**KP-04 — Login dengan password salah**

![Gambar A.7. KP-04 — Login gagal: pesan tampil dan kolom password kosong kembali](uat/screenshots/KP-04_01_login-gagal-pesan-tampil-dan-kolom-password-kosong-kembali.png)

*Gambar A.7. KP-04 — Login gagal: pesan tampil dan kolom password kosong kembali*

**KP-05 — Tamu membuka halaman selain login/registrasi**

![Gambar A.8. KP-05 — Tamu membuka /dashboard diarahkan ke login](uat/screenshots/KP-05_01_tamu-membuka-dashboard-diarahkan-ke-login.png)

*Gambar A.8. KP-05 — Tamu membuka /dashboard diarahkan ke login*

![Gambar A.9. KP-05 — Tamu membuka /admin/units diarahkan ke login](uat/screenshots/KP-05_02_tamu-membuka-admin-units-diarahkan-ke-login.png)

*Gambar A.9. KP-05 — Tamu membuka /admin/units diarahkan ke login*

**KP-06 — Anggota membuka halaman Admin**

![Gambar A.10. KP-06 — Anggota membuka /admin/units: halaman Akses ditolak (403)](uat/screenshots/KP-06_01_anggota-membuka-admin-units-halaman-akses-ditolak-403.png)

*Gambar A.10. KP-06 — Anggota membuka /admin/units: halaman Akses ditolak (403)*

**KP-07 — Pengguna mengubah telepon dan alamat profil sendiri**

![Gambar A.11. KP-07 — Halaman profil Rina sebelum diubah](uat/screenshots/KP-07_01_halaman-profil-rina-sebelum-diubah.png)

*Gambar A.11. KP-07 — Halaman profil Rina sebelum diubah*

![Gambar A.12. KP-07 — Profil Rina tersimpan dengan pesan sukses](uat/screenshots/KP-07_02_profil-rina-tersimpan-dengan-pesan-sukses.png)

*Gambar A.12. KP-07 — Profil Rina tersimpan dengan pesan sukses*

**KP-07b — Profil yang diubah adalah milik akun yang sedang login (ID dari sesi, bukan dari form)**

![Gambar A.13. KP-07b — Profil Rina diubah dengan field user_id Bima disisipkan](uat/screenshots/KP-07b_01_profil-rina-diubah-dengan-field-user-id-bima-disisipkan.png)

*Gambar A.13. KP-07b — Profil Rina diubah dengan field user_id Bima disisipkan*

**KP-08 — Daftar unit menampilkan kode, nama, kategori, dan status**

<img src="uat/screenshots/KP-08_01_daftar-unit-10-unit-lengkap-dengan-kode-nama-kategori-dan-status.png" width="320" alt="Gambar A.14. KP-08 — Daftar unit: 10 unit lengkap dengan kode, nama, kategori, dan status">

*Gambar A.14. KP-08 — Daftar unit: 10 unit lengkap dengan kode, nama, kategori, dan status*

**KP-09 — Tiga unit bernama “Zeta Saucer” tampil terpisah dengan kode berbeda**

<img src="uat/screenshots/KP-09_01_tiga-zeta-saucer-dengan-kode-unik-berbeda.png" width="320" alt="Gambar A.15. KP-09 — Tiga Zeta Saucer dengan kode unik berbeda">

*Gambar A.15. KP-09 — Tiga Zeta Saucer dengan kode unik berbeda*

**KP-10 — Pencarian “zeta” menemukan unit tanpa membedakan huruf besar-kecil**

![Gambar A.16. KP-10 — Hasil pencarian “zeta”: tiga Zeta Saucer](uat/screenshots/KP-10_01_hasil-pencarian-zeta-tiga-zeta-saucer.png)

*Gambar A.16. KP-10 — Hasil pencarian “zeta”: tiga Zeta Saucer*

![Gambar A.17. KP-10 — Hasil pencarian “ZETA” (huruf besar) sama dengan “zeta”](uat/screenshots/KP-10_02_hasil-pencarian-zeta-huruf-besar-sama-dengan-zeta.png)

*Gambar A.17. KP-10 — Hasil pencarian “ZETA” (huruf besar) sama dengan “zeta”*

**KP-11 — Pencarian tanpa hasil menampilkan pesan “Unit tidak ditemukan”**

![Gambar A.18. KP-11 — Pencarian “xyzabc”: pesan Unit tidak ditemukan dengan tautan Reset](uat/screenshots/KP-11_01_pencarian-xyzabc-pesan-unit-tidak-ditemukan-dengan-tautan-reset.png)

*Gambar A.18. KP-11 — Pencarian “xyzabc”: pesan Unit tidak ditemukan dengan tautan Reset*

**KP-12 — Admin menambah unit dengan kode baru dan 2 kategori**

<img src="uat/screenshots/KP-12_01_form-tambah-unit-terisi-kode-zts-004-2-kategori.png" width="320" alt="Gambar A.19. KP-12 — Form tambah unit terisi (kode ZTS-004, 2 kategori)">

*Gambar A.19. KP-12 — Form tambah unit terisi (kode ZTS-004, 2 kategori)*

<img src="uat/screenshots/KP-12_02_unit-zts-004-tersimpan-tersedia-2-kategori.png" width="320" alt="Gambar A.20. KP-12 — Unit ZTS-004 tersimpan: tersedia, 2 kategori">

*Gambar A.20. KP-12 — Unit ZTS-004 tersimpan: tersedia, 2 kategori*

**KP-13 — Admin menambah unit dengan kode yang sudah ada**

<img src="uat/screenshots/KP-13_01_kode-zts-001-ditolak-karena-sudah-dipakai-isian-tetap-terisi.png" width="320" alt="Gambar A.21. KP-13 — Kode ZTS-001 ditolak karena sudah dipakai, isian tetap terisi">

*Gambar A.21. KP-13 — Kode ZTS-001 ditolak karena sudah dipakai, isian tetap terisi*

**KP-14 — Admin menyimpan unit tanpa memilih kategori**

<img src="uat/screenshots/KP-14_01_unit-tanpa-kategori-ditolak-isian-lain-tetap-terisi.png" width="320" alt="Gambar A.22. KP-14 — Unit tanpa kategori ditolak, isian lain tetap terisi">

*Gambar A.22. KP-14 — Unit tanpa kategori ditolak, isian lain tetap terisi*

**KP-15 — Admin menghapus unit yang sedang disewa**

<img src="uat/screenshots/KP-15_01_zts-001-sedang-disewa-tombol-hapus-unit-tidak-tersedia.png" width="320" alt="Gambar A.23. KP-15 — ZTS-001 sedang disewa: tombol Hapus unit tidak tersedia">

*Gambar A.23. KP-15 — ZTS-001 sedang disewa: tombol Hapus unit tidak tersedia*

<img src="uat/screenshots/KP-15_02_penghapusan-dipaksa-lewat-url-ditolak-server.png" width="320" alt="Gambar A.24. KP-15 — Penghapusan dipaksa lewat URL ditolak server">

*Gambar A.24. KP-15 — Penghapusan dipaksa lewat URL ditolak server*

**KP-16 — Admin menghapus unit yang tidak sedang disewa; riwayat sewa lama tetap ada**

<img src="uat/screenshots/KP-16_01_panel-ubah-unit-kph-001-terbuka-dengan-tombol-hapus-unit.png" width="320" alt="Gambar A.25. KP-16 — Panel ubah unit KPH-001 terbuka dengan tombol Hapus unit">

*Gambar A.25. KP-16 — Panel ubah unit KPH-001 terbuka dengan tombol Hapus unit*

<img src="uat/screenshots/KP-16_02_kph-001-terhapus-dari-daftar-dengan-pesan-sukses.png" width="320" alt="Gambar A.26. KP-16 — KPH-001 terhapus dari daftar dengan pesan sukses">

*Gambar A.26. KP-16 — KPH-001 terhapus dari daftar dengan pesan sukses*

![Gambar A.27. KP-16 — Riwayat sewa Bima tetap utuh setelah unit KPH-001 dihapus](uat/screenshots/KP-16_03_riwayat-sewa-bima-tetap-utuh-setelah-unit-kph-001-dihapus.png)

*Gambar A.27. KP-16 — Riwayat sewa Bima tetap utuh setelah unit KPH-001 dihapus*

**KP-17 — Admin menambah kategori dengan nama yang sudah ada**

\<img src=“uat/screenshots/KP-17_01_kategori-kargo-ditolak-karena-nama-sudah-dipakai.png” width=“320” alt=“Gambar A.28. KP-17 — Kategori”Kargo” ditolak karena nama sudah dipakai”\>

*Gambar A.28. KP-17 — Kategori “Kargo” ditolak karena nama sudah dipakai*

**KP-18 — Admin menghapus kategori yang dipakai beberapa unit**

<img src="uat/screenshots/KP-18_01_kategori-penumpang-4-unit-terhubung-sebelum-dihapus.png" width="320" alt="Gambar A.29. KP-18 — Kategori Penumpang (4 unit terhubung) sebelum dihapus">

*Gambar A.29. KP-18 — Kategori Penumpang (4 unit terhubung) sebelum dihapus*

<img src="uat/screenshots/KP-18_02_kategori-penumpang-terhapus-dengan-pesan-sukses.png" width="320" alt="Gambar A.30. KP-18 — Kategori Penumpang terhapus dengan pesan sukses">

*Gambar A.30. KP-18 — Kategori Penumpang terhapus dengan pesan sukses*

<img src="uat/screenshots/KP-18_03_seluruh-unit-tetap-ada-tag-penumpang-lepas-dari-unit.png" width="320" alt="Gambar A.31. KP-18 — Seluruh unit tetap ada; tag Penumpang lepas dari unit">

*Gambar A.31. KP-18 — Seluruh unit tetap ada; tag Penumpang lepas dari unit*

**KP-19 — Admin menambah anggota dengan data lengkap**

<img src="uat/screenshots/KP-19_01_form-tambah-anggota-terisi-lengkap.png" width="320" alt="Gambar A.32. KP-19 — Form tambah anggota terisi lengkap">

*Gambar A.32. KP-19 — Form tambah anggota terisi lengkap*

<img src="uat/screenshots/KP-19_02_anggota-baru-tampil-di-daftar-dengan-pesan-sukses.png" width="320" alt="Gambar A.33. KP-19 — Anggota baru tampil di daftar dengan pesan sukses">

*Gambar A.33. KP-19 — Anggota baru tampil di daftar dengan pesan sukses*

![Gambar A.34. KP-19 — Anggota baru berhasil login ke Dashboard Anggota](uat/screenshots/KP-19_03_anggota-baru-berhasil-login-ke-dashboard-anggota.png)

*Gambar A.34. KP-19 — Anggota baru berhasil login ke Dashboard Anggota*

**KP-20 — Admin mengubah data anggota dengan password dikosongkan**

<img src="uat/screenshots/KP-20_01_form-ubah-anggota-bima-kolom-password-dikosongkan.png" width="320" alt="Gambar A.35. KP-20 — Form ubah anggota Bima, kolom password dikosongkan">

*Gambar A.35. KP-20 — Form ubah anggota Bima, kolom password dikosongkan*

<img src="uat/screenshots/KP-20_02_data-bima-tersimpan-dengan-pesan-sukses.png" width="320" alt="Gambar A.36. KP-20 — Data Bima tersimpan dengan pesan sukses">

*Gambar A.36. KP-20 — Data Bima tersimpan dengan pesan sukses*

![Gambar A.37. KP-20 — Bima masih bisa login dengan password lama](uat/screenshots/KP-20_03_bima-masih-bisa-login-dengan-password-lama.png)

*Gambar A.37. KP-20 — Bima masih bisa login dengan password lama*

**KP-21 — Admin menghapus anggota yang masih punya sewa aktif**

<img src="uat/screenshots/KP-21_01_rina-punya-sewa-aktif-tombol-hapus-akun-tidak-tersedia.png" width="320" alt="Gambar A.38. KP-21 — Rina punya sewa aktif: tombol Hapus akun tidak tersedia">

*Gambar A.38. KP-21 — Rina punya sewa aktif: tombol Hapus akun tidak tersedia*

<img src="uat/screenshots/KP-21_02_penghapusan-dipaksa-lewat-url-ditolak-server.png" width="320" alt="Gambar A.39. KP-21 — Penghapusan dipaksa lewat URL ditolak server">

*Gambar A.39. KP-21 — Penghapusan dipaksa lewat URL ditolak server*

**KP-22 — Anggota dengan 0 atau 1 sewa aktif menyewa unit tersedia**

![Gambar A.40. KP-22 — Detail unit ZTS-002 berstatus tersedia dengan tombol Sewa kapal](uat/screenshots/KP-22_01_detail-unit-zts-002-berstatus-tersedia-dengan-tombol-sewa-kapal.png)

*Gambar A.40. KP-22 — Detail unit ZTS-002 berstatus tersedia dengan tombol Sewa kapal*

![Gambar A.41. KP-22 — Sewa berhasil: pesan sukses memuat tanggal jatuh tempo (hari ini + 5)](uat/screenshots/KP-22_02_sewa-berhasil-pesan-sukses-memuat-tanggal-jatuh-tempo-hari-ini-5.png)

*Gambar A.41. KP-22 — Sewa berhasil: pesan sukses memuat tanggal jatuh tempo (hari ini + 5)*

![Gambar A.42. KP-22 — Sewa Saya Bima: dua sewa aktif lengkap dengan jatuh tempo](uat/screenshots/KP-22_03_sewa-saya-bima-dua-sewa-aktif-lengkap-dengan-jatuh-tempo.png)

*Gambar A.42. KP-22 — Sewa Saya Bima: dua sewa aktif lengkap dengan jatuh tempo*

**KP-23 — Anggota dengan 2 sewa aktif mencoba menyewa lagi**

![Gambar A.43. KP-23 — Sewa ke-3 ditolak: kuota sewa penuh](uat/screenshots/KP-23_01_sewa-ke-3-ditolak-kuota-sewa-penuh.png)

*Gambar A.43. KP-23 — Sewa ke-3 ditolak: kuota sewa penuh*

**KP-24 — Anggota mencoba menyewa unit berstatus disewa**

![Gambar A.44. KP-24 — Detail ZTS-001: tombol Sewa tidak tersedia, tertulis Unit sedang disewa](uat/screenshots/KP-24_01_detail-zts-001-tombol-sewa-tidak-tersedia-tertulis-unit-sedang-disewa.png)

*Gambar A.44. KP-24 — Detail ZTS-001: tombol Sewa tidak tersedia, tertulis Unit sedang disewa*

![Gambar A.45. KP-24 — Penyewaan dipaksa lewat URL ditolak server](uat/screenshots/KP-24_02_penyewaan-dipaksa-lewat-url-ditolak-server.png)

*Gambar A.45. KP-24 — Penyewaan dipaksa lewat URL ditolak server*

**KP-25 — Admin membuka detail unit: tombol Sewa tidak tampil dan server menolak sewa dari Admin**

![Gambar A.46. KP-25 — Detail unit tersedia dilihat Admin: tidak ada tombol Sewa](uat/screenshots/KP-25_01_detail-unit-tersedia-dilihat-admin-tidak-ada-tombol-sewa.png)

*Gambar A.46. KP-25 — Detail unit tersedia dilihat Admin: tidak ada tombol Sewa*

![Gambar A.47. KP-25 — Penyewaan dipaksa oleh Admin ditolak (403)](uat/screenshots/KP-25_02_penyewaan-dipaksa-oleh-admin-ditolak-403.png)

*Gambar A.47. KP-25 — Penyewaan dipaksa oleh Admin ditolak (403)*

**KP-26 — Sewa Saya hanya menampilkan sewa milik sendiri**

![Gambar A.48. KP-26 — Sewa Saya Rina: hanya 2 sewa aktif miliknya](uat/screenshots/KP-26_01_sewa-saya-rina-hanya-2-sewa-aktif-miliknya.png)

*Gambar A.48. KP-26 — Sewa Saya Rina: hanya 2 sewa aktif miliknya*

![Gambar A.49. KP-26 — Sewa Saya Bima: hanya sewa KPH-001 yang selesai beserta denda Rp20.000](uat/screenshots/KP-26_02_sewa-saya-bima-hanya-sewa-kph-001-yang-selesai-beserta-denda-rp20-000.png)

*Gambar A.49. KP-26 — Sewa Saya Bima: hanya sewa KPH-001 yang selesai beserta denda Rp20.000*

**KP-27 — Admin membuka Sewa Aktif: semua sewa berstatus disewa dari semua anggota**

![Gambar A.50. KP-27 — Sewa Aktif Admin: 3 sewa dari 2 anggota, NBS-001 bertanda Terlambat](uat/screenshots/KP-27_01_sewa-aktif-admin-3-sewa-dari-2-anggota-nbs-001-bertanda-terlambat.png)

*Gambar A.50. KP-27 — Sewa Aktif Admin: 3 sewa dari 2 anggota, NBS-001 bertanda Terlambat*

**KP-28 — Admin memproses pengembalian sebelum atau tepat di jatuh tempo**

![Gambar A.51. KP-28 — Sewa Aktif sebelum pengembalian](uat/screenshots/KP-28_01_sewa-aktif-sebelum-pengembalian.png)

*Gambar A.51. KP-28 — Sewa Aktif sebelum pengembalian*

![Gambar A.52. KP-28 — Pengembalian sebelum jatuh tempo: Tidak ada denda](uat/screenshots/KP-28_02_pengembalian-sebelum-jatuh-tempo-tidak-ada-denda.png)

*Gambar A.52. KP-28 — Pengembalian sebelum jatuh tempo: Tidak ada denda*

![Gambar A.53. KP-28 — Pengembalian tepat di hari jatuh tempo: Tidak ada denda](uat/screenshots/KP-28_03_pengembalian-tepat-di-hari-jatuh-tempo-tidak-ada-denda.png)

*Gambar A.53. KP-28 — Pengembalian tepat di hari jatuh tempo: Tidak ada denda*

![Gambar A.54. KP-28 — Sewa Saya Rina: ZTS-001 dikembalikan, denda Rp0, kuota pulih menjadi 1 dari 2](uat/screenshots/KP-28_04_sewa-saya-rina-zts-001-dikembalikan-denda-rp0-kuota-pulih-menjadi-1-da.png)

*Gambar A.54. KP-28 — Sewa Saya Rina: ZTS-001 dikembalikan, denda Rp0, kuota pulih menjadi 1 dari 2*

**KP-29 — Admin memproses pengembalian 2 hari setelah jatuh tempo**

![Gambar A.55. KP-29 — Sewa ZTS-002 Bima jatuh tempo 2 hari lalu, bertanda Terlambat](uat/screenshots/KP-29_01_sewa-zts-002-bima-jatuh-tempo-2-hari-lalu-bertanda-terlambat.png)

*Gambar A.55. KP-29 — Sewa ZTS-002 Bima jatuh tempo 2 hari lalu, bertanda Terlambat*

![Gambar A.56. KP-29 — Pengembalian diproses dengan denda Rp20.000 (2 hari × Rp10.000)](uat/screenshots/KP-29_02_pengembalian-diproses-dengan-denda-rp20-000-2-hari-rp10-000.png)

*Gambar A.56. KP-29 — Pengembalian diproses dengan denda Rp20.000 (2 hari × Rp10.000)*

![Gambar A.57. KP-29 — Sewa Saya Bima menampilkan denda Rp20.000](uat/screenshots/KP-29_03_sewa-saya-bima-menampilkan-denda-rp20-000.png)

*Gambar A.57. KP-29 — Sewa Saya Bima menampilkan denda Rp20.000*

**KP-30 — Admin memproses sewa yang sudah dikembalikan**

![Gambar A.58. KP-30 — Tab kedua masih menampilkan ZTS-001 (data lama)](uat/screenshots/KP-30_01_tab-kedua-masih-menampilkan-zts-001-data-lama.png)

*Gambar A.58. KP-30 — Tab kedua masih menampilkan ZTS-001 (data lama)*

![Gambar A.59. KP-30 — Pemrosesan kedua ditolak](uat/screenshots/KP-30_02_pemrosesan-kedua-ditolak.png)

*Gambar A.59. KP-30 — Pemrosesan kedua ditolak*

**KP-31 — Anggota mencoba memproses pengembalian lewat URL**

![Gambar A.60. KP-31 — Anggota memaksa proses kembali: halaman Akses ditolak (403)](uat/screenshots/KP-31_01_anggota-memaksa-proses-kembali-halaman-akses-ditolak-403.png)

*Gambar A.60. KP-31 — Anggota memaksa proses kembali: halaman Akses ditolak (403)*

**KP-32 — Admin membuka riwayat sewa seorang anggota**

![Gambar A.61. KP-32 — Riwayat sewa Bima: tanggal sewa, jatuh tempo, tanggal kembali, dan denda](uat/screenshots/KP-32_01_riwayat-sewa-bima-tanggal-sewa-jatuh-tempo-tanggal-kembali-dan-denda.png)

*Gambar A.61. KP-32 — Riwayat sewa Bima: tanggal sewa, jatuh tempo, tanggal kembali, dan denda*

![Gambar A.62. KP-32 — Riwayat sewa Rina: dua sewa aktif](uat/screenshots/KP-32_02_riwayat-sewa-rina-dua-sewa-aktif.png)

*Gambar A.62. KP-32 — Riwayat sewa Rina: dua sewa aktif*

**KP-33 — Admin menekan Cetak: tampilan cetak tanpa menu navigasi dan siap disimpan sebagai PDF**

![Gambar A.63. KP-33 — Halaman riwayat versi cetak di layar (sebelum mode cetak)](uat/screenshots/KP-33_01_halaman-riwayat-versi-cetak-di-layar-sebelum-mode-cetak.png)

*Gambar A.63. KP-33 — Halaman riwayat versi cetak di layar (sebelum mode cetak)*

![Gambar A.64. KP-33 — Tampilan mode cetak: tanpa menu navigasi, footer, dan tombol](uat/screenshots/KP-33_02_tampilan-mode-cetak-tanpa-menu-navigasi-footer-dan-tombol.png)

*Gambar A.64. KP-33 — Tampilan mode cetak: tanpa menu navigasi, footer, dan tombol*

![Gambar A.65. KP-33 — PDF riwayat sewa berhasil dibuat](uat/screenshots/KP-33_03_pdf-riwayat-sewa-berhasil-dibuat.png)

*Gambar A.65. KP-33 — PDF riwayat sewa berhasil dibuat*

### B. Fitur pendukung (login, logout, profil, fitur Must lain)

**F-02b — Logout mengakhiri sesi**

![Gambar A.66. F-02b — Setelah menekan Keluar, pengguna di halaman login](uat/screenshots/F-02b_01_setelah-menekan-keluar-pengguna-di-halaman-login.png)

*Gambar A.66. F-02b — Setelah menekan Keluar, pengguna di halaman login*

**F-06b — Admin mengubah data unit dan kategorinya**

<img src="uat/screenshots/F-06b_01_form-ubah-unit-zts-002-terisi-perubahan.png" width="320" alt="Gambar A.67. F-06b — Form ubah unit ZTS-002 terisi perubahan">

*Gambar A.67. F-06b — Form ubah unit ZTS-002 terisi perubahan*

<img src="uat/screenshots/F-06b_02_zts-002-setelah-diubah.png" width="320" alt="Gambar A.68. F-06b — ZTS-002 setelah diubah">

*Gambar A.68. F-06b — ZTS-002 setelah diubah*

**F-06c — Admin menyimpan perubahan unit dengan kode milik unit lain**

<img src="uat/screenshots/F-06c_01_perubahan-kode-ke-zts-003-ditolak.png" width="320" alt="Gambar A.69. F-06c — Perubahan kode ke ZTS-003 ditolak">

*Gambar A.69. F-06c — Perubahan kode ke ZTS-003 ditolak*

**F-07b — Admin menambah dan mengubah kategori**

<img src="uat/screenshots/F-07b_01_kategori-evakuasi-berhasil-ditambahkan.png" width="320" alt="Gambar A.70. F-07b — Kategori Evakuasi berhasil ditambahkan">

*Gambar A.70. F-07b — Kategori Evakuasi berhasil ditambahkan*

<img src="uat/screenshots/F-07b_02_nama-kategori-berhasil-diubah.png" width="320" alt="Gambar A.71. F-07b — Nama kategori berhasil diubah">

*Gambar A.71. F-07b — Nama kategori berhasil diubah*

**F-08b — Admin menghapus anggota tanpa sewa aktif; riwayat sewanya tetap utuh**

<img src="uat/screenshots/F-08b_01_bima-terhapus-dari-daftar-anggota.png" width="320" alt="Gambar A.72. F-08b — Bima terhapus dari daftar anggota">

*Gambar A.72. F-08b — Bima terhapus dari daftar anggota*

![Gambar A.73. F-08b — Riwayat sewa Bima tetap utuh setelah akunnya dihapus](uat/screenshots/F-08b_02_riwayat-sewa-bima-tetap-utuh-setelah-akunnya-dihapus.png)

*Gambar A.73. F-08b — Riwayat sewa Bima tetap utuh setelah akunnya dihapus*

**F-16 — README memuat nama kelompok, nama tim, nama project, fitur, gambar skema database, dan video demo**

![Gambar A.74. F-16 — Pemeriksaan isi README.md](uat/screenshots/F-16_01_pemeriksaan-isi-readme-md.png)

*Gambar A.74. F-16 — Pemeriksaan isi README.md*

### C. Fitur Should / Could

**S-02 — Dialog konfirmasi sebelum menghapus**

\<img src=“uat/screenshots/S-02_01_dialog-hapus-unit-zts-002-dibatalkan-unit-tetap-ada.png” width=“320” alt=“Gambar A.75. S-02 — Dialog”Hapus unit ZTS-002?” dibatalkan: unit tetap ada”\>

*Gambar A.75. S-02 — Dialog “Hapus unit ZTS-002?” dibatalkan: unit tetap ada*

<img src="uat/screenshots/S-02_02_dialog-disetujui-unit-terhapus.png" width="320" alt="Gambar A.76. S-02 — Dialog disetujui: unit terhapus">

*Gambar A.76. S-02 — Dialog disetujui: unit terhapus*

![Gambar A.77. S-02 — Teks dialog konfirmasi bawaan browser](uat/screenshots/S-02_03_teks-dialog-konfirmasi-bawaan-browser.png)

*Gambar A.77. S-02 — Teks dialog konfirmasi bawaan browser*

**S-03 — Paginasi daftar**

<img src="uat/screenshots/S-03_01_daftar-unit-halaman-1-dengan-navigasi-paginasi.png" width="320" alt="Gambar A.78. S-03 — Daftar unit halaman 1 dengan navigasi paginasi">

*Gambar A.78. S-03 — Daftar unit halaman 1 dengan navigasi paginasi*

<img src="uat/screenshots/S-03_02_daftar-unit-halaman-2.png" width="320" alt="Gambar A.79. S-03 — Daftar unit halaman 2">

*Gambar A.79. S-03 — Daftar unit halaman 2*

**S-05 — Dashboard menampilkan sisa kuota (Anggota) dan angka ringkasan (Admin)**

![Gambar A.80. S-05 — Dashboard Rina: kuota penuh (0 dari 2)](uat/screenshots/S-05_01_dashboard-rina-kuota-penuh-0-dari-2.png)

*Gambar A.80. S-05 — Dashboard Rina: kuota penuh (0 dari 2)*

![Gambar A.81. S-05 — Dashboard Admin: angka ringkasan hanggar](uat/screenshots/S-05_02_dashboard-admin-angka-ringkasan-hanggar.png)

*Gambar A.81. S-05 — Dashboard Admin: angka ringkasan hanggar*

### D. Validasi field per form (F-14, NFR-03)

**VAL-01 — Validasi form Registrasi**

![Gambar A.82. VAL-01 — Form kosong ditahan oleh validasi bawaan browser](uat/screenshots/VAL-01_01_form-kosong-ditahan-oleh-validasi-bawaan-browser.png)

*Gambar A.82. VAL-01 — Form kosong ditahan oleh validasi bawaan browser*

<img src="uat/screenshots/VAL-01_02_validasi-server-semua-field-wajib-menampilkan-pesan-di-bawah-field.png" width="320" alt="Gambar A.83. VAL-01 — Validasi server: semua field wajib menampilkan pesan di bawah field">

*Gambar A.83. VAL-01 — Validasi server: semua field wajib menampilkan pesan di bawah field*

![Gambar A.84. VAL-01 — Data tidak valid: pesan per field, alamat tetap terisi](uat/screenshots/VAL-01_03_data-tidak-valid-pesan-per-field-alamat-tetap-terisi.png)

*Gambar A.84. VAL-01 — Data tidak valid: pesan per field, alamat tetap terisi*

![Gambar A.85. VAL-01 — Konfirmasi password berbeda ditolak, isian lain tetap terisi](uat/screenshots/VAL-01_04_konfirmasi-password-berbeda-ditolak-isian-lain-tetap-terisi.png)

*Gambar A.85. VAL-01 — Konfirmasi password berbeda ditolak, isian lain tetap terisi*

**VAL-02 — Validasi form Login**

![Gambar A.86. VAL-02 — Login kosong: email dan password wajib diisi](uat/screenshots/VAL-02_01_login-kosong-email-dan-password-wajib-diisi.png)

*Gambar A.86. VAL-02 — Login kosong: email dan password wajib diisi*

![Gambar A.87. VAL-02 — Email berformat salah ditolak](uat/screenshots/VAL-02_02_email-berformat-salah-ditolak.png)

*Gambar A.87. VAL-02 — Email berformat salah ditolak*

**VAL-03 — Validasi form Profil**

<img src="uat/screenshots/VAL-03_01_profil-dikosongkan-semua-field-wajib-menampilkan-pesan.png" width="320" alt="Gambar A.88. VAL-03 — Profil dikosongkan: semua field wajib menampilkan pesan">

*Gambar A.88. VAL-03 — Profil dikosongkan: semua field wajib menampilkan pesan*

![Gambar A.89. VAL-03 — Email milik akun lain dan telepon non-angka ditolak](uat/screenshots/VAL-03_02_email-milik-akun-lain-dan-telepon-non-angka-ditolak.png)

*Gambar A.89. VAL-03 — Email milik akun lain dan telepon non-angka ditolak*

**VAL-04 — Validasi form Unit**

<img src="uat/screenshots/VAL-04_01_form-unit-kosong-kode-nama-dan-kategori-wajib.png" width="320" alt="Gambar A.90. VAL-04 — Form unit kosong: kode, nama, dan kategori wajib">

*Gambar A.90. VAL-04 — Form unit kosong: kode, nama, dan kategori wajib*

<img src="uat/screenshots/VAL-04_02_kode-berisi-simbol-dan-deskripsi-terlalu-panjang-ditolak.png" width="320" alt="Gambar A.91. VAL-04 — Kode berisi simbol dan deskripsi terlalu panjang ditolak">

*Gambar A.91. VAL-04 — Kode berisi simbol dan deskripsi terlalu panjang ditolak*

<img src="uat/screenshots/VAL-04_03_kode-21-karakter-ditolak.png" width="320" alt="Gambar A.92. VAL-04 — Kode 21 karakter ditolak">

*Gambar A.92. VAL-04 — Kode 21 karakter ditolak*

**VAL-05 — Validasi form Kategori**

<img src="uat/screenshots/VAL-05_01_form-kategori-kosong-nama-wajib-diisi.png" width="320" alt="Gambar A.93. VAL-05 — Form kategori kosong: nama wajib diisi">

*Gambar A.93. VAL-05 — Form kategori kosong: nama wajib diisi*

<img src="uat/screenshots/VAL-05_02_nama-51-karakter-dan-deskripsi-501-karakter-ditolak.png" width="320" alt="Gambar A.94. VAL-05 — Nama 51 karakter dan deskripsi 501 karakter ditolak">

*Gambar A.94. VAL-05 — Nama 51 karakter dan deskripsi 501 karakter ditolak*

**VAL-06 — Validasi form Anggota (Admin)**

<img src="uat/screenshots/VAL-06_01_form-anggota-kosong-seluruh-field-wajib-ditolak.png" width="320" alt="Gambar A.95. VAL-06 — Form anggota kosong: seluruh field wajib ditolak">

*Gambar A.95. VAL-06 — Form anggota kosong: seluruh field wajib ditolak*

<img src="uat/screenshots/VAL-06_02_email-dipakai-akun-lain-dan-password-7-karakter-ditolak.png" width="320" alt="Gambar A.96. VAL-06 — Email dipakai akun lain dan password 7 karakter ditolak">

*Gambar A.96. VAL-06 — Email dipakai akun lain dan password 7 karakter ditolak*

**VAL-06b — Validasi ubah anggota: email milik anggota lain ditolak**

<img src="uat/screenshots/VAL-06b_01_email-milik-rina-ditolak-saat-mengubah-bima.png" width="320" alt="Gambar A.97. VAL-06b — Email milik Rina ditolak saat mengubah Bima">

*Gambar A.97. VAL-06b — Email milik Rina ditolak saat mengubah Bima*

**VAL-07 — Validasi kata kunci pencarian maksimal 100 karakter**

<img src="uat/screenshots/VAL-07_01_pencarian-dengan-101-karakter.png" width="320" alt="Gambar A.98. VAL-07 — Pencarian dengan 101 karakter">

*Gambar A.98. VAL-07 — Pencarian dengan 101 karakter*

### E. Kondisi tampilan (PRD bagian 8)

**KT-01 — Sewa Saya kosong untuk anggota yang belum pernah menyewa**

![Gambar A.99. KT-01 — Dashboard anggota baru: belum ada sewa](uat/screenshots/KT-01_01_dashboard-anggota-baru-belum-ada-sewa.png)

*Gambar A.99. KT-01 — Dashboard anggota baru: belum ada sewa*

![Gambar A.100. KT-01 — Sewa Saya kosong dengan tautan ke Daftar Unit](uat/screenshots/KT-01_02_sewa-saya-kosong-dengan-tautan-ke-daftar-unit.png)

*Gambar A.100. KT-01 — Sewa Saya kosong dengan tautan ke Daftar Unit*

**KT-02 — Sewa Aktif kosong setelah semua unit dikembalikan**

![Gambar A.101. KT-02 — Sewa Aktif kosong setelah semua dikembalikan](uat/screenshots/KT-02_01_sewa-aktif-kosong-setelah-semua-dikembalikan.png)

*Gambar A.101. KT-02 — Sewa Aktif kosong setelah semua dikembalikan*

**KT-03 — Riwayat anggota kosong: tombol Cetak nonaktif**

![Gambar A.102. KT-03 — Riwayat anggota baru kosong dengan tombol cetak nonaktif](uat/screenshots/KT-03_01_riwayat-anggota-baru-kosong-dengan-tombol-cetak-nonaktif.png)

*Gambar A.102. KT-03 — Riwayat anggota baru kosong dengan tombol cetak nonaktif*

**KT-04 — Daftar unit kosong**

![Gambar A.103. KT-04 — Daftar unit kosong untuk Anggota](uat/screenshots/KT-04_01_daftar-unit-kosong-untuk-anggota.png)

*Gambar A.103. KT-04 — Daftar unit kosong untuk Anggota*

![Gambar A.104. KT-04 — Daftar unit kosong untuk Admin](uat/screenshots/KT-04_02_daftar-unit-kosong-untuk-admin.png)

*Gambar A.104. KT-04 — Daftar unit kosong untuk Admin*

**KT-05 — Halaman 404 untuk data yang tidak ada**

![Gambar A.105. KT-05 — Detail unit yang tidak ada: halaman 404](uat/screenshots/KT-05_01_detail-unit-yang-tidak-ada-halaman-404.png)

*Gambar A.105. KT-05 — Detail unit yang tidak ada: halaman 404*

![Gambar A.106. KT-05 — Riwayat anggota yang tidak ada: halaman 404](uat/screenshots/KT-05_02_riwayat-anggota-yang-tidak-ada-halaman-404.png)

*Gambar A.106. KT-05 — Riwayat anggota yang tidak ada: halaman 404*

**KT-06 — Tombol kirim nonaktif dan berlabel “Memproses…” sehingga klik ganda tidak membuat sewa ganda**

![Gambar A.107. KT-06 — Tombol berlabel Memproses… dan nonaktif setelah ditekan](uat/screenshots/KT-06_01_tombol-berlabel-memproses-dan-nonaktif-setelah-ditekan.png)

*Gambar A.107. KT-06 — Tombol berlabel Memproses… dan nonaktif setelah ditekan*

![Gambar A.108. KT-06 — Setelah respons datang: satu sewa tercatat](uat/screenshots/KT-06_02_setelah-respons-datang-satu-sewa-tercatat.png)

*Gambar A.108. KT-06 — Setelah respons datang: satu sewa tercatat*

### F. Tujuan bisnis (BRD bagian 3)

**TB-03 — Denda sistem sama dengan hitungan manual di semua skenario keterlambatan**

![Gambar A.109. TB-03 — NBS-001 seed (terlambat 3 hari): Denda Rp30.000](uat/screenshots/TB-03_01_nbs-001-seed-terlambat-3-hari-denda-rp30-000.png)

*Gambar A.109. TB-03 — NBS-001 seed (terlambat 3 hari): Denda Rp30.000*

![Gambar A.110. TB-03 — Terlambat 1 hari: Denda Rp10.000](uat/screenshots/TB-03_02_terlambat-1-hari-denda-rp10-000.png)

*Gambar A.110. TB-03 — Terlambat 1 hari: Denda Rp10.000*

![Gambar A.111. TB-03 — Terlambat 5 hari: Denda Rp50.000](uat/screenshots/TB-03_03_terlambat-5-hari-denda-rp50-000.png)

*Gambar A.111. TB-03 — Terlambat 5 hari: Denda Rp50.000*

![Gambar A.112. TB-03 — Rekap hitungan manual vs sistem](uat/screenshots/TB-03_04_rekap-hitungan-manual-vs-sistem.png)

*Gambar A.112. TB-03 — Rekap hitungan manual vs sistem*

### G. Kebutuhan non-fungsional (BRD bagian 6)

**NFR-01 — Password disimpan sebagai hash; halaman dilindungi login dan peran di server**

![Gambar A.113. NFR-01 — Password tersimpan sebagai hash](uat/screenshots/NFR-01_01_password-tersimpan-sebagai-hash.png)

*Gambar A.113. NFR-01 — Password tersimpan sebagai hash*

**NFR-02 — Permintaan sewa beruntun tidak bisa melewati kuota 2 unit**

![Gambar A.114. NFR-02 — Setelah 4 permintaan sewa beruntun: kuota Bima tepat penuh (2 unit)](uat/screenshots/NFR-02_01_setelah-4-permintaan-sewa-beruntun-kuota-bima-tepat-penuh-2-unit.png)

*Gambar A.114. NFR-02 — Setelah 4 permintaan sewa beruntun: kuota Bima tepat penuh (2 unit)*

![Gambar A.115. NFR-02 — Rekap permintaan sewa beruntun](uat/screenshots/NFR-02_02_rekap-permintaan-sewa-beruntun.png)

*Gambar A.115. NFR-02 — Rekap permintaan sewa beruntun*

**NFR-04 — Tampilan dapat dipakai di layar HP (lebar 390 px) tanpa scroll horizontal halaman**

<img src="uat/screenshots/NFR-04_01_hp-login.png" width="320" alt="Gambar A.116. NFR-04 — HP: Login">

*Gambar A.116. NFR-04 — HP: Login*

<img src="uat/screenshots/NFR-04_02_hp-dashboard-anggota.png" width="320" alt="Gambar A.117. NFR-04 — HP: Dashboard Anggota">

*Gambar A.117. NFR-04 — HP: Dashboard Anggota*

<img src="uat/screenshots/NFR-04_03_hp-daftar-unit.png" width="320" alt="Gambar A.118. NFR-04 — HP: Daftar Unit">

*Gambar A.118. NFR-04 — HP: Daftar Unit*

<img src="uat/screenshots/NFR-04_04_hp-detail-unit.png" width="320" alt="Gambar A.119. NFR-04 — HP: Detail Unit">

*Gambar A.119. NFR-04 — HP: Detail Unit*

<img src="uat/screenshots/NFR-04_05_hp-sewa-saya.png" width="320" alt="Gambar A.120. NFR-04 — HP: Sewa Saya">

*Gambar A.120. NFR-04 — HP: Sewa Saya*

<img src="uat/screenshots/NFR-04_06_hp-kelola-unit-admin.png" width="320" alt="Gambar A.121. NFR-04 — HP: Kelola Unit (Admin)">

*Gambar A.121. NFR-04 — HP: Kelola Unit (Admin)*

<img src="uat/screenshots/NFR-04_07_hp-sewa-aktif-admin.png" width="320" alt="Gambar A.122. NFR-04 — HP: Sewa Aktif (Admin)">

*Gambar A.122. NFR-04 — HP: Sewa Aktif (Admin)*

**NFR-07 — Tarif denda dan durasi sewa dibaca dari konfigurasi, bukan ditulis di kode**

![Gambar A.123. NFR-07 — Tarif dan durasi dari konfigurasi](uat/screenshots/NFR-07_01_tarif-dan-durasi-dari-konfigurasi.png)

*Gambar A.123. NFR-07 — Tarif dan durasi dari konfigurasi*

**NFR-08 — migrate:fresh –seed membangun ulang database dengan data seed sesuai PRD**

![Gambar A.124. NFR-08 — Hasil migrate:fresh –seed](uat/screenshots/NFR-08_01_hasil-migrate-fresh-seed.png)

*Gambar A.124. NFR-08 — Hasil migrate:fresh –seed*

### H. Integritas data (Flowchart & ERD bagian 9)

**INT-01 — Kode unit dan email unik di level database, termasuk bekas data terhapus**

<img src="uat/screenshots/INT-01_01_kode-zts-002-milik-unit-yang-sudah-dihapus-tetap-tidak-bisa-dipakai-ul.png" width="320" alt="Gambar A.125. INT-01 — Kode ZTS-002 milik unit yang sudah dihapus tetap tidak bisa dipakai ulang">

*Gambar A.125. INT-01 — Kode ZTS-002 milik unit yang sudah dihapus tetap tidak bisa dipakai ulang*

![Gambar A.126. INT-01 — Penolakan duplikat oleh database](uat/screenshots/INT-01_02_penolakan-duplikat-oleh-database.png)

*Gambar A.126. INT-01 — Penolakan duplikat oleh database*

**INT-02 — Satu akun satu profil dan pasangan unit-kategori tidak ganda**

![Gambar A.127. INT-02 — Penolakan duplikat oleh database](uat/screenshots/INT-02_01_penolakan-duplikat-oleh-database.png)

*Gambar A.127. INT-02 — Penolakan duplikat oleh database*

**INT-03 — Foreign key rentals bersifat restrict: akun/unit dengan sewa tidak bisa dihapus permanen**

![Gambar A.128. INT-03 — Penghapusan permanen ditolak database](uat/screenshots/INT-03_01_penghapusan-permanen-ditolak-database.png)

*Gambar A.128. INT-03 — Penghapusan permanen ditolak database*

## Lampiran B — Tes Otomatis Backend (PHPUnit)

Sebanyak 56 tes PHPUnit lulus seluruhnya. Tes ini menguji logika backend langsung lewat request HTTP tanpa browser, termasuk verifikasi perbaikan temuan di `tests/Feature/UatFindingsTest.php`.

| Item       | Hasil                              |
|------------|------------------------------------|
| Jumlah tes | 56                                 |
| Lulus      | 56                                 |
| Gagal      | 0                                  |
| Framework  | PHPUnit 12.5.40 di Laravel 13.38.0 |
