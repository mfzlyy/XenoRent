# Bukti Minimum Unit Tambahan XenoRent

Dokumen ini mencatat implementasi tambahan yang dibuat untuk melengkapi portofolio. Bukti hanya boleh diklaim setelah perintah tes/build benar-benar berhasil dan screenshot aktual diambil.

## Unit 10 — Migrasi ke teknologi baru

**Perubahan:** alur aset berpindah dari pemanggilan langsung file statis `public/css/xenorent.css` dan `public/js/xenorent.js` ke sumber `resources/css/app.css` dan `resources/js/app.js`, yang diproses Laravel Vite Plugin.

**Perbandingan:** cara lama sederhana dan tidak perlu build, tetapi tidak menyediakan manifest/hashing aset. Vite menyiapkan bundling dan integrasi development/production; konsekuensinya aset harus dibangun dengan `npm run build` atau dilayani oleh Vite saat development.

**Screenshot:** `vite.config.js`, pemanggilan `@vite` pada `resources/views/layouts/app.blade.php`, dan output sukses `npm run build`.

**Keterangan siap tempel:** “XenoRent memindahkan pengelolaan aset dari file statis yang dipanggil langsung ke pipeline Laravel Vite. Source CSS/JS ditempatkan di `resources/`, didaftarkan pada konfigurasi Vite, lalu diintegrasikan di layout Blade. Hasil build diperiksa sebelum aplikasi dijalankan.”

## Unit 12 — Pemrograman multimedia

**Perubahan:** halaman `/panduan-media` memainkan contoh audio WAV lokal dengan elemen HTML5 `<audio controls>`. Audio tidak diputar otomatis dan mempunyai label aksesibel.

**Rencana dan kompatibilitas:** dibuat 26 Oktober 2026 sebagai demo satu sesi untuk browser desktop/mobile yang mendukung HTML5 audio. Tes Chromium memverifikasi player tampil dan file WAV lokal merespons HTTP 200; pemutaran suara tetap dicoba manual saat presentasi.

**Screenshot:** `screenshots/unit-12-media-demo.png`; kode `resources/views/media-guide.blade.php`; file `public/audio/rental-signal.wav`.

**Keterangan siap tempel:** “Fitur demonstrasi menggunakan elemen audio HTML5 untuk memutar contoh WAV lokal. Kontrol playback dilakukan pengguna, tanpa autoplay. Pengujian dilakukan pada browser yang digunakan saat presentasi; hasil kompatibilitas jangan digeneralisasi di luar browser/perangkat yang benar-benar diuji.”

## Unit 13 — Code review

**Review pendahuluan:** scope perubahan diperiksa terhadap tujuan fitur, akses route, fallback metrik, label audio, dan hasil tes. Karena penilaian individu, peserta tetap perlu meninjau diff sendiri dan mengisi checklist dengan nama/tanggal serta temuan yang benar-benar diverifikasi.

**Screenshot:** `Hasil-Review-Pendahuluan.md`, diff perubahan, dan checklist mandiri yang sudah diisi peserta. Review pendahuluan berbantuan AI hanya panduan; peserta harus melakukan review sendiri dan menyertakan temuan aktual.

**Keterangan siap tempel:** “Perubahan ditinjau terhadap kebutuhan fitur, akses berbasis peran, penanganan kondisi gagal, dan konsistensi kode. Temuan serta tindak lanjut dicatat pada checklist review.”

**Catatan:** karena unit dinilai secara individu, isi nama peserta sebagai reviewer setelah benar-benar memeriksa diff dan checklist.

## Unit 14 — Pengujian kode statis

**Bukti yang tersedia:** `scripts/check-static.php` menggunakan parser token PHP untuk menemukan kesalahan sintaks pada berkas PHP di `app`, `routes`, `config`, `database`, dan `tests`. PHPUnit menguji perilaku fitur; `tests/performance/load-smoke.mjs` mengirim 20 request konkuren ke halaman login dan mencatat status serta waktu respons.

**Hasil yang dijalankan di workspace:** pemeriksaan sintaks PHP lulus untuk 107 file; PHPUnit lulus 59/59 test dengan 241 assertion. Smoke-load mengirim 20 request konkuren ke `GET /login`: 20 respons HTTP 200, wall time 3.821 ms dan p95 3.606 ms pada server lokal. Build Vite 8.3.5 berhasil.

**Keterangan siap tempel:** “Pemeriksaan sintaks statis dijalankan pada source PHP dan hasilnya dicatat. Tes PHPUnit menjadi driver pengujian fitur, sedangkan smoke-load mengirim request konkuren ke endpoint login. Angka waktu hanya berlaku untuk mesin dan kondisi uji yang dicatat.”

**Catatan:** pemeriksaan sintaks bukan analisis tipe penuh; smoke-load bukan bukti kapasitas produksi.

## Unit 15 — Alert notification

**Perubahan:** halaman monitoring admin menampilkan alert dalam aplikasi ketika resource melewati ambang atau database tidak dapat dihubungi. Jika metrik tidak tersedia, halaman menampilkan keterangan dan tidak menghentikan seluruh halaman.

**Screenshot:** kondisi normal dan kondisi alert yang benar-benar berhasil direproduksi. Jangan membuat screenshot kondisi alert dengan mengubah angka secara palsu.

**Keterangan siap tempel:** “Halaman admin menampilkan peringatan in-app ketika ambang disk/memori terlampaui atau database tidak tersedia. Jika metrik tidak dapat dibaca, halaman tetap memberikan status yang aman. Mekanisme ini adalah alert in-app, bukan SMS/email.”

## Unit 16 — Pemantauan resource

**Perubahan:** route admin `/admin/resources` menampilkan penggunaan disk, penggunaan memori proses PHP, ukuran file SQLite jika tersedia, status koneksi database, dan ambang peringatan disk 85% serta memori PHP 80%.

**Screenshot:** `screenshots/unit-16-resource-monitor.png`. Screenshot memuat waktu pengukuran, disk 70,3% terpakai, memori proses PHP 17,2%, dan database terhubung pada lingkungan uji saat capture. Angka hanya berlaku untuk sesi/mesin tersebut.

**Keterangan siap tempel:** “Dashboard mengambil snapshot resource saat halaman dibuka. Disk dan memori proses PHP ditampilkan sebagai persentase dengan ambang yang tercantum. Status database dan ukuran SQLite ditampilkan jika tersedia. Nilai pada screenshot hanya menggambarkan kondisi lingkungan pengujian saat itu.”

## Unit 17 — Pembaruan perangkat lunak

**Perubahan versi demonstrasi:** pembaruan XenoRent pada 26 Oktober 2026 menambahkan pipeline Vite, halaman demo audio, dashboard monitoring admin, serta tes pendukung. Fitur diterapkan pada aplikasi yang sudah ada dan akses monitoring dibatasi untuk admin.

**Screenshot:** perbandingan sebelum/sesudah pada diff Git, route dan halaman baru, serta hasil tes setelah pembaruan.

**Keterangan siap tempel:** “Pembaruan demonstrasi menambahkan pengelolaan aset Vite, halaman audio panduan, dan dashboard monitoring ke XenoRent. Perbedaan fitur dan file yang berubah ditunjukkan pada diff; hasil tes/build sesudah perubahan dilampirkan.”

## Hasil yang wajib dicatat sebelum bukti dikirim

- Environment yang dipakai saat pengujian: PHP 8.4.27, Node.js 24.11.1, Chromium Playwright.
- Build Vite lulus; pemeriksaan sintaks 107 file PHP lulus; PHPUnit 59/59 dengan 241 assertion lulus.
- Smoke-load lokal: 20 request konkuren ke `GET /login`, 20 HTTP 200, wall time 3.821 ms, p95 3.606 ms. Ini bukan jaminan kapasitas produksi.
- Tes browser untuk demo audio dan dashboard monitoring lulus 2/2; pemutaran audio manual tetap perlu didemonstrasikan saat presentasi.
- Peserta harus mengisi nama/tanggal pada checklist setelah melakukan review mandiri.
