# Catatan Pembaruan Perangkat Lunak XenoRent

## Tujuan

Catatan ini merekam pembaruan demonstrasi pada aplikasi XenoRent yang telah berjalan, agar perubahan fitur, integrasi, dan verifikasinya dapat ditelusuri.

## Kondisi sebelum pembaruan


- Aset CSS dan JavaScript dipanggil langsung dari folder `public/`.
- Belum ada halaman demo audio multimedia.
- Belum ada halaman monitoring resource khusus admin.

## Perubahan yang diterapkan

1. Aset CSS/JavaScript dipindahkan ke source `resources/` dan diintegrasikan melalui Laravel Vite Plugin.
2. Halaman Panduan Media menambahkan pemutar audio lokal HTML5.
3. Dashboard resource admin menampilkan disk, memori proses PHP, ukuran SQLite, status koneksi, dan alert in-app.
4. Tes feature dan smoke-load ditambahkan untuk membantu verifikasi.

## Pemeriksaan pembaruan

Hasil aktual pada workspace pengembangan:

- PHP 8.4.27; Node.js 24.11.1.
- Build Vite 8.3.5 berhasil.
- Pemeriksaan sintaks lulus untuk 107 file PHP.
- PHPUnit: 59/59 tes lulus, 241 assertion.
- Dua tes browser tambahan lulus (demo audio dan dashboard resource).
- Smoke-load lokal: 20 request konkuren ke `GET /login`, 20/20 HTTP 200; wall time 3.821 ms; p95 3.606 ms. Hasil ini bukan batas kapasitas produksi.
- Tes browser memverifikasi player dan file WAV dapat diakses; pemutaran manual perlu dicoba saat presentasi.
- Review source awal dicatat di `Hasil-Review-Pendahuluan.md`; peer review oleh anggota tim belum diklaim.


