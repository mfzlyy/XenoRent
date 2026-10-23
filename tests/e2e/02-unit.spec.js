import {
    AKUN, UNIT_SEED, catat, cekPesan, cekTag, expect, idUnit, idUser, kirimLangsung, login, q, tgl, uat,
} from './support.js';

const kartuUnit = (page, kode) => page.locator('.unit-card').filter({ hasText: kode });
const kartuAdmin = (page, kode) => page.locator('.record-card').filter({ hasText: kode });

async function cari(page, kataKunci) {
    await page.fill('#q', kataKunci);
    await page.getByRole('button', { name: 'Cari unit' }).click();
}

uat('KP-08', {
    fitur: 'F-04',
    ref: 'BR-06, BR-07',
    judul: 'Daftar unit menampilkan kode, nama, kategori, dan status',
    langkah: ['Login sebagai Anggota (Bima).', 'Buka menu Daftar Unit.'],
    harapan: 'Tampil 10 unit seed; setiap unit memuat kode, nama, kategori, dan status.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.getByRole('link', { name: 'Daftar Unit' }).click();
    await expect(page).toHaveURL(/\/units$/);
    await expect(page.locator('.unit-card')).toHaveCount(UNIT_SEED.length);

    for (const unit of UNIT_SEED) {
        const kartu = kartuUnit(page, unit.kode);
        await expect(kartu, unit.kode).toHaveCount(1);
        await expect(kartu.locator('h2')).toHaveText(unit.nama);
        await expect(kartu.locator('.status-tag')).toHaveText(unit.status);
        for (const kategori of unit.kategori) {
            await expect(kartu.locator('.tag-list span', { hasText: new RegExp(`^${kategori}$`) })).toHaveCount(1);
        }
    }
    await bukti('Daftar unit: 10 unit lengkap dengan kode, nama, kategori, dan status');
});

uat('KP-09', {
    fitur: 'F-04',
    ref: 'BR-07, ATB-07, TB-01',
    judul: 'Tiga unit bernama "Zeta Saucer" tampil terpisah dengan kode berbeda',
    langkah: ['Login sebagai Anggota.', 'Buka Daftar Unit dan perhatikan unit bernama Zeta Saucer.'],
    harapan: 'Tiga kartu "Zeta Saucer" tampil terpisah dengan kode ZTS-001, ZTS-002, ZTS-003.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto('/units');
    const zeta = page.locator('.unit-card').filter({ has: page.locator('h2', { hasText: 'Zeta Saucer' }) });
    await expect(zeta).toHaveCount(3);
    const kode = await zeta.locator('.unit-code').allInnerTexts();
    expect(kode.map((k) => k.trim()).sort()).toEqual(['ZTS-001', 'ZTS-002', 'ZTS-003']);
    expect(new Set(kode).size).toBe(3);
    await bukti('Tiga Zeta Saucer dengan kode unik berbeda', { halaman: page });
});

uat('KP-10', {
    fitur: 'F-05',
    ref: 'BR-08, ATB-10',
    judul: 'Pencarian "zeta" menemukan unit tanpa membedakan huruf besar-kecil',
    langkah: ['Login sebagai Anggota.', 'Di Daftar Unit cari "zeta", lalu "ZETA", "zEtA", dan sebagian kata "saucer".'],
    harapan: 'Semua unit yang namanya mengandung "Zeta" tampil, untuk huruf besar maupun kecil dan pencocokan sebagian.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto('/units');

    await cari(page, 'zeta');
    await expect(page).toHaveURL(/q=zeta/);
    await expect(page.locator('.unit-card')).toHaveCount(3);
    await expect(page.locator('.unit-card h2')).toHaveText(['Zeta Saucer', 'Zeta Saucer', 'Zeta Saucer']);
    await bukti('Hasil pencarian "zeta": tiga Zeta Saucer');

    for (const kata of ['ZETA', 'zEtA', 'sauc']) {
        await cari(page, kata);
        await expect(page.locator('.unit-card'), `kata kunci ${kata}`).toHaveCount(3);
    }
    await cari(page, 'ZETA');
    await bukti('Hasil pencarian "ZETA" (huruf besar) sama dengan "zeta"');
});

uat('KP-11', {
    fitur: 'F-05',
    ref: 'BR-08',
    judul: 'Pencarian tanpa hasil menampilkan pesan "Unit tidak ditemukan"',
    langkah: ['Login sebagai Anggota.', 'Cari kata "xyzabc" yang tidak cocok dengan unit mana pun.'],
    harapan: 'Tampil pesan "Unit tidak ditemukan" beserta tautan Reset.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto('/units');
    await cari(page, 'xyzabc');
    await expect(page.locator('.unit-card')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Unit tidak ditemukan' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Reset pencarian' })).toBeVisible();
    await bukti('Pencarian "xyzabc": pesan Unit tidak ditemukan dengan tautan Reset');

    await page.getByRole('link', { name: 'Reset pencarian' }).click();
    await expect(page.locator('.unit-card')).toHaveCount(UNIT_SEED.length);
});

uat('KP-12', {
    fitur: 'F-06',
    ref: 'BR-06, BR-09, TB-01',
    judul: 'Admin menambah unit dengan kode baru dan 2 kategori',
    langkah: [
        'Login sebagai Admin lalu buka Kelola Unit.',
        'Isi kode ZTS-004, nama Zeta Saucer, deskripsi, dan centang kategori Piring Terbang serta Penumpang.',
        'Tekan "Tambah unit".',
    ],
    harapan: 'Unit tersimpan berstatus tersedia dengan 2 kategori dan tampil di daftar.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.getByRole('link', { name: 'Kelola Unit' }).click();

    const form = page.locator('form[action$="/admin/units"]');
    await form.locator('#code').fill('ZTS-004');
    await form.locator('#name').fill('Zeta Saucer');
    await form.locator('#description').fill('Unit baru hasil penambahan Admin.');
    await form.getByLabel('Piring Terbang').check();
    await form.getByLabel('Penumpang').check();
    await bukti('Form tambah unit terisi (kode ZTS-004, 2 kategori)');
    await form.getByRole('button', { name: 'Tambah unit' }).click();

    await expect(page.locator('.alert-success')).toContainText('Unit berhasil ditambahkan');
    const kartu = kartuAdmin(page, 'ZTS-004');
    await expect(kartu).toHaveCount(1);
    await expect(kartu.locator('.status-tag')).toHaveText('Tersedia');
    await cekTag(kartu.locator('.tag-list span'), ['Piring Terbang', 'Penumpang']);
    await bukti('Unit ZTS-004 tersimpan: tersedia, 2 kategori');

    const unit = q('select status from units where code = ?', ['ZTS-004']);
    expect(unit).toHaveLength(1);
    expect(unit[0].status).toBe('tersedia');
    expect(q('select count(*) as n from category_unit cu join units u on u.id = cu.unit_id where u.code = ?', ['ZTS-004'])[0].n).toBe(2);
});

uat('KP-13', {
    fitur: 'F-06',
    ref: 'BR-07, ATB-07, TB-01, NFR-02',
    judul: 'Admin menambah unit dengan kode yang sudah ada',
    langkah: ['Login sebagai Admin lalu buka Kelola Unit.', 'Isi kode ZTS-001 (sudah dipakai), nama dan kategori, lalu tekan "Tambah unit".'],
    harapan: 'Ditolak dengan pesan "Kode unit sudah dipakai"; tidak ada unit ganda dan isian tetap terisi.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');

    const form = page.locator('form[action$="/admin/units"]');
    await form.locator('#code').fill('ZTS-001');
    await form.locator('#name').fill('Zeta Saucer Kembar');
    await form.getByLabel('Kargo').check();
    await form.getByRole('button', { name: 'Tambah unit' }).click();

    await cekPesan(page, 'Kode unit sudah dipakai', { area: page.locator('.field:has(#code) .field-error') });
    await expect(form.locator('#code')).toHaveValue('ZTS-001');
    await expect(form.locator('#name')).toHaveValue('Zeta Saucer Kembar');
    await bukti('Kode ZTS-001 ditolak karena sudah dipakai, isian tetap terisi');

    expect(q('select count(*) as n from units where code = ?', ['ZTS-001'])[0].n).toBe(1);
});

uat('KP-14', {
    fitur: 'F-06',
    ref: 'BR-06, BR-18',
    judul: 'Admin menyimpan unit tanpa memilih kategori',
    langkah: ['Login sebagai Admin lalu buka Kelola Unit.', 'Isi kode ZTS-005 dan nama, tanpa mencentang kategori, lalu tekan "Tambah unit".'],
    harapan: 'Ditolak dengan pesan "Pilih minimal 1 kategori"; unit tidak tersimpan.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');

    const form = page.locator('form[action$="/admin/units"]');
    await form.locator('#code').fill('ZTS-005');
    await form.locator('#name').fill('Zeta Saucer');
    await form.getByRole('button', { name: 'Tambah unit' }).click();

    await expect(page).toHaveURL(/\/admin\/units$/);
    await cekPesan(page, 'Pilih minimal 1 kategori', { area: page.locator('.category-field .field-error').first() });
    await expect(form.locator('#code')).toHaveValue('ZTS-005');
    await bukti('Unit tanpa kategori ditolak, isian lain tetap terisi');

    expect(q('select count(*) as n from units where code = ?', ['ZTS-005'])[0].n).toBe(0);
});

uat('KP-15', {
    fitur: 'F-06',
    ref: 'ATB-08, INT-07',
    judul: 'Admin menghapus unit yang sedang disewa',
    langkah: [
        'Login sebagai Admin lalu buka Kelola Unit.',
        'Buka "Ubah data unit" pada ZTS-001 (sedang disewa Rina) dan cari tombol hapus.',
        'Paksa penghapusan lewat permintaan langsung ke URL hapus unit.',
    ],
    harapan: 'Tombol hapus tidak tersedia; jika dipaksa, ditolak dengan pesan "Unit masih disewa" dan unit tetap ada.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');

    const kartu = kartuAdmin(page, 'ZTS-001');
    await kartu.locator('summary').click();
    await expect(kartu.getByRole('button', { name: 'Hapus unit' })).toHaveCount(0);
    await expect(kartu).toContainText('Unit masih disewa, penghapusan tidak tersedia.');
    await bukti('ZTS-001 sedang disewa: tombol Hapus unit tidak tersedia');

    await kirimLangsung(page, `/admin/units/${idUnit('ZTS-001')}`, 'DELETE');
    await expect(page).toHaveURL(/\/admin\/units$/);
    await cekPesan(page, 'Unit masih disewa');
    await bukti('Penghapusan dipaksa lewat URL ditolak server');

    expect(q('select deleted_at from units where code = ?', ['ZTS-001'])[0].deleted_at).toBeNull();
    await expect(kartuAdmin(page, 'ZTS-001')).toHaveCount(1);
});

uat('KP-16', {
    fitur: 'F-06',
    ref: 'ATB-08, INT-08, TB-05',
    judul: 'Admin menghapus unit yang tidak sedang disewa; riwayat sewa lama tetap ada',
    langkah: [
        'Login sebagai Admin lalu buka Kelola Unit.',
        'Pada KPH-001 (pernah disewa Bima, sudah dikembalikan) buka "Ubah data unit" lalu tekan "Hapus unit" dan setujui konfirmasi.',
        'Buka riwayat sewa Bima.',
    ],
    harapan: 'Unit hilang dari daftar; riwayat sewa Bima untuk unit tersebut tetap tampil.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');

    const kartu = kartuAdmin(page, 'KPH-001');
    await kartu.locator('summary').click();
    await bukti('Panel ubah unit KPH-001 terbuka dengan tombol Hapus unit');
    await kartu.getByRole('button', { name: 'Hapus unit' }).click();

    await expect(page.locator('.alert-success')).toContainText('Unit berhasil dihapus');
    expect(page.dialogs[0]).toContain('KPH-001');
    await expect(kartuAdmin(page, 'KPH-001')).toHaveCount(0);
    await bukti('KPH-001 terhapus dari daftar dengan pesan sukses');

    await page.goto('/units');
    await expect(kartuUnit(page, 'KPH-001')).toHaveCount(0);

    await page.goto(`/admin/members/${idUser(AKUN.bima.email)}/rentals`);
    const baris = page.locator('.data-table tbody tr');
    await expect(baris).toHaveCount(1);
    await expect(baris).toContainText(tgl(-12));
    await expect(baris).toContainText('Rp20.000');
    await bukti('Riwayat sewa Bima tetap utuh setelah unit KPH-001 dihapus');
    if (!(await baris.innerText()).includes('KPH-001')) {
        catat('Riwayat sewa tetap ada, tetapi setelah unit dihapus kolom kapal menampilkan "Unit tidak tersedia" sehingga kode dan nama unit tidak terbaca.');
    }

    expect(q('select deleted_at from units where code = ?', ['KPH-001'])[0].deleted_at).not.toBeNull();
    expect(q('select count(*) as n from rentals where user_id = ?', [idUser(AKUN.bima.email)])[0].n).toBe(1);
});

uat('F-06b', {
    fitur: 'F-06',
    ref: 'BR-09, BR-06',
    judul: 'Admin mengubah data unit dan kategorinya',
    langkah: ['Login sebagai Admin lalu buka Kelola Unit.', 'Buka "Ubah data unit" pada ZTS-002, ganti nama dan kategorinya, lalu simpan.'],
    harapan: 'Perubahan tersimpan dan tampil di daftar.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');
    const kartu = kartuAdmin(page, 'ZTS-002');
    await kartu.locator('summary').click();
    const id = idUnit('ZTS-002');
    await kartu.locator(`#name-${id}`).fill('Zeta Saucer Mk II');
    await kartu.getByLabel('Kargo').check();
    await kartu.getByLabel('Penumpang').uncheck();
    await bukti('Form ubah unit ZTS-002 terisi perubahan');
    await kartu.getByRole('button', { name: 'Simpan perubahan' }).click();

    await expect(page.locator('.alert-success')).toContainText('Unit berhasil diperbarui');
    const hasil = kartuAdmin(page, 'ZTS-002');
    await expect(hasil.locator('h3')).toHaveText('Zeta Saucer Mk II');
    await cekTag(hasil.locator('.tag-list span'), ['Piring Terbang', 'Kargo']);
    await bukti('ZTS-002 setelah diubah');
});

uat('F-06c', {
    fitur: 'F-06',
    ref: 'BR-09, BR-18',
    judul: 'Admin menyimpan perubahan unit dengan kode milik unit lain',
    langkah: ['Login sebagai Admin lalu buka Kelola Unit.', 'Ubah kode ZTS-002 menjadi ZTS-003 (milik unit lain) lalu simpan.'],
    harapan: 'Ditolak dengan pesan kode sudah dipakai; kode ZTS-002 tidak berubah.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');
    const kartu = kartuAdmin(page, 'ZTS-002');
    await kartu.locator('summary').click();
    await kartu.locator(`#code-${idUnit('ZTS-002')}`).fill('ZTS-003');
    await kartu.getByRole('button', { name: 'Simpan perubahan' }).click();

    await cekPesan(page, 'Kode unit sudah dipakai');
    await bukti('Perubahan kode ke ZTS-003 ditolak');
    expect(q('select count(*) as n from units where code = ?', ['ZTS-002'])[0].n).toBe(1);
    expect(q('select count(*) as n from units where code = ?', ['ZTS-003'])[0].n).toBe(1);
});
