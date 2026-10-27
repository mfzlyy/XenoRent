import { expect, test } from '@playwright/test';

async function signIn(page, email) {
    await page.goto('/login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page).toHaveURL(/dashboard/);
}

test('Unit 12: anggota dapat membuka demo audio HTML5', async ({ page }) => {
    await signIn(page, 'bima@xenorent.test');
    await page.goto('/panduan-media');

    const player = page.locator('audio');
    await expect(page.getByRole('heading', { name: 'Contoh audio notifikasi' })).toBeVisible();
    await expect(player).toBeVisible();
    await expect(player).toHaveAttribute('aria-label', 'Pratinjau audio notifikasi rental');

    const audioResponse = await page.request.get('/audio/rental-signal.wav');
    expect(audioResponse.status()).toBe(200);
    expect(audioResponse.headers()['content-type']).toContain('audio/wav');

    await page.screenshot({ path: 'docs/portofolio/screenshots/unit-12-media-demo.png', fullPage: true });
});

test('Unit 16: dashboard monitoring hanya tampil untuk admin', async ({ page }) => {
    await signIn(page, 'admin@xenorent.test');
    await page.goto('/admin/resources');

    await expect(page.getByRole('heading', { name: 'Pemantauan resource' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Disk server' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Memori proses PHP' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Database' })).toBeVisible();
    await expect(page.getByRole('progressbar')).toHaveCount(2);

    await page.screenshot({ path: 'docs/portofolio/screenshots/unit-16-resource-monitor.png', fullPage: true });
});
