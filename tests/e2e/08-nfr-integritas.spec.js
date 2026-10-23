import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { ROOT, UAT_ENV } from './env.js';
import {
    AKUN, KATEGORI, UNIT_SEED, cekPesan, cobaTulis, expect, idUnit, idUser, iso, login, q, uat,
} from './support.js';

const artisan = (args, env = {}) =>
    execFileSync('php', ['artisan', ...args], { cwd: ROOT, env: { ...process.env, ...UAT_ENV, ...env }, stdio: 'pipe' }).toString();

uat('NFR-01', {
    fitur: 'NFR-01',
    ref: 'BR-01, TB-04',
    judul: 'Password disimpan sebagai hash; halaman dilindungi login dan peran di server',
    langkah: ['Periksa kolom password semua akun di tabel users.', 'Lihat hasil KP-05, KP-06, dan KP-31 untuk akses per peran.'],
    harapan: 'Tidak ada password teks biasa; semua berupa hash bcrypt.',
}, async ({ bukti }) => {
    const akun = q('select email, password from users order by id');
    expect(akun).toHaveLength(3);
    const baris = [];
    for (const { email, password } of akun) {
        const hash = /^\$2y\$\d{2}\$.{53}$/.test(password);
        expect(hash, `${email} harus berupa hash bcrypt`).toBe(true);
        expect(password).not.toBe('password');
        baris.push([hash, `${email} → ${password.slice(0, 7)}… (${password.length} karakter, bcrypt)`]);
    }
    await bukti.kartu('Password tersimpan sebagai hash', 'NFR-01 · Isi kolom users.password', baris);
});

uat('NFR-02', {
    fitur: 'NFR-02',
    ref: 'TB-02, INT-04, INT-05',
    judul: 'Permintaan sewa beruntun tidak bisa melewati kuota 2 unit',
    langkah: ['Login sebagai Bima (0 sewa aktif).', 'Kirim 4 permintaan sewa ke 4 unit tersedia sekaligus.', 'Periksa jumlah sewa aktif Bima.'],
    harapan: 'Tepat 2 sewa berhasil; sisanya ditolak; tidak pernah lebih dari 2 sewa aktif.',
}, async ({ page, bukti }) => {
    await login(page, AKUN.bima);
    await page.goto('/units');
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const kode = ['ZTS-002', 'ZTS-003', 'KPH-001', 'ORG-001'];

    const hasil = await Promise.all(
        kode.map((k) => page.request.post(`/units/${idUnit(k)}/rent`, { form: { _token: token }, maxRedirects: 0 })),
    );
    const aktif = q("select count(*) as n from rentals r join users u on u.id = r.user_id where u.email = ? and r.status = 'disewa'", [AKUN.bima.email])[0].n;
    expect(aktif).toBe(2);
    expect(q("select count(*) as n from units where status = 'disewa'")[0].n).toBe(4);

    await page.goto('/dashboard');
    await expect(page.locator('.quota-panel strong')).toHaveText(/0\s*\/\s*2/);
    await bukti('Setelah 4 permintaan sewa beruntun: kuota Bima tepat penuh (2 unit)');
    await bukti.kartu('Rekap permintaan sewa beruntun', 'NFR-02 · 4 permintaan sewa dari satu anggota', [
        [true, `Status HTTP permintaan: ${hasil.map((h) => h.status()).join(', ')}`],
        [true, `Sewa aktif Bima di database: ${aktif} (batas 2)`],
        [true, 'Catatan: server uji berjalan satu proses; jaminan kunci baris (lockForUpdate) pada akses paralel sungguhan diuji di PHPUnit (tests/Feature).'],
    ]);
});

uat('INT-01', {
    fitur: 'INT-01',
    ref: 'ATB-07, ATB-11, TB-01',
    judul: 'Kode unit dan email unik di level database, termasuk bekas data terhapus',
    langkah: [
        'Coba menyisipkan langsung ke database kode unit ganda dan email ganda.',
        'Login sebagai Admin, hapus unit ZTS-002, lalu tambah unit baru berkode ZTS-002.',
    ],
    harapan: 'Database menolak duplikat (UNIQUE); kode bekas unit terhapus juga tidak bisa dipakai ulang.',
}, async ({ page, bukti }) => {
    const unitGanda = cobaTulis("insert into units (code, name, status, created_at, updated_at) values ('ZTS-001', 'Ganda', 'tersedia', datetime('now'), datetime('now'))");
    const emailGanda = cobaTulis("insert into users (name, email, password, role, created_at, updated_at) values ('Ganda', 'rina@xenorent.test', 'x', 'anggota', datetime('now'), datetime('now'))");
    expect(unitGanda).toMatch(/UNIQUE constraint failed: units\.code/);
    expect(emailGanda).toMatch(/UNIQUE constraint failed: users\.email/);

    await login(page, AKUN.admin);
    await page.goto('/admin/units');
    const kartu = page.locator('.record-card').filter({ hasText: 'ZTS-002' });
    await kartu.locator('summary').click();
    await kartu.getByRole('button', { name: 'Hapus unit' }).click();
    await expect(page.locator('.alert-success')).toContainText('Unit berhasil dihapus');

    const form = page.locator('form[action$="/admin/units"]');
    await form.locator('#code').fill('ZTS-002');
    await form.locator('#name').fill('Zeta Saucer Pengganti');
    await form.getByLabel('Kargo').check();
    await form.getByRole('button', { name: 'Tambah unit' }).click();
    await cekPesan(page, 'Kode unit sudah dipakai', { area: page.locator('.field:has(#code) .field-error') });
    await bukti('Kode ZTS-002 milik unit yang sudah dihapus tetap tidak bisa dipakai ulang');

    await bukti.kartu('Penolakan duplikat oleh database', 'INT-01 · Unique index', [
        [true, `units.code ganda → ${unitGanda}`],
        [true, `users.email ganda → ${emailGanda}`],
        [true, 'Kode unit bekas data terhapus ditolak lewat form (lihat tangkapan layar sebelumnya)'],
    ]);
});

uat('INT-02', {
    fitur: 'INT-02, INT-03',
    ref: 'BR-04, BR-06',
    judul: 'Satu akun satu profil dan pasangan unit-kategori tidak ganda',
    langkah: ['Coba menyisipkan profil kedua untuk akun yang sama.', 'Coba menyisipkan pasangan unit-kategori yang sudah ada.'],
    harapan: 'Database menolak keduanya dengan error UNIQUE.',
}, async ({ bukti }) => {
    const profilGanda = cobaTulis("insert into profiles (user_id, phone, address, created_at, updated_at) values (?, '081200009999', 'Alamat ganda', datetime('now'), datetime('now'))", [idUser(AKUN.rina.email)]);
    const pivot = q('select category_id, unit_id from category_unit limit 1')[0];
    const pivotGanda = cobaTulis('insert into category_unit (category_id, unit_id) values (?, ?)', [pivot.category_id, pivot.unit_id]);
    expect(profilGanda).toMatch(/UNIQUE constraint failed: profiles\.user_id/);
    expect(pivotGanda).toMatch(/UNIQUE constraint failed: category_unit/);
    await bukti.kartu('Penolakan duplikat oleh database', 'INT-02 / INT-03 · Unique dan primary key gabungan', [
        [true, `profiles.user_id ganda → ${profilGanda}`],
        [true, `category_unit pasangan ganda → ${pivotGanda}`],
    ]);
});

uat('INT-03', {
    fitur: 'INT-07, INT-08',
    ref: 'ATB-08, TB-05',
    judul: 'Foreign key rentals bersifat restrict: akun/unit dengan sewa tidak bisa dihapus permanen',
    langkah: ['Coba menghapus permanen akun Rina dan unit ZTS-001 yang punya sewa langsung dari database.'],
    harapan: 'Database menolak dengan FOREIGN KEY constraint failed sehingga riwayat sewa tidak bisa yatim.',
}, async ({ bukti }) => {
    const hapusUser = cobaTulis('delete from users where id = ?', [idUser(AKUN.rina.email)]);
    const hapusUnit = cobaTulis('delete from units where id = ?', [idUnit('ZTS-001')]);
    expect(hapusUser).toMatch(/FOREIGN KEY constraint failed/);
    expect(hapusUnit).toMatch(/FOREIGN KEY constraint failed/);
    await bukti.kartu('Penghapusan permanen ditolak database', 'INT-03 · Foreign key restrict pada rentals', [
        [true, `delete users (Rina) → ${hapusUser}`],
        [true, `delete units (ZTS-001) → ${hapusUnit}`],
    ]);
});

uat('NFR-04', {
    fitur: 'NFR-04, S-04',
    ref: 'PRD bagian 3',
    judul: 'Tampilan dapat dipakai di layar HP (lebar 390 px) tanpa scroll horizontal halaman',
    langkah: ['Atur lebar layar 390 × 844 px.', 'Buka Login, Dashboard, Daftar Unit, Detail Unit, Sewa Saya sebagai Anggota, lalu Kelola Unit dan Sewa Aktif sebagai Admin.'],
    harapan: 'Setiap halaman tampil utuh tanpa melebar melewati layar.',
}, async ({ page, bukti }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    const luber = [];
    const periksa = async (nama) => {
        const lebar = await page.evaluate(() => ({ isi: document.documentElement.scrollWidth, layar: window.innerWidth }));
        if (lebar.isi > lebar.layar + 1) luber.push(`${nama} (${lebar.isi}px > ${lebar.layar}px)`);
        await bukti(`HP: ${nama}`);
    };

    await page.goto('/login');
    await periksa('Login');
    await login(page, AKUN.rina);
    await periksa('Dashboard Anggota');
    await page.goto('/units');
    await periksa('Daftar Unit');
    await page.goto(`/units/${idUnit('ZTS-002')}`);
    await periksa('Detail Unit');
    await page.goto('/rentals/mine');
    await periksa('Sewa Saya');

    await page.context().clearCookies();
    await login(page, AKUN.admin);
    await page.goto('/admin/units');
    await periksa('Kelola Unit (Admin)');
    await page.goto('/admin/rentals/active');
    await periksa('Sewa Aktif (Admin)');

    expect(luber, `halaman yang melebar: ${luber.join('; ')}`).toEqual([]);
});

uat('NFR-07', {
    fitur: 'NFR-07',
    ref: 'INT-09, BR-13',
    judul: 'Tarif denda dan durasi sewa dibaca dari konfigurasi, bukan ditulis di kode',
    langkah: [
        'Baca nilai tarif dan durasi dari aplikasi tanpa pengaturan khusus.',
        'Ubah hanya variabel environment RENTAL_DAILY_FINE dan RENTAL_DURATION_DAYS, lalu baca ulang.',
    ],
    harapan: 'Nilai bawaan 10000 dan 5; setelah env diubah menjadi 15000 dan 7, aplikasi memakai nilai baru tanpa mengubah kode.',
}, async ({ bukti }) => {
    const baca = (env) => artisan(
        ['tinker', "--execute=$s = app(App\\Domain\\Services\\RentalPricingService::class); echo $s->getDailyFineAmount().'|'.$s->getRentalDurationDays();"],
        env,
    ).trim();

    const bawaan = baca({});
    const diubah = baca({ RENTAL_DAILY_FINE: '15000', RENTAL_DURATION_DAYS: '7' });
    expect(bawaan).toBe('10000|5');
    expect(diubah).toBe('15000|7');
    await bukti.kartu('Tarif dan durasi dari konfigurasi', 'NFR-07 · Konfigurasi denda dan durasi', [
        [true, `Tanpa pengaturan khusus → tarif|durasi = ${bawaan}`],
        [true, `RENTAL_DAILY_FINE=15000, RENTAL_DURATION_DAYS=7 → ${diubah}`],
        [true, 'Nilai berubah hanya lewat konfigurasi (config/app.php + .env), tanpa mengubah kode'],
    ]);
});

uat('NFR-08', {
    fitur: 'NFR-08, F-15',
    ref: 'BR-19, BR-20, PRD bagian 9',
    judul: 'migrate:fresh --seed membangun ulang database dengan data seed sesuai PRD',
    langkah: ['Jalankan php artisan migrate:fresh --seed.', 'Periksa jumlah dan isi data: 3 akun, 6 kategori, 10 unit, 3 sewa contoh dengan tanggal relatif.'],
    harapan: 'Perintah selesai tanpa error dan data seed sama dengan tabel di PRD bagian 9.',
}, async ({ bukti }) => {
    const keluar = artisan(['migrate:fresh', '--seed', '--force']);
    const cek = [];
    const periksa = (ok, teks) => {
        cek.push([ok, teks]);
        return ok;
    };

    periksa(!/error|exception/i.test(keluar), 'migrate:fresh --seed selesai tanpa error');
    const akun = q('select u.email, u.role, p.phone, p.address from users u join profiles p on p.user_id = u.id order by u.id');
    periksa(akun.length === 3, `3 akun beserta profil (${akun.map((a) => `${a.email}/${a.role}`).join(', ')})`);
    for (const [kunci, a] of Object.entries(AKUN)) {
        const baris = akun.find((x) => x.email === a.email);
        periksa(
            Boolean(baris) && baris.phone === a.telepon && baris.address === a.alamat && baris.role === (kunci === 'admin' ? 'admin' : 'anggota'),
            `Akun ${a.email}: peran, telepon, dan alamat sesuai PRD`,
        );
    }
    const kategori = q('select name from categories').map((k) => k.name).sort();
    periksa(JSON.stringify(kategori) === JSON.stringify([...KATEGORI].sort()), `6 kategori: ${kategori.join(', ')}`);

    const unit = q('select code, name, status from units order by code');
    periksa(unit.length === 10, '10 unit');
    for (const u of UNIT_SEED) {
        const baris = unit.find((x) => x.code === u.kode);
        periksa(Boolean(baris) && baris.name === u.nama && baris.status === u.status.toLowerCase(), `Unit ${u.kode} ${u.nama} berstatus ${u.status.toLowerCase()}`);
    }

    const sewa = q("select u.email, n.code, substr(r.rented_at,1,10) as sewa, substr(r.due_at,1,10) as tempo, substr(r.returned_at,1,10) as kembali, r.fine_amount, r.status from rentals r join users u on u.id = r.user_id join units n on n.id = r.unit_id order by r.id");
    const harapan = [
        [AKUN.rina.email, 'ZTS-001', iso(-2), iso(3), null, 0, 'disewa'],
        [AKUN.rina.email, 'NBS-001', iso(-8), iso(-3), null, 0, 'disewa'],
        [AKUN.bima.email, 'KPH-001', iso(-12), iso(-7), iso(-5), 20000, 'dikembalikan'],
    ];
    periksa(sewa.length === 3, '3 sewa contoh');
    harapan.forEach(([email, kode, sewaH, tempoH, kembaliH, denda, status]) => {
        const s = sewa.find((x) => x.email === email && x.code === kode);
        periksa(
            Boolean(s) && s.sewa === sewaH && s.tempo === tempoH && s.kembali === kembaliH && s.fine_amount === denda && s.status === status,
            `Sewa ${kode} (${email.split('@')[0]}): ${sewaH} → jatuh tempo ${tempoH}${kembaliH ? `, kembali ${kembaliH}` : ''}, ${status}, denda ${denda}`,
        );
    });

    await bukti.kartu('Hasil migrate:fresh --seed', 'NFR-08 / F-15 · Database dibangun ulang dari nol', cek);
    const gagal = cek.filter(([ok]) => !ok).map(([, teks]) => teks);
    expect(gagal, `pemeriksaan seed yang gagal: ${gagal.join('; ')}`).toEqual([]);
});

uat('F-16', {
    fitur: 'F-16',
    ref: 'BR-21, TB-06',
    judul: 'README memuat nama kelompok, nama tim, nama project, fitur, gambar skema database, dan video demo',
    langkah: ['Buka README.md di root repositori.', 'Periksa keenam unsur wajib pada BR-21.'],
    harapan: 'README memuat nama kelompok, nama tim, nama project, daftar fitur, gambar skema database, dan tautan video demo.',
}, async ({ bukti }) => {
    const readme = fs.readFileSync(path.join(ROOT, 'README.md'), 'utf8');
    const unsur = [
        ['Nama project XenoRent', /xenorent/i],
        ['Nama kelompok', /nama\s+kelompok|kelompok\s*[:|]/i],
        ['Nama tim / anggota tim', /nama\s+tim|anggota\s+tim|\btim\b\s*[:|]/i],
        ['Daftar fitur', /^#{1,6}\s*(daftar\s+)?fitur/im],
        ['Gambar skema database (ERD)', /!\[[^\]]*\]\([^)]*(erd|skema|schema)[^)]*\)/i],
        ['Video demo', /(video\s+demo|demo\s+video|youtu|drive\.google)/i],
    ];
    const hasil = unsur.map(([nama, pola]) => [pola.test(readme), nama]);
    // Penanda [ISI: ...] berarti bagian itu masih menunggu diisi oleh tim.
    const penanda = readme.match(/\[ISI:[^\]]*\]/g) ?? [];
    hasil.push([
        penanda.length === 0,
        penanda.length ? `Masih ada ${penanda.length} isian yang belum diisi: ${penanda.join(' ')}` : 'Tidak ada isian [ISI: ...] yang tertinggal',
    ]);
    await bukti.kartu('Pemeriksaan isi README.md', 'F-16 · Unsur wajib README (BR-21)', hasil);
    const kurang = hasil.filter(([ok]) => !ok).map(([, nama]) => nama);
    expect(kurang, `README belum memuat: ${kurang.join(', ')}`).toEqual([]);
});
