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

    $description = isset($row['description']) ? sanitize_text_field($row['description']) : '';

    if ($existing) {
        $post_id = $existing->ID;
        $update_data = ['ID' => $post_id, 'post_title' => $row['title'] ?? $existing->post_title];
        if ($description !== '') {
            $update_data['post_content'] = $description;
        }
        wp_update_post($update_data);
    } else {
        $post_id = wp_insert_post([
            'post_type'    => 'show',
            'post_title'   => $row['title'] ?? '',
            'post_content' => $description,
            'post_status'  => 'publish',
            'post_name'    => $slug,
        ]);
        if (is_wp_error($post_id)) {
            return ['status' => 'error', 'message' => $post_id->get_error_message()];
        }
    }

    // Also save description as post meta for ACF compatibility
    if ($description !== '') {
        update_field('description', $description, $post_id);
    }

    $fields = ['short_name', 'platform', 'platform_color', 'emoji', 'youtube_channel_id', 'affiliate_url', 'show_status', 'genre', 'target', 'priority', 'broadcast_day', 'broadcast_time'];
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

    // Handle start_date (YYYY-MM-DD format, empty OK)
    if (isset($row['start_date'])) {
        $start_date = trim($row['start_date']);
        if ($start_date === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            update_field('start_date', $start_date, $post_id);
        }
    }

    // Handle end_date (YYYY-MM-DD format, empty OK)
    if (isset($row['end_date'])) {
        $end_date = trim($row['end_date']);
        if ($end_date === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            update_field('end_date', $end_date, $post_id);
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

    // Use display_name if provided, otherwise fall back to name
    $display = $row['display_name'] ?? $row['name'] ?? '';

    $existing = get_posts([
        'post_type'      => 'cast',
        'meta_query'     => [
            ['key' => 'show', 'value' => $show->ID],
            ['key' => 'season', 'value' => $season->ID],
            ['key' => 'display_name', 'value' => $display],
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
        update_field('display_name', $display, $post_id);
        update_field('show', $show->ID, $post_id);
        update_field('season', $season->ID, $post_id);
    }

    $fields = ['ig_username', 'tiktok_username', 'x_username', 'youtube_url', 'role', 'gender', 'age', 'from_area', 'cast_status'];
    foreach ($fields as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            update_field($field, $row[$field], $post_id);
        }
    }

    // Handle height (numeric, empty OK)
    if (isset($row['height']) && $row['height'] !== '') {
        $height = intval($row['height']);
        if ($height > 0) {
            update_field('height', $height, $post_id);
        }
    }

    // Handle is_continuation (TRUE/FALSE → '1'/'0')
    if (isset($row['is_continuation'])) {
        $val = strtoupper(trim($row['is_continuation']));
        $is_continuation = ($val === 'TRUE' || $val === '1') ? '1' : '0';
        update_field('is_continuation', $is_continuation, $post_id);
    }

    // Handle note (text, empty OK)
    if (isset($row['note']) && $row['note'] !== '') {
        update_field('note', sanitize_text_field($row['note']), $post_id);
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

    // Handle as_of_episode (numeric, empty OK)
    if (isset($row['as_of_episode']) && $row['as_of_episode'] !== '') {
        $episode = intval($row['as_of_episode']);
        if ($episode > 0) {
            update_field('as_of_episode', $episode, $post_id);
        }
    }

    return ['status' => $existing ? 'updated' : 'created'];
}

/**
 * YouTube動画 UPSERT
 */
function koi_ria_upsert_youtube_video(array $row, string $mode = 'upsert'): array {
    $video_url = $row['url'] ?? $row['video_url'] ?? '';
    $video_id  = $row['video_id'] ?? '';

    // Auto-extract video ID from URL if not provided
    if (!$video_id && $video_url) {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $video_url, $m)) {
            $video_id = $m[1];
        }
    }

    if (!$video_id) {
        return ['status' => 'error', 'message' => 'video_idまたはURLが必要です'];
    }

    // Check for existing
    $existing = get_posts([
        'post_type'      => 'youtube_video',
        'meta_query'     => [['key' => 'video_id', 'value' => $video_id]],
        'posts_per_page' => 1,
    ]);

    if ($existing && $mode === 'add') {
        return ['status' => 'skipped'];
    }

    $title = $row['title'] ?? 'YouTube: ' . $video_id;

    if ($existing) {
        $post_id = $existing[0]->ID;
        wp_update_post(['ID' => $post_id, 'post_title' => $title]);
    } else {
        $post_id = wp_insert_post([
            'post_type'   => 'youtube_video',
            'post_title'  => $title,
            'post_status' => 'publish',
        ]);
        if (is_wp_error($post_id)) {
            return ['status' => 'error', 'message' => $post_id->get_error_message()];
        }
    }

    update_field('video_id', $video_id, $post_id);
    if (isset($row['channel_name'])) {
        update_field('channel_name', $row['channel_name'], $post_id);
    }
    if (isset($row['thumbnail_url'])) {
        update_field('thumbnail_url', $row['thumbnail_url'], $post_id);
    }
    // Auto-generate thumbnail if not provided
    if (!get_field('thumbnail_url', $post_id)) {
        update_field('thumbnail_url', 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg', $post_id);
    }

    // Handle show_slug → look up show post by slug, save as relation meta
    if (isset($row['show_slug']) && $row['show_slug'] !== '') {
        $show = get_page_by_path($row['show_slug'], OBJECT, 'show');
        if ($show) {
            update_field('show', $show->ID, $post_id);
        }
    }

    // Handle is_pinned (TRUE/FALSE → '1'/'0')
    if (isset($row['is_pinned'])) {
        $val = strtoupper(trim($row['is_pinned']));
        $is_pinned = ($val === 'TRUE' || $val === '1') ? '1' : '0';
        update_field('is_pinned', $is_pinned, $post_id);
    }

    return ['status' => $existing ? 'updated' : 'created'];
}
