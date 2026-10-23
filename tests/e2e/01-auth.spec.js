import { AKUN, cekPesan, expect, idUser, login, logout, q, uat } from './support.js';

uat('KP-01', {
    fitur: 'F-01',
    ref: 'BR-02, BR-04, ATB-11',
    judul: 'Registrasi anggota dengan data valid',
    langkah: [
        'Buka /register sebagai tamu.',
        'Isi nama, email, password, konfirmasi password, telepon, dan alamat dengan data valid.',
        'Tekan "Daftar sebagai anggota".',
    ],
    harapan: 'Akun berperan anggota dan profilnya tersimpan; pengguna langsung login dan masuk Dashboard Anggota dengan sisa kuota 2 dari 2.',
}, async ({ page, bukti }) => {
    await page.goto('/register');
    await page.fill('#name', 'Sari Komet');
    await page.fill('#email', 'sari.komet@xenorent.test');
    await page.fill('#password', 'rahasia123');
    await page.fill('#password_confirmation', 'rahasia123');
    await page.fill('#phone', '081234567890');
    await page.fill('#address', 'Jl. Kometa No. 9');
    await bukti('Form registrasi terisi data valid');

    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Sari Komet');
    await expect(page.locator('.quota-panel strong')).toHaveText(/2\s*\/\s*2/);
    await bukti('Dashboard anggota setelah registrasi, sisa kuota 2 dari 2');

    const baris = q(
        'select u.role, u.password, p.phone, p.address from users u join profiles p on p.user_id = u.id where u.email = ?',
        ['sari.komet@xenorent.test'],
    );
    expect(baris).toHaveLength(1);
    expect(baris[0].role).toBe('anggota');
    expect(baris[0].phone).toBe('081234567890');
    expect(baris[0].address).toBe('Jl. Kometa No. 9');
});

uat('KP-02', {
    fitur: 'F-01',
    ref: 'BR-04, ATB-11, NFR-02, NFR-03',
    judul: 'Registrasi dengan email yang sudah dipakai',
    langkah: [
        'Buka /register sebagai tamu.',
        'Isi form dengan email rina@xenorent.test yang sudah terdaftar.',
        'Tekan "Daftar sebagai anggota".',
    ],
    harapan: 'Registrasi ditolak dengan pesan "Email sudah terdaftar"; akun tidak bertambah dan isian lain tidak hilang.',
}, async ({ page, bukti }) => {
    const sebelum = q('select count(*) as n from users')[0].n;

    await page.goto('/register');
    await page.fill('#name', 'Rina Palsu');
    await page.fill('#email', AKUN.rina.email);
    await page.fill('#password', 'rahasia123');
    await page.fill('#password_confirmation', 'rahasia123');
    await page.fill('#phone', '081234567891');
    await page.fill('#address', 'Jl. Bulan No. 1');
    await page.getByRole('button', { name: 'Daftar sebagai anggota' }).click();

    await expect(page).toHaveURL(/\/register$/);
    await cekPesan(page, 'Email sudah terdaftar', { area: page.locator('.field:has(#email) .field-error') });
    await expect(page.locator('#name')).toHaveValue('Rina Palsu');
    await expect(page.locator('#address')).toHaveValue('Jl. Bulan No. 1');
    await bukti('Registrasi ditolak karena email sudah dipakai, isian lain tetap terisi');

    expect(q('select count(*) as n from users')[0].n).toBe(sebelum);
});

uat('KP-03', {
    fitur: 'F-02',
    ref: 'BR-01',
    judul: 'Login dengan email dan password benar',
    langkah: [
        'Buka /login lalu login sebagai Admin.',
        'Keluar, lalu login sebagai Anggota (Rina) dan Anggota (Bima).',
    ],
    harapan: 'Admin masuk ke Dashboard Admin; Anggota masuk ke Dashboard Anggota.',
}, async ({ page, bukti }) => {
    await page.goto('/login');
    await page.fill('#email', AKUN.admin.email);
    await page.fill('#password', 'password');
    await bukti('Form login diisi akun Admin');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Panel operasional');
    await bukti('Dashboard Admin setelah login');
    await logout(page);

    await login(page, AKUN.rina);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Rina Kosmos');
    await bukti('Dashboard Anggota (Rina) setelah login');
    await logout(page);

    await login(page, AKUN.bima);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Bima Nebula');
});

uat('KP-04', {
    fitur: 'F-02',
    ref: 'NFR-01',
    judul: 'Login dengan password salah',
    langkah: ['Buka /login.', 'Isi email admin@xenorent.test dengan password yang salah.', 'Tekan "Masuk".'],
    harapan: 'Tetap di halaman login dengan pesan gagal; password tidak ditampilkan ulang.',
}, async ({ page, bukti }) => {
    await page.goto('/login');
    await page.fill('#email', AKUN.admin.email);
    await page.fill('#password', 'salah-total');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();

    await expect(page).toHaveURL(/\/login$/);
    await cekPesan(page, 'Email atau kata sandi salah');
    await expect(page.locator('#password')).toHaveValue('');
    await expect(page.locator('#email')).toHaveValue(AKUN.admin.email);
    await bukti('Login gagal: pesan tampil dan kolom password kosong kembali');
});

uat('KP-05', {
    fitur: 'F-02',
    ref: 'BR-01, NFR-01',
    judul: 'Tamu membuka halaman selain login/registrasi',
    langkah: ['Tanpa login, buka URL /dashboard, /units, /units/1, /profile, /rentals/mine, dan seluruh halaman /admin/*.'],
    harapan: 'Setiap URL mengarahkan tamu ke halaman login.',
}, async ({ page, bukti }) => {
    const alamat = [
        '/dashboard', '/units', '/units/1', '/profile', '/rentals/mine',
        '/admin/units', '/admin/categories', '/admin/members', '/admin/members/1/rentals',
        '/admin/members/1/rentals/print', '/admin/rentals/active',
    ];
    for (const url of alamat) {
        await page.goto(url);
        await expect(page, `${url} harus diarahkan ke login`).toHaveURL(/\/login$/);
        if (url === '/dashboard') await bukti('Tamu membuka /dashboard diarahkan ke login');
    }
    await page.goto('/admin/units');
    await expect(page.getByRole('heading', { name: 'Masuk ke XenoRent' })).toBeVisible();
    await bukti('Tamu membuka /admin/units diarahkan ke login');
});

uat('KP-06', {
    fitur: 'F-02',
    ref: 'BR-01, TB-04, NFR-01',
    judul: 'Anggota membuka halaman Admin',
    langkah: ['Login sebagai Anggota (Rina).', 'Buka /admin/units, /admin/categories, /admin/members, /admin/rentals/active, dan riwayat sewa anggota.'],
    harapan: 'Setiap URL ditolak dengan halaman "Akses ditolak" (403) berikut tombol kembali ke Dashboard.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    const alamat = [
        '/admin/units', '/admin/categories', '/admin/members', '/admin/rentals/active',
        `/admin/members/${idUser(AKUN.bima.email)}/rentals`,
    ];
    for (const url of alamat) {
        const respons = await page.goto(url);
        expect(respons.status(), `${url} harus 403`).toBe(403);
        await expect(page.getByRole('heading', { name: 'Akses ditolak' })).toBeVisible();
        if (url === '/admin/units') {
            await expect(page.getByRole('link', { name: 'Kembali ke dashboard' })).toBeVisible();
            await bukti('Anggota membuka /admin/units: halaman Akses ditolak (403)');
        }
    }
});

uat('KP-07', {
    fitur: 'F-03',
    ref: 'BR-05, TB-04',
    judul: 'Pengguna mengubah telepon dan alamat profil sendiri',
    langkah: ['Login sebagai Rina.', 'Buka menu Profil.', 'Ubah telepon dan alamat lalu tekan "Simpan profil".'],
    harapan: 'Data profil Rina tersimpan dan tampil di form; profil anggota lain tidak berubah.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await page.getByRole('link', { name: 'Profil' }).click();
    await expect(page.locator('#phone')).toHaveValue(AKUN.rina.telepon);
    await bukti('Halaman profil Rina sebelum diubah');

    await page.fill('#phone', '081299990000');
    await page.fill('#address', 'Jl. Orbit No. 99');
    await page.getByRole('button', { name: 'Simpan profil' }).click();

    await expect(page.locator('.alert-success')).toContainText('Profil berhasil diperbarui');
    await expect(page.locator('#phone')).toHaveValue('081299990000');
    await expect(page.locator('#address')).toHaveValue('Jl. Orbit No. 99');
    await bukti('Profil Rina tersimpan dengan pesan sukses');

    const profil = (email) => q('select p.phone, p.address from profiles p join users u on u.id = p.user_id where u.email = ?', [email])[0];
    expect(profil(AKUN.rina.email)).toMatchObject({ phone: '081299990000', address: 'Jl. Orbit No. 99' });
    expect(profil(AKUN.bima.email)).toMatchObject({ phone: AKUN.bima.telepon, address: AKUN.bima.alamat });
    expect(profil(AKUN.admin.email)).toMatchObject({ phone: AKUN.admin.telepon, address: AKUN.admin.alamat });
});

uat('KP-07b', {
    fitur: 'F-03',
    ref: 'BR-05, Flowchart 6B',
    judul: 'Profil yang diubah adalah milik akun yang sedang login (ID dari sesi, bukan dari form)',
    langkah: ['Login sebagai Rina.', 'Buka Profil dan kirim form dengan tambahan field user_id milik Bima.', 'Periksa profil Bima.'],
    harapan: 'Profil Bima tetap sama; hanya profil Rina yang berubah.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await page.goto('/profile');
    await page.evaluate((idBima) => {
        const form = document.querySelector('form[action$="/profile"]');
        for (const [nama, nilai] of [['user_id', idBima], ['id', idBima]]) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = nama;
            input.value = nilai;
            form.appendChild(input);
        }
    }, String(idUser(AKUN.bima.email)));
    await page.fill('#phone', '081255556666');
    await page.getByRole('button', { name: 'Simpan profil' }).click();
    await expect(page.locator('.alert-success')).toContainText('Profil berhasil diperbarui');
    await bukti('Profil Rina diubah dengan field user_id Bima disisipkan');

    const telepon = (email) => q('select p.phone from profiles p join users u on u.id = p.user_id where u.email = ?', [email])[0].phone;
    expect(telepon(AKUN.rina.email)).toBe('081255556666');
    expect(telepon(AKUN.bima.email)).toBe(AKUN.bima.telepon);
});

uat('F-02b', {
    fitur: 'F-02',
    ref: 'BR-01',
    judul: 'Logout mengakhiri sesi',
    langkah: ['Login sebagai Rina lalu tekan "Keluar".', 'Buka /dashboard.'],
    harapan: 'Pengguna kembali ke halaman login dan tidak bisa membuka /dashboard tanpa login ulang.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await logout(page);
    await bukti('Setelah menekan Keluar, pengguna di halaman login');
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/login$/);
});

uat('S-05', {
    fitur: 'S-05, C-03',
    ref: 'PRD bagian 3',
    judul: 'Dashboard menampilkan sisa kuota (Anggota) dan angka ringkasan (Admin)',
    langkah: ['Login sebagai Rina (2 sewa aktif) dan Bima (0 sewa aktif), lalu Admin.'],
    harapan: 'Rina melihat sisa kuota 0 dari 2; Bima 2 dari 2; Admin melihat unit tersedia 8, sewa aktif 2, anggota 2.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.rina);
    await expect(page.locator('.quota-panel strong')).toHaveText(/0\s*\/\s*2/);
    await bukti('Dashboard Rina: kuota penuh (0 dari 2)');
    await logout(page);

    await login(page, AKUN.bima);
    await expect(page.locator('.quota-panel strong')).toHaveText(/2\s*\/\s*2/);
    await logout(page);

    await login(page, AKUN.admin);
    const metrik = (nama) =>
        page.locator('.metric-panel').filter({ has: page.locator('span', { hasText: new RegExp(`^${nama}$`) }) }).locator('strong');
    await expect(metrik('Unit tersedia')).toHaveText('8');
    await expect(metrik('Sewa aktif')).toHaveText('2');
    await expect(metrik('Anggota')).toHaveText('2');
    await bukti('Dashboard Admin: angka ringkasan hanggar');
});
