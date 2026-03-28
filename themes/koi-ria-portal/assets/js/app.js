/**
 * メインJS — カルーセル、ハンバーガーメニュー、タブ、フィルター
 *
 * @package KoiRiaPortal
 */

document.addEventListener('DOMContentLoaded', () => {
    // =============================================
    // ハンバーガーメニュー
    // =============================================
    const menuToggle = document.getElementById('menuToggle');
    const globalNav = document.getElementById('globalNav');
    if (menuToggle && globalNav) {
        menuToggle.addEventListener('click', () => {
            const isOpen = menuToggle.classList.toggle('is-open');
            globalNav.classList.toggle('is-open');
            menuToggle.setAttribute('aria-expanded', isOpen);
        });
    }

    // =============================================
    // ヒーローカルーセル
    // =============================================
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

    // =============================================
    // プラットフォームフィルター（番組一覧 & 出演者DB）
    // =============================================
    document.querySelectorAll('.pill-filters').forEach(container => {
        const pills = container.querySelectorAll('.pill-filter');

        // シーズン選択フィルター（相関図）— data-season-id がある場合
        const hasSeasonFilter = [...pills].some(p => p.dataset.seasonId);
        if (hasSeasonFilter) {
            pills.forEach(pill => {
                pill.addEventListener('click', () => {
                    pills.forEach(p => p.classList.remove('is-active'));
                    pill.classList.add('is-active');
                    const seasonId = pill.dataset.seasonId;
                    document.querySelectorAll('.correlation-season').forEach(el => {
                        el.style.display = el.dataset.seasonId === seasonId ? '' : 'none';
                    });
                });
            });
            return;
        }

        // プラットフォームフィルター
        pills.forEach(pill => {
            if (!pill.dataset.platform) return;
            pill.addEventListener('click', () => {
                pills.forEach(p => p.classList.remove('is-active'));
                pill.classList.add('is-active');

                const platform = pill.dataset.platform;
                const section = container.closest('section') || container.closest('.section') || container.parentElement;

                // 番組カード（show-card、grid内）
                section.querySelectorAll('.show-card[data-platform]').forEach(card => {
                    if (platform === 'all') {
                        card.style.display = '';
                    } else {
                        card.style.display = card.dataset.platform === platform ? '' : 'none';
                    }
                });

                // アコーディオン（出演者DB — data-platform属性を持つ.accordion）
                section.querySelectorAll('.accordion[data-platform]').forEach(acc => {
                    if (platform === 'all') {
                        acc.style.display = '';
                    } else if (platform === 'other') {
                        const p = acc.dataset.platform;
                        acc.style.display = ['ABEMA', 'Netflix', 'Prime Video'].includes(p) ? 'none' : '';
                    } else {
                        acc.style.display = acc.dataset.platform === platform ? '' : 'none';
                    }
                });
            });
        });
    });

    // =============================================
    // 番組詳細タブ
    // =============================================
    document.querySelectorAll('.show-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.dataset.tab;
            if (!tabName) return;

            // タブボタン切替（ARIA対応）
            document.querySelectorAll('.show-tab').forEach(t => {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');

            // タブコンテンツ切替
            document.querySelectorAll('.show-tab-content').forEach(content => {
                content.classList.remove('is-active');
                content.style.display = 'none';
            });

            const target = document.getElementById('tab-' + tabName);
            if (target) {
                target.classList.add('is-active');
                target.style.display = '';
            }
        });
    });

    // =============================================
    // YouTube動画カードのクリック再生（番組詳細タブ内）
    // =============================================
    document.querySelectorAll('.card[data-video-id]').forEach(card => {
        card.addEventListener('click', () => {
            const videoId = card.dataset.videoId;
            if (!videoId) return;
            const thumb = card.querySelector('.card__thumb, [style*="background"]');
            if (thumb && thumb.parentElement) {
                thumb.parentElement.innerHTML = `<iframe
                    src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0"
                    style="width:100%;aspect-ratio:16/9;border:none;border-radius:var(--radius-lg) var(--radius-lg) 0 0;"
                    allow="autoplay; encrypted-media"
                    allowfullscreen></iframe>`;
            }
        });
    });

    // =============================================
    // スムーズスクロール（アンカーリンク）
    // =============================================
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
