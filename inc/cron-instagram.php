<?php
/**
 * Instagramプロフィール更新（WP-Cron）
 *
 * Instagram Graph API (Business Discovery) で出演者情報を更新。
 * - フォロワー数: 毎日更新
 * - プロフィール画像: 週1回キャッシュ更新
 * - 最新投稿メディア: 週1回取得（Phase5以降）
 * - レート制限対応（1回あたり最大50件、API制限: 200 calls/hour）
 * - ビジネス/クリエイターアカウント以外はスキップ
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// Cron登録
add_action('init', function () {
    // フォロワー数: 毎日
    if (!wp_next_scheduled('koi_ria_update_instagram')) {
        wp_schedule_event(time(), 'daily', 'koi_ria_update_instagram');
    }
    // プロフィール画像: 週1回
    if (!wp_next_scheduled('koi_ria_update_ig_images')) {
        wp_schedule_event(time(), 'weekly', 'koi_ria_update_ig_images');
    }
});

// カスタムスケジュール: weekly
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['weekly'])) {
        $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display'  => '週1回',
        ];
    }
    return $schedules;
});

add_action('koi_ria_update_instagram', 'koi_ria_ig_update');
add_action('koi_ria_update_ig_images', 'koi_ria_ig_update_images');

// 手動実行
add_action('admin_post_koi_ria_manual_instagram', function () {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    check_admin_referer('koi_ria_manual_cron');

    $result = koi_ria_ig_update();

    set_transient('koi_ria_admin_notice', [
        'type'    => 'success',
        'message' => "Instagram更新完了: 更新 {$result['updated']}件 / スキップ {$result['skipped']}件 / エラー {$result['errors']}件",
    ], 30);

    wp_redirect(admin_url('admin.php?page=koi-ria-cron'));
    exit;
});

/**
 * フォロワー数更新（毎日）
 */
function koi_ria_ig_update(): array {
    $summary = ['updated' => 0, 'skipped' => 0, 'errors' => 0];

    $access_token = get_option('koi_ria_ig_access_token');
    $ig_user_id   = get_option('koi_ria_ig_user_id');

    if (!$access_token || !$ig_user_id) {
        koi_ria_log('Instagram', 'アクセストークンまたはユーザーIDが未設定です');
        return $summary;
    }

    $casts = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => 50, // レート制限対策: 1回あたり最大50件
        'meta_query'     => [
            [
                'key'     => 'ig_username',
                'value'   => '',
                'compare' => '!=',
            ],
        ],
        'orderby'  => 'meta_value_num',
        'meta_key' => 'followers_count',
        'order'    => 'DESC', // フォロワー多い順（人気順）
    ]);

    $api_calls = 0;
    $max_calls = 50; // 1実行あたりの上限

    foreach ($casts as $cast) {
        if ($api_calls >= $max_calls) {
            koi_ria_log('Instagram', "API呼び出し上限 ({$max_calls}件) に達しました。残りは次回実行で処理します。");
            break;
        }

        $ig_username = get_field('ig_username', $cast->ID);
        if (!$ig_username) {
            continue;
        }

        // Transientキャッシュ確認（12時間）
        $cache_key = 'koi_ria_ig_' . md5($ig_username);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            // キャッシュからフォロワー数を更新
            if (isset($cached['followers_count'])) {
                $current = get_field('followers_count', $cast->ID) ?: 0;
                if (intval($cached['followers_count']) !== intval($current)) {
                    update_field('followers_count', intval($cached['followers_count']), $cast->ID);
                    $summary['updated']++;
                } else {
                    $summary['skipped']++;
                }
            }
            continue;
        }

        $url = add_query_arg([
            'fields'       => "business_discovery.username({$ig_username}){profile_picture_url,followers_count,media_count}",
            'access_token' => $access_token,
        ], "https://graph.facebook.com/v19.0/{$ig_user_id}");

        $response = wp_remote_get($url, ['timeout' => 10]);
        $api_calls++;

        if (is_wp_error($response)) {
            koi_ria_log('Instagram', "API通信エラー (@{$ig_username}): " . $response->get_error_message());
            $summary['errors']++;
            continue;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // エラーチェック
        if ($http_code !== 200 || isset($body['error'])) {
            $error_msg = $body['error']['message'] ?? "HTTPステータス: {$http_code}";
            $error_code = $body['error']['code'] ?? 0;

            // ビジネスアカウントでない場合
            if ($error_code === 100) {
                koi_ria_log('Instagram', "@{$ig_username} はビジネス/クリエイターアカウントではありません（スキップ）");
                // 次回もスキップするようTransient設定
                set_transient($cache_key, ['not_business' => true], WEEK_IN_SECONDS);
                $summary['skipped']++;
                continue;
            }

            // レート制限
            if ($http_code === 429 || $error_code === 4) {
                koi_ria_log('Instagram', 'APIレート制限に達しました。処理を中断します。');
                break;
            }

            koi_ria_log('Instagram', "APIエラー (@{$ig_username}): {$error_msg}");
            $summary['errors']++;
            continue;
        }

        $discovery = $body['business_discovery'] ?? null;
        if (!$discovery) {
            $summary['skipped']++;
            continue;
        }

        // キャッシュ保存（12時間）
        set_transient($cache_key, $discovery, 12 * HOUR_IN_SECONDS);

        // フォロワー数更新
        if (isset($discovery['followers_count'])) {
            update_field('followers_count', intval($discovery['followers_count']), $cast->ID);
            $summary['updated']++;
        }
    }

    update_option('koi_ria_ig_last_run', [
        'time'      => current_time('mysql'),
        'summary'   => $summary,
        'api_calls' => $api_calls,
    ]);

    return $summary;
}

/**
 * プロフィール画像キャッシュ更新（週1回）
 */
function koi_ria_ig_update_images(): array {
    $summary = ['updated' => 0, 'skipped' => 0, 'errors' => 0];

    $access_token = get_option('koi_ria_ig_access_token');
    $ig_user_id   = get_option('koi_ria_ig_user_id');

    if (!$access_token || !$ig_user_id) {
        return $summary;
    }

    $upload_dir = wp_upload_dir();
    $cache_dir  = $upload_dir['basedir'] . '/ig-cache/';
    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }

    $casts = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => 30, // 画像DLは負荷が高いため制限
        'meta_query'     => [
            [
                'key'     => 'ig_username',
                'value'   => '',
                'compare' => '!=',
            ],
        ],
    ]);

    foreach ($casts as $cast) {
        $ig_username = get_field('ig_username', $cast->ID);
        if (!$ig_username) {
            continue;
        }

        // 手動アップロード画像がある場合はスキップ
        $manual_image = get_field('profile_image', $cast->ID);
        if ($manual_image && isset($manual_image['url'])) {
            $summary['skipped']++;
            continue;
        }

        // キャッシュからプロフィール画像URLを取得
        $cache_key = 'koi_ria_ig_' . md5($ig_username);
        $cached = get_transient($cache_key);

        $image_url = '';
        if ($cached && isset($cached['profile_picture_url'])) {
            $image_url = $cached['profile_picture_url'];
        } else {
            // キャッシュがない場合はAPIを呼ぶ
            $url = add_query_arg([
                'fields'       => "business_discovery.username({$ig_username}){profile_picture_url}",
                'access_token' => $access_token,
            ], "https://graph.facebook.com/v19.0/{$ig_user_id}");

            $response = wp_remote_get($url, ['timeout' => 10]);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $image_url = $body['business_discovery']['profile_picture_url'] ?? '';
            }
        }

        if (!$image_url) {
            $summary['skipped']++;
            continue;
        }

        // 画像ダウンロード＆保存
        $filename = sanitize_file_name($ig_username . '.jpg');
        $filepath = $cache_dir . $filename;

        $image_response = wp_remote_get($image_url, ['timeout' => 15]);
        if (is_wp_error($image_response)) {
            $summary['errors']++;
            continue;
        }

        $image_data = wp_remote_retrieve_body($image_response);
        $content_type = wp_remote_retrieve_header($image_response, 'content-type');

        // 画像データの検証
        if (!$image_data || strlen($image_data) < 1000 || strpos($content_type, 'image') === false) {
            $summary['errors']++;
            continue;
        }

        if (file_put_contents($filepath, $image_data) !== false) {
            update_post_meta($cast->ID, 'ig_profile_cache', $upload_dir['baseurl'] . '/ig-cache/' . $filename);
            $summary['updated']++;
        } else {
            $summary['errors']++;
        }
    }

    update_option('koi_ria_ig_images_last_run', [
        'time'    => current_time('mysql'),
        'summary' => $summary,
    ]);

    return $summary;
}
