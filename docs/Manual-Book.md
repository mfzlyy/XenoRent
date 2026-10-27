# Manual Book

**XenoRent — Sistem Rental Kapal Alien**  
Versi 1.0 · 23 Oktober 2026  
Disusun oleh: Muhammad Fazly Radithya, Stevy Reuben Januardi, Muhammad Nadhif Aqila

## 1. Tentang Panduan Ini

Panduan ini menjelaskan cara memakai XenoRent, aplikasi web sewa kapal alien, langkah demi langkah untuk setiap peran pengguna. Panduan disusun berdasarkan tugas yang ingin diselesaikan pengguna, misalnya “Menyewa unit” atau “Memproses pengembalian”.

| Pembaca                   | Bagian yang perlu dibaca |
|---------------------------|--------------------------|
| Calon anggota dan anggota | Bab 2, 3, 4, 6, dan 7    |
| Admin hanggar             | Bab 2, 3, 5, 6, dan 7    |
| Penguji atau dosen        | Bab 2, 3, dan Lampiran A |

**Konvensi penulisan**

- Teks **tebal** menunjukkan nama menu, tombol, atau isian di layar, misalnya **Sewa kapal**.
- Teks dalam tanda kutip menunjukkan pesan yang tampil di layar, misalnya “Unit berhasil disewa”.
- Screenshot diambil dari aplikasi dengan data contoh bawaan, sehingga nama dan tanggal di layar Anda dapat berbeda.

## 2. Mengenal XenoRent

XenoRent membantu hanggar menyewakan armada kapal alien kepada anggota terdaftar. Setiap kapal (unit) memiliki kode unik, sehingga beberapa kapal bernama sama tetap dapat dibedakan. Anggota menyewa kapal sendiri lewat aplikasi, sedangkan pengembalian dan perhitungan denda diproses oleh Admin.

**Peran pengguna**

| Peran   | Siapa                       | Yang dapat dilakukan                                                                           |
|---------|-----------------------------|------------------------------------------------------------------------------------------------|
| Tamu    | Pengunjung yang belum masuk | Mendaftar sebagai anggota dan masuk                                                            |
| Anggota | Penyewa terdaftar           | Mencari kapal, menyewa maksimal 2 unit, melihat sewa sendiri, mengubah profil                  |
| Admin   | Petugas hanggar             | Mengelola unit, kategori, dan anggota; memproses pengembalian dan denda; mencetak riwayat sewa |

**Menu navigasi**

| Peran   | Menu di bagian atas layar                                                                                          |
|---------|--------------------------------------------------------------------------------------------------------------------|
| Anggota | **Dashboard**, **Daftar Unit**, **Sewa Saya**, **Profil**, **Keluar**                                              |
| Admin   | **Dashboard**, **Daftar Unit**, **Kelola Unit**, **Kategori**, **Anggota**, **Sewa Aktif**, **Profil**, **Keluar** |

**Aturan utama sewa**

- Setiap anggota dapat memegang **maksimal 2 unit** yang sedang disewa secara bersamaan.
- Masa sewa **5 hari**; tanggal jatuh tempo dihitung otomatis dari tanggal sewa.
- Keterlambatan dikenai **denda Rp10.000 per hari per unit**.
- Pengembalian unit **hanya diproses oleh Admin**; anggota menghubungi Admin untuk mengembalikan unit.

## 3. Memulai

### 3.1 Persyaratan

- Browser versi terbaru: Google Chrome, Microsoft Edge, atau Mozilla Firefox.
- Layar laptop atau komputer; tampilan juga dapat dipakai di ponsel (Bab 8).
- Alamat aplikasi dari pengelola hanggar. Saat dijalankan di komputer sendiri, alamatnya `http://localhost:8000` (Lampiran A).

### 3.2 Mendaftar sebagai Anggota

1.  Buka halaman masuk, lalu klik **Buat akun anggota**.
2.  Isi **Nama lengkap**, **Email**, **Kata sandi**, **Ulangi kata sandi**, **Telepon**, dan **Alamat**. Semua isian wajib diisi; kata sandi minimal 8 karakter dan telepon berisi 10–15 angka.
3.  Klik **Daftar sebagai anggota**.
4.  Akun langsung aktif dan Anda otomatis masuk ke Dashboard Anggota.

![Gambar 1. Form pendaftaran anggota](uat/screenshots/KP-01_01_form-registrasi-terisi-data-valid.png)

*Gambar 1. Form pendaftaran anggota*

Jika email sudah dipakai akun lain, pendaftaran ditolak dengan pesan “Email sudah terdaftar.” Gunakan email lain atau masuk dengan akun yang sudah ada.

### 3.3 Masuk (Login)

1.  Buka alamat aplikasi. Halaman **Masuk ke XenoRent** akan tampil.
2.  Isi **Email** dan **Kata sandi**.
3.  Centang **Tetap masuk di perangkat ini** jika tidak ingin masuk ulang setiap kali membuka aplikasi (opsional).
4.  Klik **Masuk**. Aplikasi menampilkan Dashboard sesuai peran Anda.

![Gambar 2. Halaman masuk](uat/screenshots/KP-03_01_form-login-diisi-akun-admin.png)

*Gambar 2. Halaman masuk*

Jika email atau kata sandi salah, pesan “Email atau kata sandi salah” tampil dan kolom kata sandi dikosongkan kembali.

### 3.4 Keluar (Logout)

Klik **Keluar** di pojok kanan atas. Anda akan kembali ke halaman masuk. Selalu keluar setelah memakai komputer bersama.

### 3.5 Mengubah Profil

1.  Klik menu **Profil**.
2.  Ubah **Nama lengkap**, **Email**, **Telepon**, atau **Alamat**.
3.  Klik **Simpan profil**. Pesan “Profil berhasil diperbarui” tampil di bagian atas halaman.

![Gambar 3. Profil berhasil diperbarui](uat/screenshots/KP-07_02_profil-rina-tersimpan-dengan-pesan-sukses.png)

*Gambar 3. Profil berhasil diperbarui*

Kata sandi tidak dapat diubah dari halaman Profil. Jika lupa kata sandi, hubungi Admin.

## 4. Panduan Anggota

### 4.1 Dashboard Anggota

Dashboard menampilkan **sisa kuota sewa** (misalnya “2 dari 2” berarti Anda masih dapat menyewa 2 unit) dan ringkasan sewa yang sedang berjalan.

![Gambar 4. Dashboard anggota dengan sisa kuota sewa](uat/screenshots/KP-01_02_dashboard-anggota-setelah-registrasi-sisa-kuota-2-dari-2.png)

*Gambar 4. Dashboard anggota dengan sisa kuota sewa*

### 4.2 Mencari Unit

1.  Klik menu **Daftar Unit**. Semua kapal tampil lengkap dengan kode, nama, kategori, dan status (**Tersedia** atau **Disewa**).
2.  Ketik nama kapal pada kolom **Cari berdasarkan nama unit**, misalnya “zeta”. Huruf besar dan kecil tidak dibedakan, dan sebagian nama sudah cukup.
3.  Klik **Cari unit**.
4.  Untuk kembali ke daftar lengkap, klik **Reset pencarian**.

![Gambar 5. Daftar unit beserta status dan kategorinya](images/manual/daftar-unit.png)

*Gambar 5. Daftar unit beserta status dan kategorinya*

![Gambar 6. Hasil pencarian “zeta”](uat/screenshots/KP-10_01_hasil-pencarian-zeta-tiga-zeta-saucer.png)

*Gambar 6. Hasil pencarian “zeta”*

Beberapa kapal dapat bernama sama, misalnya tiga unit “Zeta Saucer”. Bedakan dengan **kode unit** (ZTS-001, ZTS-002, ZTS-003). Jika tidak ada kapal yang cocok, pesan “Unit tidak ditemukan” tampil.

### 4.3 Menyewa Unit

1.  Pada **Daftar Unit**, pilih kapal berstatus **Tersedia**, lalu klik **Detail unit**.
2.  Periksa kode, nama, kategori, dan deskripsi kapal.
3.  Klik **Sewa kapal**, lalu pilih **OK** pada dialog “Sewakan unit ini?”.
4.  Pesan “Unit berhasil disewa. Jatuh tempo: …” tampil. Catat tanggal jatuh tempo tersebut.

![Gambar 7. Detail unit yang tersedia dengan tombol Sewa kapal](uat/screenshots/KP-22_01_detail-unit-zts-002-berstatus-tersedia-dengan-tombol-sewa-kapal.png)

*Gambar 7. Detail unit yang tersedia dengan tombol Sewa kapal*

![Gambar 8. Sewa berhasil beserta tanggal jatuh tempo](uat/screenshots/KP-22_02_sewa-berhasil-pesan-sukses-memuat-tanggal-jatuh-tempo-hari-ini-5.png)

*Gambar 8. Sewa berhasil beserta tanggal jatuh tempo*

Sewa tidak dapat dilakukan pada dua kondisi berikut:

- **Unit sedang disewa:** tombol sewa tidak tersedia dan tertulis “Sedang disewa”. Pilih unit lain dengan nama sama yang berstatus Tersedia.
- **Kuota penuh:** jika Anda sudah memegang 2 unit, sewa ditolak dengan pesan “Kuota sewa penuh (maksimal 2 unit bersamaan)”. Kembalikan salah satu unit terlebih dahulu.

![Gambar 9. Sewa ditolak karena kuota sudah penuh](uat/screenshots/KP-23_01_sewa-ke-3-ditolak-kuota-sewa-penuh.png)

*Gambar 9. Sewa ditolak karena kuota sudah penuh*

### 4.4 Melihat Sewa Saya

Klik menu **Sewa Saya** untuk melihat semua sewa milik Anda, baik yang masih berjalan maupun yang sudah selesai, lengkap dengan tanggal sewa, jatuh tempo, tanggal kembali, status, dan denda. Sewa milik anggota lain tidak ditampilkan.

![Gambar 10. Halaman Sewa Saya beserta denda](uat/screenshots/KP-26_02_sewa-saya-bima-hanya-sewa-kph-001-yang-selesai-beserta-denda-rp20-000.png)

*Gambar 10. Halaman Sewa Saya beserta denda*

### 4.5 Mengembalikan Unit

Anggota tidak dapat mengembalikan unit sendiri lewat aplikasi.

1.  Hubungi Admin hanggar dan sebutkan kode unit yang dikembalikan.
2.  Serahkan unit kepada Admin.
3.  Admin memproses pengembalian di aplikasi. Jika terlambat, denda dihitung otomatis dan dibayarkan langsung kepada Admin.
4.  Setelah diproses, status sewa di **Sewa Saya** berubah menjadi **Dikembalikan** dan kuota sewa Anda pulih.

## 5. Panduan Admin

### 5.1 Panel Operasional

Setelah masuk, Admin melihat **Panel operasional** berisi jumlah unit tersedia, sewa aktif, dan anggota terdaftar, serta daftar sewa yang sedang berjalan.

Admin tidak dapat menyewa unit; tombol sewa hanya tersedia untuk akun Anggota.

![Gambar 11. Panel operasional Admin](uat/screenshots/KP-03_02_dashboard-admin-setelah-login.png)

*Gambar 11. Panel operasional Admin*

### 5.2 Menambah Unit

1.  Klik menu **Kelola Unit**.
2.  Pada bagian **Tambah kapal ke hanggar**, isi **Kode unit** (wajib, maksimal 20 karakter, tanpa spasi) dan **Nama kapal** (wajib).
3.  Isi **Deskripsi** jika perlu (opsional).
4.  Centang minimal satu **Kategori**. Satu unit dapat memiliki lebih dari satu kategori.
5.  Klik **Tambah unit**. Unit baru tampil di **Unit hanggar** dengan status **Tersedia**.

![Gambar 12. Form tambah unit](images/manual/kelola-unit-tambah.png)

*Gambar 12. Form tambah unit*

Kode unit harus unik. Jika kode sudah dipakai, termasuk kode milik unit yang sudah dihapus, pesan “Kode unit sudah dipakai” tampil dan isian lain tetap terisi.

![Gambar 13. Kode unit yang sudah dipakai ditolak](images/manual/kelola-unit-kode-duplikat.png)

*Gambar 13. Kode unit yang sudah dipakai ditolak*

### 5.3 Mengubah dan Menghapus Unit

1.  Pada **Kelola Unit**, cari unit di bagian **Unit hanggar**, lalu klik **Ubah data unit**.
2.  Ubah data yang diperlukan, lalu klik **Simpan perubahan**.
3.  Untuk menghapus, klik **Hapus unit**, lalu pilih **OK** pada dialog konfirmasi.

![Gambar 14. Panel ubah data unit dengan tombol Simpan perubahan dan Hapus unit](images/manual/kelola-unit-ubah-hapus.png)

*Gambar 14. Panel ubah data unit dengan tombol Simpan perubahan dan Hapus unit*

Unit yang sedang disewa tidak dapat dihapus; tombol **Hapus unit** tidak tersedia sampai unit dikembalikan. Riwayat sewa unit yang sudah dihapus tetap tersimpan.

### 5.4 Mengelola Kategori

1.  Klik menu **Kategori**.
2.  Untuk menambah, isi **Nama kategori** (wajib, unik) dan **Deskripsi** (opsional), lalu klik **Tambah kategori**.
3.  Untuk mengubah, klik **Ubah kategori** pada kategori yang dipilih, ubah data, lalu klik **Simpan perubahan**.
4.  Untuk menghapus, klik **Ubah kategori**, lalu **Hapus kategori**, dan pilih **OK** pada dialog konfirmasi.

![Gambar 15. Form tambah kategori](images/manual/kategori-tambah.png)

*Gambar 15. Form tambah kategori*

![Gambar 16. Panel ubah kategori dengan tombol Hapus kategori](images/manual/kategori-ubah-hapus.png)

*Gambar 16. Panel ubah kategori dengan tombol Hapus kategori*

Menghapus kategori tidak menghapus unit. Kategori hanya dilepas dari unit yang memakainya.

### 5.5 Menambah Anggota

1.  Klik menu **Anggota**.
2.  Pada bagian **Tambah anggota**, isi **Nama lengkap**, **Email**, **Telepon**, **Alamat**, **Kata sandi**, dan **Ulangi kata sandi**.
3.  Klik **Tambah anggota**. Akun anggota beserta profilnya langsung dapat dipakai untuk masuk.

![Gambar 17. Form tambah anggota](images/manual/anggota-tambah.png)

*Gambar 17. Form tambah anggota*

### 5.6 Mengubah dan Menghapus Anggota

1.  Pada **Anggota**, cari anggota di bagian **Akun anggota**, lalu klik **Ubah data anggota**.
2.  Ubah data yang diperlukan. Biarkan **Password baru** kosong jika kata sandi tidak diganti.
3.  Klik **Simpan perubahan**.
4.  Untuk menghapus, klik **Hapus akun**, lalu pilih **OK** pada dialog konfirmasi.

![Gambar 18. Panel ubah data anggota](images/manual/anggota-ubah.png)

*Gambar 18. Panel ubah data anggota*

Anggota yang masih memiliki sewa aktif tidak dapat dihapus; tombol **Hapus akun** diganti keterangan “Anggota masih punya sewa aktif, penghapusan tidak tersedia.” Riwayat sewa anggota yang sudah dihapus tetap tersimpan.

![Gambar 19. Anggota dengan sewa aktif tidak dapat dihapus](images/manual/anggota-hapus-ditolak.png)

*Gambar 19. Anggota dengan sewa aktif tidak dapat dihapus*

### 5.7 Memproses Pengembalian dan Denda

1.  Saat anggota menyerahkan unit, klik menu **Sewa Aktif**.
2.  Cari baris sewa berdasarkan nama anggota dan kode unit. Sewa yang melewati jatuh tempo bertanda **Terlambat**.
3.  Klik **Proses kembali**, lalu pilih **OK** pada dialog “Proses pengembalian unit ini?”.
4.  Pesan “Pengembalian diproses. Tidak ada denda” atau “Pengembalian diproses. Denda: Rp…” tampil. Terima pembayaran denda langsung dari anggota bila ada.
5.  Unit kembali berstatus **Tersedia** dan kuota anggota pulih.

![Gambar 20. Daftar Sewa Aktif dengan penanda Terlambat](uat/screenshots/KP-27_01_sewa-aktif-admin-3-sewa-dari-2-anggota-nbs-001-bertanda-terlambat.png)

*Gambar 20. Daftar Sewa Aktif dengan penanda Terlambat*

![Gambar 21. Pengembalian diproses beserta denda](uat/screenshots/KP-29_02_pengembalian-diproses-dengan-denda-rp20-000-2-hari-rp10-000.png)

*Gambar 21. Pengembalian diproses beserta denda*

### 5.8 Melihat dan Mencetak Riwayat Sewa Anggota

1.  Klik menu **Anggota**, lalu klik **Lihat riwayat sewa** pada anggota yang dipilih.
2.  Halaman **Riwayat sewa** menampilkan seluruh sewa anggota tersebut: tanggal sewa, jatuh tempo, tanggal kembali, status, dan denda.
3.  Klik **Print riwayat** untuk membuka versi cetak, lalu klik **Print riwayat** kembali.
4.  Pada dialog cetak browser, pilih printer atau **Simpan sebagai PDF**.

![Gambar 22. Riwayat sewa anggota](uat/screenshots/KP-32_01_riwayat-sewa-bima-tanggal-sewa-jatuh-tempo-tanggal-kembali-dan-denda.png)

*Gambar 22. Riwayat sewa anggota*

![Gambar 23. Tampilan cetak riwayat tanpa menu navigasi](uat/screenshots/KP-33_02_tampilan-mode-cetak-tanpa-menu-navigasi-footer-dan-tombol.png)

*Gambar 23. Tampilan cetak riwayat tanpa menu navigasi*

Jika anggota belum pernah menyewa, tombol berubah menjadi **Print tidak tersedia**.

## 6. Aturan Sewa dan Perhitungan Denda

Jatuh tempo sama dengan tanggal sewa ditambah 5 hari. Denda dihitung otomatis saat Admin memproses pengembalian:

*Denda = jumlah hari terlambat × Rp10.000 per unit*

| Tanggal sewa | Jatuh tempo | Tanggal kembali | Terlambat | Denda    |
|--------------|-------------|-----------------|-----------|----------|
| 1 November   | 6 November  | 5 November      | 0 hari    | Rp0      |
| 1 November   | 6 November  | 6 November      | 0 hari    | Rp0      |
| 1 November   | 6 November  | 7 November      | 1 hari    | Rp10.000 |
| 1 November   | 6 November  | 8 November      | 2 hari    | Rp20.000 |
| 1 November   | 6 November  | 11 November     | 5 hari    | Rp50.000 |

Denda dihitung per unit. Jika dua unit terlambat, masing-masing dikenai denda sendiri.

## 7. Pesan Sistem dan Pemecahan Masalah

| Pesan atau kondisi                                            | Penyebab                                                  | Solusi                                                  |
|---------------------------------------------------------------|-----------------------------------------------------------|---------------------------------------------------------|
| “Email atau kata sandi salah”                                 | Email atau kata sandi tidak cocok                         | Periksa kembali; daftar jika belum punya akun           |
| “Email sudah terdaftar.”                                      | Email dipakai akun lain                                   | Gunakan email lain atau masuk dengan akun tersebut      |
| “Konfirmasi kata sandi tidak cocok.”                          | Isian **Ulangi kata sandi** berbeda                       | Ketik ulang kata sandi yang sama                        |
| “nomor telepon harus terdiri dari 10 sampai 15 angka.”        | Telepon berisi huruf atau panjangnya tidak sesuai         | Isi 10–15 angka tanpa spasi atau tanda baca             |
| “Unit tidak ditemukan”                                        | Tidak ada nama kapal yang cocok                           | Periksa ejaan atau klik **Reset pencarian**             |
| “Kata kunci maksimal 100 karakter.”                           | Kata kunci pencarian terlalu panjang                      | Persingkat kata kunci                                   |
| “Kuota sewa penuh (maksimal 2 unit bersamaan)”                | Anggota sudah memegang 2 unit                             | Kembalikan salah satu unit melalui Admin                |
| “Unit sedang disewa”                                          | Unit dipakai anggota lain                                 | Pilih unit lain yang berstatus Tersedia                 |
| “Kode unit sudah dipakai”                                     | Kode sudah dipakai unit lain atau unit yang sudah dihapus | Gunakan kode lain                                       |
| “Pilih minimal 1 kategori.”                                   | Unit disimpan tanpa kategori                              | Centang minimal satu kategori                           |
| “Nama kategori sudah dipakai.”                                | Nama kategori sudah ada                                   | Gunakan nama lain                                       |
| “Unit masih disewa, penghapusan tidak tersedia.”              | Unit belum dikembalikan                                   | Proses pengembalian terlebih dahulu                     |
| “Anggota masih punya sewa aktif, penghapusan tidak tersedia.” | Anggota masih memegang unit                               | Proses pengembalian semua unitnya terlebih dahulu       |
| “Sewa sudah dikembalikan”                                     | Pengembalian sudah diproses sebelumnya                    | Muat ulang halaman **Sewa Aktif**                       |
| Halaman “Akses ditolak” (403)                                 | Anggota membuka halaman khusus Admin                      | Kembali ke Dashboard; gunakan akun Admin bila berwenang |
| Halaman “Unit ini tidak ada di radar.” (404)                  | Data sudah dihapus atau alamat salah                      | Kembali ke daftar dan pilih data yang ada               |
| Diarahkan ke halaman masuk                                    | Belum masuk atau sesi berakhir                            | Masuk kembali                                           |

Jika masalah berlanjut, hubungi Admin hanggar dengan menyebutkan pesan yang tampil dan langkah yang sedang dilakukan.

![Gambar 24. Halaman Akses ditolak](uat/screenshots/KP-06_01_anggota-membuka-admin-units-halaman-akses-ditolak-403.png)

*Gambar 24. Halaman Akses ditolak*

## 8. Memakai XenoRent di Ponsel

Seluruh halaman dapat dibuka di ponsel dan menyesuaikan lebar layar, sehingga halaman tidak perlu digeser ke samping. Menu navigasi tetap berada di bagian atas, dan langkah penggunaannya sama dengan versi laptop.

<img src="uat/screenshots/NFR-04_02_hp-dashboard-anggota.png" width="260" alt="Gambar 25. Dashboard anggota di ponsel"> <img src="uat/screenshots/NFR-04_04_hp-detail-unit.png" width="260" alt="Gambar 26. Detail unit di ponsel">

*Gambar 25. Dashboard anggota di ponsel* · *Gambar 26. Detail unit di ponsel*

## Lampiran A — Menjalankan Aplikasi untuk Pengujian

Bagian ini ditujukan bagi penguji atau dosen yang ingin menjalankan XenoRent di komputer sendiri. Kebutuhan: PHP 8.4 atau lebih baru, Composer, Node.js, dan SQLite.

``` bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Buka `http://localhost:8000`, lalu masuk dengan salah satu akun contoh berikut (kata sandi semua akun: `password`).

| Peran   | Email                 | Kondisi awal                                            |
|---------|-----------------------|---------------------------------------------------------|
| Admin   | `admin@xenorent.test` | —                                                       |
| Anggota | `rina@xenorent.test`  | 2 sewa aktif (kuota penuh), satu di antaranya terlambat |
| Anggota | `bima@xenorent.test`  | Belum ada sewa aktif; 1 riwayat dengan denda Rp20.000   |

Perintah `php artisan migrate:fresh --seed` dapat dijalankan kapan saja untuk mengembalikan seluruh data ke kondisi awal. Tarif denda dan durasi sewa dapat diubah lewat `RENTAL_DAILY_FINE` dan `RENTAL_DURATION_DAYS` di file `.env`.
