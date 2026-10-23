import fs from 'node:fs';
import { defineConfig, devices } from '@playwright/test';
import { BASE_URL, DB_PATH, PORT, ROOT, UAT_ENV } from './tests/e2e/env.js';

// migrate:fresh butuh file sqlite yang sudah ada saat dijalankan non-interaktif.
fs.closeSync(fs.openSync(DB_PATH, 'a'));

export default defineConfig({
    testDir: './tests/e2e',
    testMatch: /.*\.spec\.js/,
    fullyParallel: false,
    workers: 1,
    retries: 0,
    timeout: 45_000,
    expect: { timeout: 8_000 },
    outputDir: 'test-results',
    reporter: [
        ['list'],
        ['html', { outputFolder: 'docs/uat/playwright-report', open: 'never' }],
        ['./tests/e2e/uat-reporter.js'],
    ],
    use: {
        baseURL: BASE_URL,
        locale: 'id-ID',
        timezoneId: 'UTC',
        actionTimeout: 12_000,
        navigationTimeout: 20_000,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'], viewport: { width: 1366, height: 768 } },
        },
    ],
    webServer: {
        // Seed ulang database UAT, lalu jalankan server yang sama seperti `php artisan serve`.
        command: `php artisan migrate:fresh --seed --force && cd public && php -S 127.0.0.1:${PORT} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
        url: `${BASE_URL}/login`,
        cwd: ROOT,
        env: UAT_ENV,
        reuseExistingServer: false,
        timeout: 90_000,
    },
});
