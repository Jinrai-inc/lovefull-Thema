<?php
/**
 * Schema.org 構造化データ出力
 *
 * Phase5で本格実装予定。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('wp_head', 'koi_ria_structured_data');

function koi_ria_structured_data(): void {
    if (is_singular('cast')) {
        koi_ria_cast_schema();
    } elseif (is_singular('show')) {
        koi_ria_show_schema();
    }
}

function koi_ria_cast_schema(): void {
    $cast_id      = get_the_ID();
    $display_name = get_field('display_name', $cast_id) ?: get_the_title();
    $ig_username  = get_field('ig_username', $cast_id) ?: '';
    $tiktok_username = get_field('tiktok_username', $cast_id) ?: '';

    $same_as = [];
    if ($ig_username) {
        $same_as[] = "https://instagram.com/{$ig_username}";
    }
    if ($tiktok_username) {
        $same_as[] = "https://tiktok.com/@{$tiktok_username}";
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
    ];

    if ($same_as) {
        $schema['sameAs'] = $same_as;
    }

    if ($show_name) {
        $schema['memberOf'] = [
            '@type' => 'TVSeries',
            'name'  => $show_name,
        ];
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

function koi_ria_show_schema(): void {
    $show_id = get_the_ID();

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'TVSeries',
        'name'     => get_the_title(),
    ];

    $genre = get_field('genre', $show_id);
    if ($genre) {
        $schema['genre'] = $genre;
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
