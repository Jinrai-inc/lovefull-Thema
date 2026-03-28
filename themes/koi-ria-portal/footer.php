<?php
/**
 * フッターテンプレート
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;
?>
</main>

<footer class="site-footer" role="contentinfo">
    <div class="site-footer__inner">
        <div class="site-footer__logo">
            <span class="site-logo__icon">&#x1F496;</span>
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
        <span class="mobile-bottom-nav__icon">&#x1F3E0;</span>
        <span class="mobile-bottom-nav__label">ホーム</span>
    </a>
    <a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>" class="mobile-bottom-nav__item <?php echo is_post_type_archive('show') ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon">&#x1F4FA;</span>
        <span class="mobile-bottom-nav__label">番組</span>
    </a>
    <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>" class="mobile-bottom-nav__item <?php echo is_post_type_archive('cast') ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon">&#x1F465;</span>
        <span class="mobile-bottom-nav__label">出演者</span>
    </a>
    <a href="<?php echo esc_url(get_post_type_archive_link('poll')); ?>" class="mobile-bottom-nav__item <?php echo is_post_type_archive('poll') ? 'is-active' : ''; ?>">
        <span class="mobile-bottom-nav__icon">&#x1F4CA;</span>
        <span class="mobile-bottom-nav__label">投票</span>
    </a>
    <a href="<?php echo esc_url(home_url('/?s=')); ?>" class="mobile-bottom-nav__item">
        <span class="mobile-bottom-nav__icon">&#x1F50D;</span>
        <span class="mobile-bottom-nav__label">検索</span>
    </a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
