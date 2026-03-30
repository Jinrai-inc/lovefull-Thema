<?php
/**
 * CSV一括インポート機能（UPSERT対応）
 *
 * 番組・シーズン・出演者・相関図のCSVインポートに対応。
 * 管理画面「データ管理 → CSVインポート」から利用可能。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * 番組 UPSERT
 */
function koi_ria_upsert_show(array $row, string $mode = 'upsert'): array {
    $slug = sanitize_title($row['slug'] ?? '');
    if (!$slug) {
        return ['status' => 'error', 'message' => 'slugが空です'];
    }

    $existing = get_page_by_path($slug, OBJECT, 'show');

    if ($existing && $mode === 'add') {
        return ['status' => 'skipped', 'message' => "{$row['title']} は既に存在"];
    }

    if ($existing) {
        $post_id = $existing->ID;
        wp_update_post(['ID' => $post_id, 'post_title' => $row['title'] ?? $existing->post_title]);
    } else {
        $post_id = wp_insert_post([
            'post_type'   => 'show',
            'post_title'  => $row['title'] ?? '',
            'post_status' => 'publish',
            'post_name'   => $slug,
        ]);
        if (is_wp_error($post_id)) {
            return ['status' => 'error', 'message' => $post_id->get_error_message()];
        }
    }

    $fields = ['short_name', 'platform', 'platform_color', 'emoji', 'youtube_channel_id', 'affiliate_url', 'show_status', 'genre', 'target', 'priority'];
    foreach ($fields as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            update_field($field, $row[$field], $post_id);
        }
    }

    return ['status' => $existing ? 'updated' : 'created'];
}

/**
 * シーズン UPSERT
 */
function koi_ria_upsert_season(array $row, string $mode = 'upsert'): array {
    $show = get_page_by_path($row['show_slug'] ?? '', OBJECT, 'show');
    if (!$show) {
        return ['status' => 'error', 'message' => "番組 {$row['show_slug']} が見つかりません"];
    }

    $existing = get_posts([
        'post_type'      => 'season',
        'meta_query'     => [
            ['key' => 'show', 'value' => $show->ID],
            ['key' => 'season_name', 'value' => $row['season_name'] ?? ''],
        ],
        'posts_per_page' => 1,
    ]);

    if ($existing && $mode === 'add') {
        return ['status' => 'skipped', 'message' => "{$row['season_name']} は既に存在"];
    }

    if ($existing) {
        $post_id = $existing[0]->ID;
    } else {
        $post_id = wp_insert_post([
            'post_type'   => 'season',
            'post_title'  => ($row['season_name'] ?? '') . ' - ' . $show->post_title,
            'post_status' => 'publish',
        ]);
        if (is_wp_error($post_id)) {
            return ['status' => 'error', 'message' => $post_id->get_error_message()];
        }
        update_field('show', $show->ID, $post_id);
    }

    $fields = ['season_name', 'year', 'badge', 'order'];
    foreach ($fields as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            update_field($field, $row[$field], $post_id);
        }
    }

    return ['status' => $existing ? 'updated' : 'created'];
}

/**
 * 出演者 UPSERT
 */
function koi_ria_upsert_cast(array $row, string $mode = 'upsert'): array {
    $show = get_page_by_path($row['show_slug'] ?? '', OBJECT, 'show');
    if (!$show) {
        return ['status' => 'error', 'message' => "番組 {$row['show_slug']} が見つかりません"];
    }

    $seasons = get_posts([
        'post_type'      => 'season',
        'meta_query'     => [
            ['key' => 'show', 'value' => $show->ID],
            ['key' => 'season_name', 'value' => $row['season_name'] ?? ''],
        ],
        'posts_per_page' => 1,
    ]);
    $season = $seasons[0] ?? null;
    if (!$season) {
        return ['status' => 'error', 'message' => "シーズン {$row['season_name']} が見つかりません"];
    }

    $existing = get_posts([
        'post_type'      => 'cast',
        'meta_query'     => [
            ['key' => 'show', 'value' => $show->ID],
            ['key' => 'season', 'value' => $season->ID],
            ['key' => 'display_name', 'value' => $row['name'] ?? ''],
        ],
        'posts_per_page' => 1,
    ]);

    if ($existing && $mode === 'add') {
        return ['status' => 'skipped', 'message' => "{$row['name']} は既に存在"];
    }

    if ($existing) {
        $post_id = $existing[0]->ID;
    } else {
        $post_id = wp_insert_post([
            'post_type'   => 'cast',
            'post_title'  => $row['name'] ?? '',
            'post_status' => 'publish',
            'post_name'   => sanitize_title($row['ig_username'] ?? $row['name'] ?? ''),
        ]);
        if (is_wp_error($post_id)) {
            return ['status' => 'error', 'message' => $post_id->get_error_message()];
        }
        update_field('display_name', $row['name'] ?? '', $post_id);
        update_field('show', $show->ID, $post_id);
        update_field('season', $season->ID, $post_id);
    }

    $fields = ['ig_username', 'tiktok_username', 'x_username', 'role', 'gender', 'age', 'from_area'];
    foreach ($fields as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            update_field($field, $row[$field], $post_id);
        }
    }

    return ['status' => $existing ? 'updated' : 'created'];
}

/**
 * 相関図 UPSERT
 */
function koi_ria_upsert_relation(array $row, string $mode = 'upsert'): array {
    $show = get_page_by_path($row['show_slug'] ?? '', OBJECT, 'show');
    if (!$show) {
        return ['status' => 'error', 'message' => "番組 {$row['show_slug']} が見つかりません"];
    }

    $seasons = get_posts([
        'post_type'      => 'season',
        'meta_query'     => [
            ['key' => 'show', 'value' => $show->ID],
            ['key' => 'season_name', 'value' => $row['season_name'] ?? ''],
        ],
        'posts_per_page' => 1,
    ]);
    $season = $seasons[0] ?? null;
    if (!$season) {
        return ['status' => 'error', 'message' => "シーズン {$row['season_name']} が見つかりません"];
    }

    // from_cast を検索
    $from_casts = get_posts([
        'post_type'      => 'cast',
        'meta_query'     => [
            ['key' => 'season', 'value' => $season->ID],
            ['key' => 'display_name', 'value' => $row['from_name'] ?? ''],
        ],
        'posts_per_page' => 1,
    ]);
    $from_cast = $from_casts[0] ?? null;
    if (!$from_cast) {
        return ['status' => 'error', 'message' => "出演者 {$row['from_name']} が見つかりません"];
    }

    // to_cast を検索
    $to_casts = get_posts([
        'post_type'      => 'cast',
        'meta_query'     => [
            ['key' => 'season', 'value' => $season->ID],
            ['key' => 'display_name', 'value' => $row['to_name'] ?? ''],
        ],
        'posts_per_page' => 1,
    ]);
    $to_cast = $to_casts[0] ?? null;
    if (!$to_cast) {
        return ['status' => 'error', 'message' => "出演者 {$row['to_name']} が見つかりません"];
    }

    $existing = get_posts([
        'post_type'      => 'relation',
        'meta_query'     => [
            ['key' => 'season', 'value' => $season->ID],
            ['key' => 'from_cast', 'value' => $from_cast->ID],
            ['key' => 'to_cast', 'value' => $to_cast->ID],
        ],
        'posts_per_page' => 1,
    ]);

    if ($existing && $mode === 'add') {
        return ['status' => 'skipped', 'message' => '相関図データは既に存在'];
    }

    if ($existing) {
        $post_id = $existing[0]->ID;
    } else {
        $post_id = wp_insert_post([
            'post_type'   => 'relation',
            'post_title'  => ($row['from_name'] ?? '') . ' → ' . ($row['to_name'] ?? ''),
            'post_status' => 'publish',
        ]);
        if (is_wp_error($post_id)) {
            return ['status' => 'error', 'message' => $post_id->get_error_message()];
        }
        update_field('season', $season->ID, $post_id);
        update_field('from_cast', $from_cast->ID, $post_id);
        update_field('to_cast', $to_cast->ID, $post_id);
    }

    if (isset($row['type']) && $row['type'] !== '') {
        update_field('relation_type', $row['type'], $post_id);
    }
    if (isset($row['label']) && $row['label'] !== '') {
        update_field('relation_label', $row['label'], $post_id);
    }

    return ['status' => $existing ? 'updated' : 'created'];
}
