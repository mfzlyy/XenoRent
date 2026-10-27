export function NadhifCurrency(amountIdr, currency, usdIdrRate) {
    const amount = Number(amountIdr);
    const rate = Number(usdIdrRate);

    if (!Number.isFinite(amount) || !Number.isFinite(rate) || rate <= 0) {
        return 'Nilai tidak tersedia';
    }

    if (currency === 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount / rate);
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

function formatRate(rate) {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(rate);
}

function formatRateDate(date) {
    const parsedDate = new Date(`${date}T00:00:00Z`);

    if (Number.isNaN(parsedDate.getTime())) return date;

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeZone: 'UTC',
    }).format(parsedDate);
}

function updateCurrency(table, currency) {
    const rate = table.dataset.usdIdrRate;
    table.querySelectorAll('[data-fine-amount]').forEach((cell) => {
        cell.textContent = NadhifCurrency(cell.dataset.fineAmount, currency, rate);
    });

    const button = table.querySelector('[data-currency-toggle]');
    if (button) {
        button.dataset.currency = currency;
        button.setAttribute('aria-pressed', String(currency === 'USD'));
        button.textContent = currency === 'USD' ? 'Tampilkan Rupiah' : 'Tampilkan USD';
    }

    const currentCurrency = table.querySelector('[data-current-currency]');
    if (currentCurrency) {
        currentCurrency.textContent = currency === 'USD' ? 'USD' : 'Rupiah';
    }

    document.querySelectorAll('[data-currency-pdf]').forEach((link) => {
        const url = new URL(link.href, window.location.href);
        url.searchParams.set('currency', currency);
        link.href = url.toString();
    });
}

async function refreshCurrencyRate(table) {
    const label = table.querySelector('[data-currency-rate-label]');
    const button = table.querySelector('[data-currency-toggle]');

    try {
        const response = await fetch(table.dataset.currencyRateUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
        });
        if (!response.ok) throw new Error('Kurs tidak tersedia');

        const data = await response.json();
        const rate = Number(data.rate);
        if (!Number.isFinite(rate) || rate <= 0 || typeof data.date !== 'string') {
            throw new Error('Data kurs tidak valid');
        }

        table.dataset.usdIdrRate = String(rate);
        table.dataset.usdIdrDate = data.date;
        if (label) {
            label.textContent = `Kurs referensi ${formatRateDate(data.date)}: 1 USD = Rp${formatRate(rate)}.`;
        }
        if (button) button.disabled = false;

        const currency = button?.dataset.currency === 'USD' ? 'USD' : 'IDR';
        updateCurrency(table, currency);
    } catch {
        const hasRate = Number(table.dataset.usdIdrRate) > 0;
        if (label && hasRate) {
            label.textContent = `Koneksi kurs gagal. Kurs terakhir ${formatRateDate(table.dataset.usdIdrDate)}: 1 USD = Rp${formatRate(Number(table.dataset.usdIdrRate))}.`;
        } else if (label) {
            label.textContent = 'Kurs USD/IDR tidak tersedia. Akan dicoba lagi otomatis.';
        }
        if (button) button.disabled = !hasRate;
    }
}

async function refreshCurrencyRates() {
    await Promise.all(
        Array.from(document.querySelectorAll('[data-currency-table]'), refreshCurrencyRate),
    );
}

if (typeof document !== 'undefined') {
    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;

        const button = event.target.closest('[data-currency-toggle]');
        if (!button) return;

        const table = button.closest('[data-currency-table]');
        if (!table) return;

        updateCurrency(table, button.dataset.currency === 'USD' ? 'IDR' : 'USD');
    });

    refreshCurrencyRates();
    window.setInterval(refreshCurrencyRates, 15 * 60 * 1000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refreshCurrencyRates();
    });
}
