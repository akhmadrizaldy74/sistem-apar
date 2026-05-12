import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

const initReveal = () => {
    const elements = Array.from(document.querySelectorAll('[data-reveal]'));
    if (!elements.length) {
        return;
    }

    elements.forEach((element, index) => {
        window.setTimeout(() => {
            element.classList.add('reveal-visible');
        }, 120 + index * 80);
    });
};

const initProgressBars = () => {
    const bars = Array.from(document.querySelectorAll('[data-progress]'));
    if (!bars.length) {
        return;
    }

    requestAnimationFrame(() => {
        bars.forEach((bar) => {
            const raw = bar.getAttribute('data-progress');
            const value = Number(raw);
            if (Number.isFinite(value)) {
                bar.style.width = `${Math.max(0, Math.min(100, value))}%`;
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initReveal();
    initProgressBars();
});
