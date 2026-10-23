# Product Requirements Document (PRD)

**XenoRent — Sistem Rental Kapal Alien**  
Versi 1.0 · 22 Oktober 2026  
Disusun oleh: Muhammad Fazly Radithya, Stevy Reuben Januardi, Muhammad Nadhif Aqila

## 1. Ringkasan Produk

XenoRent adalah aplikasi web sewa kapal alien dengan dua peran, Admin dan Anggota. Dokumen ini merinci *apa* yang dibangun dari BRD: fitur, prioritas, kriteria penerimaan, validasi, halaman, dan data awal. Kode BR, ATB, NFR mengacu ke BRD; nama tabel mengacu ke dokumen Flowchart & ERD.

**Masalah yang diselesaikan**

- Banyak kapal bernama sama sulit dibedakan → setiap unit punya kode unik dan bisa dicari berdasarkan nama.

- Kuota dan durasi sewa tidak terkontrol → sistem menolak sewa ke-3 dan mencatat jatuh tempo 5 hari.

- Denda dihitung manual → denda dihitung otomatis saat Admin memproses pengembalian.

- Riwayat sulit diaudit → Admin bisa melihat dan mencetak riwayat sewa per anggota.

## 2. Target Pengguna

Admin mengelola seluruh data dan menutup sewa; Anggota hanya menyewa dan melihat datanya sendiri. Admin tidak bisa menyewa unit, karena hanya pengguna berperan Anggota yang boleh menyewa (BR-03).

| Peran   | Siapa                                                       | Kebutuhan utama                                                                                      | Batasan akses                                                 |
|---------|-------------------------------------------------------------|------------------------------------------------------------------------------------------------------|---------------------------------------------------------------|
| Admin   | Petugas hanggar XenoRent; akun dibuat lewat seed            | Mengelola unit, kategori, dan anggota; memantau sewa aktif; memproses pengembalian; mencetak riwayat | Tidak bisa menyewa unit                                       |
| Anggota | Penyewa terdaftar; mendaftar sendiri atau ditambahkan Admin | Mencari kapal, menyewa maksimal 2 unit, melihat sewa sendiri, mengubah profil                        | Tidak bisa membuka halaman Admin; hanya melihat sewa miliknya |
| Tamu    | Pengunjung yang belum login                                 | Mendaftar atau login                                                                                 | Hanya halaman login dan registrasi                            |

## 3. Prioritas Fitur

Seluruh kebutuhan inti masuk prioritas Must; aplikasi dinyatakan selesai setelah 16 fitur Must berfungsi. Should adalah perapian tampilan yang mendukung fitur Must. Could dikerjakan jika waktu memungkinkan.

| Kode | Fitur                                                               | Prioritas | Peran   | Dasar BRD           |
|------|---------------------------------------------------------------------|-----------|---------|---------------------|
| F-01 | Registrasi anggota beserta profil                                   | Must      | Tamu    | BR-02, BR-04        |
| F-02 | Login dan logout                                                    | Must      | Semua   | BR-01               |
| F-03 | Ubah profil sendiri                                                 | Must      | Semua   | BR-05               |
| F-04 | Daftar dan detail unit                                              | Must      | Semua   | BR-06, BR-07        |
| F-05 | Pencarian unit berdasarkan nama                                     | Must      | Semua   | BR-08               |
| F-06 | Kelola unit: tambah, ubah, hapus, multi-kategori, kode unik         | Must      | Admin   | BR-06, BR-07, BR-09 |
| F-07 | Kelola kategori: tambah, ubah, hapus                                | Must      | Admin   | BR-10               |
| F-08 | Kelola anggota: tambah, ubah, hapus                                 | Must      | Admin   | BR-11               |
| F-09 | Sewa unit (maks. 2 aktif, jatuh tempo 5 hari)                       | Must      | Anggota | BR-03, BR-12, BR-13 |
| F-10 | Sewa Saya: daftar sewa milik sendiri                                | Must      | Anggota | BR-16               |
| F-11 | Sewa Aktif: daftar semua unit yang sedang disewa                    | Must      | Admin   | BR-15               |
| F-12 | Proses pengembalian dan hitung denda                                | Must      | Admin   | BR-13, BR-14        |
| F-13 | Riwayat sewa per anggota dan cetak                                  | Must      | Admin   | BR-17               |
| F-14 | Validasi field wajib di semua form                                  | Must      | Semua   | BR-18               |
| F-15 | Migration dan data seed awal                                        | Must      | Tim     | BR-19, BR-20        |
| F-16 | README lengkap (kelompok, tim, fitur, skema DB, video demo)         | Must      | Tim     | BR-21               |
| S-01 | Pesan sukses atau gagal setelah setiap aksi                         | Should    | Semua   | NFR-03              |
| S-02 | Dialog konfirmasi sebelum menghapus                                 | Should    | Admin   | BR-09 s.d. BR-11    |
| S-03 | Paginasi daftar, 10 baris per halaman                               | Should    | Semua   | —                   |
| S-04 | Tampilan responsif untuk laptop dan HP                              | Should    | Semua   | NFR-04              |
| S-05 | Dashboard sederhana: Anggota melihat sisa kuota, Admin melihat menu | Should    | Semua   | —                   |
| C-01 | Filter unit berdasarkan kategori                                    | Could     | Semua   | —                   |
| C-02 | Penanda “Terlambat” di daftar Sewa Aktif                            | Could     | Admin   | —                   |
| C-03 | Angka ringkasan di dashboard Admin (unit tersedia, sewa aktif)      | Could     | Admin   | —                   |

## 4. Kebutuhan Fungsional & Kriteria Penerimaan

Setiap baris adalah satu kondisi yang bisa diuji. Kode KP dipakai ulang sebagai skenario di dokumen Hasil UAT.

| Kode  | Fitur | Kondisi                                                        | Hasil yang diharapkan                                                                              |
|-------|-------|----------------------------------------------------------------|----------------------------------------------------------------------------------------------------|
| KP-01 | F-01  | Tamu mengisi form registrasi dengan data valid                 | Akun berperan anggota dan profilnya tersimpan; pengguna langsung login dan masuk Dashboard Anggota |
| KP-02 | F-01  | Email sudah dipakai akun lain                                  | Registrasi ditolak dengan pesan “Email sudah terdaftar”                                            |
| KP-03 | F-02  | Login dengan email dan password benar                          | Masuk ke dashboard sesuai peran                                                                    |
| KP-04 | F-02  | Login dengan password salah                                    | Tetap di halaman login dengan pesan gagal; password tidak ditampilkan ulang                        |
| KP-05 | F-02  | Tamu membuka URL halaman mana pun selain login/registrasi      | Diarahkan ke halaman login                                                                         |
| KP-06 | F-02  | Anggota membuka URL halaman Admin                              | Ditolak dengan halaman “Akses ditolak” (403)                                                       |
| KP-07 | F-03  | Pengguna mengubah telepon dan alamat                           | Data profil miliknya tersimpan; profil orang lain tidak berubah                                    |
| KP-08 | F-04  | Membuka daftar unit                                            | Tampil kode, nama, kategori, dan status setiap unit                                                |
| KP-09 | F-04  | Ada 3 unit bernama “Zeta Saucer”                               | Ketiganya tampil terpisah dengan kode berbeda                                                      |
| KP-10 | F-05  | Mencari “zeta”                                                 | Semua unit yang namanya mengandung “Zeta” tampil, tanpa membedakan huruf besar-kecil               |
| KP-11 | F-05  | Mencari kata yang tidak cocok dengan unit mana pun             | Tampil pesan “Unit tidak ditemukan”                                                                |
| KP-12 | F-06  | Admin menambah unit dengan kode baru dan 2 kategori            | Unit tersimpan berstatus tersedia dengan 2 kategori                                                |
| KP-13 | F-06  | Admin menambah unit dengan kode yang sudah ada                 | Ditolak dengan pesan “Kode unit sudah dipakai”                                                     |
| KP-14 | F-06  | Admin menyimpan unit tanpa memilih kategori                    | Ditolak dengan pesan “Pilih minimal 1 kategori”                                                    |
| KP-15 | F-06  | Admin menghapus unit yang sedang disewa                        | Ditolak dengan pesan “Unit masih disewa”                                                           |
| KP-16 | F-06  | Admin menghapus unit yang tidak sedang disewa                  | Unit hilang dari daftar; riwayat sewa lamanya tetap ada                                            |
| KP-17 | F-07  | Admin menambah kategori dengan nama yang sudah ada             | Ditolak dengan pesan “Nama kategori sudah dipakai”                                                 |
| KP-18 | F-07  | Admin menghapus kategori yang dipakai beberapa unit            | Kategori terhapus; unit tetap ada tanpa kategori tersebut                                          |
| KP-19 | F-08  | Admin menambah anggota dengan data lengkap                     | Akun anggota dan profilnya tersimpan; anggota bisa login                                           |
| KP-20 | F-08  | Admin mengubah data anggota dengan password dikosongkan        | Data lain tersimpan; password lama tetap berlaku                                                   |
| KP-21 | F-08  | Admin menghapus anggota yang masih punya sewa aktif            | Ditolak dengan pesan “Anggota masih punya sewa aktif”                                              |
| KP-22 | F-09  | Anggota dengan 0 atau 1 sewa aktif menyewa unit tersedia       | Sewa tercatat; unit berubah disewa; jatuh tempo = hari ini + 5 hari                                |
| KP-23 | F-09  | Anggota dengan 2 sewa aktif mencoba menyewa lagi               | Ditolak dengan pesan “Kuota sewa penuh (maksimal 2 unit)”                                          |
| KP-24 | F-09  | Anggota mencoba menyewa unit berstatus disewa                  | Tombol Sewa nonaktif; jika dipaksa lewat URL, ditolak dengan pesan “Unit sedang disewa”            |
| KP-25 | F-09  | Admin membuka detail unit                                      | Tombol Sewa tidak tampil untuk Admin                                                               |
| KP-26 | F-10  | Anggota membuka Sewa Saya                                      | Hanya sewa miliknya yang tampil, aktif dan selesai, beserta jatuh tempo dan denda                  |
| KP-27 | F-11  | Admin membuka Sewa Aktif                                       | Semua sewa berstatus disewa dari semua anggota tampil                                              |
| KP-28 | F-12  | Admin memproses pengembalian sebelum atau tepat di jatuh tempo | Status sewa dikembalikan, denda Rp0, unit kembali tersedia                                         |
| KP-29 | F-12  | Admin memproses pengembalian 2 hari setelah jatuh tempo        | Denda = 2 × tarif per hari (Rp20.000 dengan tarif Rp10.000)                                        |
| KP-30 | F-12  | Admin memproses sewa yang sudah dikembalikan                   | Ditolak dengan pesan “Sewa sudah dikembalikan”                                                     |
| KP-31 | F-12  | Anggota mencoba memproses pengembalian lewat URL               | Ditolak dengan halaman “Akses ditolak” (403)                                                       |
| KP-32 | F-13  | Admin membuka riwayat seorang anggota                          | Semua sewa anggota tersebut tampil dengan tanggal sewa, jatuh tempo, tanggal kembali, dan denda    |
| KP-33 | F-13  | Admin menekan Cetak                                            | Tampilan versi cetak tanpa menu navigasi, siap dicetak atau disimpan sebagai PDF                   |

## 5. Validasi Field per Form

Validasi dijalankan di server untuk setiap form (F-14, BR-18). Kolom “Aturan Laravel” langsung bisa dipakai di Form Request. Pesan error ditulis dalam Bahasa Indonesia dan tampil di bawah field terkait.

| Form            | Field                        | Aturan Laravel                                                                         | Pesan jika gagal                                                     |
|-----------------|------------------------------|----------------------------------------------------------------------------------------|----------------------------------------------------------------------|
| Registrasi      | Nama                         | required\|string\|max:100                                                              | Nama wajib diisi                                                     |
| Registrasi      | Email                        | required\|email\|max:255\|unique:users,email                                           | Email wajib diisi / format email tidak valid / email sudah terdaftar |
| Registrasi      | Password                     | required\|string\|min:8\|confirmed                                                     | Password minimal 8 karakter / konfirmasi password tidak cocok        |
| Registrasi      | Telepon                      | required\|digits_between:10,15                                                         | Telepon wajib diisi, 10–15 digit angka                               |
| Registrasi      | Alamat                       | required\|string\|max:500                                                              | Alamat wajib diisi                                                   |
| Login           | Email                        | required\|email                                                                        | Email wajib diisi                                                    |
| Login           | Password                     | required                                                                               | Password wajib diisi                                                 |
| Profil          | Nama, telepon, alamat        | Sama seperti registrasi                                                                | Sama seperti registrasi                                              |
| Unit            | Kode                         | required\|alpha_dash\|max:20\|unique:units,code (abaikan unit yang sedang diubah)      | Kode wajib diisi / kode unit sudah dipakai                           |
| Unit            | Nama                         | required\|string\|max:100                                                              | Nama unit wajib diisi                                                |
| Unit            | Deskripsi                    | nullable\|string\|max:1000                                                             | Deskripsi maksimal 1000 karakter                                     |
| Unit            | Kategori                     | required\|array\|min:1; tiap item exists:categories,id                                 | Pilih minimal 1 kategori                                             |
| Kategori        | Nama                         | required\|string\|max:50\|unique:categories,name (abaikan kategori yang sedang diubah) | Nama kategori wajib diisi / sudah dipakai                            |
| Kategori        | Deskripsi                    | nullable\|string\|max:500                                                              | Deskripsi maksimal 500 karakter                                      |
| Anggota (Admin) | Nama, email, telepon, alamat | Sama seperti registrasi; email abaikan anggota yang sedang diubah                      | Sama seperti registrasi                                              |
| Anggota (Admin) | Password                     | Tambah: required\|min:8\|confirmed; Ubah: nullable\|min:8\|confirmed                   | Kosongkan saat mengubah jika password tidak diganti                  |
| Pencarian       | Kata kunci                   | nullable\|string\|max:100                                                              | Kata kunci maksimal 100 karakter                                     |

Form sewa dan pengembalian tidak punya isian. Validasinya berupa aturan bisnis (status unit, kuota, status sewa) yang dicek di server sesuai KP-22 s.d. KP-31.

## 6. Daftar Halaman & Hak Akses

Aplikasi terdiri dari 14 halaman. URL pada tabel berikut merupakan rancangan awal.

| No  | Halaman                            | URL usulan                        | Tamu | Anggota       | Admin          | Fitur      |
|-----|------------------------------------|-----------------------------------|------|---------------|----------------|------------|
| 1   | Login                              | /login                            | ✓    | —             | —              | F-02       |
| 2   | Registrasi                         | /register                         | ✓    | —             | —              | F-01       |
| 3   | Dashboard                          | /dashboard                        | —    | ✓             | ✓              | S-05       |
| 4   | Daftar unit + pencarian            | /units                            | —    | ✓             | ✓              | F-04, F-05 |
| 5   | Detail unit + tombol Sewa          | /units/{id}                       | —    | ✓ (bisa sewa) | ✓ (lihat saja) | F-04, F-09 |
| 6   | Profil saya                        | /profile                          | —    | ✓             | ✓              | F-03       |
| 7   | Sewa Saya                          | /my-rentals                       | —    | ✓             | —              | F-10       |
| 8   | Kelola Unit (daftar, tambah, ubah) | /admin/units                      | —    | —             | ✓              | F-06       |
| 9   | Kelola Kategori                    | /admin/categories                 | —    | —             | ✓              | F-07       |
| 10  | Kelola Anggota                     | /admin/members                    | —    | —             | ✓              | F-08       |
| 11  | Sewa Aktif + tombol Proses Kembali | /admin/rentals                    | —    | —             | ✓              | F-11, F-12 |
| 12  | Riwayat sewa anggota               | /admin/members/{id}/rentals       | —    | —             | ✓              | F-13       |
| 13  | Riwayat versi cetak                | /admin/members/{id}/rentals/print | —    | —             | ✓              | F-13       |
| 14  | Akses ditolak / tidak ditemukan    | 403 / 404                         | ✓    | ✓             | ✓              | KP-06      |

Menu navigasi Anggota: Dashboard, Daftar Unit, Sewa Saya, Profil, Logout. Menu Admin: Dashboard, Daftar Unit, Kelola Unit, Kelola Kategori, Kelola Anggota, Sewa Aktif, Profil, Logout.

## 7. Alur Pengguna

Detail percabangan kedua alur berikut terdapat pada Flowchart 1–4 di dokumen Flowchart & ERD.

**Alur Anggota: dari daftar sampai sewa**

1.  Buka /register, isi nama, email, password, telepon, dan alamat, lalu tekan Daftar.

2.  Sistem login otomatis dan menampilkan Dashboard dengan sisa kuota “2 dari 2”.

3.  Buka Daftar Unit, cari “zeta”, lalu pilih salah satu Zeta Saucer yang berstatus tersedia.

4.  Tekan Sewa di halaman detail. Sistem menampilkan pesan sukses dan tanggal jatuh tempo.

5.  Buka Sewa Saya untuk melihat sewa aktif beserta jatuh temponya.

6.  Untuk mengembalikan, Anggota menghubungi Admin di luar aplikasi.

**Alur Admin: mengelola data sampai mencetak riwayat**

1.  Login dengan akun Admin dari seed.

2.  Di Kelola Kategori, tambah kategori baru.

3.  Di Kelola Unit, tambah unit dengan kode unik dan pilih kategorinya.

4.  Buka Sewa Aktif, pilih sewa milik anggota yang datang, lalu tekan Proses Kembali. Sistem menampilkan ringkasan dan denda jika terlambat.

5.  Buka Kelola Anggota, pilih anggota, lalu buka Riwayat.

6.  Tekan Cetak dan simpan sebagai PDF.

## 8. Kondisi Tampilan

Setiap halaman harus punya tampilan untuk kondisi kosong, error, sukses, dan proses, bukan hanya kondisi normal.

| Kondisi               | Di mana                              | Tampilan                                                                       |
|-----------------------|--------------------------------------|--------------------------------------------------------------------------------|
| Kosong                | Daftar unit (belum ada data)         | “Belum ada unit.” Admin melihat tombol Tambah Unit                             |
| Kosong                | Hasil pencarian                      | “Unit tidak ditemukan untuk kata kunci ‘…’” dengan tautan Reset                |
| Kosong                | Sewa Saya                            | “Kamu belum pernah menyewa unit.” dengan tautan ke Daftar Unit                 |
| Kosong                | Sewa Aktif                           | “Tidak ada unit yang sedang disewa.”                                           |
| Kosong                | Riwayat anggota                      | “Anggota ini belum pernah menyewa.”; tombol Cetak nonaktif                     |
| Error validasi        | Semua form                           | Pesan merah di bawah field; isian lain tetap terisi                            |
| Error aturan bisnis   | Sewa, pengembalian, hapus            | Pesan gagal di bagian atas halaman, sesuai kalimat di KP-15, 21, 23, 24, 30    |
| Error akses           | URL Admin dibuka Anggota             | Halaman 403 “Akses ditolak” dengan tombol kembali ke Dashboard                 |
| Error tidak ditemukan | ID unit atau anggota tidak ada       | Halaman 404 “Data tidak ditemukan”                                             |
| Sukses                | Setelah simpan, hapus, sewa, kembali | Pesan hijau di bagian atas halaman tujuan (S-01)                               |
| Proses                | Semua tombol kirim form              | Tombol nonaktif dan berlabel “Memproses…” setelah ditekan, mencegah klik ganda |

## 9. Data Seed Awal

Satu perintah `php artisan migrate:fresh --seed` harus menghasilkan data yang cukup untuk mendemokan semua fitur Must (F-15, BR-20). Tanggal sewa contoh dihitung relatif terhadap hari seed dijalankan (H), sehingga kondisi “aktif”, “terlambat”, dan “sudah kembali” selalu muncul kapan pun demo dilakukan.

**Akun** (password semua akun contoh: `password`, khusus untuk demo)

| Nama           | Email               | Peran   | Telepon      | Alamat                  |
|----------------|---------------------|---------|--------------|-------------------------|
| Admin XenoRent | admin@xenorent.test | admin   | 081200000001 | Hanggar Utama, Sektor 7 |
| Rina Kosmos    | rina@xenorent.test  | anggota | 081200000002 | Jl. Orbit No. 12        |
| Bima Nebula    | bima@xenorent.test  | anggota | 081200000003 | Jl. Galaksi No. 5       |

**Kategori:** Piring Terbang, Pengintai, Kargo, Penumpang, Antargalaksi, Ramah Atmosfer Bumi.

**Unit**

| Kode    | Nama              | Kategori                                       | Status awal |
|---------|-------------------|------------------------------------------------|-------------|
| ZTS-001 | Zeta Saucer       | Piring Terbang, Penumpang                      | disewa      |
| ZTS-002 | Zeta Saucer       | Piring Terbang, Penumpang                      | tersedia    |
| ZTS-003 | Zeta Saucer       | Piring Terbang, Penumpang, Ramah Atmosfer Bumi | tersedia    |
| NBS-001 | Nebula Scout      | Pengintai, Antargalaksi                        | disewa      |
| NBS-002 | Nebula Scout      | Pengintai, Antargalaksi                        | tersedia    |
| KPH-001 | Kepler Hauler     | Kargo                                          | tersedia    |
| KPH-002 | Kepler Hauler     | Kargo, Ramah Atmosfer Bumi                     | tersedia    |
| ADC-001 | Andromeda Cruiser | Penumpang, Antargalaksi                        | tersedia    |
| ORG-001 | Orion Glider      | Pengintai, Ramah Atmosfer Bumi                 | tersedia    |
| ORG-002 | Orion Glider      | Pengintai, Ramah Atmosfer Bumi                 | tersedia    |

**Sewa contoh**

| Anggota     | Unit    | Tanggal sewa | Jatuh tempo | Tanggal kembali | Status       | Denda    | Untuk mendemokan                         |
|-------------|---------|--------------|-------------|-----------------|--------------|----------|------------------------------------------|
| Rina Kosmos | ZTS-001 | H−2          | H+3         | —               | disewa       | —        | Sewa aktif normal                        |
| Rina Kosmos | NBS-001 | H−8          | H−3         | —               | disewa       | —        | Sewa terlambat; kuota Rina penuh (KP-23) |
| Bima Nebula | KPH-001 | H−12         | H−7         | H−5             | dikembalikan | Rp20.000 | Riwayat dengan denda (KP-32)             |

Dengan data ini, Bima bisa dipakai untuk demo sewa baru (kuota kosong), Rina untuk demo kuota penuh, dan pengembalian NBS-001 untuk demo denda 3 hari.

## 10. Kriteria Sukses

Produk dinyatakan siap rilis jika keenam syarat berikut terpenuhi.

| Syarat                                                                        | Cara mengukur                                   | Tujuan BRD       |
|-------------------------------------------------------------------------------|-------------------------------------------------|------------------|
| 33 dari 33 kriteria penerimaan (KP-01 s.d. KP-33) lolos                       | Tabel Hasil UAT                                 | TB-01 s.d. TB-05 |
| Semua form menolak isian kosong pada field wajib                              | Uji setiap form di bagian 5 dengan field kosong | NFR-03           |
| `php artisan migrate:fresh --seed` berjalan tanpa error di laptop yang bersih | Dicoba oleh anggota tim selain pembuat          | NFR-08           |
| Hitungan denda sistem sama dengan hitungan manual                             | KP-29 dan pengembalian NBS-001 dari seed        | TB-03            |
| Riwayat tercetak rapi sebagai PDF                                             | KP-33                                           | NFR-06           |
| README, dokumen, kode, dan video demo ada di GitLab                           | Cek isi repositori                              | TB-06            |

## 11. Di Luar Cakupan

Fitur berikut tidak dibuat, sesuai ruang lingkup BRD:

- Pembayaran online, payment gateway, dan biaya sewa. Denda hanya dicatat dan dibayar langsung ke Admin.

- Pengembalian mandiri oleh Anggota.

- Reservasi atau booking unit untuk tanggal mendatang.

- Notifikasi email, WhatsApp, atau push, termasuk verifikasi email dan lupa password.

- Upload foto unit.

- Aplikasi mobile native.

## 12. Ringkasan Keputusan

Lima keputusan berikut menjadi acuan untuk tahap implementasi dan pengujian.

1.  16 fitur Must (F-01 s.d. F-16) adalah definisi selesai. Should dan Could tidak boleh mendahului Must.

2.  Admin tidak bisa menyewa. Tombol Sewa hanya tampil untuk Anggota, dan server juga menolak sewa dari Admin.

3.  33 kriteria penerimaan (KP) menjadi skenario UAT dengan kode yang sama, sehingga hasil uji bisa dilacak ke fitur dan BRD.

4.  Aturan validasi di bagian 5 dipakai apa adanya di Form Request Laravel, dengan pesan error Bahasa Indonesia.

5.  Seed memakai tanggal relatif (H) dan memuat 3 akun, 6 kategori, 10 unit, serta 3 sewa contoh, sehingga semua kondisi bisa didemokan kapan pun.
