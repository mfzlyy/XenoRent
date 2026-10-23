import fs from 'node:fs';
import path from 'node:path';
import { PDF_DIR } from './env.js';
import {
    AKUN, TARIF_DENDA, aturTanggalSewa, cekPesan, expect, idSewa, idUnit, idUser, kirimLangsung, login, logout, q, rupiah, tabBaru, tgl, uat,
} from './support.js';

const baris = (page, kode) => page.locator('.data-table tbody tr').filter({ hasText: kode });
const sisaKuota = (page) => page.locator('.quota-panel strong');

/** Anggota menyewa unit lewat halaman detail, lalu keluar. */
async function sewa(page, akun, kode) {
    await login(page, akun);
    await page.goto(`/units/${idUnit(kode)}`);
    await page.getByRole('button', { name: 'Sewa kapal' }).click();
    await expect(page.locator('.alert-success')).toContainText('Unit berhasil disewa');
    await logout(page);
}

/** Admin menekan "Proses kembali" pada baris sewa tertentu di Sewa Aktif. */
async function prosesKembali(page, kode) {
    await page.goto('/admin/rentals/active');
    await baris(page, kode).getByRole('button', { name: 'Proses kembali' }).click();
}

uat('KP-28', {
    fitur: 'F-12',
    ref: 'BR-14, ATB-02, ATB-04, ATB-05, ATB-06',
    judul: 'Admin memproses pengembalian sebelum atau tepat di jatuh tempo',
    langkah: [
        'Login sebagai Admin lalu buka Sewa Aktif.',
        'Pada ZTS-001 milik Rina (jatuh tempo hari ini + 3) tekan "Proses kembali" dan setujui konfirmasi.',
        'Ulangi untuk sewa Bima yang jatuh temponya tepat hari ini.',
        'Login sebagai Rina dan periksa Sewa Saya serta sisa kuota.',
    ],
    harapan: 'Status sewa dikembalikan, denda Rp0, unit kembali tersedia, dan kuota anggota pulih.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.getByRole('link', { name: 'Sewa Aktif', exact: true }).click();
    await bukti('Sewa Aktif sebelum pengembalian');
    await baris(page, 'ZTS-001').getByRole('button', { name: 'Proses kembali' }).click();

    expect(page.dialogs[0]).toContain('pengembalian');
    await expect(page.locator('.alert-success')).toContainText('Pengembalian diproses. Tidak ada denda');
    await expect(baris(page, 'ZTS-001')).toHaveCount(0);
    await bukti('Pengembalian sebelum jatuh tempo: Tidak ada denda');

    const sewaRina = q("select r.status, r.fine_amount, r.returned_at, u.status as status_unit from rentals r join units u on u.id = r.unit_id where r.id = ?", [idSewa(AKUN.rina.email, 'ZTS-001')])[0];
    expect(sewaRina).toMatchObject({ status: 'dikembalikan', fine_amount: 0, status_unit: 'tersedia' });
    expect(sewaRina.returned_at.slice(0, 10)).toBe(new Date().toISOString().slice(0, 10));

    // Kasus tepat di jatuh tempo: Bima menyewa, jatuh tempo diatur hari ini.
    await logout(page);
    await sewa(page, AKUN.bima, 'ZTS-002');
    aturTanggalSewa(idSewa(AKUN.bima.email, 'ZTS-002'), -5, 0);
    await login(page, AKUN.admin);
    await prosesKembali(page, 'ZTS-002');
    await expect(page.locator('.alert-success')).toContainText('Pengembalian diproses. Tidak ada denda');
    await bukti('Pengembalian tepat di hari jatuh tempo: Tidak ada denda');
    expect(q('select fine_amount, status from rentals where id = ?', [idSewa(AKUN.bima.email, 'ZTS-002')])[0]).toMatchObject({ fine_amount: 0, status: 'dikembalikan' });
    await logout(page);

    await login(page, AKUN.rina);
    await expect(sisaKuota(page)).toHaveText(/1\s*\/\s*2/);
    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(baris(page, 'ZTS-001')).toContainText('Dikembalikan');
    await expect(baris(page, 'ZTS-001')).toContainText('Rp0');
    await expect(baris(page, 'ZTS-001')).toContainText(tgl(0));
    await bukti('Sewa Saya Rina: ZTS-001 dikembalikan, denda Rp0, kuota pulih menjadi 1 dari 2');

    await page.goto('/units');
    await expect(page.locator('.unit-card').filter({ hasText: 'ZTS-001' }).locator('.status-tag')).toHaveText('Tersedia');
});

uat('KP-29', {
    fitur: 'F-12',
    ref: 'BR-13, ATB-05, TB-03, NFR-07',
    judul: 'Admin memproses pengembalian 2 hari setelah jatuh tempo',
    langkah: [
        'Login sebagai Bima dan sewa ZTS-002; atur tanggal sewa menjadi 7 hari lalu sehingga jatuh tempo 2 hari lalu.',
        'Login sebagai Admin, buka Sewa Aktif, tekan "Proses kembali" pada sewa tersebut.',
        'Login sebagai Bima dan periksa Sewa Saya.',
    ],
    harapan: `Denda = 2 × tarif per hari (${rupiah(2 * TARIF_DENDA)} dengan tarif ${rupiah(TARIF_DENDA)}); tercatat di sewa dan unit kembali tersedia.`,
}, async ({ page, bukti }) => {
    await sewa(page, AKUN.bima, 'ZTS-002');
    const id = idSewa(AKUN.bima.email, 'ZTS-002');
    aturTanggalSewa(id, -7, -2);

    await login(page, AKUN.admin);
    await page.goto('/admin/rentals/active');
    await expect(baris(page, 'ZTS-002').locator('.status-late')).toHaveText('Terlambat');
    await expect(baris(page, 'ZTS-002')).toContainText(tgl(-2));
    await bukti('Sewa ZTS-002 Bima jatuh tempo 2 hari lalu, bertanda Terlambat');
    await baris(page, 'ZTS-002').getByRole('button', { name: 'Proses kembali' }).click();

    await expect(page.locator('.alert-success')).toContainText(`Pengembalian diproses. Denda: ${rupiah(2 * TARIF_DENDA)}`);
    await bukti('Pengembalian diproses dengan denda Rp20.000 (2 hari × Rp10.000)');
    expect(q('select fine_amount, status from rentals where id = ?', [id])[0]).toMatchObject({ fine_amount: 20_000, status: 'dikembalikan' });
    expect(q('select status from units where code = ?', ['ZTS-002'])[0].status).toBe('tersedia');
    await logout(page);

    await login(page, AKUN.bima);
    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(baris(page, 'ZTS-002')).toContainText('Rp20.000');
    await expect(baris(page, 'ZTS-002')).toContainText('Dikembalikan');
    await bukti('Sewa Saya Bima menampilkan denda Rp20.000');
});

uat('TB-03', {
    fitur: 'F-12',
    ref: 'TB-03, PRD kriteria sukses 4',
    judul: 'Denda sistem sama dengan hitungan manual di semua skenario keterlambatan',
    langkah: [
        'Proses pengembalian NBS-001 dari data seed (jatuh tempo 3 hari lalu).',
        'Buat sewa Bima yang terlambat 1 hari dan 5 hari lalu proses pengembaliannya.',
        'Bandingkan denda di layar dengan hitungan manual: hari terlambat × Rp10.000.',
    ],
    harapan: 'Denda 3 hari = Rp30.000 (NBS-001 seed), 1 hari = Rp10.000, 5 hari = Rp50.000.',
}, async ({ page, bukti }) => {
    const kasus = [];

    await login(page, AKUN.admin);
    await prosesKembali(page, 'NBS-001');
    await expect(page.locator('.alert-success')).toContainText(`Denda: ${rupiah(3 * TARIF_DENDA)}`);
    await bukti('NBS-001 seed (terlambat 3 hari): Denda Rp30.000');
    kasus.push([idSewa(AKUN.rina.email, 'NBS-001'), 3]);
    await logout(page);

    await sewa(page, AKUN.bima, 'ZTS-002');
    await sewa(page, AKUN.bima, 'ORG-001');
    aturTanggalSewa(idSewa(AKUN.bima.email, 'ZTS-002'), -6, -1);
    aturTanggalSewa(idSewa(AKUN.bima.email, 'ORG-001'), -10, -5);
    kasus.push([idSewa(AKUN.bima.email, 'ZTS-002'), 1], [idSewa(AKUN.bima.email, 'ORG-001'), 5]);

    await login(page, AKUN.admin);
    await prosesKembali(page, 'ZTS-002');
    await expect(page.locator('.alert-success')).toContainText(`Denda: ${rupiah(1 * TARIF_DENDA)}`);
    await bukti('Terlambat 1 hari: Denda Rp10.000');
    await prosesKembali(page, 'ORG-001');
    await expect(page.locator('.alert-success')).toContainText(`Denda: ${rupiah(5 * TARIF_DENDA)}`);
    await bukti('Terlambat 5 hari: Denda Rp50.000');

    const rekap = [];
    for (const [id, hari] of kasus) {
        const aktual = q('select fine_amount from rentals where id = ?', [id])[0].fine_amount;
        expect(aktual, `${hari} hari terlambat`).toBe(hari * TARIF_DENDA);
        rekap.push([true, `${hari} hari × ${rupiah(TARIF_DENDA)} = ${rupiah(hari * TARIF_DENDA)} (hitungan manual) — sistem: ${rupiah(aktual)}`]);
    }
    await bukti.kartu('Rekap hitungan manual vs sistem', 'TB-03 · Denda sistem = hitungan manual', rekap);
});

uat('KP-30', {
    fitur: 'F-12',
    ref: 'NFR-02, INT-06, Flowchart 3',
    judul: 'Admin memproses sewa yang sudah dikembalikan',
    langkah: [
        'Login sebagai Admin dan buka Sewa Aktif di dua tab.',
        'Di tab pertama, proses pengembalian ZTS-001.',
        'Di tab kedua (tampilan lama), tekan "Proses kembali" pada sewa yang sama.',
    ],
    harapan: 'Ditolak dengan pesan "Sewa sudah dikembalikan"; denda dan tanggal kembali tidak berubah dan tidak diproses dua kali.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    const tabKedua = await tabBaru(page);
    await page.goto('/admin/rentals/active');
    await tabKedua.goto('/admin/rentals/active');

    await baris(page, 'ZTS-001').getByRole('button', { name: 'Proses kembali' }).click();
    await expect(page.locator('.alert-success')).toContainText('Pengembalian diproses');
    const id = idSewa(AKUN.rina.email, 'ZTS-001');
    const setelahPertama = q('select returned_at, fine_amount, updated_at from rentals where id = ?', [id])[0];

    await expect(baris(tabKedua, 'ZTS-001')).toHaveCount(1);
    await bukti('Tab kedua masih menampilkan ZTS-001 (data lama)', { halaman: tabKedua });
    await baris(tabKedua, 'ZTS-001').getByRole('button', { name: 'Proses kembali' }).click();

    await cekPesan(tabKedua, 'Sewa sudah dikembalikan');
    await bukti('Pemrosesan kedua ditolak', { halaman: tabKedua });
    expect(q('select returned_at, fine_amount, updated_at from rentals where id = ?', [id])[0]).toEqual(setelahPertama);
    expect(q('select status from units where code = ?', ['ZTS-001'])[0].status).toBe('tersedia');
});

uat('KP-31', {
    fitur: 'F-12',
    ref: 'BR-14, ATB-06, TB-04, NFR-01',
    judul: 'Anggota mencoba memproses pengembalian lewat URL',
    langkah: ['Login sebagai Rina (pemilik sewa ZTS-001).', 'Paksa permintaan pengembalian ke URL proses kembali sewa tersebut.'],
    harapan: 'Ditolak dengan halaman "Akses ditolak" (403); sewa tetap berstatus disewa.',
}, async ({ page, bukti }) => {
    const id = idSewa(AKUN.rina.email, 'ZTS-001');
    await login(page, AKUN.rina);
    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(page.getByRole('button', { name: 'Proses kembali' })).toHaveCount(0);

    const respons = await kirimLangsung(page, `/admin/rentals/${id}/return`);
    expect(respons.status()).toBe(403);
    await expect(page.getByRole('heading', { name: 'Akses ditolak' })).toBeVisible();
    await bukti('Anggota memaksa proses kembali: halaman Akses ditolak (403)');

    expect(q('select status, returned_at from rentals where id = ?', [id])[0]).toMatchObject({ status: 'disewa', returned_at: null });
    expect(q('select status from units where code = ?', ['ZTS-001'])[0].status).toBe('disewa');
});

uat('KP-32', {
    fitur: 'F-13',
    ref: 'BR-17, TB-05',
    judul: 'Admin membuka riwayat sewa seorang anggota',
    langkah: ['Login sebagai Admin lalu buka menu Anggota.', 'Pada Bima tekan "Lihat riwayat sewa".'],
    harapan: 'Semua sewa anggota tampil lengkap dengan tanggal sewa, jatuh tempo, tanggal kembali, dan denda.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.getByRole('link', { name: 'Anggota', exact: true }).click();
    await page.locator('.record-card').filter({ hasText: AKUN.bima.email }).getByRole('link', { name: 'Lihat riwayat sewa' }).click();

    await expect(page).toHaveURL(new RegExp(`/admin/members/${idUser(AKUN.bima.email)}/rentals$`));
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Riwayat sewa');
    await expect(page.locator('.page-heading')).toContainText(`${AKUN.bima.nama} · ${AKUN.bima.email}`);
    const kph = baris(page, 'KPH-001');
    await expect(page.locator('.data-table tbody tr')).toHaveCount(1);
    await expect(kph).toContainText('Kepler Hauler');
    await expect(kph).toContainText(tgl(-12));
    await expect(kph).toContainText(tgl(-7));
    await expect(kph).toContainText(tgl(-5));
    await expect(kph).toContainText('Dikembalikan');
    await expect(kph).toContainText('Rp20.000');
    await bukti('Riwayat sewa Bima: tanggal sewa, jatuh tempo, tanggal kembali, dan denda');

    await page.goto(`/admin/members/${idUser(AKUN.rina.email)}/rentals`);
    await expect(page.locator('.data-table tbody tr')).toHaveCount(2);
    await bukti('Riwayat sewa Rina: dua sewa aktif');
});

uat('KP-33', {
    fitur: 'F-13',
    ref: 'BR-17, NFR-06',
    judul: 'Admin menekan Cetak: tampilan cetak tanpa menu navigasi dan siap disimpan sebagai PDF',
    langkah: [
        'Login sebagai Admin, buka riwayat sewa Bima, lalu tekan "Print riwayat".',
        'Di halaman versi cetak tekan "Print riwayat" (memanggil dialog cetak browser).',
        'Tampilkan halaman dalam mode cetak dan simpan sebagai PDF.',
    ],
    harapan: 'Versi cetak tanpa menu navigasi, footer, dan tombol; tabel riwayat tampil rapi dan berhasil disimpan sebagai PDF.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto(`/admin/members/${idUser(AKUN.bima.email)}/rentals`);
    await page.getByRole('link', { name: 'Print riwayat' }).click();
    await expect(page).toHaveURL(/\/rentals\/print$/);

    // Layar biasa: menu navigasi masih ada. Mode cetak: menu hilang.
    await expect(page.locator('.topbar')).toBeVisible();
    await bukti('Halaman riwayat versi cetak di layar (sebelum mode cetak)');

    await page.evaluate(() => {
        window.__dicetak = 0;
        window.print = () => { window.__dicetak += 1; };
    });
    await page.locator('[data-print]').click();
    expect(await page.evaluate(() => window.__dicetak)).toBe(1);

    await page.emulateMedia({ media: 'print' });
    await expect(page.locator('.topbar')).toBeHidden();
    await expect(page.locator('.site-footer')).toBeHidden();
    await expect(page.locator('[data-print]')).toBeHidden();
    await expect(page.locator('.data-table')).toBeVisible();
    await expect(baris(page, 'KPH-001')).toContainText('Rp20.000');
    await bukti('Tampilan mode cetak: tanpa menu navigasi, footer, dan tombol');

    fs.mkdirSync(PDF_DIR, { recursive: true });
    const berkasPdf = path.join(PDF_DIR, 'KP-33_riwayat-sewa-bima.pdf');
    await page.pdf({ path: berkasPdf, format: 'A4', landscape: true, printBackground: true, preferCSSPageSize: true });
    const isi = fs.readFileSync(berkasPdf);
    expect(isi.subarray(0, 5).toString()).toBe('%PDF-');
    expect(isi.length).toBeGreaterThan(5_000);
    await bukti.kartu('PDF riwayat sewa berhasil dibuat', 'KP-33 · Simpan sebagai PDF', [
        [true, 'Berkas: docs/uat/pdf/KP-33_riwayat-sewa-bima.pdf'],
        [true, `Ukuran: ${(isi.length / 1024).toFixed(1)} KB, ditandai header %PDF-`],
        [true, 'Menu navigasi, footer, dan tombol Print tidak ikut tercetak'],
    ]);
});
