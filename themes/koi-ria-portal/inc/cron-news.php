<?php
/**
 * ニュース自動取得（RSS + WP-Cron）
 *
 * Phase4で本格実装予定。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    if (!wp_next_scheduled('koi_ria_fetch_news')) {
        wp_schedule_event(time(), 'hourly', 'koi_ria_fetch_news');
    }
});

add_action('koi_ria_fetch_news', 'koi_ria_news_fetch');

function koi_ria_news_fetch(): void {
    $feeds = get_option('koi_ria_news_feeds', []);
    if (empty($feeds)) {
        return;
    }

    foreach ($feeds as $feed_url => $show_name) {
        $rss = fetch_feed($feed_url);
        if (is_wp_error($rss)) {
            continue;
        }

        foreach ($rss->get_items(0, 10) as $item) {
            $title = sanitize_text_field($item->get_title());
            $link  = esc_url_raw($item->get_link());

            // 重複チェック（タイトルベース）
            $existing = get_posts([
                'post_type'      => 'post',
                's'              => $title,
                'posts_per_page' => 1,
            ]);
            if ($existing) {
                continue;
            }

            $post_id = wp_insert_post([
                'post_type'    => 'post',
                'post_title'   => $title,
                'post_status'  => 'draft',
                'post_content' => sprintf('元記事: %s', esc_url($link)),
            ]);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, 'is_auto_fetched', true);
                update_post_meta($post_id, 'source_url', $link);
                wp_set_object_terms($post_id, $show_name, 'post_tag', true);
            }
        }
    }
}
