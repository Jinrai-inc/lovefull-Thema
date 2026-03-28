<?php
/**
 * YouTube動画自動取得（WP-Cron）
 *
 * Phase4で本格実装予定。
 * 現段階ではCronイベント登録のみ。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    if (!wp_next_scheduled('koi_ria_fetch_youtube')) {
        wp_schedule_event(time(), 'twicedaily', 'koi_ria_fetch_youtube');
    }
});

add_action('koi_ria_fetch_youtube', 'koi_ria_youtube_fetch');

function koi_ria_youtube_fetch(): void {
    $api_key = get_option('koi_ria_youtube_api_key');
    if (!$api_key) {
        return;
    }

    $shows = get_posts(['post_type' => 'show', 'posts_per_page' => -1]);

    foreach ($shows as $show) {
        $channel_id = get_field('youtube_channel_id', $show->ID);
        if (!$channel_id) {
            continue;
        }

        $url = add_query_arg([
            'part'       => 'snippet',
            'channelId'  => $channel_id,
            'maxResults' => 5,
            'order'      => 'date',
            'type'       => 'video',
            'key'        => $api_key,
        ], 'https://www.googleapis.com/youtube/v3/search');

        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

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
                continue;
            }

            $post_id = wp_insert_post([
                'post_type'   => 'youtube_video',
                'post_title'  => sanitize_text_field($item['snippet']['title'] ?? ''),
                'post_status' => 'publish',
            ]);

            if (!is_wp_error($post_id)) {
                update_field('video_id', $video_id, $post_id);
                update_field('channel_name', sanitize_text_field($item['snippet']['channelTitle'] ?? ''), $post_id);
                update_field('thumbnail_url', esc_url_raw($item['snippet']['thumbnails']['high']['url'] ?? ''), $post_id);
                update_field('published_at', sanitize_text_field($item['snippet']['publishedAt'] ?? ''), $post_id);
                update_field('is_auto', true, $post_id);
                update_field('platform', get_field('platform', $show->ID) ?: '', $post_id);
            }
        }
    }
}
