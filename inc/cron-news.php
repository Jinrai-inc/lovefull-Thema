<?php
/**
 * ニュース自動取得（RSS + WP-Cron）
 *
 * Googleアラート等のRSSフィードから恋リア関連ニュースを自動取得。
 * - 下書きとして保存（管理者確認後に公開）
 * - タイトル類似度チェックによる重複防止
 * - 自動タグ付け
 * - Transientキャッシュ（1時間）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// Cron登録
add_action('init', function () {
    if (!wp_next_scheduled('koi_ria_fetch_news')) {
        wp_schedule_event(time(), 'hourly', 'koi_ria_fetch_news');
    }
});

add_action('koi_ria_fetch_news', 'koi_ria_news_fetch');

// 手動実行
add_action('admin_post_koi_ria_manual_news', function () {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    check_admin_referer('koi_ria_manual_cron');

    $result = koi_ria_news_fetch();

    set_transient('koi_ria_admin_notice', [
        'type'    => 'success',
        'message' => "ニュース取得完了: 新規 {$result['created']}件 / スキップ {$result['skipped']}件 / エラー {$result['errors']}件",
    ], 30);

    wp_redirect(admin_url('admin.php?page=koi-ria-cron'));
    exit;
});

/**
 * ニュース取得メイン処理
 */
function koi_ria_news_fetch(): array {
    $summary = ['created' => 0, 'skipped' => 0, 'errors' => 0];

    $feeds = get_option('koi_ria_news_feeds', []);
    if (empty($feeds)) {
        return $summary;
    }

    // フィードがJSON文字列の場合をハンドリング
    if (is_string($feeds)) {
        $feeds = json_decode($feeds, true) ?: [];
    }

    foreach ($feeds as $feed_url => $config) {
        // 設定が文字列の場合（後方互換）
        if (is_string($config)) {
            $show_name = $config;
            $category  = '';
        } else {
            $show_name = $config['show_name'] ?? '';
            $category  = $config['category'] ?? '';
        }

        // Transientキャッシュ（1時間）
        $cache_key = 'koi_ria_rss_' . md5($feed_url);
        if (get_transient($cache_key) !== false) {
            continue; // 最近取得済み
        }

        $rss = fetch_feed(esc_url_raw($feed_url));
        if (is_wp_error($rss)) {
            koi_ria_log('News', "RSS取得エラー ({$show_name}): " . $rss->get_error_message());
            $summary['errors']++;
            continue;
        }

        set_transient($cache_key, true, HOUR_IN_SECONDS);

        $items = $rss->get_items(0, 10);

        foreach ($items as $item) {
            $title = sanitize_text_field($item->get_title());
            $link  = esc_url_raw($item->get_link());
            $date  = $item->get_date('Y-m-d H:i:s') ?: current_time('mysql');

            if (!$title || !$link) {
                continue;
            }

            // 重複チェック: source_url メタで一致するものがあるか
            $existing_by_url = get_posts([
                'post_type'      => 'post',
                'meta_query'     => [
                    ['key' => 'source_url', 'value' => $link, 'compare' => '='],
                ],
                'posts_per_page' => 1,
                'post_status'    => 'any',
            ]);

            if ($existing_by_url) {
                $summary['skipped']++;
                continue;
            }

            // タイトル類似チェック（完全一致）
            $existing_by_title = get_posts([
                'post_type'      => 'post',
                'title'          => $title,
                'posts_per_page' => 1,
                'post_status'    => 'any',
            ]);

            if ($existing_by_title) {
                $summary['skipped']++;
                continue;
            }

            // 下書きとして保存
            $post_data = [
                'post_type'    => 'post',
                'post_title'   => $title,
                'post_status'  => 'draft',
                'post_date'    => $date,
                'post_content' => sprintf(
                    '<p>この記事は外部ソースから自動取得されました。</p><p>元記事: <a href="%s" target="_blank" rel="noopener nofollow">%s</a></p>',
                    esc_url($link),
                    esc_html($title)
                ),
            ];

            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                koi_ria_log('News', "投稿作成エラー: " . $post_id->get_error_message());
                $summary['errors']++;
                continue;
            }

            // メタデータ
            update_post_meta($post_id, 'is_auto_fetched', true);
            update_post_meta($post_id, 'source_url', $link);

            // タグ付け（番組名）
            if ($show_name) {
                wp_set_object_terms($post_id, $show_name, 'post_tag', true);
            }

            // カテゴリ（指定がある場合）
            if ($category) {
                $cat = get_cat_ID($category);
                if ($cat) {
                    wp_set_post_categories($post_id, [$cat], true);
                }
            }

            // 自動タグ付け（タイトルに番組名が含まれる場合）
            koi_ria_auto_tag_news($post_id, $title);

            $summary['created']++;
            koi_ria_log('News', "新規取得(下書き): {$title}");
        }
    }

    update_option('koi_ria_news_last_run', [
        'time'    => current_time('mysql'),
        'summary' => $summary,
    ]);

    return $summary;
}

/**
 * ニュースの自動タグ付け
 *
 * タイトルに番組名・略称が含まれる場合にタグを付与
 */
function koi_ria_auto_tag_news(int $post_id, string $title): void {
    $shows = get_posts(['post_type' => 'show', 'posts_per_page' => -1]);

    foreach ($shows as $show) {
        $names = array_filter([
            $show->post_title,
            get_field('short_name', $show->ID),
        ]);

        foreach ($names as $name) {
            if (mb_strpos($title, $name) !== false) {
                wp_set_object_terms($post_id, $name, 'post_tag', true);
                break; // 1番組1タグのみ
            }
        }
    }

    // 速報キーワード検知
    $breaking_keywords = ['速報', '緊急', 'カップル成立', '破局', '結婚', '妊娠'];
    foreach ($breaking_keywords as $keyword) {
        if (mb_strpos($title, $keyword) !== false) {
            wp_set_object_terms($post_id, 'breaking', 'post_tag', true);
            break;
        }
    }
}
