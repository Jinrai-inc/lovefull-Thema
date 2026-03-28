<?php
/**
 * REST APIカスタムエンドポイント
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('rest_api_init', 'koi_ria_register_rest_routes');

function koi_ria_register_rest_routes(): void {
    // 投票API
    register_rest_route('koi-ria/v1', '/vote', [
        'methods'             => 'POST',
        'callback'            => 'koi_ria_rest_vote',
        'permission_callback' => '__return_true',
    ]);

    // 出演者検索API
    register_rest_route('koi-ria/v1', '/cast/search', [
        'methods'             => 'GET',
        'callback'            => 'koi_ria_rest_cast_search',
        'permission_callback' => '__return_true',
    ]);
}

/**
 * 投票API
 */
function koi_ria_rest_vote(WP_REST_Request $request): WP_REST_Response {
    $poll_id      = intval($request->get_param('poll_id'));
    $option_index = intval($request->get_param('option_index'));

    if (!$poll_id || !get_post($poll_id)) {
        return new WP_REST_Response(['success' => false, 'message' => '投票が見つかりません'], 404);
    }

    // Cookie重複チェック
    $cookie_name = 'koi_ria_voted_' . $poll_id;
    if (isset($_COOKIE[$cookie_name])) {
        return new WP_REST_Response(['success' => false, 'message' => '既に投票済みです'], 403);
    }

    $is_active = get_field('is_active', $poll_id);
    if (!$is_active) {
        return new WP_REST_Response(['success' => false, 'message' => 'この投票は終了しています'], 403);
    }

    $options = get_field('options', $poll_id) ?: [];
    if (!isset($options[$option_index])) {
        return new WP_REST_Response(['success' => false, 'message' => '無効な選択肢です'], 400);
    }

    // 投票数をインクリメント
    $options[$option_index]['option_votes'] = intval($options[$option_index]['option_votes']) + 1;
    update_field('options', $options, $poll_id);

    // 合計計算
    $total = 0;
    foreach ($options as $opt) {
        $total += intval($opt['option_votes']);
    }

    $results = [];
    foreach ($options as $opt) {
        $votes = intval($opt['option_votes']);
        $results[] = [
            'label' => $opt['option_label'],
            'votes' => $votes,
            'pct'   => $total > 0 ? round($votes / $total * 100) : 0,
        ];
    }

    // Cookie設定（30日）
    setcookie($cookie_name, '1', time() + 30 * DAY_IN_SECONDS, '/');

    return new WP_REST_Response(['success' => true, 'results' => $results]);
}

/**
 * 出演者検索API
 */
function koi_ria_rest_cast_search(WP_REST_Request $request): WP_REST_Response {
    $q = sanitize_text_field($request->get_param('q') ?: '');

    if (mb_strlen($q) < 1) {
        return new WP_REST_Response([]);
    }

    $casts = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => 20,
        's'              => $q,
    ]);

    $results = [];
    foreach ($casts as $cast) {
        $show_id = get_field('show', $cast->ID);
        $season_id = get_field('season', $cast->ID);

        $show_name = '';
        if ($show_id) {
            $show_post = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
            $show_name = $show_post ? (get_field('short_name', $show_post->ID) ?: $show_post->post_title) : '';
        }

        $season_name = '';
        if ($season_id) {
            $season_post = is_array($season_id) ? get_post($season_id[0]) : get_post($season_id);
            $season_name = $season_post ? (get_field('season_name', $season_post->ID) ?: $season_post->post_title) : '';
        }

        $results[] = [
            'id'      => $cast->ID,
            'name'    => get_field('display_name', $cast->ID) ?: $cast->post_title,
            'show'    => $show_name,
            'season'  => $season_name,
            'ig'      => get_field('ig_username', $cast->ID) ?: '',
            'url'     => get_permalink($cast),
        ];
    }

    return new WP_REST_Response($results);
}
