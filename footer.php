<?php
/**
 * フッターテンプレート
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;
?>
</main>

<?php if (is_active_sidebar('footer-banners')) : ?>
<div class="footer-banners-area">
    <div class="footer-banners-area__inner">
        <?php dynamic_sidebar('footer-banners'); ?>
    </div>
</div>
<?php endif; ?>

<footer class="site-footer" role="contentinfo">
    <div class="site-footer__inner">
        <div class="site-footer__logo">
            <span class="site-logo__icon"><?php echo koi_ria_icon('heart-filled', 24); ?></span>
            <span class="site-logo__text">恋リアポータル</span>
        </div>

        <nav class="site-footer__nav" aria-label="フッターナビゲーション">
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container'      => false,
                'menu_class'     => 'footer-nav__list',
                'fallback_cb'    => false,
            ]);
            ?>
        </nav>

        <div class="site-footer__links">
            <?php
            $legal_pages = [
                'company'  => '運営会社',
                'terms'    => '利用規約',
                'privacy-policy' => 'プライバシーポリシー',
                'tokushoho' => '特定商取引法に基づく表記',
                'contact'  => 'お問い合わせ',
            ];
            foreach ($legal_pages as $slug => $label) :
                $page = get_page_by_path($slug);
                if ($page) :
            ?>
                <a href="<?php echo esc_url(get_permalink($page)); ?>"><?php echo esc_html($label); ?></a>
            <?php
                endif;
            endforeach;

            // フォールバック: プライバシーポリシーURL（WordPress設定）
            $privacy_url = get_privacy_policy_url();
            if ($privacy_url && !get_page_by_path('privacy-policy')) :
            ?>
                <a href="<?php echo esc_url($privacy_url); ?>">プライバシーポリシー</a>
            <?php endif; ?>
        </div>

        <p class="site-footer__copy">
            &copy; <?php echo esc_html(date('Y')); ?> 恋リアポータル All Rights Reserved.
        </p>
    </div>
</footer>

<!-- モバイルボトムナビ -->
<nav class="mobile-bottom-nav" aria-label="モバイルナビゲーション">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-bottom-nav__item <?php echo is_front_page() ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon"><?php echo koi_ria_icon('home', 22); ?></span>
        <span class="mobile-bottom-nav__label">ホーム</span>
    </a>
    <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>" class="mobile-bottom-nav__item <?php echo is_post_type_archive('cast') ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon"><?php echo koi_ria_icon('users', 22); ?></span>
        <span class="mobile-bottom-nav__label">出演者</span>
    </a>
    <?php
    $fav_page = get_page_by_path('favorites');
    $fav_url  = $fav_page ? get_permalink($fav_page) : home_url('/favorites/');
    ?>
    <a href="<?php echo esc_url($fav_url); ?>" class="mobile-bottom-nav__item <?php echo is_page('favorites') ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon"><?php echo koi_ria_icon('heart', 22); ?></span>
        <span class="mobile-bottom-nav__label">推し</span>
    </a>
    <a href="<?php echo esc_url(get_post_type_archive_link('poll')); ?>" class="mobile-bottom-nav__item <?php echo is_post_type_archive('poll') ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon"><?php echo koi_ria_icon('chart', 22); ?></span>
        <span class="mobile-bottom-nav__label">投票</span>
    </a>
    <button class="mobile-bottom-nav__item" id="mobileMenuBtn" aria-label="メニューを開く" aria-expanded="false">
        <span class="mobile-bottom-nav__icon"><?php echo koi_ria_icon('menu', 22); ?></span>
        <span class="mobile-bottom-nav__label">メニュー</span>
    </button>
</nav>

<!-- モバイルドロワーメニュー -->
<div class="mobile-drawer" id="mobileDrawer" style="display:none;">
    <div class="mobile-drawer__overlay" id="mobileDrawerOverlay"></div>
    <div class="mobile-drawer__panel">
        <div class="mobile-drawer__header">
            <span>メニュー</span>
            <button class="mobile-drawer__close" id="mobileDrawerClose" aria-label="閉じる">&times;</button>
        </div>
        <nav class="mobile-drawer__nav">
            <a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>"><?php echo koi_ria_icon('tv', 20); ?> 番組一覧</a>
            <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>"><?php echo koi_ria_icon('users', 20); ?> 出演者データベース</a>
            <a href="<?php echo esc_url(get_post_type_archive_link('couple')); ?>"><?php echo koi_ria_icon('couple', 20); ?> カップルその後</a>
            <a href="<?php echo esc_url(get_post_type_archive_link('poll')); ?>"><?php echo koi_ria_icon('chart', 20); ?> みんなの予想</a>
            <?php
            $shindan_page = get_page_by_path('shindan');
            ?>
            <?php if ($shindan_page) : ?>
            <a href="<?php echo esc_url(get_permalink($shindan_page)); ?>"><?php echo koi_ria_icon('sparkle', 20); ?> 番組診断</a>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/?s=')); ?>"><?php echo koi_ria_icon('search', 20); ?> 検索</a>
        </nav>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
