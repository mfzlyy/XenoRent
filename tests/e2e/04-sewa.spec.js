import {
    AKUN, DURASI_HARI, cekPesan, expect, idUnit, kirimLangsung, login, logout, q, tgl, uat,
} from './support.js';

const baris = (page, kode) => page.locator('.data-table tbody tr').filter({ hasText: kode });
const sisaKuota = (page) => page.locator('.quota-panel strong');

/** Anggota membuka detail unit lewat Daftar Unit lalu menekan "Sewa kapal". */
async function sewaLewatUi(page, kode) {
    await page.goto('/units');
    await page.locator('.unit-card').filter({ hasText: kode }).getByRole('link', { name: 'Detail unit' }).click();
    await page.getByRole('button', { name: 'Sewa kapal' }).click();
}

uat('KP-22', {
    fitur: 'F-09',
    ref: 'BR-03, BR-12, BR-13, ATB-01, ATB-02, ATB-03, ATB-04',
    judul: 'Anggota dengan 0 atau 1 sewa aktif menyewa unit tersedia',
    langkah: [
        'Login sebagai Bima (0 sewa aktif).',
        'Buka Daftar Unit, cari "zeta", buka detail ZTS-002, tekan "Sewa kapal" dan setujui konfirmasi.',
        'Sewa satu unit lagi (NBS-002) saat sudah memegang 1 sewa aktif.',
    ],
    harapan: 'Sewa tercatat, unit berubah disewa, jatuh tempo = hari ini + 5 hari; sewa kedua juga berhasil.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await expect(sisaKuota(page)).toHaveText(/2\s*\/\s*2/);

    await page.getByRole('link', { name: 'Daftar Unit' }).click();
    await page.fill('#q', 'zeta');
    await page.getByRole('button', { name: 'Cari unit' }).click();
    await page.locator('.unit-card').filter({ hasText: 'ZTS-002' }).getByRole('link', { name: 'Detail unit' }).click();
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Zeta Saucer');
    await bukti('Detail unit ZTS-002 berstatus tersedia dengan tombol Sewa kapal');

    await page.getByRole('button', { name: 'Sewa kapal' }).click();
    expect(page.dialogs[0]).toBe('Sewakan unit ini?');
    await expect(page.locator('.alert-success')).toContainText(`Unit berhasil disewa. Jatuh tempo: ${tgl(DURASI_HARI)}`);
    await bukti('Sewa berhasil: pesan sukses memuat tanggal jatuh tempo (hari ini + 5)');

    const sewa = q('select r.status, r.rented_at, r.due_at, u.status as status_unit from rentals r join units u on u.id = r.unit_id join users p on p.id = r.user_id where p.email = ? and u.code = ?', [AKUN.bima.email, 'ZTS-002']);
    expect(sewa).toHaveLength(1);
    expect(sewa[0].status).toBe('disewa');
    expect(sewa[0].status_unit).toBe('disewa');
    expect(sewa[0].due_at.slice(0, 10)).toBe(new Date(Date.now() + DURASI_HARI * 864e5).toISOString().slice(0, 10));

    await page.getByRole('link', { name: 'Dashboard' }).click();
    await expect(sisaKuota(page)).toHaveText(/1\s*\/\s*2/);

    await sewaLewatUi(page, 'NBS-002');
    await expect(page.locator('.alert-success')).toContainText('Unit berhasil disewa');
    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(baris(page, 'ZTS-002')).toContainText(tgl(DURASI_HARI));
    await expect(baris(page, 'NBS-002')).toContainText(tgl(DURASI_HARI));
    await bukti('Sewa Saya Bima: dua sewa aktif lengkap dengan jatuh tempo');
});

uat('KP-23', {
    fitur: 'F-09',
    ref: 'BR-12, ATB-02, TB-02, NFR-02',
    judul: 'Anggota dengan 2 sewa aktif mencoba menyewa lagi',
    langkah: [
        'Login sebagai Rina (sudah memegang 2 unit aktif).',
        'Buka detail unit ZTS-002 yang tersedia lalu tekan "Sewa kapal".',
    ],
    harapan: 'Ditolak dengan pesan "Kuota sewa penuh (maksimal 2 unit)"; sewa tidak bertambah dan unit tetap tersedia.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await expect(sisaKuota(page)).toHaveText(/0\s*\/\s*2/);

    await page.goto(`/units/${idUnit('ZTS-002')}`);
    await page.getByRole('button', { name: 'Sewa kapal' }).click();

    await cekPesan(page, 'Kuota sewa penuh (maksimal 2 unit');
    await bukti('Sewa ke-3 ditolak: kuota sewa penuh');

    expect(q('select count(*) as n from rentals r join users u on u.id = r.user_id where u.email = ? and r.status = ?', [AKUN.rina.email, 'disewa'])[0].n).toBe(2);
    expect(q('select status from units where code = ?', ['ZTS-002'])[0].status).toBe('tersedia');
});

uat('KP-24', {
    fitur: 'F-09',
    ref: 'ATB-03',
    judul: 'Anggota mencoba menyewa unit berstatus disewa',
    langkah: [
        'Login sebagai Bima.',
        'Buka detail ZTS-001 yang sedang disewa dan perhatikan tombol Sewa.',
        'Paksa penyewaan lewat permintaan langsung ke URL sewa unit tersebut.',
    ],
    harapan: 'Tombol Sewa tidak aktif; jika dipaksa, ditolak dengan pesan "Unit sedang disewa" dan status sewa tidak berubah.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto('/units');
    const kartu = page.locator('.unit-card').filter({ hasText: 'ZTS-001' });
    await expect(kartu).toContainText('Sedang disewa');
    await expect(kartu.getByRole('link', { name: 'Sewa', exact: true })).toHaveCount(0);

    await page.goto(`/units/${idUnit('ZTS-001')}`);
    await expect(page.getByRole('button', { name: 'Sewa kapal' })).toHaveCount(0);
    await expect(page.locator('.alert-error')).toContainText('Unit sedang disewa');
    await bukti('Detail ZTS-001: tombol Sewa tidak tersedia, tertulis Unit sedang disewa');

    await kirimLangsung(page, `/units/${idUnit('ZTS-001')}/rent`);
    await cekPesan(page, 'Unit sedang disewa');
    await bukti('Penyewaan dipaksa lewat URL ditolak server');

    expect(q('select count(*) as n from rentals r join users u on u.id = r.user_id where u.email = ?', [AKUN.bima.email])[0].n).toBe(1);
    expect(q('select count(*) as n from rentals r join units u on u.id = r.unit_id where u.code = ?', ['ZTS-001'])[0].n).toBe(1);
});

uat('KP-25', {
    fitur: 'F-09',
    ref: 'BR-03, PRD keputusan 2',
    judul: 'Admin membuka detail unit: tombol Sewa tidak tampil dan server menolak sewa dari Admin',
    langkah: [
        'Login sebagai Admin lalu buka detail unit ZTS-002 yang tersedia.',
        'Paksa penyewaan lewat permintaan langsung ke URL sewa.',
    ],
    harapan: 'Tombol Sewa tidak tampil untuk Admin; permintaan paksa ditolak (403) dan unit tetap tersedia.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/units');
    await expect(page.getByRole('link', { name: 'Sewa', exact: true })).toHaveCount(0);

    await page.goto(`/units/${idUnit('ZTS-002')}`);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Zeta Saucer');
    await expect(page.getByRole('button', { name: 'Sewa kapal' })).toHaveCount(0);
    await bukti('Detail unit tersedia dilihat Admin: tidak ada tombol Sewa');

    const respons = await kirimLangsung(page, `/units/${idUnit('ZTS-002')}/rent`);
    expect(respons.status()).toBe(403);
    await expect(page.getByRole('heading', { name: 'Akses ditolak' })).toBeVisible();
    await bukti('Penyewaan dipaksa oleh Admin ditolak (403)');

    expect(q('select status from units where code = ?', ['ZTS-002'])[0].status).toBe('tersedia');
    expect(q('select count(*) as n from rentals')[0].n).toBe(3);
});

uat('KP-26', {
    fitur: 'F-10',
    ref: 'BR-16, TB-04',
    judul: 'Sewa Saya hanya menampilkan sewa milik sendiri',
    langkah: ['Login sebagai Rina lalu buka Sewa Saya.', 'Keluar, login sebagai Bima lalu buka Sewa Saya.'],
    harapan: 'Rina hanya melihat 2 sewa aktifnya; Bima hanya melihat sewa KPH-001 yang selesai beserta denda; keduanya memuat jatuh tempo.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(page).toHaveURL(/\/rentals\/mine$/);
    await expect(page.locator('.data-table tbody tr')).toHaveCount(2);
    await expect(baris(page, 'ZTS-001')).toContainText(tgl(-2));
    await expect(baris(page, 'ZTS-001')).toContainText(tgl(3));
    await expect(baris(page, 'ZTS-001')).toContainText('Belum dikembalikan');
    await expect(baris(page, 'NBS-001')).toContainText(tgl(-3));
    await expect(baris(page, 'KPH-001')).toHaveCount(0);
    await bukti('Sewa Saya Rina: hanya 2 sewa aktif miliknya');
    await logout(page);

    await login(page, AKUN.bima);
    await page.getByRole('link', { name: 'Sewa Saya' }).click();
    await expect(page.locator('.data-table tbody tr')).toHaveCount(1);
    const kph = baris(page, 'KPH-001');
    await expect(kph).toContainText(tgl(-12));
    await expect(kph).toContainText(tgl(-7));
    await expect(kph).toContainText(tgl(-5));
    await expect(kph).toContainText('Dikembalikan');
    await expect(kph).toContainText('Rp20.000');
    await expect(baris(page, 'ZTS-001')).toHaveCount(0);
    await bukti('Sewa Saya Bima: hanya sewa KPH-001 yang selesai beserta denda Rp20.000');
});

uat('KP-27', {
    fitur: 'F-11',
    ref: 'BR-15, C-02',
    judul: 'Admin membuka Sewa Aktif: semua sewa berstatus disewa dari semua anggota',
    langkah: [
        'Login sebagai Bima dan sewa ZTS-002, lalu keluar.',
        'Login sebagai Admin lalu buka menu Sewa Aktif.',
    ],
    harapan: 'Tampil seluruh sewa disewa dari Rina dan Bima (3 baris); sewa selesai tidak tampil; sewa lewat jatuh tempo bertanda Terlambat.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await sewaLewatUi(page, 'ZTS-002');
    await expect(page.locator('.alert-success')).toContainText('Unit berhasil disewa');
    await logout(page);

    await login(page, AKUN.admin);
    await page.getByRole('link', { name: 'Sewa Aktif', exact: true }).click();
    await expect(page).toHaveURL(/\/admin\/rentals\/active$/);
    await expect(page.locator('.data-table tbody tr')).toHaveCount(3);
    await expect(baris(page, 'ZTS-001')).toContainText(AKUN.rina.nama);
    await expect(baris(page, 'NBS-001')).toContainText(AKUN.rina.nama);
    await expect(baris(page, 'ZTS-002')).toContainText(AKUN.bima.nama);
    await expect(baris(page, 'KPH-001')).toHaveCount(0);
    await expect(baris(page, 'NBS-001').locator('.status-late')).toHaveText('Terlambat');
    await expect(baris(page, 'ZTS-001').locator('.status-late')).toHaveCount(0);
    for (const kode of ['ZTS-001', 'NBS-001', 'ZTS-002']) {
        await expect(baris(page, kode).getByRole('button', { name: 'Proses kembali' })).toBeVisible();
    }
    await bukti('Sewa Aktif Admin: 3 sewa dari 2 anggota, NBS-001 bertanda Terlambat');
});
