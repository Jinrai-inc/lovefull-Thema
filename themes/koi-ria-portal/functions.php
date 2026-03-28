<?php
/**
 * 恋リアポータル テーマ functions
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

define('KOI_RIA_VERSION', '1.0.0');
define('KOI_RIA_DIR', get_template_directory());
define('KOI_RIA_URI', get_template_directory_uri());

/**
 * テーマセットアップ
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    register_nav_menus([
        'primary'      => 'ヘッダーナビ',
        'footer'       => 'フッターナビ',
        'mobile_bottom' => 'モバイルボトムナビ',
    ]);

    // サムネイルサイズ
    add_image_size('cast-avatar', 120, 120, true);
    add_image_size('show-card', 260, 160, true);
    add_image_size('hero-thumb', 800, 450, true);
});

/**
 * スタイル・スクリプト読み込み
 */
add_action('wp_enqueue_scripts', function () {
    // Google Fonts
    wp_enqueue_style(
        'koi-ria-google-fonts',
        'https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap',
        [],
        null
    );

    // テーマCSS
    wp_enqueue_style('koi-ria-base', KOI_RIA_URI . '/assets/css/base.css', [], KOI_RIA_VERSION);
    wp_enqueue_style('koi-ria-components', KOI_RIA_URI . '/assets/css/components.css', ['koi-ria-base'], KOI_RIA_VERSION);
    wp_enqueue_style('koi-ria-layout', KOI_RIA_URI . '/assets/css/layout.css', ['koi-ria-base'], KOI_RIA_VERSION);
    wp_enqueue_style('koi-ria-pages', KOI_RIA_URI . '/assets/css/pages.css', ['koi-ria-layout'], KOI_RIA_VERSION);
    wp_enqueue_style('koi-ria-style', get_stylesheet_uri(), ['koi-ria-pages'], KOI_RIA_VERSION);

    // テーマJS
    wp_enqueue_script('koi-ria-app', KOI_RIA_URI . '/assets/js/app.js', [], KOI_RIA_VERSION, true);
    wp_enqueue_script('koi-ria-column-slider', KOI_RIA_URI . '/assets/js/column-slider.js', [], KOI_RIA_VERSION, true);
    wp_enqueue_script('koi-ria-cast-accordion', KOI_RIA_URI . '/assets/js/cast-accordion.js', [], KOI_RIA_VERSION, true);
    wp_enqueue_script('koi-ria-poll-vote', KOI_RIA_URI . '/assets/js/poll-vote.js', [], KOI_RIA_VERSION, true);

    // Ajax用のローカライズ
    wp_localize_script('koi-ria-poll-vote', 'koiRia', [
        'ajaxUrl'  => rest_url('koi-ria/v1/'),
        'nonce'    => wp_create_nonce('wp_rest'),
        'siteUrl'  => home_url('/'),
    ]);
});

/**
 * Preconnect / DNS Prefetch（パフォーマンス最適化）
 */
add_action('wp_head', function () {
    // Google Fonts
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // YouTube embeds
    echo '<link rel="dns-prefetch" href="//www.youtube.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//i.ytimg.com">' . "\n";
    // Instagram CDN
    echo '<link rel="dns-prefetch" href="//scontent.cdninstagram.com">' . "\n";
    // Facebook Graph API
    echo '<link rel="dns-prefetch" href="//graph.facebook.com">' . "\n";
}, 0);

/**
 * AdSenseスクリプト読み込み
 */
add_action('wp_head', function () {
    $adsense_client = get_option('koi_ria_adsense_client_id', '');
    if ($adsense_client) {
        echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr($adsense_client) . '" crossorigin="anonymous"></script>' . "\n";
    }
}, 1);

/**
 * インクルードファイル読み込み
 */
$koi_ria_includes = [
    'inc/cpt-register.php',
    'inc/acf-fields.php',
    'inc/rest-api.php',
    'inc/cron-youtube.php',
    'inc/cron-news.php',
    'inc/cron-instagram.php',
    'inc/csv-importer.php',
    'inc/structured-data.php',
    'inc/ogp-meta.php',
    'inc/admin-menu.php',
];

foreach ($koi_ria_includes as $file) {
    $filepath = KOI_RIA_DIR . '/' . $file;
    if (file_exists($filepath)) {
        require_once $filepath;
    }
}

/**
 * wp_get_attachment_image に loading="lazy" をデフォルト設定
 * WordPress 5.5+ はデフォルト対応済みだが、
 * カスタムテンプレート内の the_post_thumbnail にも確実に適用
 */
add_filter('wp_get_attachment_image_attributes', function (array $attr, WP_Post $attachment): array {
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}, 10, 2);

/**
 * Emoji スクリプト無効化（パフォーマンス最適化）
 */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
});

/**
 * フォロワー数の短縮表示（例: 12.3K, 1.5M）
 */
function koi_ria_format_number(int $num): string {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return (string) $num;
}
