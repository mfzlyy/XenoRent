import {
    AKUN, catat, expect, idUnit, idUser, login, logout, q, run, uat,
} from './support.js';

/** Mendaftarkan anggota baru lewat form registrasi; anggota langsung login. */
async function daftarAnggotaBaru(page, { nama, email }) {
    await page.goto('/register');
    await page.fill('#name', nama);
    await page.fill('#email', email);
    await page.fill('#password', 'rahasia123');
    await page.fill('#password_confirmation', 'rahasia123');
    await page.fill('#phone', '081300001111');
    await page.fill('#address', 'Jl. Uji Coba No. 1');
    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

uat('KT-01', {
    fitur: 'F-10',
    ref: 'PRD bagian 8 (kondisi kosong)',
    judul: 'Sewa Saya kosong untuk anggota yang belum pernah menyewa',
    langkah: ['Daftar sebagai anggota baru.', 'Buka Sewa Saya dan Dashboard.'],
    harapan: 'Tampil "Kamu belum pernah menyewa unit." dengan tautan ke Daftar Unit.',
}, async ({ page, bukti }) => {
    await daftarAnggotaBaru(page, { nama: 'Nova Kosong', email: 'nova.kosong@xenorent.test' });
    await expect(page.getByRole('heading', { name: 'Belum ada kapal yang disewa.' })).toBeVisible();
    await bukti('Dashboard anggota baru: belum ada sewa');

    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(page.getByRole('heading', { name: 'Kamu belum pernah menyewa unit.' })).toBeVisible();
    await bukti('Sewa Saya kosong dengan tautan ke Daftar Unit');
    await page.getByRole('link', { name: 'Buka daftar unit' }).click();
    await expect(page).toHaveURL(/\/units$/);
});

uat('KT-02', {
    fitur: 'F-11',
    ref: 'PRD bagian 8 (kondisi kosong)',
    judul: 'Sewa Aktif kosong setelah semua unit dikembalikan',
    langkah: ['Login sebagai Admin dan proses pengembalian seluruh sewa aktif (ZTS-001 dan NBS-001).', 'Buka Sewa Aktif dan Dashboard Admin.'],
    harapan: 'Tampil "Tidak ada unit yang sedang disewa."',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/rentals/active');
    for (const kode of ['ZTS-001', 'NBS-001']) {
        await page.locator('.data-table tbody tr').filter({ hasText: kode }).getByRole('button', { name: 'Proses kembali' }).click();
        await expect(page.locator('.alert-success')).toContainText('Pengembalian diproses');
    }
    await expect(page.getByRole('heading', { name: 'Tidak ada unit yang sedang disewa.' })).toBeVisible();
    await bukti('Sewa Aktif kosong setelah semua dikembalikan');

    await page.getByRole('link', { name: 'Dashboard' }).click();
    await expect(page.getByRole('heading', { name: 'Tidak ada kapal yang sedang disewa.' })).toBeVisible();
});

uat('KT-03', {
    fitur: 'F-13',
    ref: 'PRD bagian 8 (kondisi kosong)',
    judul: 'Riwayat anggota kosong: tombol Cetak nonaktif',
    langkah: ['Daftar anggota baru lalu keluar.', 'Login sebagai Admin dan buka riwayat sewa anggota baru tersebut.'],
    harapan: 'Tampil "Anggota ini belum pernah menyewa."; tombol Cetak nonaktif.',
}, async ({ page, bukti }) => {
    await daftarAnggotaBaru(page, { nama: 'Nova Kosong', email: 'nova.kosong@xenorent.test' });
    await logout(page);

    await login(page, AKUN.admin);
    await page.goto(`/admin/members/${idUser('nova.kosong@xenorent.test')}/rentals`);
    await expect(page.getByRole('heading', { name: 'Anggota ini belum pernah menyewa.' })).toBeVisible();
    const tombol = page.getByRole('button', { name: 'Print tidak tersedia' });
    await expect(tombol).toBeDisabled();
    await bukti('Riwayat anggota baru kosong dengan tombol cetak nonaktif');
});

uat('KT-04', {
    fitur: 'F-04',
    ref: 'PRD bagian 8 (kondisi kosong)',
    judul: 'Daftar unit kosong',
    langkah: ['Kosongkan seluruh unit (kondisi uji).', 'Login sebagai Anggota lalu sebagai Admin dan buka Daftar Unit.'],
    harapan: 'Tampil "Belum ada unit."; Admin melihat tombol/tautan untuk menambah unit.',
}, async ({ page, bukti }) => {
    run("update units set deleted_at = datetime('now')");

    await login(page, AKUN.bima);
    await page.goto('/units');
    await expect(page.getByRole('heading', { name: 'Belum ada unit.' })).toBeVisible();
    await bukti('Daftar unit kosong untuk Anggota');
    await logout(page);

    await login(page, AKUN.admin);
    await page.goto('/units');
    await expect(page.getByRole('heading', { name: 'Belum ada unit.' })).toBeVisible();
    await bukti('Daftar unit kosong untuk Admin');
    const tambah = page.getByRole('link', { name: /tambah unit/i });
    if ((await tambah.count()) === 0) {
        await expect(page.getByRole('link', { name: 'Kelola armada' })).toBeVisible();
        catat('PRD menyebut tombol "Tambah Unit" pada daftar unit kosong; aplikasi menampilkan tautan "Kelola armada" yang mengarah ke form tambah unit.');
    }
});

uat('KT-05', {
    fitur: 'PRD halaman 14',
    ref: 'PRD bagian 8 (error tidak ditemukan)',
    judul: 'Halaman 404 untuk data yang tidak ada',
    langkah: ['Login sebagai Anggota lalu buka /units/99999.', 'Login sebagai Admin lalu buka riwayat anggota dengan ID yang tidak ada.'],
    harapan: 'Tampil halaman 404 "Data tidak ditemukan" dengan tautan kembali.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    const respons = await page.goto('/units/99999');
    expect(respons.status()).toBe(404);
    await expect(page.locator('.eyebrow')).toContainText('404 / DATA TIDAK DITEMUKAN');
    await expect(page.getByRole('link', { name: 'Kembali ke daftar unit' })).toBeVisible();
    await bukti('Detail unit yang tidak ada: halaman 404');
    await logout(page);

    await login(page, AKUN.admin);
    const respons2 = await page.goto('/admin/members/99999/rentals');
    expect(respons2.status()).toBe(404);
    await expect(page.locator('.eyebrow')).toContainText('404 / DATA TIDAK DITEMUKAN');
    await bukti('Riwayat anggota yang tidak ada: halaman 404');
});

uat('KT-06', {
    fitur: 'S-01',
    ref: 'PRD bagian 8 (proses), NFR-02',
    judul: 'Tombol kirim nonaktif dan berlabel "Memproses..." sehingga klik ganda tidak membuat sewa ganda',
    langkah: [
        'Login sebagai Bima dan buka detail unit ZTS-002.',
        'Perlambat respons server, lalu klik ganda "Sewa kapal".',
        'Periksa label tombol dan jumlah sewa yang tercatat.',
    ],
    harapan: 'Tombol berubah menjadi "Memproses..." dan nonaktif; hanya satu sewa yang tercatat.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto(`/units/${idUnit('ZTS-002')}`);

    // Respons server ditahan sampai keadaan tombol selesai diperiksa dan difoto.
    let lepas;
    const gerbang = new Promise((selesai) => { lepas = selesai; });
    await page.route('**/units/*/rent', async (route) => {
        await gerbang;
        await route.continue();
    });
    await page.getByRole('button', { name: 'Sewa kapal' }).dblclick({ noWaitAfter: true });

    await expect.poll(() => page.evaluate(() => document.querySelector('.rental-action button')?.textContent.trim())).toBe('Memproses...');
    expect(await page.evaluate(() => document.querySelector('.rental-action button').disabled)).toBe(true);
    await bukti('Tombol berlabel Memproses... dan nonaktif setelah ditekan', { fullPage: false });
    lepas();

    await expect(page.locator('.alert-success')).toContainText('Unit berhasil disewa');
    expect(q('select count(*) as n from rentals r join units u on u.id = r.unit_id where u.code = ?', ['ZTS-002'])[0].n).toBe(1);
    await bukti('Setelah respons datang: satu sewa tercatat');
});

uat('S-02', {
    fitur: 'S-02',
    ref: 'BR-09 s.d. BR-11',
    judul: 'Dialog konfirmasi sebelum menghapus',
    langkah: [
        'Login sebagai Admin dan buka "Ubah data unit" pada ZTS-002.',
        'Tekan "Hapus unit" lalu batalkan dialog konfirmasi.',
        'Tekan "Hapus unit" lagi dan setujui dialog.',
    ],
    harapan: 'Dialog "Hapus unit ZTS-002?" muncul; saat dibatalkan unit tetap ada, saat disetujui unit terhapus.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');
    const kartu = page.locator('.record-card').filter({ hasText: 'ZTS-002' });
    await kartu.locator('summary').click();

    page.dialogMode = 'dismiss';
    await kartu.getByRole('button', { name: 'Hapus unit' }).click();
    await expect.poll(() => page.dialogs.length).toBe(1);
    expect(page.dialogs[0]).toBe('Hapus unit ZTS-002?');
    await expect(kartu).toBeVisible();
    expect(q('select deleted_at from units where code = ?', ['ZTS-002'])[0].deleted_at).toBeNull();
    await bukti('Dialog "Hapus unit ZTS-002?" dibatalkan: unit tetap ada');

    page.dialogMode = 'accept';
    await kartu.getByRole('button', { name: 'Hapus unit' }).click();
    await expect(page.locator('.alert-success')).toContainText('Unit berhasil dihapus');
    expect(page.dialogs).toEqual(['Hapus unit ZTS-002?', 'Hapus unit ZTS-002?']);
    await bukti('Dialog disetujui: unit terhapus');
    await bukti.kartu('Teks dialog konfirmasi bawaan browser', 'S-02 · Dialog konfirmasi yang ditampilkan browser', [
        [true, `Dialog muncul 2 kali dengan teks: "${page.dialogs[0]}"`],
        [true, 'Dibatalkan pada percobaan pertama: unit tidak terhapus'],
        [true, 'Disetujui pada percobaan kedua: unit terhapus'],
    ]);
});

uat('S-03', {
    fitur: 'S-03',
    ref: 'PRD bagian 3',
    judul: 'Paginasi daftar',
    langkah: ['Tambahkan 20 unit uji (kondisi uji) sehingga total 30 unit.', 'Login sebagai Anggota dan buka Daftar Unit, lalu pindah ke halaman 2.'],
    harapan: 'Daftar dipecah per halaman (PRD: 10 baris per halaman) dengan navigasi halaman.',
}, async ({ page, bukti }) => {
    for (let i = 1; i <= 20; i += 1) {
        run("insert into units (code, name, status, created_at, updated_at) values (?, 'Unit Uji', 'tersedia', datetime('now'), datetime('now'))", [`TST-${String(i).padStart(3, '0')}`]);
    }
    await login(page, AKUN.bima);
    await page.goto('/units');
    const halamanPertama = await page.locator('.unit-card').count();
    expect(halamanPertama).toBeGreaterThan(0);
    expect(halamanPertama).toBeLessThan(30);
    await expect(page.locator('.pagination-wrap')).toBeVisible();
    await bukti('Daftar unit halaman 1 dengan navigasi paginasi');

    await page.locator('.pagination-wrap').getByRole('link', { name: /2|berikut|next/i }).first().click();
    await expect(page).toHaveURL(/page=2/);
    await bukti('Daftar unit halaman 2');
    if (halamanPertama !== 10) {
        catat(`PRD S-03 menyebut 10 baris per halaman; aplikasi menampilkan ${halamanPertama} unit per halaman pada Daftar Unit.`);
    }
});
