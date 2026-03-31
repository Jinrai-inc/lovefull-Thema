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

    // VOD検索API
    register_rest_route('koi-ria/v1', '/vod-search', [
        'methods'             => 'GET',
        'callback'            => 'koi_ria_rest_vod_search',
        'permission_callback' => '__return_true',
    ]);

    // 推しフィードAPI
    register_rest_route('koi-ria/v1', '/favorites-feed', [
        'methods'             => 'GET',
        'callback'            => 'koi_ria_rest_favorites_feed',
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

    $options = koi_ria_parse_poll_options(get_field('options', $poll_id));
    if (!isset($options[$option_index])) {
        return new WP_REST_Response(['success' => false, 'message' => '無効な選択肢です'], 400);
    }

    // 投票数をインクリメント
    $options[$option_index]['option_votes'] = intval($options[$option_index]['option_votes']) + 1;

    // テキストエリア形式で保存し直す
    $lines = [];
    foreach ($options as $opt) {
        $lines[] = $opt['option_label'] . '|' . $opt['option_votes'];
    }
    update_field('options', implode("\n", $lines), $poll_id);

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

/**
 * VOD検索API — 番組名でshowを検索しVOD配信情報を返す
 */
function koi_ria_rest_vod_search(WP_REST_Request $request): WP_REST_Response {
    $q = sanitize_text_field($request->get_param('q') ?: '');

    if (mb_strlen($q) < 1) {
        return new WP_REST_Response([]);
    }

    // 番組名 or 略称でマッチ
    $shows = get_posts([
        'post_type'      => 'show',
        'posts_per_page' => 20,
        's'              => $q,
    ]);

    $results = [];
    foreach ($shows as $show) {
        $platform      = get_field('platform', $show->ID) ?: '';
        $vod_links     = get_field('vod_links', $show->ID) ?: [];
        $affiliate_url = get_field('affiliate_url', $show->ID) ?: '';
        $available     = get_field('available_vods', $show->ID) ?: [];

        $vods = [];

        // メインプラットフォーム
        if ($platform) {
            $vods[] = [
                'name'    => $platform,
                'url'     => $affiliate_url ?: '',
                'is_free' => false,
            ];
        }

        // VODリピーターフィールド
        if (is_array($vod_links)) {
            foreach ($vod_links as $vl) {
                $vod_name = $vl['vod_name'] ?? '';
                if ($vod_name && $vod_name !== $platform) {
                    $vods[] = [
                        'name'    => $vod_name,
                        'url'     => $vl['url'] ?? '',
                        'is_free' => !empty($vl['is_free']),
                    ];
                }
            }
        }

        $results[] = [
            'show_id'       => $show->ID,
            'title'         => get_field('short_name', $show->ID) ?: $show->post_title,
            'url'           => get_permalink($show),
            'platform'      => $platform,
            'available_vods' => $available,
            'vods'          => $vods,
        ];
    }

    return new WP_REST_Response($results);
}

/**
 * 推しフィードAPI — 推し登録されたcastに関連する記事を返す
 */
function koi_ria_rest_favorites_feed(WP_REST_Request $request): WP_REST_Response {
    $cast_ids_raw = sanitize_text_field($request->get_param('cast_ids') ?: '');

    if (empty($cast_ids_raw)) {
        return new WP_REST_Response([]);
    }

    $cast_ids = array_filter(array_map('intval', explode(',', $cast_ids_raw)));
    if (empty($cast_ids)) {
        return new WP_REST_Response([]);
    }

    // 出演者名からタグスラッグを収集
    $tag_slugs = [];
    foreach ($cast_ids as $cid) {
        $cast_post = get_post($cid);
        if (!$cast_post) continue;
        $display_name = get_field('display_name', $cid) ?: $cast_post->post_title;
        $tag_slugs[] = sanitize_title($display_name);
        $tag_slugs[] = sanitize_title($cast_post->post_title);
    }

    $tag_slugs = array_unique(array_filter($tag_slugs));
    if (empty($tag_slugs)) {
        return new WP_REST_Response([]);
    }

    // タグに一致する記事を取得
    $posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 20,
        'tax_query'      => [
            [
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => $tag_slugs,
            ],
        ],
        'orderby' => 'date',
        'order'   => 'DESC',
    ]);

    $results = [];
    foreach ($posts as $post) {
        $thumb = has_post_thumbnail($post) ? get_the_post_thumbnail_url($post, 'thumbnail') : '';
        $cats  = get_the_category($post->ID);
        $results[] = [
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'url'       => get_permalink($post),
            'date'      => get_the_date('Y.m.d', $post),
            'thumbnail' => $thumb,
            'category'  => $cats ? $cats[0]->name : '',
        ];
    }

    return new WP_REST_Response($results);
}
