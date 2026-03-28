<?php
/**
 * Schema.org 構造化データ出力
 *
 * - WebSite (トップページ: サイト名 + SearchAction)
 * - TVSeries (番組詳細)
 * - Person (出演者詳細)
 * - Article / NewsArticle (記事)
 * - BreadcrumbList (全ページ)
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('wp_head', 'koi_ria_structured_data');
add_action('wp_head', 'koi_ria_breadcrumb_schema');

/**
 * ページ種別に応じた構造化データ出力
 */
function koi_ria_structured_data(): void {
    if (is_front_page()) {
        koi_ria_website_schema();
    }

    if (is_singular('cast')) {
        koi_ria_cast_schema();
    } elseif (is_singular('show')) {
        koi_ria_show_schema();
    } elseif (is_singular('post')) {
        koi_ria_article_schema();
    }
}

/**
 * WebSite スキーマ（トップページ）
 */
function koi_ria_website_schema(): void {
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => get_bloginfo('name'),
        'url'      => home_url('/'),
        'description' => get_bloginfo('description'),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => home_url('/?s={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ];

    koi_ria_output_schema($schema);
}

/**
 * TVSeries スキーマ（番組詳細）
 */
function koi_ria_show_schema(): void {
    $show_id  = get_the_ID();
    $platform = get_field('platform', $show_id) ?: '';
    $genre    = get_field('genre', $show_id) ?: '';
    $status   = get_field('show_status', $show_id) ?: '';

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'TVSeries',
        'name'        => get_the_title(),
        'url'         => get_permalink(),
        'description' => wp_trim_words(get_the_excerpt(), 50, '…'),
    ];

    if ($genre) {
        $schema['genre'] = $genre;
    }

    if ($platform) {
        $schema['productionCompany'] = [
            '@type' => 'Organization',
            'name'  => $platform,
        ];
    }

    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url($show_id, 'large');
    }

    // シーズン情報
    $seasons = get_posts([
        'post_type'      => 'season',
        'posts_per_page' => -1,
        'meta_query'     => [['key' => 'show', 'value' => $show_id]],
        'meta_key'       => 'order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);

    if ($seasons) {
        $schema['containsSeason'] = [];
        foreach ($seasons as $season) {
            $s_name = get_field('season_name', $season->ID) ?: $season->post_title;
            $s_year = get_field('year', $season->ID) ?: '';
            $season_data = [
                '@type' => 'TVSeason',
                'name'  => $s_name,
            ];
            if ($s_year) {
                $season_data['datePublished'] = $s_year;
            }
            $schema['containsSeason'][] = $season_data;
        }
        $schema['numberOfSeasons'] = count($seasons);
    }

    koi_ria_output_schema($schema);
}

/**
 * Person スキーマ（出演者詳細）
 */
function koi_ria_cast_schema(): void {
    $cast_id         = get_the_ID();
    $display_name    = get_field('display_name', $cast_id) ?: get_the_title();
    $ig_username     = get_field('ig_username', $cast_id) ?: '';
    $tiktok_username = get_field('tiktok_username', $cast_id) ?: '';
    $x_username      = get_field('x_username', $cast_id) ?: '';
    $from_area       = get_field('from_area', $cast_id) ?: '';
    $profile_image   = get_field('profile_image', $cast_id);
    $ig_cache        = get_post_meta($cast_id, 'ig_profile_cache', true);

    $same_as = [];
    if ($ig_username) {
        $same_as[] = "https://instagram.com/{$ig_username}";
    }
    if ($tiktok_username) {
        $same_as[] = "https://tiktok.com/@{$tiktok_username}";
    }
    if ($x_username) {
        $same_as[] = "https://x.com/{$x_username}";
    }

    $show_id = get_field('show', $cast_id);
    $show_name = '';
    if ($show_id) {
        $show_post = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
        $show_name = $show_post ? $show_post->post_title : '';
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Person',
        'name'     => $display_name,
        'url'      => get_permalink(),
    ];

    if ($same_as) {
        $schema['sameAs'] = $same_as;
    }

    if ($from_area) {
        $schema['homeLocation'] = [
            '@type' => 'Place',
            'name'  => $from_area,
        ];
    }

    // 画像
    $image_url = '';
    if ($profile_image && isset($profile_image['url'])) {
        $image_url = $profile_image['url'];
    } elseif ($ig_cache) {
        $image_url = $ig_cache;
    }
    if ($image_url) {
        $schema['image'] = $image_url;
    }

    if ($show_name) {
        $schema['memberOf'] = [
            '@type' => 'TVSeries',
            'name'  => $show_name,
        ];
    }

    koi_ria_output_schema($schema);
}

/**
 * Article スキーマ（記事）
 */
function koi_ria_article_schema(): void {
    $post_id = get_the_ID();

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'NewsArticle',
        'headline'      => get_the_title(),
        'url'           => get_permalink(),
        'datePublished' => get_the_date('c'),
        'dateModified'  => get_the_modified_date('c'),
        'author'        => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
        ],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
        ],
        'description'   => wp_trim_words(get_the_excerpt(), 50, '…'),
    ];

    if (has_post_thumbnail()) {
        $thumb_id  = get_post_thumbnail_id();
        $thumb_url = get_the_post_thumbnail_url($post_id, 'large');
        $meta      = wp_get_attachment_metadata($thumb_id);
        $schema['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $thumb_url,
            'width'  => $meta['width'] ?? 1200,
            'height' => $meta['height'] ?? 630,
        ];
    }

    // 記事本文の文字数
    $content = get_the_content();
    if ($content) {
        $schema['wordCount'] = mb_strlen(strip_tags($content));
    }

    koi_ria_output_schema($schema);
}

/**
 * パンくずリスト スキーマ
 */
function koi_ria_breadcrumb_schema(): void {
    if (is_front_page()) {
        return;
    }

    $items = [];
    $position = 1;

    // ホーム
    $items[] = [
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => 'ホーム',
        'item'     => home_url('/'),
    ];

    if (is_singular('show')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '番組一覧',
            'item'     => get_post_type_archive_link('show'),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
        ];
    } elseif (is_singular('cast')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '出演者データベース',
            'item'     => get_post_type_archive_link('cast'),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_field('display_name') ?: get_the_title(),
        ];
    } elseif (is_singular('post')) {
        $cats = get_the_category();
        if ($cats) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $cats[0]->name,
                'item'     => get_category_link($cats[0]),
            ];
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
        ];
    } elseif (is_post_type_archive()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => post_type_archive_title('', false),
        ];
    } elseif (is_category() || is_tag()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => single_term_title('', false),
        ];
    } elseif (is_search()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '検索結果: ' . get_search_query(),
        ];
    }

    if (count($items) < 2) {
        return;
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    koi_ria_output_schema($schema);
}

/**
 * JSON-LD出力ヘルパー
 */
function koi_ria_output_schema(array $schema): void {
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
}
