<?php
/**
 * YouTube動画自動取得（WP-Cron）
 *
 * YouTube Data API v3 を使用して各番組の最新動画を自動取得。
 * - Transients APIでAPIレスポンスをキャッシュ（6時間）
 * - APIクォータ管理（10,000 units/日）
 * - エラーログ記録
 * - 管理画面から手動実行可能
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
 * YouTube動画取得メイン処理
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

        // Transient キャッシュ確認（6時間）
        $cache_key = 'koi_ria_yt_' . md5($channel_id);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            $data = $cached;
        } else {
            $url = add_query_arg([
                'part'       => 'snippet',
                'channelId'  => $channel_id,
                'maxResults' => 5,
                'order'      => 'date',
                'type'       => 'video',
                'key'        => $api_key,
            ], 'https://www.googleapis.com/youtube/v3/search');

            $response = wp_remote_get($url, ['timeout' => 15]);

            if (is_wp_error($response)) {
                koi_ria_log('YouTube', "API通信エラー ({$show_name}): " . $response->get_error_message());
                $summary['errors']++;
                continue;
            }

            $http_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // APIエラーチェック
            if ($http_code !== 200 || isset($data['error'])) {
                $error_msg = $data['error']['message'] ?? "HTTPステータス: {$http_code}";
                koi_ria_log('YouTube', "APIエラー ({$show_name}): {$error_msg}");
                $summary['errors']++;

                // クォータ超過の場合は処理中止
                if ($http_code === 403) {
                    koi_ria_log('YouTube', 'APIクォータ超過の可能性。処理を中断します。');
                    break;
                }
                continue;
            }

            // キャッシュ保存（6時間）
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
                $summary['skipped']++;
                continue;
            }

            $title = sanitize_text_field($item['snippet']['title'] ?? '');
            $channel_name = sanitize_text_field($item['snippet']['channelTitle'] ?? '');
            $thumbnail = esc_url_raw($item['snippet']['thumbnails']['high']['url'] ?? '');
            $published = sanitize_text_field($item['snippet']['publishedAt'] ?? '');

            $post_id = wp_insert_post([
                'post_type'   => 'youtube_video',
                'post_title'  => $title,
                'post_status' => 'publish',
            ]);

            if (is_wp_error($post_id)) {
                koi_ria_log('YouTube', "投稿作成エラー: " . $post_id->get_error_message());
                $summary['errors']++;
                continue;
            }

            update_field('video_id', $video_id, $post_id);
            update_field('channel_name', $channel_name, $post_id);
            update_field('thumbnail_url', $thumbnail, $post_id);
            update_field('published_at', $published, $post_id);
            update_field('is_auto', true, $post_id);
            update_field('is_pinned', false, $post_id);
            update_field('platform', get_field('platform', $show->ID) ?: '', $post_id);

            $summary['created']++;
            koi_ria_log('YouTube', "新規取得: {$title} ({$show_name})");
        }
    }

    // 実行ログ保存
    update_option('koi_ria_youtube_last_run', [
        'time'    => current_time('mysql'),
        'summary' => $summary,
    ]);

    return $summary;
}
