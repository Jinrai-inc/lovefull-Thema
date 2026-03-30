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
 * ACF が未インストールの場合のフォールバック
 * get_field() が存在しないと Fatal Error になるため、ダミー関数を定義
 */
if (! function_exists('get_field')) {
    function get_field(string $selector, $post_id = false, bool $format_value = true) {
        return null;
    }
}

/**
 * ACF Pro が必要である旨の管理画面通知
 */
add_action('admin_notices', function () {
    if (class_exists('ACF')) {
        return;
    }
    echo '<div class="notice notice-warning is-dismissible"><p>';
    echo '<strong>恋リアポータル:</strong> このテーマはカスタムフィールドの管理に <strong>Advanced Custom Fields PRO</strong> プラグインが必要です。インストール・有効化してください。';
    echo '</p></div>';
});

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
 * デフォルトカテゴリの自動登録
 */
add_action('init', function() {
    // Ensure default categories exist
    if (!term_exists('column', 'category')) {
        wp_insert_term('恋愛コラム', 'category', ['slug' => 'column', 'description' => '恋愛に関するコラム・特集記事']);
    }
    if (!term_exists('news', 'category')) {
        wp_insert_term('ニュース', 'category', ['slug' => 'news', 'description' => '最新ニュース・速報']);
    }
    if (!term_exists('guide', 'category')) {
        wp_insert_term('ガイド', 'category', ['slug' => 'guide', 'description' => 'HOW TO・ガイド記事']);
    }
}, 20);

/**
 * スタイル・スクリプト読み込み
 */
add_action('wp_enqueue_scripts', function () {
    // Google Fonts
    wp_enqueue_style(
        'koi-ria-google-fonts',
        'https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap',
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
    wp_enqueue_script('koi-ria-favorites', KOI_RIA_URI . '/assets/js/favorites.js', [], KOI_RIA_VERSION, true);

    // Ajax用のローカライズ
    wp_localize_script('koi-ria-poll-vote', 'koiRia', [
        'ajaxUrl'  => rest_url('koi-ria/v1/'),
        'nonce'    => wp_create_nonce('wp_rest'),
        'siteUrl'  => home_url('/'),
    ]);

    // VOD検索ページ用JS
    if (is_page_template('page-vod-search.php')) {
        wp_enqueue_script('koi-ria-vod-search', KOI_RIA_URI . '/assets/js/vod-search.js', [], KOI_RIA_VERSION, true);
    }

    // 診断ページ用JS
    if (is_page_template('page-shindan.php')) {
        wp_enqueue_script('koi-ria-shindan', KOI_RIA_URI . '/assets/js/shindan.js', [], KOI_RIA_VERSION, true);
    }
});

/**
 * 記事下にVODウィジェット自動挿入
 */
add_filter('the_content', function (string $content): string {
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    // タグから番組を特定
    $tags = get_the_tags();
    if (!$tags) return $content;

    $show_id = 0;
    foreach ($tags as $tag) {
        $found = get_posts([
            'post_type'      => 'show',
            'posts_per_page' => 1,
            'meta_query'     => [['key' => 'short_name', 'value' => $tag->name]],
        ]);
        if ($found) {
            $show_id = $found[0]->ID;
            break;
        }
    }

    if (!$show_id) return $content;

    ob_start();
    get_template_part('template-parts/vod-auto-insert', null, ['show_id' => $show_id]);
    $widget = ob_get_clean();

    return $content . $widget;
}, 20);

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
    'inc/svg-icons.php',
    'inc/cpt-register.php',
    'inc/acf-fields.php',
    'inc/rest-api.php',
    'inc/cron-youtube.php',
    'inc/cron-news.php',
    'inc/cron-instagram.php',
    'inc/csv-importer.php',
    'inc/structured-data.php',
    'inc/ogp-meta.php',
    'inc/seo-helpers.php',
    'inc/legal-pages.php',
    'inc/admin-menu.php',
];

foreach ($koi_ria_includes as $file) {
    $filepath = KOI_RIA_DIR . '/' . $file;
    if (file_exists($filepath)) {
        require_once $filepath;
    }
}

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
