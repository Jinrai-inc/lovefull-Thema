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
    // ヒーローカルーセル（黒背景スライダー + 自動再生）
    // =============================================
    const heroTrack = document.getElementById('heroTrack');
    const heroDots = document.getElementById('heroDots');
    const heroCounter = document.getElementById('heroCounterCurrent');
    const heroPrev = document.getElementById('heroPrev');
    const heroNext = document.getElementById('heroNext');

    if (heroTrack && heroDots) {
        const slides = heroTrack.querySelectorAll('.hero-carousel__slide');
        const dots = heroDots.querySelectorAll('.hero-carousel__dot');
        let current = 0;
        let autoSlideTimer;
        const AUTO_SLIDE_INTERVAL = 5000;

        function goToSlide(index) {
            // ループ対応
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            current = index;
            heroTrack.style.transform = `translateX(-${current * 100}%)`;

            // ドット更新
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === current);
            });

            // カウンター更新
            if (heroCounter) {
                heroCounter.textContent = current + 1;
            }
        }

        function nextSlide() {
            goToSlide(current + 1);
        }

        function prevSlide() {
            goToSlide(current - 1);
        }

        function startAutoSlide() {
            stopAutoSlide();
            autoSlideTimer = setInterval(nextSlide, AUTO_SLIDE_INTERVAL);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideTimer);
        }

        // ドットナビ
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                stopAutoSlide();
                goToSlide(parseInt(dot.dataset.index, 10));
                startAutoSlide();
            });
        });

        // 矢印ナビ
        if (heroPrev) {
            heroPrev.addEventListener('click', () => {
                stopAutoSlide();
                prevSlide();
                startAutoSlide();
            });
        }
        if (heroNext) {
            heroNext.addEventListener('click', () => {
                stopAutoSlide();
                nextSlide();
                startAutoSlide();
            });
        }

        // YouTube iframe 埋め込み再生
        slides.forEach(slide => {
            slide.addEventListener('click', () => {
                const videoId = slide.dataset.videoId;
                if (!videoId || slide.classList.contains('is-playing')) return;

                stopAutoSlide();
                slide.classList.add('is-playing');

                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
                iframe.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;border:none;z-index:5;';
                iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
                iframe.allowFullscreen = true;
                slide.appendChild(iframe);
            });
        });

        // 自動スライド開始
        if (slides.length > 1) {
            startAutoSlide();
        }

        // マウスホバーで一時停止
        const carousel = document.getElementById('heroCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', stopAutoSlide);
            carousel.addEventListener('mouseleave', () => {
                if (slides.length > 1) startAutoSlide();
            });
        }

        // タッチスワイプ（ループ対応）
        let touchStartX = 0;
        let touchStartY = 0;
        heroTrack.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            stopAutoSlide();
        }, { passive: true });

        heroTrack.addEventListener('touchend', (e) => {
            const diffX = touchStartX - e.changedTouches[0].clientX;
            const diffY = touchStartY - e.changedTouches[0].clientY;

            // 水平スワイプのみ反応（縦スクロールと区別）
            if (Math.abs(diffX) > 50 && Math.abs(diffX) > Math.abs(diffY)) {
                if (diffX > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
            if (slides.length > 1) startAutoSlide();
        }, { passive: true });

        // キーボードナビ（フォーカス時）
        if (carousel) {
            carousel.setAttribute('tabindex', '0');
            carousel.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') { stopAutoSlide(); prevSlide(); startAutoSlide(); }
                if (e.key === 'ArrowRight') { stopAutoSlide(); nextSlide(); startAutoSlide(); }
            });
        }
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
    // カップルフィルター（ステータス・番組）
    // =============================================
    const coupleGrid = document.getElementById('coupleGrid');
    if (coupleGrid) {
        const coupleCards = coupleGrid.querySelectorAll('.couple-card');
        let activeStatus = 'all';
        let activeShowId = 'all';

        function filterCouples() {
            coupleCards.forEach(card => {
                const cardStatus = card.dataset.status || '';
                const cardShowId = card.dataset.showId || '';
                const matchStatus = activeStatus === 'all' || cardStatus === activeStatus;
                const matchShow = activeShowId === 'all' || cardShowId === activeShowId;
                card.style.display = (matchStatus && matchShow) ? '' : 'none';
            });
        }

        // Status filter pills
        document.querySelectorAll('.pill-filter[data-status]').forEach(pill => {
            pill.addEventListener('click', () => {
                document.querySelectorAll('.pill-filter[data-status]').forEach(p => p.classList.remove('is-active'));
                pill.classList.add('is-active');
                activeStatus = pill.dataset.status;
                filterCouples();
            });
        });

        // Show filter pills
        document.querySelectorAll('.pill-filter[data-show-id]').forEach(pill => {
            pill.addEventListener('click', () => {
                document.querySelectorAll('.pill-filter[data-show-id]').forEach(p => p.classList.remove('is-active'));
                pill.classList.add('is-active');
                activeShowId = pill.dataset.showId;
                filterCouples();
            });
        });
    }

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

    // =============================================
    // モバイルドロワーメニュー
    // =============================================
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
    const mobileDrawerClose = document.getElementById('mobileDrawerClose');

    function openDrawer() {
        if (mobileDrawer) {
            mobileDrawer.style.display = '';
            mobileMenuBtn?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDrawer() {
        if (mobileDrawer) {
            mobileDrawer.style.display = 'none';
            mobileMenuBtn?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openDrawer);
    }
    if (mobileDrawerOverlay) {
        mobileDrawerOverlay.addEventListener('click', closeDrawer);
    }
    if (mobileDrawerClose) {
        mobileDrawerClose.addEventListener('click', closeDrawer);
    }
});
