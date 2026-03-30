<?php
/**
 * 人気記事（Google Analytics PVデータ連携）
 *
 * Site Kit (Google Analytics 4) から実PVデータを取得してランキング表示。
 * フォールバック: AJAX閲覧数カウンター。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * AJAX閲覧数カウンター（キャッシュ環境対応）
 * wp_head の PHP カウンターと違い、ページキャッシュがあっても動作する。
 */
function koi_ria_enqueue_view_counter() {
    if (is_single() && get_post_type() === 'post' && !is_admin()) {
        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }
        wp_enqueue_script(
            'koi-ria-view-counter',
            false,
            [],
            false,
            ['in_footer' => true, 'strategy' => 'async']
        );
        wp_add_inline_script('koi-ria-view-counter', sprintf(
            '(function(){var x=new XMLHttpRequest();x.open("POST","%s",true);x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");x.send("action=koi_ria_count_view&post_id=%d&nonce=%s");})();',
            esc_url(admin_url('admin-ajax.php')),
            $post_id,
            wp_create_nonce('koi_ria_view_' . $post_id)
        ));
    }
}
add_action('wp_enqueue_scripts', 'koi_ria_enqueue_view_counter');

/**
 * AJAX: 閲覧数インクリメント
 */
function koi_ria_ajax_count_view() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $nonce   = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!$post_id || !wp_verify_nonce($nonce, 'koi_ria_view_' . $post_id)) {
        wp_send_json_error('invalid', 403);
    }

    if (get_post_type($post_id) !== 'post') {
        wp_send_json_error('invalid_type', 400);
    }

    $count = (int) get_post_meta($post_id, 'post_views_count', true);
    update_post_meta($post_id, 'post_views_count', $count + 1);

    wp_send_json_success($count + 1);
}
add_action('wp_ajax_koi_ria_count_view', 'koi_ria_ajax_count_view');
add_action('wp_ajax_nopriv_koi_ria_count_view', 'koi_ria_ajax_count_view');

/**
 * Site Kit (GA4) から人気ページのPVデータを同期
 * 管理画面アクセス時に6時間おきに実行。
 */
function koi_ria_sync_ga_popular_posts() {
    // admin画面 + 管理者のみ
    if (!is_admin() || wp_doing_cron() || wp_doing_ajax()) {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }
    // 6時間ごとに同期
    if (get_transient('koi_ria_ga_sync_lock')) {
        return;
    }

    // Site Kit が利用可能か確認
    if (!class_exists('Google\Site_Kit\Plugin')) {
        return;
    }

    // REST API サーバーを初期化
    rest_get_server();

    $request = new WP_REST_Request('GET', '/google-site-kit/v1/modules/analytics-4/data/report');
    $request->set_query_params([
        'startDate'  => gmdate('Y-m-d', strtotime('-30 days')),
        'endDate'    => gmdate('Y-m-d'),
        'metrics'    => wp_json_encode([['name' => 'screenPageViews']]),
        'dimensions' => wp_json_encode([['name' => 'pagePath']]),
        'orderBys'   => wp_json_encode([
            ['metric' => ['metricName' => 'screenPageViews'], 'desc' => true],
        ]),
        'limit'      => '50',
    ]);

    $response = rest_do_request($request);

    // エラー時は1時間後にリトライ
    if ($response->is_error()) {
        set_transient('koi_ria_ga_sync_lock', 1, HOUR_IN_SECONDS);
        update_option('koi_ria_ga_sync_status', 'error: ' . $response->as_error()->get_error_message());
        return;
    }

    $data        = $response->get_data();
    $popular_ids = [];
    $pv_map      = [];

    // レスポンス形式: rows配列 (Site Kit v1.x 形式)
    $rows = [];
    if (isset($data['rows']) && is_array($data['rows'])) {
        $rows = $data['rows'];
    } elseif (is_array($data)) {
        // 配列直接返却の場合
        foreach ($data as $item) {
            if (isset($item['dimensionValues'], $item['metricValues'])) {
                $rows[] = $item;
            }
        }
    }

    foreach ($rows as $row) {
        $path  = '';
        $views = 0;

        // Site Kit レスポンス形式パターン1: dimensionValues / metricValues
        if (isset($row['dimensionValues'][0]['value'])) {
            $path  = $row['dimensionValues'][0]['value'];
            $views = (int) ($row['metricValues'][0]['value'] ?? 0);
        }
        // パターン2: フラットな配列
        elseif (isset($row['pagePath'])) {
            $path  = $row['pagePath'];
            $views = (int) ($row['screenPageViews'] ?? 0);
        }

        if (empty($path) || $path === '/' || $views < 1) {
            continue;
        }

        // URLパスから投稿IDを解決
        $post_id = url_to_postid(home_url($path));
        if ($post_id && get_post_type($post_id) === 'post') {
            update_post_meta($post_id, 'post_views_count', $views);
            update_post_meta($post_id, 'ga_views_30d', $views);
            $popular_ids[]     = $post_id;
            $pv_map[$post_id]  = $views;
        }
    }

    if (!empty($popular_ids)) {
        update_option('koi_ria_ga_popular_post_ids', array_slice($popular_ids, 0, 30), false);
        update_option('koi_ria_ga_popular_pv_map', $pv_map, false);
    }

    update_option('koi_ria_ga_sync_status', 'ok');
    update_option('koi_ria_ga_sync_time', current_time('mysql'));
    set_transient('koi_ria_ga_sync_lock', 1, 6 * HOUR_IN_SECONDS);
}
add_action('admin_init', 'koi_ria_sync_ga_popular_posts');

/**
 * 人気記事を取得（GA4 PVデータ優先 → AJAXカウンター → 最新記事）
 * 優先カテゴリの記事を上位に、それ以外をPV順で埋める。
 *
 * @param int   $count              取得件数
 * @param array $exclude_cat_ids    除外カテゴリID配列
 * @param int   $priority_cat_id    優先表示カテゴリID（0=なし）
 * @return WP_Post[]
 */
function koi_ria_get_popular_posts($count = 3, $exclude_cat_ids = [], $priority_cat_id = 0) {

    // 共通: カテゴリ除外フィルタ
    $filter_excluded = function ($ids) use ($exclude_cat_ids) {
        if (empty($exclude_cat_ids)) {
            return $ids;
        }
        return array_values(array_filter($ids, function ($id) use ($exclude_cat_ids) {
            foreach ($exclude_cat_ids as $cat_id) {
                if (has_category($cat_id, $id)) {
                    return false;
                }
            }
            return true;
        }));
    };

    // 共通: 優先カテゴリを先頭にソート
    $sort_priority = function ($ids) use ($priority_cat_id) {
        if (!$priority_cat_id) {
            return $ids;
        }
        $priority = [];
        $rest     = [];
        foreach ($ids as $id) {
            if (has_category($priority_cat_id, $id)) {
                $priority[] = $id;
            } else {
                $rest[] = $id;
            }
        }
        return array_merge($priority, $rest);
    };

    // ① GA同期データ（実PV順）
    $ga_ids = get_option('koi_ria_ga_popular_post_ids', []);

    if (!empty($ga_ids)) {
        $ga_ids = $filter_excluded($ga_ids);
        $ga_ids = $sort_priority($ga_ids);
        $ga_ids = array_slice($ga_ids, 0, $count);

        if (!empty($ga_ids)) {
            $posts = get_posts([
                'post_type'      => 'post',
                'post__in'       => $ga_ids,
                'orderby'        => 'post__in',
                'posts_per_page' => $count,
                'post_status'    => 'publish',
            ]);
            if (!empty($posts)) {
                return $posts;
            }
        }
    }

    // ② AJAXカウンター（post_views_count）— 優先カテゴリ + その他
    if ($priority_cat_id) {
        $priority_args = [
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'meta_key'       => 'post_views_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'post_status'    => 'publish',
            'category'       => $priority_cat_id,
        ];
        if (!empty($exclude_cat_ids)) {
            $priority_args['category__not_in'] = $exclude_cat_ids;
        }
        $priority_posts = get_posts($priority_args);

        $remaining = $count - count($priority_posts);
        $rest_posts = [];
        if ($remaining > 0) {
            $exclude_ids = wp_list_pluck($priority_posts, 'ID');
            $rest_args = [
                'post_type'      => 'post',
                'posts_per_page' => $remaining,
                'meta_key'       => 'post_views_count',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
                'post_status'    => 'publish',
                'post__not_in'   => $exclude_ids,
            ];
            if (!empty($exclude_cat_ids)) {
                $rest_args['category__not_in'] = $exclude_cat_ids;
            }
            $rest_posts = get_posts($rest_args);
        }

        $merged = array_merge($priority_posts, $rest_posts);
        if (!empty($merged)) {
            return $merged;
        }
    }

    // 優先カテゴリなしの場合
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'meta_key'       => 'post_views_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ];
    if (!empty($exclude_cat_ids)) {
        $args['category__not_in'] = $exclude_cat_ids;
    }
    $posts = get_posts($args);
    if (!empty($posts)) {
        return $posts;
    }

    // ③ フォールバック: 最新記事
    $fallback = [
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ];
    if (!empty($exclude_cat_ids)) {
        $fallback['category__not_in'] = $exclude_cat_ids;
    }
    return get_posts($fallback);
}

/**
 * 管理画面に同期ステータスを表示
 */
function koi_ria_ga_sync_status_notice() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }

    $status    = get_option('koi_ria_ga_sync_status', '');
    $sync_time = get_option('koi_ria_ga_sync_time', '');
    $pv_map    = get_option('koi_ria_ga_popular_pv_map', []);

    if ($status === 'ok' && $sync_time) {
        $count = count($pv_map);
        echo '<div class="notice notice-info is-dismissible"><p>';
        echo '<strong>恋リアポータル:</strong> ';
        echo 'Google Analytics PVデータを同期済み（' . esc_html($sync_time) . '、' . intval($count) . '記事）';
        echo '</p></div>';
    }
}
add_action('admin_notices', 'koi_ria_ga_sync_status_notice');
