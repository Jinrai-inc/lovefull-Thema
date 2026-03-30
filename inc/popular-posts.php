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
 * URLパスから投稿IDを解決（複数の方法でフォールバック）
 */
function koi_ria_resolve_post_id_from_path(string $path): int {
    // パスの正規化
    $path = trim($path, '/');
    if (empty($path)) {
        return 0;
    }

    // 方法1: url_to_postid（WordPress標準）
    $post_id = url_to_postid(home_url('/' . $path . '/'));
    if ($post_id && get_post_type($post_id) === 'post') {
        return $post_id;
    }

    // 方法2: スラッグで直接検索（パーマリンク設定に依存しない）
    $slug = basename($path);
    $found = get_posts([
        'post_type'      => 'post',
        'name'           => $slug,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]);
    if (!empty($found)) {
        return $found[0];
    }

    // 方法3: パス全体をpost_nameとして検索
    $slug_from_path = sanitize_title($path);
    if ($slug_from_path !== $slug) {
        $found = get_posts([
            'post_type'      => 'post',
            'name'           => $slug_from_path,
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ]);
        if (!empty($found)) {
            return $found[0];
        }
    }

    return 0;
}

/**
 * Site Kit GA4 レスポンスから rows を抽出（複数フォーマット対応）
 */
function koi_ria_parse_ga_rows($data): array {
    $rows = [];

    // フォーマット1: { rows: [...] }
    if (isset($data['rows']) && is_array($data['rows'])) {
        return $data['rows'];
    }

    // フォーマット2: 直接配列 [{ dimensionValues, metricValues }, ...]
    if (is_array($data) && !isset($data['error'])) {
        foreach ($data as $key => $item) {
            if (is_numeric($key) && is_array($item)) {
                if (isset($item['dimensionValues']) || isset($item['pagePath'])) {
                    $rows[] = $item;
                }
            }
        }
        if (!empty($rows)) {
            return $rows;
        }
    }

    // フォーマット3: { data: { rows: [...] } }
    if (isset($data['data']['rows']) && is_array($data['data']['rows'])) {
        return $data['data']['rows'];
    }

    // フォーマット4: { report: { rows: [...] } }
    if (isset($data['report']['rows']) && is_array($data['report']['rows'])) {
        return $data['report']['rows'];
    }

    return $rows;
}

/**
 * GA行データからパスとPVを抽出
 */
function koi_ria_extract_path_views(array $row): array {
    $path  = '';
    $views = 0;

    // パターン1: dimensionValues / metricValues（GA4 API形式）
    if (isset($row['dimensionValues'][0]['value'])) {
        $path  = $row['dimensionValues'][0]['value'];
        $views = (int) ($row['metricValues'][0]['value'] ?? 0);
    }
    // パターン2: フラットキー
    elseif (isset($row['pagePath'])) {
        $path  = $row['pagePath'];
        $views = (int) ($row['screenPageViews'] ?? $row['pageViews'] ?? $row['views'] ?? 0);
    }
    // パターン3: dimensions / metrics 配列
    elseif (isset($row['dimensions'][0])) {
        $path  = $row['dimensions'][0];
        $views = (int) ($row['metrics'][0]['values'][0] ?? $row['metrics'][0] ?? 0);
    }

    return ['path' => $path, 'views' => $views];
}

/**
 * Site Kit (GA4) から人気ページのPVデータを同期
 *
 * @param bool $force  trueの場合、ロックを無視して強制同期
 * @return array       同期結果の詳細情報
 */
function koi_ria_sync_ga_popular_posts(bool $force = false): array {
    $result = [
        'success'     => false,
        'message'     => '',
        'debug'       => [],
        'matched'     => 0,
        'unmatched'   => 0,
        'total_rows'  => 0,
    ];

    if (!current_user_can('manage_options')) {
        $result['message'] = '権限がありません';
        return $result;
    }

    // ロック確認（強制時はスキップ）
    if (!$force && get_transient('koi_ria_ga_sync_lock')) {
        $result['message'] = '同期ロック中（6時間ごとに自動実行）';
        return $result;
    }

    // Site Kit が利用可能か確認
    if (!class_exists('Google\Site_Kit\Plugin')) {
        $result['message'] = 'Site Kit プラグインが有効化されていません';
        update_option('koi_ria_ga_sync_status', 'error: Site Kit not found');
        return $result;
    }

    $result['debug'][] = 'Site Kit 検出OK';

    // REST API サーバーを初期化
    rest_get_server();

    // Site Kit REST API を呼び出し
    $request = new WP_REST_Request('GET', '/google-site-kit/v1/modules/analytics-4/data/report');
    $request->set_query_params([
        'startDate'  => gmdate('Y-m-d', strtotime('-28 days')),
        'endDate'    => gmdate('Y-m-d', strtotime('-1 day')),
        'metrics'    => wp_json_encode([['name' => 'screenPageViews']]),
        'dimensions' => wp_json_encode([['name' => 'pagePath']]),
        'orderBys'   => wp_json_encode([
            ['metric' => ['metricName' => 'screenPageViews'], 'desc' => true],
        ]),
        'limit'      => '50',
    ]);

    $result['debug'][] = 'REST API リクエスト送信: /google-site-kit/v1/modules/analytics-4/data/report';

    $response = rest_do_request($request);

    if ($response->is_error()) {
        $error_msg = $response->as_error()->get_error_message();
        $result['message'] = 'REST APIエラー: ' . $error_msg;
        $result['debug'][] = 'エラーコード: ' . $response->get_status();
        $result['debug'][] = 'エラー詳細: ' . $error_msg;

        set_transient('koi_ria_ga_sync_lock', 1, HOUR_IN_SECONDS);
        update_option('koi_ria_ga_sync_status', 'error: ' . $error_msg);
        update_option('koi_ria_ga_sync_debug', $result);
        return $result;
    }

    $data = $response->get_data();
    $result['debug'][] = 'レスポンスステータス: ' . $response->get_status();
    $result['debug'][] = 'レスポンスデータ型: ' . gettype($data);

    if (is_array($data)) {
        $top_keys = array_slice(array_keys($data), 0, 10);
        $result['debug'][] = 'トップレベルキー: ' . implode(', ', $top_keys);
    }

    // レスポンスからrowsを抽出
    $rows = koi_ria_parse_ga_rows($data);
    $result['total_rows'] = count($rows);
    $result['debug'][] = '抽出rows数: ' . count($rows);

    if (empty($rows)) {
        // データが空の場合、レスポンス全体をデバッグ用に保存
        $result['message'] = 'GAデータの解析に失敗（rows が空）';
        $result['debug'][] = 'レスポンスサンプル: ' . mb_substr(wp_json_encode($data, JSON_UNESCAPED_UNICODE), 0, 1000);
        update_option('koi_ria_ga_sync_status', 'error: empty rows');
        update_option('koi_ria_ga_sync_debug', $result);
        set_transient('koi_ria_ga_sync_lock', 1, HOUR_IN_SECONDS);
        return $result;
    }

    // 最初の行のデバッグ情報
    $result['debug'][] = '最初のrow: ' . mb_substr(wp_json_encode($rows[0], JSON_UNESCAPED_UNICODE), 0, 500);

    $popular_ids = [];
    $pv_map      = [];
    $unmatched   = [];

    foreach ($rows as $row) {
        $extracted = koi_ria_extract_path_views($row);
        $path  = $extracted['path'];
        $views = $extracted['views'];

        if (empty($path) || $path === '/' || $views < 1) {
            continue;
        }

        $post_id = koi_ria_resolve_post_id_from_path($path);

        if ($post_id) {
            update_post_meta($post_id, 'post_views_count', $views);
            update_post_meta($post_id, 'ga_views_30d', $views);
            $popular_ids[]    = $post_id;
            $pv_map[$post_id] = $views;
            $result['matched']++;
        } else {
            $unmatched[] = $path . ' (' . $views . ' PV)';
            $result['unmatched']++;
        }
    }

    if (!empty($unmatched)) {
        $result['debug'][] = 'マッチしなかったパス: ' . implode(', ', array_slice($unmatched, 0, 10));
    }

    // 結果保存
    if (!empty($popular_ids)) {
        update_option('koi_ria_ga_popular_post_ids', array_slice($popular_ids, 0, 30), false);
        update_option('koi_ria_ga_popular_pv_map', $pv_map, false);
        $result['success'] = true;
        $result['message'] = $result['matched'] . '記事のPVデータを同期しました';
    } else {
        $result['message'] = 'マッチする投稿が見つかりませんでした（' . $result['unmatched'] . 'パス未解決）';
    }

    update_option('koi_ria_ga_sync_status', $result['success'] ? 'ok' : 'partial');
    update_option('koi_ria_ga_sync_time', current_time('mysql'));
    update_option('koi_ria_ga_sync_debug', $result);
    set_transient('koi_ria_ga_sync_lock', 1, 6 * HOUR_IN_SECONDS);

    return $result;
}

/**
 * admin_init 自動同期（従来の自動実行ロジック）
 */
function koi_ria_auto_sync_ga() {
    if (!is_admin() || wp_doing_cron() || wp_doing_ajax()) {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }
    if (get_transient('koi_ria_ga_sync_lock')) {
        return;
    }
    if (!class_exists('Google\Site_Kit\Plugin')) {
        return;
    }

    koi_ria_sync_ga_popular_posts(false);
}
add_action('admin_init', 'koi_ria_auto_sync_ga');

/**
 * AJAX: 手動GA同期（管理画面のボタンから実行）
 */
function koi_ria_ajax_sync_ga() {
    check_ajax_referer('koi_ria_sync_ga', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('権限がありません');
    }

    // ロック解除して強制実行
    delete_transient('koi_ria_ga_sync_lock');
    $result = koi_ria_sync_ga_popular_posts(true);

    wp_send_json_success($result);
}
add_action('wp_ajax_koi_ria_sync_ga', 'koi_ria_ajax_sync_ga');

/**
 * 人気記事を取得（GA4 PVデータ優先 → AJAXカウンター → 最新記事）
 */
function koi_ria_get_popular_posts($count = 3, $exclude_cat_ids = [], $priority_cat_id = 0) {

    // 管理画面の設定を適用
    $popular_settings = get_option('koi_ria_popular_settings', []);

    if (!empty($popular_settings)) {
        if (isset($popular_settings['count'])) {
            $count = (int) $popular_settings['count'];
        }
        if (isset($popular_settings['priority_cat_id'])) {
            $priority_cat_id = (int) $popular_settings['priority_cat_id'];
        }
        if (isset($popular_settings['exclude_cats']) && is_array($popular_settings['exclude_cats'])) {
            $exclude_cat_ids = $popular_settings['exclude_cats'];
        }
    }

    // 手動モード
    if (($popular_settings['mode'] ?? 'auto') === 'manual' && !empty($popular_settings['manual_post_ids'])) {
        $manual_ids = array_slice($popular_settings['manual_post_ids'], 0, $count);
        $posts = get_posts([
            'post_type'      => 'post',
            'post__in'       => $manual_ids,
            'orderby'        => 'post__in',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
        ]);
        if (!empty($posts)) {
            return $posts;
        }
    }

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
 * AJAX: 記事検索（人気記事設定用）
 */
function koi_ria_ajax_search_posts() {
    check_ajax_referer('koi_ria_search_posts', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('permission denied');
    }

    $q = sanitize_text_field($_POST['q'] ?? '');
    if (mb_strlen($q) < 1) {
        wp_send_json_success([]);
    }

    $posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 20,
        's'              => $q,
        'post_status'    => 'publish',
        'orderby'        => 'relevance',
    ]);

    $results = [];
    foreach ($posts as $p) {
        $cats = get_the_category($p->ID);
        $results[] = [
            'id'    => $p->ID,
            'title' => $p->post_title,
            'thumb' => get_the_post_thumbnail_url($p, 'thumbnail') ?: '',
            'cat'   => $cats ? $cats[0]->name : '',
            'pv'    => (int) get_post_meta($p->ID, 'post_views_count', true),
            'date'  => get_the_date('Y-m-d', $p),
        ];
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_koi_ria_search_posts', 'koi_ria_ajax_search_posts');

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
