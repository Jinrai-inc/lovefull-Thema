<?php
/**
 * OGP / Twitter Card / メタタグ出力（SEO/GEO最適化版）
 *
 * 出力:
 * - meta description（全ページ）
 * - canonical URL（全ページ）
 * - robots メタ（ページ種別に応じた制御）
 * - OGP (Open Graph Protocol) タグ
 * - Twitter Card タグ
 * - article: タグ（記事ページ）
 * - profile: タグ（出演者ページ）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('wp_head', 'koi_ria_meta_description', 1);
add_action('wp_head', 'koi_ria_canonical_url', 1);
add_action('wp_head', 'koi_ria_robots_meta', 1);
add_action('wp_head', 'koi_ria_ogp_meta', 2);

/**
 * meta description 出力
 */
function koi_ria_meta_description(): void {
    $description = koi_ria_get_page_description();
    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
}

/**
 * canonical URL 出力
 */
function koi_ria_canonical_url(): void {
    $url = koi_ria_get_canonical_url();
    if ($url) {
        echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    }
}

/**
 * robots メタタグ
 */
function koi_ria_robots_meta(): void {
    $robots = [];

    // 検索結果ページ: noindex
    if (is_search()) {
        $robots[] = 'noindex';
        $robots[] = 'follow';
    }

    // ページネーション 2ページ以降: noindex
    if (is_paged()) {
        $robots[] = 'noindex';
        $robots[] = 'follow';
    }

    // 404: noindex
    if (is_404()) {
        $robots[] = 'noindex';
        $robots[] = 'nofollow';
    }

    // タグアーカイブ: noindex (重複コンテンツ防止)
    if (is_tag()) {
        $robots[] = 'noindex';
        $robots[] = 'follow';
    }

    if ($robots) {
        echo '<meta name="robots" content="' . esc_attr(implode(', ', array_unique($robots))) . '">' . "\n";
    } else {
        // デフォルト: max-snippet と max-image-preview でリッチリザルト最大化
        echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
    }
}

/**
 * OGP / Twitter Card メタタグ
 */
function koi_ria_ogp_meta(): void {
    $site_name   = get_bloginfo('name');
    $title       = '';
    $description = '';
    $url         = '';
    $image       = '';
    $image_alt   = '';
    $type        = 'article';

    if (is_front_page()) {
        $type        = 'website';
        $title       = $site_name . ' - ' . get_bloginfo('description');
        $description = koi_ria_get_page_description();
        $url         = home_url('/');
        $image       = koi_ria_ogp_default_image();
        $image_alt   = $site_name;
    } elseif (is_singular()) {
        $post_id     = get_the_ID();
        $title       = get_the_title() . ' | ' . $site_name;
        $description = koi_ria_get_page_description();
        $url         = get_permalink();

        if (has_post_thumbnail($post_id)) {
            $thumb_id = get_post_thumbnail_id($post_id);
            $image    = get_the_post_thumbnail_url($post_id, 'large');
            $image_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: get_the_title();
        }

        // 出演者: プロフィール画像をOGP画像に
        if (is_singular('cast')) {
            $display_name = get_field('display_name', $post_id);
            if ($display_name) {
                $title = $display_name . ' | ' . $site_name;
            }
            if (!$image) {
                $profile_image = get_field('profile_image', $post_id);
                if ($profile_image && isset($profile_image['url'])) {
                    $image = $profile_image['url'];
                    $image_alt = $display_name ?: get_the_title();
                } else {
                    $ig_cache = get_post_meta($post_id, 'ig_profile_cache', true);
                    if ($ig_cache) {
                        $image = $ig_cache;
                        $image_alt = $display_name ?: get_the_title();
                    }
                }
            }
        }
    } elseif (is_post_type_archive()) {
        $title       = post_type_archive_title('', false) . ' | ' . $site_name;
        $description = koi_ria_get_page_description();
        $url         = koi_ria_get_canonical_url();
    } elseif (is_category() || is_tag()) {
        $title       = single_term_title('', false) . ' | ' . $site_name;
        $description = koi_ria_get_page_description();
        $url         = koi_ria_get_canonical_url();
    } elseif (is_search()) {
        $title       = '検索結果: ' . get_search_query() . ' | ' . $site_name;
        $description = get_search_query() . ' の検索結果';
        $url         = get_search_link();
    } else {
        $title       = wp_get_document_title();
        $description = koi_ria_get_page_description();
        $url         = koi_ria_get_canonical_url();
    }

    // フォールバック
    if (!$description) {
        $description = get_bloginfo('description');
    }
    if (!$image) {
        $image = koi_ria_ogp_default_image();
        $image_alt = $site_name;
    }

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
        if ($image_alt) {
            echo '<meta property="og:image:alt" content="' . esc_attr($image_alt) . '">' . "\n";
        }
    }

    // article: タグ（記事・番組・出演者）
    if (is_singular('post')) {
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
        echo '<meta property="article:author" content="' . esc_url(home_url('/')) . '">' . "\n";
        $tags = get_the_tags();
        if ($tags) {
            foreach ($tags as $tag) {
                echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
            }
        }
        $cats = get_the_category();
        if ($cats) {
            echo '<meta property="article:section" content="' . esc_attr($cats[0]->name) . '">' . "\n";
        }
    }

    // profile: タグ（出演者）
    if (is_singular('cast')) {
        $display_name = get_field('display_name') ?: get_the_title();
        echo '<meta property="og:type" content="profile">' . "\n";
        echo '<meta property="profile:first_name" content="' . esc_attr($display_name) . '">' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
        if ($image_alt) {
            echo '<meta name="twitter:image:alt" content="' . esc_attr($image_alt) . '">' . "\n";
        }
    }
}

/**
 * ページごとの description 取得
 */
function koi_ria_get_page_description(): string {
    if (is_front_page()) {
        return get_bloginfo('description') ?: get_bloginfo('name') . ' - 恋愛リアリティ番組の出演者・番組情報・最新ニュースをまとめたポータルサイト';
    }

    if (is_singular()) {
        $post_id = get_the_ID();

        if (is_singular('show')) {
            $platform = get_field('platform', $post_id) ?: '';
            $genre    = get_field('genre', $post_id) ?: '';
            $desc     = get_the_title() . 'の出演者・あらすじ・最新情報まとめ。';
            if ($platform) $desc .= $platform . 'で配信中。';
            if ($genre) $desc .= 'ジャンル: ' . $genre . '。';
            $excerpt = wp_strip_all_tags(get_the_excerpt());
            if ($excerpt) {
                $desc .= mb_substr($excerpt, 0, 80);
            }
            return mb_substr($desc, 0, 160);
        }

        if (is_singular('cast')) {
            $display_name = get_field('display_name', $post_id) ?: get_the_title();
            $show_id      = get_field('show', $post_id);
            $show_name    = '';
            if ($show_id) {
                $sp = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
                $show_name = $sp ? $sp->post_title : '';
            }
            $age       = get_field('age', $post_id) ?: '';
            $from_area = get_field('from_area', $post_id) ?: '';

            $desc = $display_name . 'のプロフィール・SNS・出演情報。';
            if ($show_name) $desc .= '「' . $show_name . '」出演。';
            if ($age) $desc .= $age . '歳。';
            if ($from_area) $desc .= '出身: ' . $from_area . '。';
            $desc .= 'Instagram・TikTokのフォロワー数、最新情報をチェック。';
            return mb_substr($desc, 0, 160);
        }

        // 通常記事
        $excerpt = wp_strip_all_tags(get_the_excerpt());
        return $excerpt ? mb_substr($excerpt, 0, 160) : '';
    }

    if (is_post_type_archive('show')) {
        return '恋愛リアリティ番組の一覧。今日好き、あいのり、バチェラー、テラスハウスなど人気番組の出演者・最新情報をまとめています。';
    }

    if (is_post_type_archive('cast')) {
        return '恋愛リアリティ番組の出演者データベース。プロフィール、SNSアカウント、フォロワー数、出演番組情報を網羅。';
    }

    if (is_category()) {
        $desc = category_description();
        return $desc ? mb_substr(wp_strip_all_tags($desc), 0, 160) : single_cat_title('', false) . 'の記事一覧 | ' . get_bloginfo('name');
    }

    return '';
}

/**
 * canonical URL 取得
 */
function koi_ria_get_canonical_url(): string {
    if (is_singular()) {
        return get_permalink();
    }
    if (is_front_page()) {
        return home_url('/');
    }
    if (is_post_type_archive()) {
        return get_post_type_archive_link(get_query_var('post_type'));
    }
    if (is_category()) {
        return get_category_link(get_queried_object_id());
    }
    if (is_tag()) {
        return get_tag_link(get_queried_object_id());
    }
    global $wp;
    return home_url(add_query_arg([], $wp->request));
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
    return KOI_RIA_URI . '/assets/images/ogp-default.png';
}
