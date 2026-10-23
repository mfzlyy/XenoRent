# XenoRent — Sistem Rental Kapal Alien

XenoRent adalah aplikasi web untuk menyewa kapal alien dari sebuah hanggar. Anggota bisa mendaftar, mencari kapal berdasarkan nama, lalu menyewa maksimal 2 unit selama paling lama 5 hari. Pengembalian hanya diproses oleh Admin, dan keterlambatan dikenai denda otomatis.

## Identitas Tim

| | |
|---|---|
| **Nama kelompok** | XenoRent |
| **Nama tim** | XenoRent |
| **Nama project** | XenoRent |
| **Anggota tim** | Frontend: Muhammad Nadhif Aqila, Backend: Stevy Reuben Januardi, Dokumentasi: Muhammad Fazly Radithya |

## Video Demo

LINK

## Fitur

**Semua pengguna**
- Registrasi mandiri (otomatis berperan Anggota), login, dan logout.
- Ubah profil sendiri (satu akun = satu profil).
- Daftar dan detail unit, pencarian unit berdasarkan nama (tidak membedakan huruf besar-kecil, cocok sebagian).

**Anggota**
- Sewa unit: maksimal 2 sewa aktif bersamaan, jatuh tempo otomatis tanggal sewa + 5 hari.
- Sewa Saya: daftar sewa milik sendiri beserta jatuh tempo dan denda.

**Admin**
- Kelola unit (tambah, ubah, hapus; satu unit bisa punya banyak kategori; kode unit unik, nama boleh sama).
- Kelola kategori dan anggota (tambah, ubah, hapus).
- Sewa Aktif: lihat semua unit yang sedang disewa dan proses pengembalian; denda dihitung otomatis.
- Riwayat sewa per anggota dan versi cetak (bisa disimpan sebagai PDF).

**Aturan bisnis** ditegakkan di backend: kuota 2 unit, unit hanya bisa disewa saat tersedia, sewa dan pengembalian berjalan dalam satu transaksi database, dan riwayat sewa tidak hilang saat unit atau anggota dihapus (soft delete).

Denda = hari terlambat × tarif per hari (bawaan Rp10.000). Tarif dan durasi sewa diatur lewat `.env`: `RENTAL_DAILY_FINE` dan `RENTAL_DURATION_DAYS`.

## Skema Database

![ERD XenoRent](docs/images/erd.png)

Enam tabel inti: `users`, `profiles`, `categories`, `units`, `category_unit`, `rentals`. Penjelasan lengkap ada di [docs/Flowchart-ERD.md](docs/Flowchart-ERD.md).

## Menjalankan Aplikasi

Kebutuhan: PHP 8.3+, Composer, Node.js, dan SQLite.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Buka http://localhost:8000. Satu perintah `php artisan migrate:fresh --seed` membangun ulang seluruh database beserta data contoh.

### Akun contoh (password semua akun: `password`)

| Peran | Email |
|---|---|
| Admin | admin@xenorent.test |
| Anggota | rina@xenorent.test (kuota penuh, ada sewa terlambat) |
| Anggota | bima@xenorent.test (kuota kosong, punya riwayat dengan denda) |

## Pengujian

```bash
php artisan test        # PHPUnit (unit dan feature test backend)
npm run uat             # UAT end-to-end dengan Playwright
```

Hasil UAT beserta tangkapan layar per skenario ada di [docs/uat/Hasil-UAT.md](docs/uat/Hasil-UAT.md).

## Dokumentasi

- [BRD](docs/BRD.md) — kebutuhan bisnis dan aturan bisnis
- [Flowchart & ERD](docs/Flowchart-ERD.md) — alur proses dan rancangan database
- [PRD](docs/PRD.md) — fitur, kriteria penerimaan, validasi, dan data seed
- [Hasil UAT](docs/uat/Hasil-UAT.md)
- [Manual Book](docs/uat/Manual-Book.md)
- [Dokumentasi Project](docs/uat/Dokumentasi-Project.md)

## Teknologi

Laravel 13, PHP 8.3, SQLite, Blade, CSS kustom (`public/css/xenorent.css`).