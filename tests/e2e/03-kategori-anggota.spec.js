import {
    AKUN, UNIT_SEED, cekPesan, cekTag, expect, idUser, kirimLangsung, login, logout, q, uat,
} from './support.js';

const kartuAdmin = (page, teks) => page.locator('.record-card').filter({ hasText: teks });

uat('KP-17', {
    fitur: 'F-07',
    ref: 'BR-10, NFR-02',
    judul: 'Admin menambah kategori dengan nama yang sudah ada',
    langkah: ['Login sebagai Admin lalu buka menu Kategori.', 'Isi nama "Kargo" (sudah ada) lalu tekan "Tambah kategori".'],
    harapan: 'Ditolak dengan pesan "Nama kategori sudah dipakai"; jumlah kategori tidak bertambah.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.getByRole('link', { name: 'Kategori' }).click();

    const form = page.locator('form[action$="/admin/categories"]');
    await form.locator('#name').fill('Kargo');
    await form.getByRole('button', { name: 'Tambah kategori' }).click();

    await cekPesan(page, 'Nama kategori sudah dipakai', { area: page.locator('.field:has(#name) .field-error') });
    await bukti('Kategori "Kargo" ditolak karena nama sudah dipakai');
    expect(q('select count(*) as n from categories where name = ?', ['Kargo'])[0].n).toBe(1);
    expect(q('select count(*) as n from categories')[0].n).toBe(6);
});

uat('F-07b', {
    fitur: 'F-07',
    ref: 'BR-10',
    judul: 'Admin menambah dan mengubah kategori',
    langkah: ['Login sebagai Admin lalu buka menu Kategori.', 'Tambah kategori "Evakuasi" dengan deskripsi.', 'Ubah namanya menjadi "Evakuasi Darurat" dan simpan.'],
    harapan: 'Kategori baru tampil dengan pesan sukses; perubahan nama tersimpan.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/categories');
    const form = page.locator('form[action$="/admin/categories"]');
    await form.locator('#name').fill('Evakuasi');
    await form.locator('#description').fill('Kapal untuk misi evakuasi.');
    await form.getByRole('button', { name: 'Tambah kategori' }).click();
    await expect(page.locator('.alert-success')).toContainText('Kategori berhasil ditambahkan');
    await expect(kartuAdmin(page, 'Kapal untuk misi evakuasi.')).toHaveCount(1);
    await bukti('Kategori Evakuasi berhasil ditambahkan');

    const kartu = kartuAdmin(page, 'Kapal untuk misi evakuasi.');
    await kartu.locator('summary').click();
    await kartu.locator('input[name="name"]').fill('Evakuasi Darurat');
    await kartu.getByRole('button', { name: 'Simpan perubahan' }).click();
    await expect(page.locator('.alert-success')).toContainText('Kategori berhasil diperbarui');
    await expect(kartuAdmin(page, 'Kapal untuk misi evakuasi.').locator('h3')).toHaveText('Evakuasi Darurat');
    await bukti('Nama kategori berhasil diubah');
});

uat('KP-18', {
    fitur: 'F-07',
    ref: 'BR-10, ATB-09',
    judul: 'Admin menghapus kategori yang dipakai beberapa unit',
    langkah: [
        'Login sebagai Admin lalu buka menu Kategori.',
        'Pada kategori "Penumpang" (dipakai 4 unit) buka "Ubah kategori", tekan "Hapus kategori", dan setujui konfirmasi.',
        'Buka Daftar Unit.',
    ],
    harapan: 'Kategori terhapus; seluruh 10 unit tetap ada tanpa kategori tersebut.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/categories');

    const kartu = page.locator('.record-card').filter({ has: page.locator('h3', { hasText: /^Penumpang$/ }) });
    await expect(kartu).toContainText('4 unit terhubung');
    
    await kartu.locator('summary').click();
    await bukti('Kategori Penumpang (4 unit terhubung) sebelum dihapus');
    await kartu.getByRole('button', { name: 'Hapus kategori' }).click();

    await expect(page.locator('.alert-success')).toContainText('Kategori berhasil dihapus');
    expect(page.dialogs[0]).toContain('Penumpang');
    await expect(page.locator('.record-card h3', { hasText: /^Penumpang$/ })).toHaveCount(0);
    await bukti('Kategori Penumpang terhapus dengan pesan sukses');

    await page.goto('/units');
    await expect(page.locator('.unit-card')).toHaveCount(UNIT_SEED.length);
    await expect(page.locator('.tag-list span', { hasText: /^Penumpang$/ })).toHaveCount(0);
    await cekTag(page.locator('.unit-card').filter({ hasText: 'ZTS-001' }).locator('.tag-list span'), ['Piring Terbang']);
    await bukti('Seluruh unit tetap ada; tag Penumpang lepas dari unit');

    expect(q('select count(*) as n from units where deleted_at is null')[0].n).toBe(UNIT_SEED.length);
    expect(q('select count(*) as n from category_unit')[0].n).toBe(
        UNIT_SEED.reduce((jumlah, u) => jumlah + u.kategori.filter((k) => k !== 'Penumpang').length, 0),
    );
});

uat('KP-19', {
    fitur: 'F-08',
    ref: 'BR-11, BR-04',
    judul: 'Admin menambah anggota dengan data lengkap',
    langkah: [
        'Login sebagai Admin lalu buka menu Anggota.',
        'Isi nama, email, telepon, alamat, password, dan konfirmasi password lalu tekan "Tambah anggota".',
        'Keluar, lalu login memakai akun anggota baru tersebut.',
    ],
    harapan: 'Akun anggota dan profilnya tersimpan; anggota baru bisa login ke Dashboard Anggota.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.getByRole('link', { name: 'Anggota' }).click();

    await page.fill('#new-member-name', 'Tala Pulsar');
    await page.fill('#new-member-email', 'tala.pulsar@xenorent.test');
    await page.fill('#new-member-phone', '081311112222');
    await page.fill('#new-member-address', 'Jl. Pulsar No. 3');
    await page.fill('#new-member-password', 'rahasia123');
    await page.fill('#new-member-confirmation', 'rahasia123');
    await bukti('Form tambah anggota terisi lengkap');
    await page.getByRole('button', { name: 'Tambah anggota' }).click();

    await expect(page.locator('.alert-success')).toContainText('Anggota berhasil ditambahkan');
    await expect(kartuAdmin(page, 'tala.pulsar@xenorent.test')).toHaveCount(1);
    await bukti('Anggota baru tampil di daftar dengan pesan sukses');

    const baris = q('select u.role, p.phone from users u join profiles p on p.user_id = u.id where u.email = ?', ['tala.pulsar@xenorent.test']);
    expect(baris).toHaveLength(1);
    expect(baris[0]).toMatchObject({ role: 'anggota', phone: '081311112222' });

    await logout(page);
    await page.fill('#email', 'tala.pulsar@xenorent.test');
    await page.fill('#password', 'rahasia123');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Tala Pulsar');
    await bukti('Anggota baru berhasil login ke Dashboard Anggota');
});

uat('KP-20', {
    fitur: 'F-08',
    ref: 'BR-11, NFR-01',
    judul: 'Admin mengubah data anggota dengan password dikosongkan',
    langkah: [
        'Login sebagai Admin lalu buka menu Anggota.',
        'Buka "Ubah data anggota" pada Bima, ubah nama dan telepon, biarkan password kosong, lalu simpan.',
        'Keluar dan login sebagai Bima memakai password lama.',
    ],
    harapan: 'Nama dan telepon tersimpan; password lama tetap berlaku.',
}, async ({ page, bukti }) => {
    const idBima = idUser(AKUN.bima.email);
    const hashSebelum = q('select password from users where id = ?', [idBima])[0].password;

    await login(page, AKUN.admin);
    await page.goto('/admin/members');
    const kartu = kartuAdmin(page, AKUN.bima.email);
    await kartu.locator('summary').click();
    await kartu.locator(`#member-name-${idBima}`).fill('Bima Nebula Prime');
    await kartu.locator(`#member-phone-${idBima}`).fill('081377778888');
    await expect(kartu.locator(`#member-password-${idBima}`)).toHaveValue('');
    await bukti('Form ubah anggota Bima, kolom password dikosongkan');
    await kartu.getByRole('button', { name: 'Simpan perubahan' }).click();

    await expect(page.locator('.alert-success')).toContainText('Anggota berhasil diperbarui');
    await expect(kartuAdmin(page, AKUN.bima.email)).toContainText('Bima Nebula Prime');
    await expect(kartuAdmin(page, AKUN.bima.email)).toContainText('081377778888');
    await bukti('Data Bima tersimpan dengan pesan sukses');

    expect(q('select password from users where id = ?', [idBima])[0].password).toBe(hashSebelum);

    await logout(page);
    await page.fill('#email', AKUN.bima.email);
    await page.fill('#password', 'password');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Bima Nebula Prime');
    await bukti('Bima masih bisa login dengan password lama');
});

uat('KP-21', {
    fitur: 'F-08',
    ref: 'ATB-08, INT-07',
    judul: 'Admin menghapus anggota yang masih punya sewa aktif',
    langkah: [
        'Login sebagai Admin lalu buka menu Anggota.',
        'Buka "Ubah data anggota" pada Rina (2 sewa aktif) dan cari tombol hapus.',
        'Paksa penghapusan lewat permintaan langsung ke URL hapus anggota.',
    ],
    harapan: 'Tombol hapus tidak tersedia; jika dipaksa, ditolak dengan pesan "Anggota masih punya sewa aktif" dan akun tetap ada.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/members');

    const kartu = kartuAdmin(page, AKUN.rina.email);
    await kartu.locator('summary').click();
    await expect(kartu.getByRole('button', { name: 'Hapus akun' })).toHaveCount(0);
    await expect(kartu).toContainText('masih punya sewa aktif');
    await bukti('Rina punya sewa aktif: tombol Hapus akun tidak tersedia');

    await kirimLangsung(page, `/admin/members/${idUser(AKUN.rina.email)}`, 'DELETE');
    await expect(page).toHaveURL(/\/admin\/members$/);
    await cekPesan(page, 'Anggota masih punya sewa aktif');
    await bukti('Penghapusan dipaksa lewat URL ditolak server');

    expect(q('select deleted_at from users where email = ?', [AKUN.rina.email])[0].deleted_at).toBeNull();
    await expect(kartuAdmin(page, AKUN.rina.email)).toHaveCount(1);
});

uat('F-08b', {
    fitur: 'F-08',
    ref: 'BR-11, ATB-08, INT-08, TB-05',
    judul: 'Admin menghapus anggota tanpa sewa aktif; riwayat sewanya tetap utuh',
    langkah: [
        'Login sebagai Admin lalu buka menu Anggota.',
        'Pada Bima (tidak punya sewa aktif) buka "Ubah data anggota", tekan "Hapus akun", dan setujui konfirmasi.',
        'Buka riwayat sewa Bima lewat URL riwayat.',
    ],
    harapan: 'Anggota hilang dari daftar; riwayat sewa Bima tetap bisa dibuka lengkap dengan dendanya.',
}, async ({ page, bukti }) => {
    const idBima = idUser(AKUN.bima.email);
    await login(page, AKUN.admin);
    await page.goto('/admin/members');
    const kartu = kartuAdmin(page, AKUN.bima.email);
    await kartu.locator('summary').click();
    await kartu.getByRole('button', { name: 'Hapus akun' }).click();

    await expect(page.locator('.alert-success')).toContainText('Anggota berhasil dihapus');
    await expect(kartuAdmin(page, AKUN.bima.email)).toHaveCount(0);
    await bukti('Bima terhapus dari daftar anggota');

    await page.goto(`/admin/members/${idBima}/rentals`);
    await expect(page.locator('.data-table tbody tr')).toHaveCount(1);
    await expect(page.locator('.data-table tbody tr')).toContainText('Rp20.000');
    await bukti('Riwayat sewa Bima tetap utuh setelah akunnya dihapus');

    expect(q('select deleted_at from users where id = ?', [idBima])[0].deleted_at).not.toBeNull();
});
