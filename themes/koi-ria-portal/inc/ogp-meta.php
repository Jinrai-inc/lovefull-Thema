<?php
/**
 * OGP / Twitter Card メタタグ出力
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('wp_head', 'koi_ria_ogp_meta');

/**
 * OGP / Twitter Card メタタグ
 */
function koi_ria_ogp_meta(): void {
    $site_name   = get_bloginfo('name');
    $title       = '';
    $description = '';
    $url         = '';
    $image       = '';
    $type        = 'article';

    if (is_front_page()) {
        $type        = 'website';
        $title       = $site_name;
        $description = get_bloginfo('description');
        $url         = home_url('/');
        $image       = koi_ria_ogp_default_image();
    } elseif (is_singular()) {
        $post_id     = get_the_ID();
        $title       = get_the_title() . ' | ' . $site_name;
        $description = wp_trim_words(get_the_excerpt(), 50, '...');
        $url         = get_permalink();

        if (has_post_thumbnail($post_id)) {
            $image = get_the_post_thumbnail_url($post_id, 'large');
        }

        // 出演者: プロフィール画像をOGP画像に
        if (is_singular('cast') && !$image) {
            $profile_image = get_field('profile_image', $post_id);
            if ($profile_image && isset($profile_image['url'])) {
                $image = $profile_image['url'];
            } else {
                $ig_cache = get_post_meta($post_id, 'ig_profile_cache', true);
                if ($ig_cache) {
                    $image = $ig_cache;
                }
            }
        }

        // 出演者: display_nameをタイトルに使用
        if (is_singular('cast')) {
            $display_name = get_field('display_name', $post_id);
            if ($display_name) {
                $title = $display_name . ' | ' . $site_name;
            }
        }
    } elseif (is_post_type_archive()) {
        $title       = post_type_archive_title('', false) . ' | ' . $site_name;
        $description = $site_name . 'の' . post_type_archive_title('', false) . '一覧';
        $url         = koi_ria_current_url();
    } elseif (is_category() || is_tag()) {
        $title       = single_term_title('', false) . ' | ' . $site_name;
        $description = $site_name . ' - ' . single_term_title('', false) . 'の記事一覧';
        $url         = koi_ria_current_url();
    } elseif (is_search()) {
        $title       = '検索結果: ' . get_search_query() . ' | ' . $site_name;
        $description = get_search_query() . ' の検索結果';
        $url         = get_search_link();
    } else {
        $title       = wp_get_document_title();
        $description = get_bloginfo('description');
        $url         = koi_ria_current_url();
    }

    // フォールバック
    if (!$description) {
        $description = get_bloginfo('description');
    }
    if (!$image) {
        $image = koi_ria_ogp_default_image();
    }

    // description を160文字に制限
    $description = mb_substr(wp_strip_all_tags($description), 0, 160);

    // OGP
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:locale" content="ja_JP">' . "\n";
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }
}

/**
 * デフォルトOGP画像
 */
function koi_ria_ogp_default_image(): string {
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo_url) {
            return $logo_url;
        }
    }

    // テーマ内のデフォルト画像
    $default = KOI_RIA_URI . '/assets/images/ogp-default.png';
    return $default;
}

/**
 * 現在のURLを取得
 */
function koi_ria_current_url(): string {
    global $wp;
    return home_url(add_query_arg([], $wp->request));
}
