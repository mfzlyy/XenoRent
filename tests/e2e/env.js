import path from 'node:path';
import { fileURLToPath } from 'node:url';

export const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');
export const PORT = 8765;
export const BASE_URL = `http://127.0.0.1:${PORT}`;

// Database khusus UAT, terpisah dari database.sqlite milik development.
export const DB_PATH = path.join(ROOT, 'database', 'uat.sqlite');

export const UAT_DIR = path.join(ROOT, 'docs', 'uat');
export const EVIDENCE_DIR = path.join(UAT_DIR, 'screenshots');
export const PDF_DIR = path.join(UAT_DIR, 'pdf');

// Variabel environment untuk server PHP dan perintah artisan selama UAT.
// Variabel proses menang atas isi .env, jadi .env milik development tidak diubah.
export const UAT_ENV = {
    APP_ENV: 'local',
    APP_DEBUG: 'false',
    APP_URL: BASE_URL,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: DB_PATH,
    SESSION_DRIVER: 'database',
    // Dikunci eksplisit: Git Bash (MSYS) mengubah nilai "/" menjadi "C:/Program Files/Git/" dan merusak cookie sesi.
    SESSION_PATH: '/',
    CACHE_STORE: 'file',
    QUEUE_CONNECTION: 'sync',
    BCRYPT_ROUNDS: '4',
    LOG_CHANNEL: 'single',
};
