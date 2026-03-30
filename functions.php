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
 * ACF互換レイヤー — ACF未インストール時のフォールバック
 * get_field() / update_field() をpost_metaで代替
 *
 * ACF有効化時の "Cannot redeclare" エラーを防止するため、
 * ACFプラグインの有効化リクエスト中はフォールバック関数を定義しない。
 */
$koi_ria_define_acf_fallback = true;

if (is_admin()) {
    // 単体プラグイン有効化の検出
    $activating_plugin = isset($_GET['plugin']) ? sanitize_text_field(wp_unslash($_GET['plugin'])) : '';
    $admin_action      = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';

    if ($admin_action === 'activate' && strpos($activating_plugin, 'advanced-custom-fields') !== false) {
        $koi_ria_define_acf_fallback = false;
    }

    // 一括プラグイン有効化の検出
    $bulk_action = isset($_POST['action']) ? sanitize_text_field(wp_unslash($_POST['action'])) : '';
    if ($bulk_action === 'activate-selected' && !empty($_POST['checked'])) {
        foreach ((array) $_POST['checked'] as $p) {
            if (strpos($p, 'advanced-custom-fields') !== false) {
                $koi_ria_define_acf_fallback = false;
                break;
            }
        }
    }
}

if ($koi_ria_define_acf_fallback && ! function_exists('get_field')) {
    function get_field($selector, $post_id = false, $format_value = true) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        if (is_object($post_id)) {
            $post_id = $post_id->ID ?? 0;
        }
        if (is_array($post_id)) {
            $post_id = $post_id[0] ?? 0;
        }
        $value = get_post_meta($post_id, $selector, true);
        return $value !== '' ? $value : null;
    }
}

if ($koi_ria_define_acf_fallback && ! function_exists('update_field')) {
    function update_field($selector, $value, $post_id = false) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        if (is_object($post_id)) {
            $post_id = $post_id->ID ?? 0;
        }
        if (is_array($post_id)) {
            $post_id = $post_id[0] ?? 0;
        }
        return update_post_meta($post_id, $selector, $value);
    }
}

unset($koi_ria_define_acf_fallback);

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
 * 既存投稿を恋愛コラムカテゴリに自動割り当て（一度だけ実行）
 * 「未分類」のみに属する投稿をcolumnカテゴリに移動する
 */
add_action('admin_init', function () {
    if (get_option('koi_ria_migrated_columns')) {
        return;
    }

    $column_term = get_category_by_slug('column');
    if (!$column_term) {
        return;
    }

    $default_cat_id = (int) get_option('default_category', 1);

    // 未分類カテゴリのみに属する投稿を取得
    $posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'category'       => $default_cat_id,
        'post_status'    => 'any',
    ]);

    foreach ($posts as $post) {
        $cats = wp_get_post_categories($post->ID);
        // 未分類のみに属する場合、恋愛コラムに変更
        if (count($cats) === 1 && (int) $cats[0] === $default_cat_id) {
            wp_set_post_categories($post->ID, [$column_term->term_id]);
        }
    }

    update_option('koi_ria_migrated_columns', true);
});

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
    'inc/metaboxes.php',
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
