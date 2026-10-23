import fs from 'node:fs';
import path from 'node:path';
import { UAT_DIR } from './env.js';

const JSON_PATH = path.join(UAT_DIR, 'hasil-uat.json');
const MD_PATH = path.join(UAT_DIR, 'Hasil-UAT.md');

const tanpaAnsi = (teks) => String(teks ?? '').replace(/\u001b\[[0-9;]*m/g, '');
const satuBaris = (teks) => tanpaAnsi(teks).replace(/\s+/g, ' ').trim();
const ambil = (anotasi, tipe) => anotasi.filter((a) => a.type === tipe).map((a) => a.description);

/** Urutan tampil: KP-xx dulu, lalu kelompok lain berdasarkan awalan. */
const URUTAN_GRUP = ['KP', 'F', 'S', 'VAL', 'KT', 'TB', 'NFR', 'INT'];
const grupDari = (kode) => kode.split('-')[0];
const bandingKode = (a, b) => {
    const ga = URUTAN_GRUP.indexOf(grupDari(a));
    const gb = URUTAN_GRUP.indexOf(grupDari(b));
    if (ga !== gb) return (ga === -1 ? 99 : ga) - (gb === -1 ? 99 : gb);
    return a.localeCompare(b, 'en', { numeric: true });
};

const NAMA_GRUP = {
    KP: 'A. Kriteria Penerimaan (KP-01 s.d. KP-33)',
    F: 'B. Fitur pendukung (login, logout, profil, fitur Must lain)',
    S: 'C. Fitur Should / Could',
    VAL: 'D. Validasi field per form (F-14, NFR-03)',
    KT: 'E. Kondisi tampilan (PRD bagian 8)',
    TB: 'F. Tujuan bisnis (BRD bagian 3)',
    NFR: 'G. Kebutuhan non-fungsional (BRD bagian 6)',
    INT: 'H. Integritas data (Flowchart & ERD bagian 9)',
};

export default class UatReporter {
    constructor() {
        this.hasil = {};
        this.mulai = new Date();
    }

    onTestEnd(test, result) {
        const kode = ambil(test.annotations, 'kode')[0];
        if (!kode) return;

        const gagal = result.status === 'failed' || result.status === 'timedOut' || result.status === 'interrupted';
        const deviasi = ambil(test.annotations, 'deviasi');
        let status = 'Lulus';
        if (result.status === 'skipped') status = 'Dilewati';
        else if (gagal) status = 'Gagal';
        else if (deviasi.length) status = 'Lulus dengan catatan';

        this.hasil[kode] = {
            kode,
            judul: test.title.replace(/^\S+ — /, ''),
            fitur: ambil(test.annotations, 'fitur')[0] ?? '',
            ref: ambil(test.annotations, 'ref')[0] ?? '',
            langkah: ambil(test.annotations, 'langkah'),
            harapan: ambil(test.annotations, 'harapan')[0] ?? '',
            status,
            deviasi,
            kegagalan: gagal ? satuBaris(result.errors.map((e) => e.message).join(' ')).slice(0, 700) : '',
            bukti: ambil(test.annotations, 'bukti').map((b) => {
                const [berkas, ...label] = b.split('|');
                return { berkas, label: label.join('|') };
            }),
            durasiMs: result.duration,
            dijalankan: new Date().toISOString(),
        };
    }

    onEnd() {
        fs.mkdirSync(UAT_DIR, { recursive: true });

        // Gabungkan dengan hasil run sebelumnya supaya menjalankan sebagian test tidak menghapus sisanya.
        let semua = {};
        if (fs.existsSync(JSON_PATH)) {
            try {
                semua = JSON.parse(fs.readFileSync(JSON_PATH, 'utf8'));
            } catch {
                semua = {};
            }
        }
        Object.assign(semua, this.hasil);
        fs.writeFileSync(JSON_PATH, JSON.stringify(semua, null, 2));
        fs.writeFileSync(MD_PATH, this.render(semua));
    }

    render(semua) {
        const daftar = Object.values(semua).sort((a, b) => bandingKode(a.kode, b.kode));
        const hitung = (s) => daftar.filter((t) => t.status === s).length;
        const lulus = hitung('Lulus') + hitung('Lulus dengan catatan');

        const baris = [];
        baris.push('# Hasil UAT — XenoRent', '');
        baris.push(`Dibuat otomatis oleh Playwright pada ${new Date().toISOString().replace('T', ' ').slice(0, 19)} UTC. Skenario diturunkan dari BRD, Flowchart & ERD, dan PRD (kode KP, F, S, NFR, TB mengacu ke dokumen tersebut).`, '');
        baris.push('## Ringkasan', '');
        baris.push('| | Jumlah |', '|---|---|');
        baris.push(`| Total skenario | ${daftar.length} |`);
        baris.push(`| Lulus | ${hitung('Lulus')} |`);
        baris.push(`| Lulus dengan catatan (perilaku benar, kalimat pesan berbeda dari PRD) | ${hitung('Lulus dengan catatan')} |`);
        baris.push(`| Gagal | ${hitung('Gagal')} |`);
        if (hitung('Dilewati')) baris.push(`| Dilewati | ${hitung('Dilewati')} |`);
        baris.push(`| Persentase lulus | ${daftar.length ? Math.round((lulus / daftar.length) * 100) : 0}% |`, '');
        baris.push('**Lingkungan uji:** Chromium (Playwright) 1366×768, database SQLite terpisah (`database/uat.sqlite`) yang di-seed ulang sebelum setiap skenario (`php artisan migrate:fresh --seed`).', '');

        const grupAda = [...new Set(daftar.map((t) => grupDari(t.kode)))];
        baris.push('## Rekap per skenario', '');
        for (const g of grupAda) {
            baris.push(`### ${NAMA_GRUP[g] ?? g}`, '');
            baris.push('| Kode | Fitur | Skenario | Hasil |', '|---|---|---|---|');
            for (const t of daftar.filter((x) => grupDari(x.kode) === g)) {
                baris.push(`| ${t.kode} | ${t.fitur} | ${t.judul} | ${t.status} |`);
            }
            baris.push('');
        }

        const bermasalah = daftar.filter((t) => t.status === 'Gagal' || t.status === 'Lulus dengan catatan');
        if (bermasalah.length) {
            baris.push('## Temuan yang perlu ditindaklanjuti', '');
            for (const t of bermasalah) {
                baris.push(`- **${t.kode} — ${t.judul}** (${t.status})`);
                if (t.kegagalan) baris.push(`  - Penyebab gagal: ${t.kegagalan}`);
                for (const d of t.deviasi) baris.push(`  - ${d}`);
            }
            baris.push('');
        }

        baris.push('## Detail skenario dan bukti', '');
        for (const g of grupAda) {
            baris.push(`### ${NAMA_GRUP[g] ?? g}`, '');
            for (const t of daftar.filter((x) => grupDari(x.kode) === g)) {
                baris.push(`#### ${t.kode} — ${t.judul}`, '');
                baris.push(`- **Fitur / referensi:** ${[t.fitur, t.ref].filter(Boolean).join(' · ')}`);
                baris.push(`- **Hasil:** ${t.status}`);
                baris.push('- **Langkah:**');
                t.langkah.forEach((l, i) => baris.push(`  ${i + 1}. ${l}`));
                baris.push(`- **Hasil yang diharapkan:** ${t.harapan}`);
                if (t.kegagalan) baris.push(`- **Hasil aktual (gagal):** ${t.kegagalan}`);
                for (const d of t.deviasi) baris.push(`- **Catatan deviasi:** ${d}`);
                baris.push('');
                for (const b of t.bukti) {
                    baris.push(`*${b.label}* — \`${b.berkas}\``, '', `![${b.label}](screenshots/${b.berkas})`, '');
                }
            }
        }
        return baris.join('\n');
    }
}
