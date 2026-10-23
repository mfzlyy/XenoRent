import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { DatabaseSync } from 'node:sqlite';
import { test as base, expect } from '@playwright/test';
import { DB_PATH, EVIDENCE_DIR, ROOT, UAT_ENV } from './env.js';

export { expect };

/** Jika STRICT_MESSAGES=1, selisih kalimat pesan terhadap PRD ikut menggagalkan test. */
const STRICT_MESSAGES = process.env.STRICT_MESSAGES === '1';

// ---------------------------------------------------------------- data acuan (PRD bagian 9)

export const PASSWORD = 'password';
export const AKUN = {
    admin: { email: 'admin@xenorent.test', nama: 'Admin XenoRent', telepon: '081200000001', alamat: 'Hanggar Utama, Sektor 7' },
    rina: { email: 'rina@xenorent.test', nama: 'Rina Kosmos', telepon: '081200000002', alamat: 'Jl. Orbit No. 12' },
    bima: { email: 'bima@xenorent.test', nama: 'Bima Nebula', telepon: '081200000003', alamat: 'Jl. Galaksi No. 5' },
};

export const KATEGORI = ['Piring Terbang', 'Pengintai', 'Kargo', 'Penumpang', 'Antargalaksi', 'Ramah Atmosfer Bumi'];

export const UNIT_SEED = [
    { kode: 'ZTS-001', nama: 'Zeta Saucer', status: 'Disewa', kategori: ['Piring Terbang', 'Penumpang'] },
    { kode: 'ZTS-002', nama: 'Zeta Saucer', status: 'Tersedia', kategori: ['Piring Terbang', 'Penumpang'] },
    { kode: 'ZTS-003', nama: 'Zeta Saucer', status: 'Tersedia', kategori: ['Piring Terbang', 'Penumpang', 'Ramah Atmosfer Bumi'] },
    { kode: 'NBS-001', nama: 'Nebula Scout', status: 'Disewa', kategori: ['Pengintai', 'Antargalaksi'] },
    { kode: 'NBS-002', nama: 'Nebula Scout', status: 'Tersedia', kategori: ['Pengintai', 'Antargalaksi'] },
    { kode: 'KPH-001', nama: 'Kepler Hauler', status: 'Tersedia', kategori: ['Kargo'] },
    { kode: 'KPH-002', nama: 'Kepler Hauler', status: 'Tersedia', kategori: ['Kargo', 'Ramah Atmosfer Bumi'] },
    { kode: 'ADC-001', nama: 'Andromeda Cruiser', status: 'Tersedia', kategori: ['Penumpang', 'Antargalaksi'] },
    { kode: 'ORG-001', nama: 'Orion Glider', status: 'Tersedia', kategori: ['Pengintai', 'Ramah Atmosfer Bumi'] },
    { kode: 'ORG-002', nama: 'Orion Glider', status: 'Tersedia', kategori: ['Pengintai', 'Ramah Atmosfer Bumi'] },
];

export const TARIF_DENDA = 10_000;
export const DURASI_HARI = 5;

// ---------------------------------------------------------------- tanggal (aplikasi memakai UTC)

const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const geser = (n) => {
    const d = new Date();
    d.setUTCDate(d.getUTCDate() + n);
    return d;
};

/** Tanggal H+n dalam format tampilan aplikasi, contoh "28 Oct 2026". */
export const tgl = (n = 0) => {
    const d = geser(n);
    return `${String(d.getUTCDate()).padStart(2, '0')} ${BULAN[d.getUTCMonth()]} ${d.getUTCFullYear()}`;
};

/** Tanggal H+n dalam format ISO (YYYY-MM-DD). */
export const iso = (n = 0) => geser(n).toISOString().slice(0, 10);

export const rupiah = (angka) => `Rp${angka.toLocaleString('id-ID')}`;

// ---------------------------------------------------------------- database UAT

export function resetDb() {
    execFileSync('php', ['artisan', 'migrate:fresh', '--seed', '--force'], {
        cwd: ROOT,
        env: { ...process.env, ...UAT_ENV },
        stdio: 'pipe',
    });
}

function dbTerbuka() {
    const db = new DatabaseSync(DB_PATH);
    db.exec('PRAGMA busy_timeout = 5000');
    return db;
}

/** Query baca ke database UAT. */
export function q(sql, params = []) {
    const db = dbTerbuka();
    try {
        return db.prepare(sql).all(...params);
    } finally {
        db.close();
    }
}

/** Perintah tulis untuk menyiapkan kondisi uji (bukan bagian yang diuji). */
export function run(sql, params = []) {
    const db = dbTerbuka();
    try {
        return db.prepare(sql).run(...params);
    } finally {
        db.close();
    }
}

/** Mencoba perintah tulis yang seharusnya ditolak database; mengembalikan pesan error atau null jika berhasil. */
export function cobaTulis(sql, params = []) {
    const db = dbTerbuka();
    try {
        db.prepare(sql).run(...params);
        return null;
    } catch (galat) {
        return galat.message;
    } finally {
        db.close();
    }
}

export const idUnit = (kode) => q('select id from units where code = ?', [kode])[0].id;
export const idUser = (email) => q('select id from users where email = ?', [email])[0].id;
export const idSewa = (email, kode) =>
    q(
        'select r.id from rentals r join users u on u.id = r.user_id join units n on n.id = r.unit_id where u.email = ? and n.code = ? order by r.id desc',
        [email, kode],
    )[0].id;

/** Mengatur tanggal sewa dan jatuh tempo (offset hari terhadap hari ini) untuk simulasi keterlambatan. */
export function aturTanggalSewa(idRental, offsetSewa, offsetTempo) {
    run('update rentals set rented_at = ?, due_at = ? where id = ?', [`${iso(offsetSewa)} 00:00:00`, `${iso(offsetTempo)} 00:00:00`, idRental]);
}

// ---------------------------------------------------------------- aksi umum

export async function login(page, akun) {
    await page.goto('/login');
    await page.fill('#email', akun.email);
    await page.fill('#password', PASSWORD);
    await Promise.all([page.waitForURL('**/dashboard'), page.getByRole('button', { name: 'Masuk', exact: true }).click()]);
}

export async function logout(page) {
    await Promise.all([page.waitForURL('**/login'), page.getByRole('button', { name: 'Keluar' }).click()]);
}

/**
 * Menonaktifkan validasi HTML5 browser (required, pattern, minlength, maxlength) agar validasi di server yang teruji.
 * Panggil sebelum mengisi field: fill() menghormati atribut maxlength, dan halaman perlu dipanggil ulang setelah reload.
 */
export async function tanpaValidasiBrowser(page) {
    await page.evaluate(() => {
        document.querySelectorAll('form').forEach((f) => (f.noValidate = true));
        document.querySelectorAll('[maxlength], [minlength], [pattern]').forEach((el) => {
            for (const atribut of ['maxlength', 'minlength', 'pattern']) el.removeAttribute(atribut);
        });
    });
}

/**
 * Mengirim form POST (dengan _method opsional) langsung ke URL tertentu, seolah pengguna
 * memaksa aksi lewat URL. Dipakai untuk menguji penolakan di sisi server.
 */
export async function kirimLangsung(page, action, method = 'POST') {
    const [respons] = await Promise.all([
        page.waitForNavigation(),
        page.evaluate(
            ({ action, method }) => {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const form = document.createElement('form');
                form.method = 'post';
                form.action = action;
                const tambah = (nama, nilai) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = nama;
                    input.value = nilai;
                    form.appendChild(input);
                };
                tambah('_token', token);
                if (method !== 'POST') tambah('_method', method);
                document.body.appendChild(form);
                form.submit();
            },
            { action, method },
        ),
    ]);
    return respons;
}

/** Membuka tab baru di konteks yang sama dengan dialog konfirmasi otomatis disetujui. */
export async function tabBaru(page) {
    const tab = await page.context().newPage();
    pasangDialog(tab);
    return tab;
}

/** Dialog konfirmasi (window.confirm) disetujui, kecuali page.dialogMode = 'dismiss'. */
function pasangDialog(page) {
    page.dialogs = [];
    page.dialogMode = 'accept';
    page.on('dialog', async (dialog) => {
        page.dialogs.push(dialog.message());
        if (page.dialogMode === 'dismiss') await dialog.dismiss();
        else await dialog.accept();
    });
}

// ---------------------------------------------------------------- pengecekan pesan

/** Seluruh teks pesan (alert dan error di bawah field) yang sedang tampil. */
export async function teksPesan(page) {
    const bagian = await page.locator('.alert, .field-error').allInnerTexts();
    return bagian.map((t) => t.replace(/\s+/g, ' ').trim()).join(' | ');
}

/**
 * Memastikan sebuah pesan muncul dan membandingkannya dengan kalimat di PRD.
 * Perilaku (pesan tampil) selalu wajib. Selisih kalimat dicatat sebagai deviasi,
 * dan baru menggagalkan test jika STRICT_MESSAGES=1.
 */
export async function cekPesan(page, kalimatPrd, { area } = {}) {
    const lokasi = area ?? page.locator('.alert, .field-error');
    await expect(lokasi.first(), 'harus ada pesan yang tampil').toBeVisible();
    const aktual = area ? (await area.allInnerTexts()).join(' | ').replace(/\s+/g, ' ').trim() : await teksPesan(page);
    const cocok = aktual.toLowerCase().includes(kalimatPrd.toLowerCase());
    if (!cocok) {
        const catatan = `Kalimat PRD "${kalimatPrd}" tidak persis sama; pesan di aplikasi: "${aktual}"`;
        base.info().annotations.push({ type: 'deviasi', description: catatan });
        if (STRICT_MESSAGES) expect(aktual.toLowerCase(), catatan).toContain(kalimatPrd.toLowerCase());
    }
    return aktual;
}

/** Memastikan daftar tag kategori persis berisi nama-nama tertentu, tanpa mengandalkan urutan. */
export async function cekTag(lokasiTag, daftar) {
    await expect(lokasiTag).toHaveCount(daftar.length);
    for (const nama of daftar) {
        await expect(lokasiTag.filter({ hasText: new RegExp(`^${nama}$`) }), `kategori ${nama}`).toHaveCount(1);
    }
}

export function catat(teks) {
    base.info().annotations.push({ type: 'deviasi', description: teks });
}

// ---------------------------------------------------------------- fixture dan pendefinisian test

const slug = (teks) =>
    teks
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 70);

const kodeDari = (testInfo) => testInfo.annotations.find((a) => a.type === 'kode').description;

const esc = (teks) => String(teks).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

export const test = base.extend({
    // Setiap test mulai dari data seed yang segar.
    _resetDb: [
        async ({}, use) => {
            resetDb();
            await use();
        },
        { auto: true },
    ],

    page: async ({ page }, use) => {
        pasangDialog(page);
        await use(page);
    },

    /**
     * bukti(label)            -> screenshot halaman penuh sebagai bukti UAT
     * bukti.kartu(label, ...) -> bukti berbentuk kartu teks, untuk pemeriksaan non-UI (database, berkas)
     */
    bukti: async ({ page, browser }, use, testInfo) => {
        const kode = kodeDari(testInfo);
        fs.mkdirSync(EVIDENCE_DIR, { recursive: true });
        for (const berkas of fs.readdirSync(EVIDENCE_DIR)) {
            if (berkas.startsWith(`${kode}_`)) fs.rmSync(path.join(EVIDENCE_DIR, berkas));
        }

        let urutan = 0;
        const simpan = async (label, halaman, opsi = {}) => {
            urutan += 1;
            const nama = `${kode}_${String(urutan).padStart(2, '0')}_${slug(label)}.png`;
            const lokasi = path.join(EVIDENCE_DIR, nama);
            await halaman.screenshot({ path: lokasi, fullPage: true, ...opsi });
            testInfo.annotations.push({ type: 'bukti', description: `${nama}|${label}` });
            await testInfo.attach(label, { path: lokasi, contentType: 'image/png' });
        };

        const bukti = (label, opsi = {}) => {
            const { halaman, ...sisa } = opsi;
            return simpan(label, halaman ?? page, sisa);
        };

        bukti.kartu = async (label, judul, baris) => {
            const halaman = await browser.newPage({ viewport: { width: 1100, height: 400 } });
            const isi = baris
                .map((b) => {
                    const [status, teks] = Array.isArray(b) ? b : ['', b];
                    const ikon = status === true ? '✔' : status === false ? '✘' : '•';
                    const warna = status === true ? '#1a7f37' : status === false ? '#cf222e' : '#57606a';
                    return `<li><span style="color:${warna};font-weight:700">${ikon}</span> ${esc(teks)}</li>`;
                })
                .join('');
            await halaman.setContent(
                `<body style="margin:0;padding:28px 32px;font:15px/1.6 Consolas,'Courier New',monospace;background:#fff;color:#1f2328">
                   <h2 style="margin:0 0 14px;font:600 20px/1.3 'Segoe UI',sans-serif">${esc(judul)}</h2>
                   <ul style="list-style:none;margin:0;padding:0">${isi}</ul>
                   <p style="margin:18px 0 0;color:#6e7781;font:12px 'Segoe UI',sans-serif">Dibuat otomatis oleh UAT Playwright · ${new Date().toISOString()}</p>
                 </body>`,
            );
            await simpan(label, halaman);
            await halaman.close();
        };

        await use(bukti);
    },
});

/** Mendefinisikan satu skenario UAT beserta metadata yang dipakai untuk laporan. */
export function uat(kode, meta, isi) {
    const anotasi = [
        { type: 'kode', description: kode },
        { type: 'fitur', description: meta.fitur ?? '' },
        { type: 'ref', description: meta.ref ?? '' },
        { type: 'harapan', description: meta.harapan },
        ...(meta.langkah ?? []).map((l) => ({ type: 'langkah', description: l })),
    ];
    test(`${kode} — ${meta.judul}`, { annotation: anotasi }, isi);
}
