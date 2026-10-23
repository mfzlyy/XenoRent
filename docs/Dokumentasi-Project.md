---
title: "06 Dokumentasi Project"
subtitle: "Dokumentasi Teknis XenoRent — Sistem Rental Kapal Alien"
author:
  - Muhammad Fazly Radithya
  - Stevy Reuben Januardi
  - Muhammad Nadhif Aqila
date: "Versi 1.0 · 23 Oktober 2026"
lang: id-ID
---

## 1. Ringkasan Sistem

XenoRent adalah aplikasi web untuk mengelola penyewaan kapal alien dari hanggar. Anggota dapat mencari unit, menyewa unit yang tersedia, melihat sewa miliknya, dan mengubah profil. Admin mengelola unit, kategori, dan anggota, memproses pengembalian, serta melihat dan mencetak riwayat sewa anggota.

Aplikasi dibangun dengan Laravel dan pola arsitektur berlapis. Antarmuka menggunakan Blade dan CSS kustom; data operasional disimpan dalam SQLite. Aturan utama membatasi anggota pada dua sewa aktif, menetapkan masa sewa lima hari, dan menghitung denda keterlambatan Rp10.000 per hari per unit. Tarif dan durasi dapat diatur melalui konfigurasi aplikasi.

Dokumen ini menjelaskan implementasi teknis berdasarkan source code, migration, seeder, konfigurasi, dan laporan pengujian dalam repositori. Dokumen kebutuhan bisnis, rancangan proses dan data, produk, UAT, serta panduan pengguna tetap menjadi rujukan masing-masing: [01 BRD](BRD.md), [02 Flowchart & ERD](Flowchart-ERD.md), [03 PRD](PRD.md), [04 Hasil UAT](Hasil-UAT.md), dan [05 Manual Book](Manual-Book.md).

## 2. Tim dan Pembagian Tugas

| Anggota tim | Tanggung jawab | Fokus pekerjaan |
|---|---|---|
| Muhammad Fazly Radithya | Dokumentasi | BRD, flowchart dan ERD, PRD, UAT, Manual Book, dokumentasi teknis, dan koordinasi bukti pengujian |
| Stevy Reuben Januardi | Backend | Aturan domain, use case, repository, persistensi, autentikasi dan otorisasi sisi server, serta pengujian backend |
| Muhammad Nadhif Aqila | Frontend | Tampilan Blade, CSS responsif, formulir, navigasi, dan perilaku antarmuka pengguna |

Pembagian tersebut menunjukkan area tanggung jawab utama. Integrasi fitur dan pengujian penerimaan dilakukan lintas anggota tim.

## 3. Teknologi dan Versi

| Komponen | Versi atau pilihan | Penggunaan |
|---|---|---|
| PHP | 8.3; kebutuhan Composer `^8.3` | Runtime aplikasi dan tes backend |
| Laravel Framework | 13.38.0 (terkunci di `composer.lock`) | Routing web, middleware, validasi, session, dependency injection, dan ORM |
| Database | SQLite | Penyimpanan data aplikasi dan basis data lokal untuk UAT |
| Antarmuka | Blade dan CSS kustom | Template halaman dan tampilan responsif; stylesheet utama `public/css/xenorent.css` |
| Asset build | Vite 8.3.5 dan Laravel Vite Plugin 3.2.0 | Pengembangan dan kompilasi aset frontend |
| Pengujian backend | PHPUnit 12.5.40 | Tes unit dan feature melalui PHPUnit/Laravel |
| Pengujian browser | Playwright Test 1.64.1 | Skenario end-to-end serta tangkapan layar UAT |
| Package manager frontend | Node.js dan npm | Instalasi dependency serta menjalankan Vite dan Playwright |

Versi Laravel, PHPUnit, Vite, plugin Laravel Vite, dan Playwright di atas mengacu pada lockfile proyek. SQLite tidak diberi nomor versi khusus pada konfigurasi aplikasi.

## 4. Arsitektur dan Alur Request

### 4.1 Lapisan aplikasi

| Lapisan | Lokasi utama | Tanggung jawab |
|---|---|---|
| Presentasi HTTP | `app/Presentation/Http/Controllers`, `app/Presentation/Http/Middleware` | Menerima request web, memvalidasi input, menerapkan middleware peran, memanggil use case, dan memilih respons atau view |
| Application | `app/Application/UseCases`, `app/Application/DTOs`, `app/Application/Interfaces` | Mengatur alur kasus penggunaan, membawa data melalui DTO, dan mendefinisikan kontrak repository |
| Domain | `app/Domain/Entities`, `Enums`, `Exceptions`, `Services`, `ValueObjects` | Menyimpan model konseptual, status dan peran, kebijakan sewa, perhitungan denda, validasi nilai, dan pengecualian bisnis |
| Infrastruktur | `app/Infrastructure/Eloquent`, `app/Infrastructure/Repositories` | Mengubah data ORM menjadi entitas domain dan mengimplementasikan kontrak repository dengan Eloquent |
| Framework dan tampilan | `app/Models`, `app/Providers`, `resources/views`, `routes` | Integrasi Laravel, autentikasi Eloquent, binding dependency, deklarasi route, dan halaman Blade |

`AppServiceProvider` menghubungkan antarmuka repository aplikasi ke implementasi Eloquent. Dengan demikian, use case bergantung pada kontrak repository, sedangkan detail SQLite dan Eloquent berada di infrastruktur.

### 4.2 Siklus request

1. Browser mengirim request HTTP ke route di `routes/web.php`.
2. Middleware `auth` memeriksa sesi. Untuk route berperan, alias `role` memeriksa peran akun dan menolak akses yang tidak sesuai.
3. Controller pada `app/Presentation/Http/Controllers` memvalidasi data request, mengambil ID pengguna dari sesi bila relevan, lalu membentuk DTO.
4. Controller memanggil use case di `app/Application/UseCases`.
5. Use case menerapkan aturan melalui entitas atau layanan domain, kemudian memanggil antarmuka repository.
6. Binding di `AppServiceProvider` memilih repository Eloquent. Repository membaca atau menyimpan record melalui model infrastruktur.
7. Untuk perubahan sewa dan pengembalian, use case menjalankan rangkaian perubahan di dalam transaksi database dan menggunakan operasi penguncian record repository.
8. Hasil dikembalikan ke controller sebagai redirect dengan pesan atau view Blade; browser merender halaman menggunakan aset frontend.

Alur ringkas: **Browser → Route → Middleware → Controller → Use case/DTO → Domain + Repository Interface → Repository Eloquent → SQLite → View/redirect**.

## 5. Struktur Folder

```text
app/
├── Application/
│   ├── DTOs/                 Data input/output use case
│   ├── Interfaces/           Kontrak repository
│   └── UseCases/             Auth, kategori, profil, rental, dan unit
├── Domain/
│   ├── Entities/             User, Profile, Category, Unit, Rental
│   ├── Enums/                Peran user, status unit, status rental
│   ├── Exceptions/           Pengecualian aturan domain
│   ├── Services/              Kebijakan rental dan perhitungan harga/denda
│   └── ValueObjects/          Email, nomor telepon, kode unit
├── Infrastructure/
│   ├── Eloquent/             Model persistensi
│   └── Repositories/         Implementasi repository Eloquent
├── Presentation/Http/
│   ├── Controllers/          Controller HTTP aplikasi
│   └── Middleware/           Pemeriksaan peran
├── Models/                   Model Laravel untuk autentikasi
└── Providers/                Binding dependency aplikasi

database/
├── migrations/               Skema tabel dan indeks
├── seeders/                  Data awal demo
└── factories/                Factory untuk pengujian

resources/views/               Template Blade
public/css/                    CSS kustom
public/js/                     JavaScript frontend
routes/web.php                 Route aplikasi web
config/app.php                 Tarif denda dan durasi rental
tests/                          Unit, feature, dan end-to-end
docs/                           Dokumen proyek, bukti UAT, dan gambar
```

Folder `tests/` berada di tingkat root repositori. Struktur di atas menampilkan area penting untuk pemeliharaan aplikasi, bukan daftar seluruh file.

## 6. Skema Database, Migration, dan Seeder

### 6.1 Tabel dan relasi

Migration bernomor urut di `database/migrations/` membuat tabel sesi dan enam tabel inti berikut.

| Tabel | Kolom dan batasan penting | Relasi atau fungsi |
|---|---|---|
| `users` | `id`, `name`, `email` unik, `password`, `role` (`admin`/`anggota`), `email_verified_at`, timestamp, `deleted_at` | Identitas akun; akun dapat di-soft-delete agar data historis tetap terjaga |
| `profiles` | `id`, `user_id` unik, `phone`, `address`, timestamp | Satu profil untuk satu akun; foreign key ke `users` |
| `categories` | `id`, `name` unik, `description` opsional, timestamp | Kategori untuk klasifikasi unit |
| `units` | `id`, `code` unik, `name`, `description` opsional, `status` (`tersedia`/`disewa`), timestamp, `deleted_at` | Unit kapal; kode unik dan soft delete |
| `category_unit` | `category_id`, `unit_id`, pasangan kunci primer | Tabel pivot relasi many-to-many antara kategori dan unit; foreign key pivot dihapus berantai |
| `rentals` | `id`, `user_id`, `unit_id`, `rented_at`, `due_at`, `returned_at` opsional, `fine_amount`, `status` (`disewa`/`dikembalikan`), timestamp | Riwayat penyewaan; foreign key ke akun dan unit menggunakan aturan `restrictOnDelete` |
| `sessions` | `id`, `user_id` opsional, alamat IP, user agent, `last_activity` | Penyimpanan sesi web sesuai migration Laravel |

Relasi utama: satu akun mempunyai satu profil dan banyak rental; satu unit mempunyai banyak rental sepanjang waktu; kategori dan unit terhubung many-to-many melalui `category_unit`. Indeks pada `rentals` mendukung pencarian berdasarkan pasangan `user_id/status` dan `unit_id/status`. Kunci unik pada email, kode unit, nama kategori, dan `profiles.user_id` membantu menjaga integritas data.

Soft delete pada akun dan unit mempertahankan record untuk konteks historis. Foreign key rental yang bersifat `restrict` mencegah penghapusan permanen akun atau unit yang masih dirujuk oleh riwayat sewa.

### 6.2 Migration dan data seed

Migration membangun tabel berdasarkan urutan dependensi: sesi dan akun, profil, kategori, unit, pivot kategori-unit, lalu rental. Seeder utama berada di `database/seeders/DatabaseSeeder.php` dan dapat dijalankan ulang untuk menyiapkan skenario demo.

Seeder menghasilkan tiga akun contoh, enam kategori, sepuluh unit, dan tiga rental. Tanggal rental dihitung relatif terhadap hari seed dijalankan agar skenario sewa normal, sewa terlambat, dan riwayat dengan denda tetap tersedia. Rina memiliki dua sewa aktif; Bima tidak memiliki sewa aktif dan mempunyai riwayat pengembalian dengan denda Rp20.000.

## 7. Route dan Hak Akses

Semua route aplikasi berada di `routes/web.php`. Hak akses diperiksa oleh middleware server, bukan hanya dengan menyembunyikan menu di tampilan.

### 7.1 Route publik dan route bagi pengguna masuk

| Metode | URI | Pengendali/aksi | Hak akses |
|---|---|---|---|
| `GET` | `/` | Redirect ke route login | Semua pengunjung |
| `GET` | `/login` | Form login | Semua pengunjung |
| `POST` | `/login` | Proses login | Semua pengunjung |
| `GET` | `/register` | Form pendaftaran | Semua pengunjung |
| `POST` | `/register` | Membuat akun anggota | Semua pengunjung; peran ditetapkan sebagai anggota di use case |
| `POST` | `/logout` | Mengakhiri sesi | Route web tersedia; digunakan oleh akun yang masuk |
| `GET` | `/dashboard` | Dashboard | Pengguna masuk (`auth`) |
| `GET` | `/profile` | Form profil | Pengguna masuk (`auth`) |
| `PUT` | `/profile` | Simpan profil | Pengguna masuk; target akun diambil dari sesi |
| `GET` | `/units` | Daftar dan pencarian unit | Pengguna masuk dengan peran anggota atau admin |
| `GET` | `/units/{unit}` | Detail unit | Pengguna masuk dengan peran anggota atau admin |

### 7.2 Route anggota

| Metode | URI | Pengendali/aksi | Hak akses |
|---|---|---|---|
| `POST` | `/units/{unit}/rent` | Buat rental untuk unit | Anggota saja; diperiksa oleh `auth` dan `role:anggota` |
| `GET` | `/rentals/mine` | Riwayat rental pengguna | Anggota saja; data dibatasi ke ID akun dari sesi |

### 7.3 Route admin

Seluruh route di tabel ini berada di bawah prefix `/admin` dan middleware `auth` serta `role:admin`.

| Metode | URI | Pengendali/aksi | Hak akses |
|---|---|---|---|
| `GET` | `/admin/units` | Daftar dan pengelolaan unit | Admin |
| `GET` | `/admin/units/create` | Form unit baru | Admin |
| `POST` | `/admin/units` | Simpan unit baru | Admin |
| `GET` | `/admin/units/{unit}/edit` | Form ubah unit | Admin |
| `PUT` atau `PATCH` | `/admin/units/{unit}` | Simpan perubahan unit | Admin |
| `DELETE` | `/admin/units/{unit}` | Hapus unit | Admin |
| `GET` | `/admin/categories` | Daftar kategori | Admin |
| `POST` | `/admin/categories` | Tambah kategori | Admin |
| `PUT` atau `PATCH` | `/admin/categories/{category}` | Ubah kategori | Admin |
| `DELETE` | `/admin/categories/{category}` | Hapus kategori | Admin |
| `GET` | `/admin/members` | Daftar anggota | Admin |
| `POST` | `/admin/members` | Tambah anggota | Admin |
| `PUT` | `/admin/members/{id}` | Ubah anggota | Admin; record sasaran harus merupakan anggota |
| `DELETE` | `/admin/members/{id}` | Hapus anggota | Admin; akun dengan rental aktif ditolak |
| `GET` | `/admin/members/{id}/rentals` | Riwayat rental anggota | Admin; target harus merupakan anggota |
| `GET` | `/admin/members/{id}/rentals/print` | Tampilan cetak riwayat anggota | Admin; target harus merupakan anggota |
| `GET` | `/admin/rentals/active` | Daftar semua rental aktif | Admin |
| `POST` | `/admin/rentals/{rental}/return` | Memproses pengembalian | Admin saja |

Route resource unit tidak menyediakan halaman `show` admin tersendiri; detail unit publik yang telah masuk tersedia melalui `/units/{unit}` dan dapat dibuka anggota maupun admin. Akses ke akun dengan peran selain anggota pada endpoint pengelolaan/riwayat anggota dibatasi di sisi server.

## 8. Logika Bisnis Inti

### 8.1 Penyewaan unit

Use case `CreateRental` menjalankan proses inti dalam transaksi database. Sistem mengunci record pengguna dan unit melalui repository, memastikan unit ditemukan dan tersedia, menghitung jumlah rental aktif anggota, lalu menerapkan kebijakan batas maksimal dua rental aktif. Jika lolos, sistem menyimpan tanggal sewa, menghitung jatuh tempo dari konfigurasi, membuat rental berstatus `disewa`, lalu mengubah status unit menjadi `disewa`.

Apabila unit sudah disewa atau kuota anggota telah penuh, proses dihentikan oleh pengecualian domain dan pengguna menerima pesan kesalahan. Controller penyewaan hanya menerima request anggota, sedangkan use case tetap memeriksa kebijakan untuk menjaga aturan di lapisan bisnis.

### 8.2 Pengembalian dan denda

Use case `ReturnRental` hanya dapat dipanggil melalui route Admin. Proses memeriksa keberadaan rental dan memastikan statusnya masih aktif. Sistem menetapkan waktu kembali, menghitung denda, mengubah status rental menjadi `dikembalikan`, menyimpan nilai denda, dan mengembalikan status unit menjadi `tersedia` dalam transaksi database.

Tanggal jatuh tempo dihitung sebagai tanggal sewa ditambah durasi konfigurasi. Denda dihitung dari jumlah hari keterlambatan dikalikan tarif denda harian. Pengembalian pada atau sebelum tanggal jatuh tempo menghasilkan denda Rp0; rental yang telah kembali tidak diproses ulang.

Konfigurasi bawaan pada `config/app.php` adalah:

| Pengaturan | Kunci lingkungan | Nilai bawaan |
|---|---|---:|
| Tarif denda per hari | `RENTAL_DAILY_FINE` | Rp10.000 |
| Durasi sewa | `RENTAL_DURATION_DAYS` | 5 hari |

## 9. Validasi dan Keamanan

| Area | Implementasi |
|---|---|
| Autentikasi | Route terlindungi menggunakan middleware `auth`; login memvalidasi email dan kata sandi, lalu meregenerasi sesi. Logout menginvalidasi sesi dan meregenerasi token CSRF. |
| Otorisasi peran | Alias `role` terdaftar di `bootstrap/app.php`. Middleware membandingkan peran akun dengan daftar peran route dan mengembalikan 403 untuk akses yang tidak sesuai. Route operasional Admin memakai `role:admin`; route pembuatan rental memakai `role:anggota`. |
| Penetapan peran saat registrasi | Use case registrasi membuat akun baru dengan peran anggota; formulir tidak menentukan peran. |
| Pengelolaan kata sandi | Kata sandi disimpan menggunakan hashing Laravel (`Hash::make`/bcrypt), bukan teks biasa. Kata sandi wajib saat registrasi dan akun baru; perubahan kata sandi bersifat opsional pada form edit yang sesuai. |
| Validasi request | Controller memvalidasi field wajib, format dan panjang data, email unik, rentang digit telepon, keberadaan kategori, dan pilihan kategori unit. Pencarian unit dibatasi maksimal 100 karakter. |
| Perlindungan data pribadi | Pembaruan profil menggunakan ID akun yang sedang login, bukan ID bebas dari input form. Riwayat “Sewa Saya” diambil hanya untuk pengguna tersebut. |
| Integritas data | Unique index dan foreign key memastikan batasan data utama. Rental memakai foreign key `restrictOnDelete`; akun dan unit memakai soft delete. |
| Perubahan state rental | Pembuatan dan pengembalian rental berlangsung di dalam transaksi; kebijakan domain menolak unit tidak tersedia, kuota berlebih, dan pengembalian ganda. |
| Proteksi endpoint anggota | Endpoint pengubahan, penghapusan, dan riwayat anggota hanya menerima record dengan peran anggota; sasaran dengan peran lain ditangani sebagai tidak ditemukan. |

Validasi di browser dapat membantu pengalaman pengguna, tetapi keputusan akses, keunikan, dan aturan rental tetap ditegakkan di server dan database.

## 10. Instalasi dan Akun Demo

### 10.1 Persyaratan dan langkah menjalankan

Persyaratan lokal: PHP 8.3 atau lebih baru, Composer, Node.js/npm, dan SQLite. Dari root repositori, jalankan:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Buka `http://localhost:8000`. Untuk pengembangan frontend dengan hot reload, gunakan `npm run dev` pada terminal lain. Sebelum menjalankan migration, pastikan koneksi database di `.env` memakai SQLite. Variabel `RENTAL_DAILY_FINE` dan `RENTAL_DURATION_DAYS` dapat diatur sesuai kebijakan yang digunakan.

`php artisan migrate:fresh --seed` menghapus dan membangun ulang semua tabel pada koneksi database aktif. Gunakan hanya untuk lingkungan lokal atau pengujian yang datanya boleh direset; jangan menjalankannya terhadap database berisi data operasional.

### 10.2 Akun demo

| Peran | Email | Kata sandi | Kondisi seed |
|---|---|---|---|
| Admin | `admin@xenorent.test` | `password` | Mengelola unit, kategori, anggota, pengembalian, dan riwayat |
| Anggota — Rina Kosmos | `rina@xenorent.test` | `password` | Dua rental aktif; satu rental telah melewati jatuh tempo |
| Anggota — Bima Nebula | `bima@xenorent.test` | `password` | Tidak ada rental aktif; mempunyai riwayat dengan denda Rp20.000 |

Akun dan kata sandi tersebut hanya untuk demonstrasi lokal. Ganti atau nonaktifkan data demo sebelum aplikasi digunakan di lingkungan selain pengujian.

## 11. Pengujian

| Jenis | Perintah | Hasil yang dicatat |
|---|---|---|
| Tes backend PHPUnit | `php artisan test` | 56 dari 56 tes lulus |
| UAT end-to-end Playwright | `npm run uat` | 66 dari 66 skenario lulus; 0 gagal |
| Laporan Playwright | `npm run uat:report` | Membuka laporan HTML pada `docs/uat/playwright-report` |

UAT mencakup kelompok KP, fitur pendukung, Should/Could, validasi form, kondisi tampilan, tujuan bisnis, kebutuhan nonfungsional, dan integritas data. Lingkungan UAT menggunakan SQLite terpisah dan seeder dijalankan ulang untuk setiap skenario. Bukti skenario berada di `docs/uat/screenshots/`; hasil rinci dan riwayat temuan terdapat pada [laporan UAT v2.0](Hasil-UAT.md).

Setelah perubahan kode, jalankan ulang `php artisan test` dan `npm run uat` sebelum rilis agar aturan backend dan alur browser tetap terverifikasi.

## 12. Kendala dan Solusi

Lima temuan pada putaran pengujian sebelumnya telah diperbaiki dan diverifikasi ulang. Ringkasan berikut mengikuti riwayat UAT.

| Kode | Kendala | Perbaikan yang diterapkan |
|---|---|---|
| TEMUAN-01 | Endpoint anggota dapat menerima target akun Admin | Endpoint ubah, hapus, riwayat, dan cetak memeriksa peran record sasaran; akun nonanggota ditangani sebagai 404. |
| TEMUAN-02 | Penyimpanan kategori tanpa deskripsi menyebabkan error | Deskripsi kategori dibuat opsional dan nilai kosong disimpan sebagai `null`. |
| TEMUAN-03 | Pencarian unit tidak membatasi panjang kata kunci | Validasi sisi server membatasi kata kunci hingga 100 karakter dan menampilkan pesan berbahasa Indonesia. |
| TEMUAN-04 | Sejumlah pesan validasi berbeda dari teks PRD | Pesan untuk skenario terkait diselaraskan dengan kriteria penerimaan PRD. |
| TEMUAN-05 | README belum memiliki identitas tim dan bagian video demo | Identitas tim dan bagian video demo ditambahkan. Sebelum rilis dokumentasi, placeholder tautan video perlu diganti dengan URL demo final. |

## 13. Rencana Pengembangan

| Prioritas usulan | Arah pengembangan | Hasil yang diharapkan |
|---|---|---|
| 1 | Menambahkan pipeline integrasi berkelanjutan untuk menjalankan PHPUnit, build aset, dan UAT pada perubahan kode | Regresi terdeteksi sebelum perubahan digabungkan atau dirilis |
| 2 | Menyiapkan lingkungan staging dan konfigurasi produksi yang aman, termasuk pengelolaan secret, `APP_DEBUG=false`, pencadangan database, dan prosedur pemulihan | Pengoperasian lebih aman dan dapat dipulihkan |
| 3 | Menambahkan notifikasi sebelum jatuh tempo dan saat rental terlambat | Anggota dan Admin dapat menindaklanjuti rental tepat waktu |
| 4 | Menambahkan audit trail untuk perubahan data Admin dan tindakan pengembalian | Perubahan operasional dapat ditelusuri |
| 5 | Menguji skenario transaksi dan konkurensi pada database target deployment | Konsistensi kuota dan ketersediaan unit terukur pada kondisi operasional |

Prioritas tersebut merupakan arah pengembangan setelah ruang lingkup saat ini, bukan fitur yang sudah tersedia.

## 14. Ringkasan Keputusan

1. XenoRent memakai Laravel 13.38.0, PHP 8.3, SQLite, Blade, CSS kustom, dan struktur aplikasi berlapis.
2. Pembagian kode memisahkan controller HTTP, use case dan kontrak repository, aturan domain, serta implementasi Eloquent.
3. Database memuat enam tabel inti beserta tabel sesi; constraint dan soft delete menjaga konsistensi riwayat rental.
4. Sewa dibatasi dua unit aktif per anggota, berdurasi bawaan lima hari, dan dikenai denda bawaan Rp10.000 per hari terlambat; pengembalian hanya dilakukan Admin.
5. Hasil pengujian yang tercatat adalah 56/56 tes backend dan 66/66 skenario UAT lulus; lima temuan sebelumnya telah diperbaiki dan diverifikasi ulang.
6. Sebelum dokumentasi dirilis, ganti placeholder URL video demo pada README dengan tautan final.
