<?php
/**
 * WP-Cronジョブ: IGアバター一括更新（unavatar.io方式）
 *
 * 週1回、全出演者のアバターをunavatar.io経由で更新する。
 * 1リクエスト2秒間隔でサーバー負荷を抑制。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

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

// Cronスケジュール登録
add_action('init', function () {
    if (!wp_next_scheduled('koi_ria_refresh_all_avatars')) {
        wp_schedule_event(strtotime('next monday 4:00am'), 'weekly', 'koi_ria_refresh_all_avatars');
    }
});

// Cronジョブ本体
add_action('koi_ria_refresh_all_avatars', 'koi_ria_cron_refresh_avatars');

function koi_ria_cron_refresh_avatars(): array {
    $casts = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $total   = count($casts);
    $success = 0;
    $failed  = 0;
    $skipped = 0;

    error_log("[KoiRia] Avatar refresh started: {$total} cast members");

    foreach ($casts as $cast) {
        $ig_username = get_field('ig_username', $cast->ID);

        if (empty($ig_username)) {
            $skipped++;
            continue;
        }

        // 手動アップロード画像がある場合はスキップ
        $manual_image = get_field('profile_image', $cast->ID);
        if ($manual_image && isset($manual_image['url'])) {
            $skipped++;
            continue;
        }

        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/ig-cache/' . sanitize_file_name($ig_username) . '.jpg';

        $result = koi_ria_fetch_and_cache_avatar($ig_username, $filepath);

        if ($result) {
            // post metaにキャッシュURLを保存（テンプレート互換）
            $cache_url = $upload_dir['baseurl'] . '/ig-cache/' . sanitize_file_name($ig_username) . '.jpg';
            update_post_meta($cast->ID, 'ig_profile_cache', $cache_url);
            $success++;
        } else {
            $failed++;
        }

        // レート制限: 2秒間隔
        sleep(2);
    }

    error_log("[KoiRia] Avatar refresh completed: success={$success}, failed={$failed}, skipped={$skipped}");

    update_option('koi_ria_avatar_last_run', [
        'time'    => current_time('mysql'),
        'summary' => [
            'updated' => $success,
            'skipped' => $skipped,
            'errors'  => $failed,
        ],
    ]);

    return ['updated' => $success, 'skipped' => $skipped, 'errors' => $failed];
}

// 手動実行: アバター一括更新（バックグラウンド）
add_action('admin_post_koi_ria_manual_avatar_refresh', function () {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    check_admin_referer('koi_ria_manual_cron');

    // バックグラウンドで実行（即座にリダイレクト）
    wp_schedule_single_event(time(), 'koi_ria_refresh_all_avatars');
    spawn_cron();

    set_transient('koi_ria_admin_notice', [
        'type'    => 'info',
        'message' => 'アバター更新をバックグラウンドで開始しました。数分後にこのページを再読み込みして結果を確認してください。',
    ], 60);

    wp_redirect(admin_url('admin.php?page=koi-ria-cron'));
    exit;
});
