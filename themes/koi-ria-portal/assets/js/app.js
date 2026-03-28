/**
 * メインJS — カルーセル、ハンバーガーメニュー
 *
 * @package KoiRiaPortal
 */

document.addEventListener('DOMContentLoaded', () => {
    // ハンバーガーメニュー
    const menuToggle = document.getElementById('menuToggle');
    const globalNav = document.getElementById('globalNav');
    if (menuToggle && globalNav) {
        menuToggle.addEventListener('click', () => {
            const isOpen = menuToggle.classList.toggle('is-open');
            globalNav.classList.toggle('is-open');
            menuToggle.setAttribute('aria-expanded', isOpen);
        });
    }

    // ヒーローカルーセル
    const heroTrack = document.getElementById('heroTrack');
    const heroDots = document.getElementById('heroDots');
    if (heroTrack && heroDots) {
        const slides = heroTrack.querySelectorAll('.hero-carousel__slide');
        const dots = heroDots.querySelectorAll('.hero-carousel__dot');
        let current = 0;
        let autoSlideTimer;

        function goToSlide(index) {
            current = index;
            heroTrack.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === current);
            });
        }

        function nextSlide() {
            goToSlide((current + 1) % slides.length);
        }

        function startAutoSlide() {
            autoSlideTimer = setInterval(nextSlide, 6000);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideTimer);
        }

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                stopAutoSlide();
                goToSlide(parseInt(dot.dataset.index, 10));
                startAutoSlide();
            });
        });

        // YouTube iframe 埋め込み再生
        slides.forEach(slide => {
            slide.addEventListener('click', () => {
                const videoId = slide.dataset.videoId;
                if (!videoId) return;
                stopAutoSlide();
                slide.innerHTML = `<iframe
                    src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0"
                    style="width:100%;height:100%;border:none;"
                    allow="autoplay; encrypted-media"
                    allowfullscreen></iframe>`;
            });
        });

        if (slides.length > 1) {
            startAutoSlide();
        }

        // タッチスワイプ
        let touchStartX = 0;
        heroTrack.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            stopAutoSlide();
        }, { passive: true });

        heroTrack.addEventListener('touchend', (e) => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                if (diff > 0 && current < slides.length - 1) {
                    goToSlide(current + 1);
                } else if (diff < 0 && current > 0) {
                    goToSlide(current - 1);
                }
            }
            startAutoSlide();
        }, { passive: true });
    }

    // プラットフォームフィルター
    document.querySelectorAll('.pill-filters').forEach(container => {
        const pills = container.querySelectorAll('.pill-filter');
        pills.forEach(pill => {
            pill.addEventListener('click', () => {
                pills.forEach(p => p.classList.remove('is-active'));
                pill.classList.add('is-active');

                const platform = pill.dataset.platform;
                const parent = container.closest('.section') || container.parentElement;
                const items = parent.querySelectorAll('[data-platform]');

                items.forEach(item => {
                    if (item.classList.contains('pill-filter')) return;
                    if (platform === 'all') {
                        item.style.display = '';
                    } else {
                        item.style.display = item.dataset.platform === platform ? '' : 'none';
                    }
                });
            });
        });
    });
});
