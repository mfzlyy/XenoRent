import './NadhifCurrency/NadhifCurrency.js';

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;

    const button = event.target.closest('[data-print]');
    if (!(button instanceof HTMLButtonElement) || button.disabled) return;

    window.print();
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    const confirmation = form.dataset.confirm;
    if (confirmation && !window.confirm(confirmation)) {
        event.preventDefault();
        return;
    }

    const button = event.submitter;
    if (button instanceof HTMLButtonElement && button.type === 'submit') {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Memproses...';
    }
});

