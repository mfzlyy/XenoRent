import {
    AKUN, catat, expect, idUser, login, q, tanpaValidasiBrowser, uat,
} from './support.js';

/** Pesan error yang tampil tepat di bawah sebuah field. */
const galat = (page, selektor) => page.locator(`.field:has(${selektor}) .field-error`);

/** Mengisi field dengan string sepanjang n karakter; fill() melewati batas maxlength HTML seperti permintaan langsung ke server. */
const panjang = (n) => 'x'.repeat(n);

uat('VAL-01', {
    fitur: 'F-14, F-01',
    ref: 'PRD bagian 5 (Registrasi), NFR-03',
    judul: 'Validasi form Registrasi',
    langkah: [
        'Buka /register dan tekan Daftar dengan semua field kosong (validasi browser aktif).',
        'Nonaktifkan validasi browser lalu kirim kosong agar validasi server yang teruji.',
        'Kirim data tidak valid: nama 101 karakter, email salah format, password 3 karakter, telepon non-angka.',
        'Kirim dengan konfirmasi password berbeda.',
    ],
    harapan: 'Setiap field wajib ditolak dengan pesan Bahasa Indonesia berwarna merah di bawah field; isian lain tetap terisi; tidak ada akun baru.',
}, async ({ page, bukti }) => {
    await page.goto('/register');
    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();
    await expect(page).toHaveURL(/\/register$/);
    expect(await page.locator('#name').evaluate((el) => el.validity.valueMissing)).toBe(true);
    await bukti('Form kosong ditahan oleh validasi bawaan browser');

    await tanpaValidasiBrowser(page);
    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();
    for (const field of ['#name', '#email', '#password', '#phone', '#address']) {
        await expect(galat(page, field), field).toContainText('wajib diisi');
    }
    await bukti('Validasi server: semua field wajib menampilkan pesan di bawah field');

    await tanpaValidasiBrowser(page);
    await page.fill('#name', panjang(101));
    await page.fill('#email', 'bukan-email');
    await page.fill('#password', '123');
    await page.fill('#password_confirmation', '123');
    await page.fill('#phone', 'abc12');
    await page.fill('#address', 'Jl. Uji Coba No. 1');
    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();
    await expect(galat(page, '#name')).toContainText('tidak boleh lebih dari 100 karakter');
    await expect(galat(page, '#email')).toContainText('alamat email yang valid');
    await expect(galat(page, '#password')).toContainText('minimal 8 karakter');
    await expect(galat(page, '#phone')).toContainText('10 sampai 15 angka');
    await expect(page.locator('#address')).toHaveValue('Jl. Uji Coba No. 1');
    await bukti('Data tidak valid: pesan per field, alamat tetap terisi');

    await tanpaValidasiBrowser(page);
    await page.fill('#name', 'Sari Komet');
    await page.fill('#email', 'sari@xenorent.test');
    await page.fill('#password', 'rahasia123');
    await page.fill('#password_confirmation', 'berbeda123');
    await page.fill('#phone', '081234567890');
    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();
    await expect(galat(page, '#password')).toContainText('tidak cocok');
    await expect(page.locator('#name')).toHaveValue('Sari Komet');
    await expect(page.locator('#phone')).toHaveValue('081234567890');
    await bukti('Konfirmasi password berbeda ditolak, isian lain tetap terisi');

    expect(q('select count(*) as n from users')[0].n).toBe(3);
});

uat('VAL-02', {
    fitur: 'F-14, F-02',
    ref: 'PRD bagian 5 (Login)',
    judul: 'Validasi form Login',
    langkah: ['Buka /login dan kirim form kosong tanpa validasi browser.', 'Kirim dengan email berformat salah.'],
    harapan: 'Email dan password wajib diisi; format email dicek; pesan tampil di bawah field.',
}, async ({ page, bukti }) => {
    await page.goto('/login');
    await tanpaValidasiBrowser(page);
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/login$/);
    await expect(galat(page, '#email')).toContainText('wajib diisi');
    await expect(galat(page, '#password')).toContainText('wajib diisi');
    await bukti('Login kosong: email dan password wajib diisi');

    await tanpaValidasiBrowser(page);
    await page.fill('#email', 'bukan-email');
    await page.fill('#password', 'apa-saja');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(galat(page, '#email')).toContainText('alamat email yang valid');
    await expect(page.locator('#email')).toHaveValue('bukan-email');
    await bukti('Email berformat salah ditolak');
});

uat('VAL-03', {
    fitur: 'F-14, F-03',
    ref: 'PRD bagian 5 (Profil)',
    judul: 'Validasi form Profil',
    langkah: [
        'Login sebagai Rina dan buka Profil.',
        'Kosongkan semua field lalu simpan (tanpa validasi browser).',
        'Isi email milik Bima dan telepon non-angka lalu simpan.',
    ],
    harapan: 'Field wajib ditolak, email yang dipakai akun lain ditolak, isian lain tetap terisi, dan profil Rina tidak berubah.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await page.goto('/profile');
    for (const field of ['#name', '#email', '#phone', '#address']) await page.fill(field, '');
    await tanpaValidasiBrowser(page);
    await page.getByRole('button', { name: 'Simpan profil' }).click();
    for (const field of ['#name', '#email', '#phone', '#address']) {
        await expect(galat(page, field), field).toContainText('wajib diisi');
    }
    await bukti('Profil dikosongkan: semua field wajib menampilkan pesan');

    await tanpaValidasiBrowser(page);
    await page.fill('#name', 'Rina Kosmos');
    await page.fill('#email', AKUN.bima.email);
    await page.fill('#phone', 'abc');
    await page.fill('#address', 'Jl. Orbit Baru No. 1');
    await page.getByRole('button', { name: 'Simpan profil' }).click();
    await expect(galat(page, '#email')).toContainText('Email sudah terdaftar');
    await expect(galat(page, '#phone')).toContainText('10 sampai 15 angka');
    await expect(page.locator('#address')).toHaveValue('Jl. Orbit Baru No. 1');
    await bukti('Email milik akun lain dan telepon non-angka ditolak');

    expect(q('select email from users where id = ?', [idUser(AKUN.rina.email)])[0].email).toBe(AKUN.rina.email);
    expect(q('select phone from profiles where user_id = ?', [idUser(AKUN.rina.email)])[0].phone).toBe(AKUN.rina.telepon);
});

uat('VAL-04', {
    fitur: 'F-14, F-06',
    ref: 'PRD bagian 5 (Unit)',
    judul: 'Validasi form Unit',
    langkah: [
        'Login sebagai Admin dan buka Kelola Unit.',
        'Kirim form tambah unit kosong (tanpa validasi browser).',
        'Kirim kode berisi spasi/simbol, kode 21 karakter, dan deskripsi 1001 karakter.',
    ],
    harapan: 'Kode, nama, dan kategori wajib; kode hanya huruf/angka/tanda hubung/garis bawah dan maksimal 20; deskripsi maksimal 1000; tidak ada unit tersimpan.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/units');
    const form = page.locator('form[action$="/admin/units"]');

    await tanpaValidasiBrowser(page);
    await form.getByRole('button', { name: 'Tambah unit' }).click();
    await expect(galat(page, '#code')).toContainText('wajib diisi');
    await expect(galat(page, '#name')).toContainText('wajib diisi');
    await expect(page.locator('.category-field .field-error').first()).toBeVisible();
    await bukti('Form unit kosong: kode, nama, dan kategori wajib');

    await tanpaValidasiBrowser(page);
    await form.locator('#code').fill('ZTS 04!');
    await form.locator('#name').fill('Zeta Saucer');
    await form.locator('#description').fill(panjang(1001));
    await form.getByLabel('Kargo').check();
    await form.getByRole('button', { name: 'Tambah unit' }).click();
    await expect(galat(page, '#code')).toContainText('huruf, angka, tanda hubung');
    await expect(galat(page, '#description')).toContainText('tidak boleh lebih dari 1000');
    await expect(form.locator('#name')).toHaveValue('Zeta Saucer');
    await bukti('Kode berisi simbol dan deskripsi terlalu panjang ditolak');

    await tanpaValidasiBrowser(page);
    await form.locator('#code').fill('A'.repeat(21));
    await form.locator('#description').fill('');
    await form.getByRole('button', { name: 'Tambah unit' }).click();
    await expect(galat(page, '#code')).toContainText('tidak boleh lebih dari 20');
    await bukti('Kode 21 karakter ditolak');

    expect(q('select count(*) as n from units')[0].n).toBe(10);
});

uat('VAL-05', {
    fitur: 'F-14, F-07',
    ref: 'PRD bagian 5 (Kategori)',
    judul: 'Validasi form Kategori',
    langkah: ['Login sebagai Admin dan buka Kategori.', 'Kirim form kosong, lalu nama 51 karakter dan deskripsi 501 karakter.'],
    harapan: 'Nama wajib, maksimal 50 karakter; deskripsi maksimal 500 karakter; tidak ada kategori tersimpan.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/categories');
    const form = page.locator('form[action$="/admin/categories"]');

    await tanpaValidasiBrowser(page);
    await form.getByRole('button', { name: 'Tambah kategori' }).click();
    await expect(galat(page, '#name')).toContainText('wajib diisi');
    await bukti('Form kategori kosong: nama wajib diisi');

    await tanpaValidasiBrowser(page);
    await form.locator('#name').fill(panjang(51));
    await form.locator('#description').fill(panjang(501));
    await form.getByRole('button', { name: 'Tambah kategori' }).click();
    await expect(galat(page, '#name')).toContainText('tidak boleh lebih dari 50');
    await expect(galat(page, '#description')).toContainText('tidak boleh lebih dari 500');
    await bukti('Nama 51 karakter dan deskripsi 501 karakter ditolak');

    expect(q('select count(*) as n from categories')[0].n).toBe(6);
});

uat('VAL-06', {
    fitur: 'F-14, F-08',
    ref: 'PRD bagian 5 (Anggota Admin), NFR-03',
    judul: 'Validasi form Anggota (Admin)',
    langkah: [
        'Login sebagai Admin dan buka Anggota.',
        'Kirim form tambah anggota kosong (tanpa validasi browser).',
        'Isi sebagian data dengan password 7 karakter dan email milik anggota lain, lalu kirim.',
        'Ubah email Bima menjadi email Rina dan simpan.',
    ],
    harapan: 'Semua field wajib ditolak dengan pesan merah; password minimal 8; email unik; isian lain tidak hilang saat gagal simpan; tidak ada anggota baru.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.admin);
    await page.goto('/admin/members');
    const form = page.locator('form[action$="/admin/members"]');

    await tanpaValidasiBrowser(page);
    await form.getByRole('button', { name: 'Tambah anggota' }).click();
    const kotak = page.locator('.alert-error');
    for (const kata of ['nama wajib diisi', 'email wajib diisi', 'nomor telepon wajib diisi', 'alamat wajib diisi', 'kata sandi wajib diisi']) {
        await expect(kotak, kata).toContainText(kata);
    }
    await bukti('Form anggota kosong: seluruh field wajib ditolak');

    await tanpaValidasiBrowser(page);
    await form.locator('#new-member-name').fill('Tala Pulsar');
    await form.locator('#new-member-email').fill(AKUN.rina.email);
    await form.locator('#new-member-phone').fill('081311112222');
    await form.locator('#new-member-address').fill('Jl. Pulsar No. 3');
    await form.locator('#new-member-password').fill('1234567');
    await form.locator('#new-member-confirmation').fill('1234567');
    await form.getByRole('button', { name: 'Tambah anggota' }).click();
    await expect(kotak).toContainText('Email sudah terdaftar');
    await expect(kotak).toContainText('minimal 8 karakter');
    await bukti('Email dipakai akun lain dan password 7 karakter ditolak');

    // NFR-03: isian lain tidak boleh hilang saat gagal simpan.
    const sisa = {
        nama: await form.locator('#new-member-name').inputValue(),
        telepon: await form.locator('#new-member-phone').inputValue(),
        alamat: await form.locator('#new-member-address').inputValue(),
    };
    if ((await page.locator('.field:has(#new-member-email) .field-error, .field:has(#new-member-password) .field-error').count()) === 0) {
        catat('PRD bagian 8 meminta pesan error di bawah field; pada form tambah anggota (Admin) pesan hanya tampil di kotak peringatan atas halaman.');
    }
    expect(sisa, 'isian form tambah anggota harus tetap terisi setelah gagal simpan (NFR-03)').toEqual({
        nama: 'Tala Pulsar', telepon: '081311112222', alamat: 'Jl. Pulsar No. 3',
    });

    expect(q('select count(*) as n from users')[0].n).toBe(3);
});

uat('VAL-06b', {
    fitur: 'F-14, F-08',
    ref: 'KP-20, PRD bagian 5 (Anggota Admin)',
    judul: 'Validasi ubah anggota: email milik anggota lain ditolak',
    langkah: ['Login sebagai Admin dan buka Anggota.', 'Pada Bima, ubah email menjadi email Rina lalu simpan.'],
    harapan: 'Ditolak dengan pesan "Email sudah terdaftar"; email Bima tidak berubah.',
}, async ({ page, bukti }) => {
    const idBima = idUser(AKUN.bima.email);
    await login(page, AKUN.admin);
    await page.goto('/admin/members');
    const kartu = page.locator('.record-card').filter({ hasText: AKUN.bima.email });
    await kartu.locator('summary').click();
    await kartu.locator(`#member-email-${idBima}`).fill(AKUN.rina.email);
    await kartu.getByRole('button', { name: 'Simpan perubahan' }).click();
    await expect(page.locator('.alert-error')).toContainText('Email sudah terdaftar');
    await bukti('Email milik Rina ditolak saat mengubah Bima');
    expect(q('select email from users where id = ?', [idBima])[0].email).toBe(AKUN.bima.email);
});

uat('VAL-07', {
    fitur: 'F-14, F-05',
    ref: 'PRD bagian 5 (Pencarian)',
    judul: 'Validasi kata kunci pencarian maksimal 100 karakter',
    langkah: ['Login sebagai Anggota.', 'Buka /units?q= dengan kata kunci 101 karakter (melewati batas input di browser).'],
    harapan: 'Server menolak dengan pesan "Kata kunci maksimal 100 karakter".',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto(`/units?q=${'a'.repeat(101)}`);
    await bukti('Pencarian dengan 101 karakter');
    await expect(
        page.locator('.alert-error, .field-error'),
        'server harus menolak kata kunci lebih dari 100 karakter (aturan nullable|string|max:100 pada PRD)',
    ).toContainText(/100 karakter/i);
});
