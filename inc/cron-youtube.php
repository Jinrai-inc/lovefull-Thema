<?php
/**
 * YouTube動画自動取得（WP-Cron + 保存時チャンネル自動取得）
 *
 * YouTube Data API v3 を使用して各番組の最新動画を自動取得。
 * - Transients APIでAPIレスポンスをキャッシュ（6時間）
 * - APIクォータ管理（10,000 units/日）
 * - エラーログ記録
 * - 管理画面から手動実行可能
 * - 動画保存時にチャンネルの他の最新動画を自動取得
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// Cron登録
add_action('init', function () {
    if (!wp_next_scheduled('koi_ria_fetch_youtube')) {
        wp_schedule_event(time(), 'twicedaily', 'koi_ria_fetch_youtube');
    }
});

add_action('koi_ria_fetch_youtube', 'koi_ria_youtube_fetch');

// 管理画面から手動実行
add_action('admin_post_koi_ria_manual_youtube', function () {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    check_admin_referer('koi_ria_manual_cron');

    $result = koi_ria_youtube_fetch();

    set_transient('koi_ria_admin_notice', [
        'type'    => 'success',
        'message' => "YouTube取得完了: 新規 {$result['created']}件 / スキップ {$result['skipped']}件 / エラー {$result['errors']}件",
    ], 30);

    wp_redirect(admin_url('admin.php?page=koi-ria-cron'));
    exit;
});

/**
 * YouTube動画取得メイン処理（番組のチャンネルIDから取得）
 */
function koi_ria_youtube_fetch(): array {
    $summary = ['created' => 0, 'skipped' => 0, 'errors' => 0];

    $api_key = get_option('koi_ria_youtube_api_key');
    if (!$api_key) {
        koi_ria_log('YouTube', 'APIキーが設定されていません');
        return $summary;
    }

    $shows = get_posts(['post_type' => 'show', 'posts_per_page' => -1]);

    foreach ($shows as $show) {
        $channel_id = get_field('youtube_channel_id', $show->ID);
        if (!$channel_id) {
            continue;
        }

        $show_name = get_field('short_name', $show->ID) ?: $show->post_title;
        $platform  = get_field('platform', $show->ID) ?: '';

        $result = koi_ria_fetch_channel_videos($channel_id, $show_name, $platform, $api_key);
        $summary['created'] += $result['created'];
        $summary['skipped'] += $result['skipped'];
        $summary['errors']  += $result['errors'];

        // クォータ超過で中断
        if ($result['quota_exceeded']) {
            break;
        }
    }

    // 実行ログ保存
    update_option('koi_ria_youtube_last_run', [
        'time'    => current_time('mysql'),
        'summary' => $summary,
    ]);

    return $summary;
}

/**
 * 指定チャンネルの最新動画を取得して保存
 */
function koi_ria_fetch_channel_videos(string $channel_id, string $label, string $platform, string $api_key, int $max_results = 5): array {
    $result = ['created' => 0, 'skipped' => 0, 'errors' => 0, 'quota_exceeded' => false];

    // Transient キャッシュ確認（6時間）
    $cache_key = 'koi_ria_yt_' . md5($channel_id);
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        $data = $cached;
    } else {
        $url = add_query_arg([
            'part'       => 'snippet',
            'channelId'  => $channel_id,
            'maxResults' => $max_results,
            'order'      => 'date',
            'type'       => 'video',
            'key'        => $api_key,
        ], 'https://www.googleapis.com/youtube/v3/search');

        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            koi_ria_log('YouTube', "API通信エラー ({$label}): " . $response->get_error_message());
            $result['errors']++;
            return $result;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($http_code !== 200 || isset($data['error'])) {
            $error_msg = $data['error']['message'] ?? "HTTPステータス: {$http_code}";
            koi_ria_log('YouTube', "APIエラー ({$label}): {$error_msg}");
            $result['errors']++;

            if ($http_code === 403) {
                koi_ria_log('YouTube', 'APIクォータ超過の可能性。処理を中断します。');
                $result['quota_exceeded'] = true;
            }
            return $result;
        }

        set_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);
    }

    // 動画の保存処理
    foreach ($data['items'] ?? [] as $item) {
        $video_id = $item['id']['videoId'] ?? '';
        if (!$video_id) {
            continue;
        }

        // 重複チェック
        $existing = get_posts([
            'post_type'      => 'youtube_video',
            'meta_query'     => [['key' => 'video_id', 'value' => $video_id]],
            'posts_per_page' => 1,
        ]);

        if ($existing) {
            $result['skipped']++;
            continue;
        }

        $title        = sanitize_text_field($item['snippet']['title'] ?? '');
        $channel_name = sanitize_text_field($item['snippet']['channelTitle'] ?? '');
        // 最高解像度のサムネイルを優先
        $thumbs    = $item['snippet']['thumbnails'] ?? [];
        $thumbnail = esc_url_raw(
            $thumbs['maxres']['url']  ?? $thumbs['standard']['url']
                                      ?? $thumbs['high']['url']
                                      ?? $thumbs['medium']['url']
                                      ?? $thumbs['default']['url']
                                      ?? ''
        );
        $published    = sanitize_text_field($item['snippet']['publishedAt'] ?? '');

        $post_id = wp_insert_post([
            'post_type'   => 'youtube_video',
            'post_title'  => $title,
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            koi_ria_log('YouTube', "投稿作成エラー: " . $post_id->get_error_message());
            $result['errors']++;
            continue;
        }

        update_field('video_id', $video_id, $post_id);
        update_field('channel_name', $channel_name, $post_id);
        update_field('thumbnail_url', $thumbnail, $post_id);
        update_field('published_at', $published, $post_id);
        update_field('is_auto', '1', $post_id);
        update_field('is_pinned', '0', $post_id);
        update_field('platform', $platform, $post_id);

        $result['created']++;
        koi_ria_log('YouTube', "新規取得: {$title} ({$label})");
    }

    return $result;
}

/**
 * YouTube動画保存時: 同じチャンネルの最新動画を自動取得
 *
 * 1つの動画を手動登録すると、そのチャンネルの他の最新動画を自動的に取得する。
 * YouTube Data API を使って video_id → channelId を解決し、
 * そのチャンネルの最新動画を取得する。
 */
add_action('save_post_youtube_video', function (int $post_id): void {
    // 自動保存・リビジョンはスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (get_post_status($post_id) !== 'publish') return;

    // 自動取得された動画の再保存で無限ループ防止
    $is_auto = get_post_meta($post_id, 'is_auto', true);
    if ($is_auto === '1' || $is_auto === true) return;

    // 既にこの投稿でチャンネル取得済みならスキップ
    $already_fetched = get_post_meta($post_id, '_channel_videos_fetched', true);
    if ($already_fetched) return;

    $api_key = get_option('koi_ria_youtube_api_key');
    if (!$api_key) return;

    $video_id = get_post_meta($post_id, 'video_id', true);
    if (!$video_id || !preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) return;

    // YouTube Data API で動画情報からチャンネルIDを取得
    $url = add_query_arg([
        'part' => 'snippet',
        'id'   => $video_id,
        'key'  => $api_key,
    ], 'https://www.googleapis.com/youtube/v3/videos');

    $response = wp_remote_get($url, ['timeout' => 15]);
    if (is_wp_error($response)) return;

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $snippet = $data['items'][0]['snippet'] ?? null;
    if (!$snippet) return;

    $channel_id   = $snippet['channelId'] ?? '';
    $channel_name = $snippet['channelTitle'] ?? '';

    if (!$channel_id) return;

    // 現在の動画のチャンネル名が空なら自動入力
    if (!get_post_meta($post_id, 'channel_name', true) && $channel_name) {
        update_post_meta($post_id, 'channel_name', $channel_name);
    }

    // サムネイルが空なら自動入力
    if (!get_post_meta($post_id, 'thumbnail_url', true)) {
        $thumb = $snippet['thumbnails']['high']['url'] ?? $snippet['thumbnails']['default']['url'] ?? '';
        if ($thumb) {
            update_post_meta($post_id, 'thumbnail_url', esc_url_raw($thumb));
        }
    }

    // フラグを立てて二重取得を防止
    update_post_meta($post_id, '_channel_videos_fetched', '1');

    // チャンネルの最新動画を取得（最大4件）
    $platform = get_post_meta($post_id, 'platform', true) ?: '';
    $result = koi_ria_fetch_channel_videos($channel_id, $channel_name, $platform, $api_key, 4);

    if ($result['created'] > 0) {
        set_transient('koi_ria_admin_notice', [
            'type'    => 'success',
            'message' => "「{$channel_name}」チャンネルから {$result['created']}件の動画を自動取得しました。",
        ], 30);
    }
}, 20);
