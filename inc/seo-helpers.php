<?php
/**
 * SEO/GEO ヘルパー関数
 *
 * - XML Sitemapのカスタムポストタイプ最適化
 * - コンテンツ鮮度シグナル
 * - 内部リンク最適化
 * - RSS フィード最適化
 * - パフォーマンス・クロール最適化
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/* =========================================================================
 * XML Sitemap 最適化（WordPress 5.5+ ネイティブサイトマップ）
 * ========================================================================= */

/**
 * カスタム投稿タイプをサイトマップに追加
 */
add_filter('wp_sitemaps_post_types', function (array $post_types): array {
    // youtube_video, relation, season はサイトマップから除外（単体ページ不要）
    unset($post_types['youtube_video']);
    unset($post_types['relation']);
    unset($post_types['season']);
    return $post_types;
});

/**
 * サイトマップの投稿エントリにlastmod追加
 */
add_filter('wp_sitemaps_posts_entry', function (array $entry, WP_Post $post): array {
    $entry['lastmod'] = get_the_modified_date('c', $post);
    return $entry;
}, 10, 2);

/**
 * サイトマップの最大URL数を調整（パフォーマンス）
 */
add_filter('wp_sitemaps_max_urls', function (): int {
    return 1000;
});

/**
 * サイトマップにカスタムプロバイダー不要のタクソノミーを除外
 */
add_filter('wp_sitemaps_taxonomies', function (array $taxonomies): array {
    // post_format は不要
    unset($taxonomies['post_format']);
    return $taxonomies;
});

/* =========================================================================
 * RSS フィード最適化
 * ========================================================================= */

/**
 * RSSフィードにアイキャッチ画像を追加
 */
add_action('rss2_item', function (): void {
    if (has_post_thumbnail()) {
        $url = get_the_post_thumbnail_url(get_the_ID(), 'large');
        if ($url) {
            echo '<enclosure url="' . esc_url($url) . '" type="image/jpeg" />' . "\n";
        }
    }
});

/**
 * RSSフィードにカスタム投稿タイプを含める
 */
add_filter('request', function (array $query_vars): array {
    if (isset($query_vars['feed']) && !isset($query_vars['post_type'])) {
        $query_vars['post_type'] = ['post', 'show', 'cast'];
    }
    return $query_vars;
});

/* =========================================================================
 * コンテンツ鮮度シグナル
 * ========================================================================= */

/**
 * Last-Modified ヘッダー出力（クロール効率化）
 */
add_action('template_redirect', function (): void {
    if (is_singular()) {
        $modified = get_the_modified_date('D, d M Y H:i:s', get_the_ID());
        if ($modified) {
            header('Last-Modified: ' . $modified . ' GMT');
        }
    }
});

/* =========================================================================
 * 内部リンク・URL最適化
 * ========================================================================= */

/**
 * 自動的にrel="noopener nofollow"を外部リンクに付与
 * （投稿コンテンツ内のリンク）
 */
add_filter('the_content', function (string $content): string {
    if (empty($content)) return $content;

    $home_host = wp_parse_url(home_url(), PHP_URL_HOST);

    return preg_replace_callback(
        '/<a\s([^>]*href=["\']https?:\/\/[^"\']+["\'][^>]*)>/i',
        function ($matches) use ($home_host) {
            $tag = $matches[0];
            $href = '';

            if (preg_match('/href=["\']([^"\']+)["\']/', $tag, $href_match)) {
                $href = $href_match[1];
            }

            $link_host = wp_parse_url($href, PHP_URL_HOST);

            // 外部リンクの場合
            if ($link_host && $link_host !== $home_host) {
                // rel属性を追加/更新
                if (strpos($tag, 'rel=') === false) {
                    $tag = str_replace('>', ' rel="noopener nofollow" target="_blank">', $tag);
                }
            }

            return $tag;
        },
        $content
    );
});

/**
 * ページネーションにrel="prev" / rel="next"を追加
 */
add_action('wp_head', function (): void {
    if (is_singular() || is_front_page()) return;

    global $wp_query;
    $paged     = max(1, get_query_var('paged'));
    $max_pages = $wp_query->max_num_pages;

    if ($max_pages <= 1) return;

    if ($paged > 1) {
        $prev_url = get_pagenum_link($paged - 1);
        echo '<link rel="prev" href="' . esc_url($prev_url) . '">' . "\n";
    }

    if ($paged < $max_pages) {
        $next_url = get_pagenum_link($paged + 1);
        echo '<link rel="next" href="' . esc_url($next_url) . '">' . "\n";
    }
}, 3);

/* =========================================================================
 * WordPress デフォルトのクリーンアップ（SEOノイズ除去）
 * ========================================================================= */

/**
 * 不要なhead出力を削除
 */
add_action('init', function (): void {
    // Windows Live Writer マニフェスト
    remove_action('wp_head', 'wlwmanifest_link');
    // RSD (Really Simple Discovery)
    remove_action('wp_head', 'rsd_link');
    // WordPress バージョン（セキュリティ）
    remove_action('wp_head', 'wp_generator');
    // ショートリンク
    remove_action('wp_head', 'wp_shortlink_wp_head');
    // REST API リンク（headに不要）
    remove_action('wp_head', 'rest_output_link_wp_head');
    // oEmbed ディスカバリー
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
}, 99);

/**
 * X-Pingback ヘッダー削除
 */
add_filter('wp_headers', function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});

/* =========================================================================
 * 画像SEO最適化
 * ========================================================================= */

/**
 * 画像にwidth/height属性を確実に付与（CLS対策）
 */
add_filter('wp_get_attachment_image_attributes', function (array $attr, WP_Post $attachment, $size): array {
    if (empty($attr['width']) || empty($attr['height'])) {
        $meta = wp_get_attachment_metadata($attachment->ID);
        if ($meta) {
            if (is_string($size) && isset($meta['sizes'][$size])) {
                $attr['width']  = $meta['sizes'][$size]['width'];
                $attr['height'] = $meta['sizes'][$size]['height'];
            } elseif (isset($meta['width'], $meta['height'])) {
                $attr['width']  = $meta['width'];
                $attr['height'] = $meta['height'];
            }
        }
    }
    return $attr;
}, 10, 3);

/**
 * 画像にdecoding="async"属性を追加
 */
add_filter('wp_get_attachment_image_attributes', function (array $attr): array {
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    return $attr;
});

/* =========================================================================
 * タイトルタグ最適化
 * ========================================================================= */

/**
 * タイトルセパレータ変更
 */
add_filter('document_title_separator', function (): string {
    return '|';
});

/**
 * カスタム投稿タイプのタイトル最適化
 */
add_filter('document_title_parts', function (array $title): array {
    // フロントページ: SEO強化タイトル
    if (is_front_page()) {
        $custom_title = get_theme_mod('koi_ria_seo_title', '');
        if ($custom_title) {
            $title['title'] = $custom_title;
            // カスタムタイトルにセパレータ+サイト名を付けない
            unset($title['tagline']);
        } else {
            $title['title'] = get_bloginfo('name');
            $title['tagline'] = '恋愛リアリティ番組の出演者・ニュース・番組情報まとめ';
        }
    }

    if (is_singular('cast')) {
        $display_name = get_field('display_name');
        if ($display_name) {
            $title['title'] = $display_name . ' プロフィール・SNS情報';
        }
    }

    if (is_singular('show')) {
        $title['title'] = get_the_title() . ' 出演者・最新情報まとめ';
    }

    if (is_post_type_archive('show')) {
        $title['title'] = '恋愛リアリティ番組一覧';
    }

    if (is_post_type_archive('cast')) {
        $title['title'] = '出演者データベース';
    }

    if (is_post_type_archive('poll')) {
        $title['title'] = 'みんなの予想・投票';
    }

    return $title;
});
