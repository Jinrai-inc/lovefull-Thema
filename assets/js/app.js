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
    // ヒーローカルーセル（YouTube自動再生スライダー）
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
        let userClickedPlay = false;
        const AUTO_SLIDE_INTERVAL = 8000;

        // 全スライドのiframeを強制削除（音の重なり防止）
        function killAllIframes() {
            slides.forEach(s => {
                const iframes = s.querySelectorAll('iframe');
                iframes.forEach(f => {
                    try { f.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); } catch(e) {}
                    f.src = 'about:blank';
                    f.remove();
                });
                s.classList.remove('is-playing');
            });
        }

        // YouTube iframe を埋め込み
        function embedVideo(slide, muted) {
            const videoId = slide.dataset.videoId;
            if (!videoId) return;

            // まず全iframeを消す（重複防止）
            killAllIframes();

            slide.classList.add('is-playing');

            const iframe = document.createElement('iframe');
            const muteParam = muted ? '&mute=1' : '';
            iframe.src = 'https://www.youtube.com/embed/' + videoId
                + '?autoplay=1' + muteParam
                + '&rel=0&modestbranding=1&playsinline=1&enablejsapi=1';
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');
            iframe.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;border:none;z-index:5;';
            slide.appendChild(iframe);
        }

        function goToSlide(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            // スライド変更時: 全iframeを削除して音を完全停止
            killAllIframes();

            current = index;
            heroTrack.style.transform = 'translateX(-' + (current * 100) + '%)';

            dots.forEach(function(dot, i) {
                dot.classList.toggle('is-active', i === current);
            });

            if (heroCounter) {
                heroCounter.textContent = current + 1;
            }

            // ユーザーが手動再生していない場合、次のスライドもミュート自動再生
            if (!userClickedPlay) {
                setTimeout(function() { embedVideo(slides[current], true); }, 400);
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
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                userClickedPlay = false;
                stopAutoSlide();
                goToSlide(parseInt(dot.dataset.index, 10));
                startAutoSlide();
            });
        });

        // 矢印ナビ
        if (heroPrev) {
            heroPrev.addEventListener('click', function() {
                userClickedPlay = false;
                stopAutoSlide();
                prevSlide();
                startAutoSlide();
            });
        }
        if (heroNext) {
            heroNext.addEventListener('click', function() {
                userClickedPlay = false;
                stopAutoSlide();
                nextSlide();
                startAutoSlide();
            });
        }

        // スライドクリック → 音声付き再生
        slides.forEach(function(slide) {
            slide.addEventListener('click', function(e) {
                // iframe内のクリックは無視（動画操作用）
                if (e.target.tagName === 'IFRAME') return;

                var videoId = slide.dataset.videoId;
                if (!videoId) return;

                userClickedPlay = true;
                stopAutoSlide();
                embedVideo(slide, false);
            });
        });

        // ページ読み込み時: 最初のスライドをミュート自動再生
        if (slides.length > 0 && slides[0].dataset.videoId) {
            setTimeout(function() { embedVideo(slides[0], true); }, 500);
        }

        // 複数スライドなら自動スライド
        if (slides.length > 1) {
            startAutoSlide();
        }

        // マウスホバーで一時停止
        var carousel = document.getElementById('heroCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', stopAutoSlide);
            carousel.addEventListener('mouseleave', function() {
                if (slides.length > 1 && !userClickedPlay) startAutoSlide();
            });
        }

        // タッチスワイプ
        var touchStartX = 0;
        var touchStartY = 0;
        heroTrack.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            stopAutoSlide();
        }, { passive: true });

        heroTrack.addEventListener('touchend', function(e) {
            var diffX = touchStartX - e.changedTouches[0].clientX;
            var diffY = touchStartY - e.changedTouches[0].clientY;

            if (Math.abs(diffX) > 50 && Math.abs(diffX) > Math.abs(diffY)) {
                userClickedPlay = false;
                if (diffX > 0) { nextSlide(); } else { prevSlide(); }
            }
            if (slides.length > 1 && !userClickedPlay) startAutoSlide();
        }, { passive: true });

        // キーボードナビ
        if (carousel) {
            carousel.setAttribute('tabindex', '0');
            carousel.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') { userClickedPlay = false; stopAutoSlide(); prevSlide(); startAutoSlide(); }
                if (e.key === 'ArrowRight') { userClickedPlay = false; stopAutoSlide(); nextSlide(); startAutoSlide(); }
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
