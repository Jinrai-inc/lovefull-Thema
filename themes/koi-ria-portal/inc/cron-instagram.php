<?php
/**
 * Instagramプロフィール更新（WP-Cron）
 *
 * Phase4で本格実装予定。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    if (!wp_next_scheduled('koi_ria_update_instagram')) {
        wp_schedule_event(time(), 'daily', 'koi_ria_update_instagram');
    }
});

add_action('koi_ria_update_instagram', 'koi_ria_ig_update');

function koi_ria_ig_update(): void {
    $access_token = get_option('koi_ria_ig_access_token');
    $ig_user_id   = get_option('koi_ria_ig_user_id');

    if (!$access_token || !$ig_user_id) {
        return;
    }

    $casts = get_posts(['post_type' => 'cast', 'posts_per_page' => -1]);

    foreach ($casts as $cast) {
        $ig_username = get_field('ig_username', $cast->ID);
        if (!$ig_username) {
            continue;
        }

        $url = add_query_arg([
            'fields'       => "business_discovery.username({$ig_username}){profile_picture_url,followers_count,media_count}",
            'access_token' => $access_token,
        ], "https://graph.facebook.com/v19.0/{$ig_user_id}");

        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            continue;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $discovery = $data['business_discovery'] ?? null;
        if (!$discovery) {
            continue;
        }

        // フォロワー数更新
        if (isset($discovery['followers_count'])) {
            update_field('followers_count', intval($discovery['followers_count']), $cast->ID);
        }

        // プロフィール画像キャッシュ
        $image_url = $discovery['profile_picture_url'] ?? '';
        if ($image_url) {
            $upload_dir = wp_upload_dir();
            $cache_dir  = $upload_dir['basedir'] . '/ig-cache/';
            if (!file_exists($cache_dir)) {
                wp_mkdir_p($cache_dir);
            }

            $filename = sanitize_file_name($ig_username . '.jpg');
            $filepath = $cache_dir . $filename;

            $image_response = wp_remote_get($image_url);
            if (!is_wp_error($image_response)) {
                $image_data = wp_remote_retrieve_body($image_response);
                if ($image_data) {
                    file_put_contents($filepath, $image_data);
                    update_post_meta($cast->ID, 'ig_profile_cache', $upload_dir['baseurl'] . '/ig-cache/' . $filename);
                }
            }
        }
    }
}
