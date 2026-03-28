/**
 * コラムスライダー制御
 *
 * @package KoiRiaPortal
 */

document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('columnSlider');
    const dotsContainer = document.getElementById('columnDots');
    if (!slider || !dotsContainer) return;

    const slides = slider.querySelectorAll('.column-slide');
    if (slides.length === 0) return;

    // ドット生成
    slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('aria-label', `スライド${i + 1}`);
        dot.addEventListener('click', () => {
            slides[i].scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
        });
        dotsContainer.appendChild(dot);
    });

    // スクロール監視
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const idx = [...slides].indexOf(entry.target);
                dotsContainer.querySelectorAll('.slider-dot').forEach((d, i) => {
                    d.classList.toggle('active', i === idx);
                });
            }
        });
    }, { root: slider, threshold: 0.6 });

    slides.forEach(s => observer.observe(s));
});
