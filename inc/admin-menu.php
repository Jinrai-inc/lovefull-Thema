<?php
/**
 * 管理画面メニュー追加
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('admin_menu', 'koi_ria_admin_menus');

function koi_ria_admin_menus(): void {
    add_menu_page(
        '恋リアデータ管理',
        'データ管理',
        'manage_options',
        'koi-ria-import',
        'koi_ria_import_page',
        'dashicons-upload',
        30
    );

    add_submenu_page(
        'koi-ria-import',
        'はじめに',
        'はじめに',
        'manage_options',
        'koi-ria-guide',
        'koi_ria_guide_page'
    );

    add_submenu_page(
        'koi-ria-import',
        'CSVインポート',
        'CSVインポート',
        'manage_options',
        'koi-ria-import',
        'koi_ria_import_page'
    );

    add_submenu_page(
        'koi-ria-import',
        'API設定',
        'API設定',
        'manage_options',
        'koi-ria-settings',
        'koi_ria_settings_page'
    );

    add_submenu_page(
        'koi-ria-import',
        '自動取得管理',
        '自動取得管理',
        'manage_options',
        'koi-ria-cron',
        'koi_ria_cron_page'
    );

    add_submenu_page(
        'koi-ria-import',
        '広告設定',
        '広告設定',
        'manage_options',
        'koi-ria-ads',
        'koi_ria_ads_page'
    );

    add_submenu_page(
        'koi-ria-import',
        '人気記事設定',
        '人気記事設定',
        'manage_options',
        'koi-ria-popular',
        'koi_ria_popular_page'
    );

    add_submenu_page(
        'koi-ria-import',
        '表示設定',
        '表示設定',
        'manage_options',
        'koi-ria-display',
        'koi_ria_display_page'
    );
}

/**
 * はじめにガイドページ
 */
function koi_ria_guide_page(): void {
    $guide_page = KOI_RIA_DIR . '/admin/getting-started.php';
    if (file_exists($guide_page)) {
        require_once $guide_page;
    }
}

// 管理画面通知
add_action('admin_notices', function () {
    $notice = get_transient('koi_ria_admin_notice');
    if (!$notice) return;
    delete_transient('koi_ria_admin_notice');
    $type = $notice['type'] ?? 'info';
    $message = $notice['message'] ?? '';
    echo "<div class='notice notice-{$type} is-dismissible'><p>" . esc_html($message) . "</p></div>";
});

/**
 * CSVインポートページ
 */
function koi_ria_import_page(): void {
    $import_page = KOI_RIA_DIR . '/admin/csv-import-page.php';
    if (file_exists($import_page)) {
        require_once $import_page;
    }
}

/**
 * API設定ページ
 */
function koi_ria_settings_page(): void {
    if (isset($_POST['koi_ria_settings_nonce']) && wp_verify_nonce($_POST['koi_ria_settings_nonce'], 'koi_ria_save_settings')) {
        update_option('koi_ria_youtube_api_key', sanitize_text_field($_POST['youtube_api_key'] ?? ''));
        $avatar_cache_days = intval($_POST['avatar_cache_days'] ?? 7);
        $avatar_cache_days = max(1, min(30, $avatar_cache_days));
        update_option('koi_ria_avatar_cache_days', $avatar_cache_days);
        update_option('koi_ria_unavatar_api_key', sanitize_text_field($_POST['unavatar_api_key'] ?? ''));

        // スライダー表示件数（1〜30、デフォルト10）
        $slider_max = intval($_POST['slider_max_slides'] ?? 10);
        $slider_max = max(1, min(30, $slider_max));
        update_option('koi_ria_slider_max_slides', $slider_max);

        // ニュースフィード設定
        $feeds_raw = sanitize_textarea_field($_POST['news_feeds'] ?? '');
        $feeds = [];
        foreach (explode("\n", $feeds_raw) as $line) {
            $line = trim($line);
            if (!$line || strpos($line, '|') === false) continue;
            $parts = array_map('trim', explode('|', $line, 3));
            if (count($parts) >= 2 && filter_var($parts[0], FILTER_VALIDATE_URL)) {
                $feeds[$parts[0]] = [
                    'show_name' => $parts[1],
                    'category'  => $parts[2] ?? '',
                ];
            }
        }
        update_option('koi_ria_news_feeds', $feeds);

        echo '<div class="notice notice-success"><p>設定を保存しました。</p></div>';
    }

    $youtube_key    = get_option('koi_ria_youtube_api_key', '');
    $avatar_cache_days  = get_option('koi_ria_avatar_cache_days', 7);
    $unavatar_api_key   = get_option('koi_ria_unavatar_api_key', '');
    $slider_max     = get_option('koi_ria_slider_max_slides', 10);
    $feeds       = get_option('koi_ria_news_feeds', []);

    // フィードをテキスト形式に変換
    $feeds_text = '';
    if (is_array($feeds)) {
        foreach ($feeds as $url => $config) {
            if (is_string($config)) {
                $feeds_text .= "{$url}|{$config}\n";
            } else {
                $show_name = $config['show_name'] ?? '';
                $category  = $config['category'] ?? '';
                $feeds_text .= "{$url}|{$show_name}" . ($category ? "|{$category}" : '') . "\n";
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>API設定</h1>
        <form method="post">
            <?php wp_nonce_field('koi_ria_save_settings', 'koi_ria_settings_nonce'); ?>

            <h2 class="title">YouTube Data API</h2>
            <table class="form-table">
                <tr>
                    <th><label for="youtube_api_key">API Key</label></th>
                    <td>
                        <input type="text" id="youtube_api_key" name="youtube_api_key" value="<?php echo esc_attr($youtube_key); ?>" class="regular-text">
                        <p class="description">YouTube Data API v3のAPIキー（無料枠: 10,000 units/日）</p>
                    </td>
                </tr>
            </table>

            <h2 class="title">動画スライダー設定</h2>
            <table class="form-table">
                <tr>
                    <th><label for="slider_max_slides">スライダー表示件数</label></th>
                    <td>
                        <input type="number" id="slider_max_slides" name="slider_max_slides" value="<?php echo esc_attr($slider_max); ?>" min="1" max="30" step="1" style="width: 80px;">
                        <span> 件（1〜30）</span>
                        <p class="description">トップページのYouTube動画スライダーに表示する最大動画数です。チャンネル別最新動画＋ピン留め動画の合計がこの件数に制限されます。</p>
                    </td>
                </tr>
            </table>

            <h2 class="title">IGアバター設定（unavatar.io）</h2>
            <table class="form-table">
                <tr>
                    <th><label for="avatar_cache_days">キャッシュ有効期間</label></th>
                    <td>
                        <input type="number" id="avatar_cache_days" name="avatar_cache_days" value="<?php echo esc_attr($avatar_cache_days); ?>" min="1" max="30" step="1" style="width: 80px;">
                        <span> 日（1〜30）</span>
                        <p class="description">unavatar.io経由で取得したプロフィール画像のローカルキャッシュ有効日数。週1回のCronで自動更新されます。</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="unavatar_api_key">unavatar.io APIキー</label></th>
                    <td>
                        <input type="text" id="unavatar_api_key" name="unavatar_api_key" value="<?php echo esc_attr($unavatar_api_key); ?>" class="regular-text">
                        <p class="description">
                            有料プラン（$9/月）のAPIキー。設定するとリクエスト制限が解除されます。<br>
                            空欄の場合は無料プラン（1日50リクエスト）で動作します。<br>
                            取得: <a href="https://unavatar.io/#pricing" target="_blank">unavatar.io → Pricing</a>
                        </p>
                    </td>
                </tr>
            </table>

            <h2 class="title">ニュースフィード（RSS）</h2>
            <table class="form-table">
                <tr>
                    <th><label for="news_feeds">RSSフィード一覧</label></th>
                    <td>
                        <textarea id="news_feeds" name="news_feeds" rows="8" class="large-text code"><?php echo esc_textarea(trim($feeds_text)); ?></textarea>
                        <p class="description">
                            1行1フィード: <code>URL|番組名|カテゴリ(任意)</code><br>
                            例: <code>https://www.google.com/alerts/feeds/xxx|今日好き|ニュース</code>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button('設定を保存'); ?>
        </form>
    </div>
    <?php
}

/**
 * 自動取得管理ページ（Cronダッシュボード）
 */
function koi_ria_cron_page(): void {
    $yt_last    = get_option('koi_ria_youtube_last_run', null);
    $news_last  = get_option('koi_ria_news_last_run', null);
    $avatar_last = get_option('koi_ria_avatar_last_run', null);

    // Cronの次回実行時刻
    $yt_next     = wp_next_scheduled('koi_ria_fetch_youtube');
    $news_next   = wp_next_scheduled('koi_ria_fetch_news');
    $avatar_next = wp_next_scheduled('koi_ria_refresh_all_avatars');

    // ログ取得
    $logs = get_option('koi_ria_cron_log', []);
    $logs = array_slice($logs, -50); // 最新50件

    $nonce_url = wp_nonce_url('', 'koi_ria_manual_cron');
    ?>
    <div class="wrap">
        <h1>自動取得管理</h1>

        <h2>ステータス</h2>
        <table class="widefat striped" style="max-width: 900px;">
            <thead>
                <tr>
                    <th>処理</th>
                    <th>スケジュール</th>
                    <th>最終実行</th>
                    <th>結果</th>
                    <th>次回</th>
                    <th>手動実行</th>
                </tr>
            </thead>
            <tbody>
                <?php // YouTube ?>
                <tr>
                    <td><strong>YouTube動画取得</strong></td>
                    <td>1日2回</td>
                    <td><?php echo $yt_last ? esc_html($yt_last['time']) : '—'; ?></td>
                    <td>
                        <?php if ($yt_last && isset($yt_last['summary'])) :
                            $s = $yt_last['summary']; ?>
                            新規: <?php echo intval($s['created']); ?> /
                            スキップ: <?php echo intval($s['skipped']); ?> /
                            エラー: <?php echo intval($s['errors']); ?>
                        <?php else : ?>—<?php endif; ?>
                    </td>
                    <td><?php echo $yt_next ? esc_html(date('Y-m-d H:i', $yt_next)) : '未設定'; ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin-post.php?action=koi_ria_manual_youtube&_wpnonce=' . wp_create_nonce('koi_ria_manual_cron'))); ?>" class="button button-secondary">実行</a>
                    </td>
                </tr>
                <?php // ニュース ?>
                <tr>
                    <td><strong>ニュース取得</strong></td>
                    <td>1時間ごと</td>
                    <td><?php echo $news_last ? esc_html($news_last['time']) : '—'; ?></td>
                    <td>
                        <?php if ($news_last && isset($news_last['summary'])) :
                            $s = $news_last['summary']; ?>
                            新規: <?php echo intval($s['created']); ?> /
                            スキップ: <?php echo intval($s['skipped']); ?> /
                            エラー: <?php echo intval($s['errors']); ?>
                        <?php else : ?>—<?php endif; ?>
                    </td>
                    <td><?php echo $news_next ? esc_html(date('Y-m-d H:i', $news_next)) : '未設定'; ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin-post.php?action=koi_ria_manual_news&_wpnonce=' . wp_create_nonce('koi_ria_manual_cron'))); ?>" class="button button-secondary">実行</a>
                    </td>
                </tr>
                <?php // IGアバター ?>
                <tr>
                    <td><strong>IGアバター更新</strong><br><small style="color:#666;">unavatar.io経由</small></td>
                    <td>週1回</td>
                    <td><?php echo $avatar_last ? esc_html($avatar_last['time']) : '—'; ?></td>
                    <td>
                        <?php if ($avatar_last && isset($avatar_last['summary'])) :
                            $s = $avatar_last['summary']; ?>
                            更新: <?php echo intval($s['updated']); ?> /
                            スキップ: <?php echo intval($s['skipped']); ?> /
                            エラー: <?php echo intval($s['errors']); ?>
                        <?php else : ?>—<?php endif; ?>
                        <?php
                        // キャッシュ統計
                        $cache_dir = wp_upload_dir()['basedir'] . '/ig-cache/';
                        $cached_count = file_exists($cache_dir) ? count(glob($cache_dir . '*.jpg')) : 0;
                        $total_cast = wp_count_posts('cast')->publish ?? 0;
                        echo "<br><small>キャッシュ済み: {$cached_count}件 / 全出演者: {$total_cast}件</small>";
                        ?>
                    </td>
                    <td><?php echo $avatar_next ? esc_html(date('Y-m-d H:i', $avatar_next)) : '未設定'; ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin-post.php?action=koi_ria_manual_avatar_refresh&_wpnonce=' . wp_create_nonce('koi_ria_manual_cron'))); ?>" class="button button-secondary">実行</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <h2 style="margin-top: 30px;">実行ログ（最新50件）</h2>
        <?php if ($logs) : ?>
        <table class="widefat striped" style="max-width: 900px;">
            <thead>
                <tr>
                    <th style="width:140px;">日時</th>
                    <th style="width:100px;">ソース</th>
                    <th>メッセージ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($logs) as $log) : ?>
                <tr>
                    <td><code><?php echo esc_html($log['time'] ?? ''); ?></code></td>
                    <td><strong><?php echo esc_html($log['source'] ?? ''); ?></strong></td>
                    <td><?php echo esc_html($log['message'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p>ログはまだありません。</p>
        <?php endif; ?>

        <?php if ($logs) : ?>
        <form method="post" style="margin-top: 10px;">
            <?php wp_nonce_field('koi_ria_clear_log'); ?>
            <input type="hidden" name="koi_ria_clear_log" value="1">
            <button type="submit" class="button">ログをクリア</button>
        </form>
        <?php
            if (isset($_POST['koi_ria_clear_log']) && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'koi_ria_clear_log')) {
                update_option('koi_ria_cron_log', []);
                echo '<script>location.reload();</script>';
            }
        ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * 広告設定ページ
 */
function koi_ria_ads_page(): void {
    if (isset($_POST['koi_ria_ads_nonce']) && wp_verify_nonce($_POST['koi_ria_ads_nonce'], 'koi_ria_save_ads')) {
        // AdSense設定
        update_option('koi_ria_adsense_client_id', sanitize_text_field($_POST['adsense_client_id'] ?? ''));
        update_option('koi_ria_adsense_slot_top', sanitize_text_field($_POST['adsense_slot_top'] ?? ''));
        update_option('koi_ria_adsense_slot_article', sanitize_text_field($_POST['adsense_slot_article'] ?? ''));
        update_option('koi_ria_adsense_slot_sidebar', sanitize_text_field($_POST['adsense_slot_sidebar'] ?? ''));

        // アフィリエイトバナー設定
        $banners = [];
        $names   = $_POST['affiliate_name'] ?? [];
        $urls    = $_POST['affiliate_url'] ?? [];
        $ctas    = $_POST['affiliate_cta'] ?? [];
        $colors  = $_POST['affiliate_color'] ?? [];

        for ($i = 0; $i < count($names); $i++) {
            $name = sanitize_text_field($names[$i] ?? '');
            $url  = esc_url_raw($urls[$i] ?? '');
            if ($name && $url) {
                $banners[] = [
                    'name'  => $name,
                    'url'   => $url,
                    'cta'   => sanitize_text_field($ctas[$i] ?? ''),
                    'color' => sanitize_text_field($colors[$i] ?? ''),
                ];
            }
        }
        update_option('koi_ria_affiliate_banners', $banners);

        echo '<div class="notice notice-success"><p>広告設定を保存しました。</p></div>';
    }

    $adsense_client  = get_option('koi_ria_adsense_client_id', '');
    $slot_top        = get_option('koi_ria_adsense_slot_top', '');
    $slot_article    = get_option('koi_ria_adsense_slot_article', '');
    $slot_sidebar    = get_option('koi_ria_adsense_slot_sidebar', '');
    $banners         = get_option('koi_ria_affiliate_banners', []);

    // デフォルトバナー
    if (empty($banners)) {
        $banners = [
            ['name' => 'ABEMAプレミアム', 'url' => '', 'cta' => '2週間無料でお試し', 'color' => 'linear-gradient(135deg, #00B900, #00D900)'],
            ['name' => 'Netflix', 'url' => '', 'cta' => '今すぐ視聴する', 'color' => 'linear-gradient(135deg, #E50914, #B20710)'],
            ['name' => 'Amazonプライム', 'url' => '', 'cta' => '30日間無料体験', 'color' => 'linear-gradient(135deg, #00A8E1, #0077B5)'],
        ];
    }
    ?>
    <div class="wrap">
        <h1>広告設定</h1>
        <form method="post">
            <?php wp_nonce_field('koi_ria_save_ads', 'koi_ria_ads_nonce'); ?>

            <h2 class="title">Google AdSense</h2>
            <table class="form-table">
                <tr>
                    <th><label for="adsense_client_id">パブリッシャーID</label></th>
                    <td>
                        <input type="text" id="adsense_client_id" name="adsense_client_id" value="<?php echo esc_attr($adsense_client); ?>" class="regular-text" placeholder="ca-pub-XXXXXXXXXXXXXXXX">
                        <p class="description">AdSenseのパブリッシャーID（例: ca-pub-1234567890123456）</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="adsense_slot_top">トップページ広告スロット</label></th>
                    <td>
                        <input type="text" id="adsense_slot_top" name="adsense_slot_top" value="<?php echo esc_attr($slot_top); ?>" class="regular-text" placeholder="1234567890">
                    </td>
                </tr>
                <tr>
                    <th><label for="adsense_slot_article">記事内広告スロット</label></th>
                    <td>
                        <input type="text" id="adsense_slot_article" name="adsense_slot_article" value="<?php echo esc_attr($slot_article); ?>" class="regular-text" placeholder="1234567890">
                    </td>
                </tr>
                <tr>
                    <th><label for="adsense_slot_sidebar">サイドバー広告スロット</label></th>
                    <td>
                        <input type="text" id="adsense_slot_sidebar" name="adsense_slot_sidebar" value="<?php echo esc_attr($slot_sidebar); ?>" class="regular-text" placeholder="1234567890">
                    </td>
                </tr>
            </table>

            <h2 class="title">アフィリエイトバナー</h2>
            <p class="description">トップページ下部やサイドバーに表示するアフィリエイトバナーを設定します。</p>

            <table class="widefat striped" style="max-width:900px; margin-top:10px;" id="affiliate-banners">
                <thead>
                    <tr>
                        <th>サービス名</th>
                        <th>URL</th>
                        <th>CTAテキスト</th>
                        <th>背景グラデーション</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $i => $banner) : ?>
                    <tr>
                        <td><input type="text" name="affiliate_name[]" value="<?php echo esc_attr($banner['name']); ?>" class="regular-text"></td>
                        <td><input type="url" name="affiliate_url[]" value="<?php echo esc_attr($banner['url']); ?>" class="regular-text" placeholder="https://..."></td>
                        <td><input type="text" name="affiliate_cta[]" value="<?php echo esc_attr($banner['cta']); ?>" class="regular-text"></td>
                        <td><input type="text" name="affiliate_color[]" value="<?php echo esc_attr($banner['color']); ?>" class="regular-text" placeholder="linear-gradient(135deg, #xxx, #yyy)"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button" onclick="addBannerRow()">+ バナーを追加</button>
            </p>

            <?php submit_button('広告設定を保存'); ?>
        </form>
    </div>
    <script>
    function addBannerRow() {
        var tbody = document.querySelector('#affiliate-banners tbody');
        var row = document.createElement('tr');
        row.innerHTML = '<td><input type="text" name="affiliate_name[]" class="regular-text"></td>' +
            '<td><input type="url" name="affiliate_url[]" class="regular-text" placeholder="https://..."></td>' +
            '<td><input type="text" name="affiliate_cta[]" class="regular-text"></td>' +
            '<td><input type="text" name="affiliate_color[]" class="regular-text" placeholder="linear-gradient(135deg, #xxx, #yyy)"></td>';
        tbody.appendChild(row);
    }
    </script>
    <?php
}

/**
 * 人気記事設定ページ
 */
function koi_ria_popular_page(): void {
    // 保存処理
    if (isset($_POST['koi_ria_popular_nonce']) && wp_verify_nonce($_POST['koi_ria_popular_nonce'], 'koi_ria_save_popular')) {
        $mode  = in_array($_POST['popular_mode'] ?? '', ['auto', 'manual'], true) ? $_POST['popular_mode'] : 'auto';
        $count = max(1, min(10, intval($_POST['popular_count'] ?? 3)));

        $priority_cat_id = intval($_POST['priority_cat_id'] ?? 0);

        $exclude_cats = [];
        if (!empty($_POST['exclude_cats']) && is_array($_POST['exclude_cats'])) {
            $exclude_cats = array_map('intval', $_POST['exclude_cats']);
        }

        $manual_ids = [];
        if (!empty($_POST['manual_post_ids'])) {
            $raw_ids = array_map('intval', explode(',', sanitize_text_field($_POST['manual_post_ids'])));
            foreach ($raw_ids as $pid) {
                if ($pid > 0 && get_post_type($pid) === 'post') {
                    $manual_ids[] = $pid;
                }
            }
        }

        $settings = [
            'mode'            => $mode,
            'count'           => $count,
            'priority_cat_id' => $priority_cat_id,
            'exclude_cats'    => $exclude_cats,
            'manual_post_ids' => $manual_ids,
        ];
        update_option('koi_ria_popular_settings', $settings);
        echo '<div class="notice notice-success"><p>人気記事設定を保存しました。</p></div>';
    }

    $settings = get_option('koi_ria_popular_settings', []);
    $mode            = $settings['mode'] ?? 'auto';
    $count           = $settings['count'] ?? 3;
    $priority_cat_id = $settings['priority_cat_id'] ?? 0;
    $exclude_cats    = $settings['exclude_cats'] ?? [];
    $manual_ids      = $settings['manual_post_ids'] ?? [];

    // GA同期ステータス
    $ga_status   = get_option('koi_ria_ga_sync_status', '');
    $ga_time     = get_option('koi_ria_ga_sync_time', '');
    $ga_pv_map   = get_option('koi_ria_ga_popular_pv_map', []);
    $ga_debug    = get_option('koi_ria_ga_sync_debug', []);
    $ga_post_ids = get_option('koi_ria_ga_popular_post_ids', []);

    // カテゴリ一覧
    $categories = get_categories(['hide_empty' => false]);

    // 手動選択中の記事情報
    $manual_posts = [];
    if (!empty($manual_ids)) {
        $manual_posts = get_posts([
            'post_type'      => 'post',
            'post__in'       => $manual_ids,
            'orderby'        => 'post__in',
            'posts_per_page' => count($manual_ids),
            'post_status'    => 'any',
        ]);
    }
    ?>
    <style>
    .koi-popular-wrap { max-width: 900px; }
    .koi-mode-box { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
    .koi-mode-box.is-active { border-color: #e8619a; border-width: 2px; }
    .koi-mode-box h3 { margin: 0 0 10px; }
    .koi-mode-box .mode-radio { margin-right: 8px; }
    .koi-manual-list { margin: 16px 0; min-height: 50px; }
    .koi-manual-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 6px; }
    .koi-manual-item .drag-handle { cursor: grab; color: #999; font-size: 18px; }
    .koi-manual-item .item-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 3px; background: #eee; }
    .koi-manual-item .item-info { flex: 1; }
    .koi-manual-item .item-title { font-weight: 600; font-size: 14px; }
    .koi-manual-item .item-meta { color: #666; font-size: 12px; margin-top: 2px; }
    .koi-manual-item .item-remove { color: #d63638; cursor: pointer; border: none; background: none; font-size: 18px; }
    .koi-search-box { margin-top: 12px; display: flex; gap: 8px; }
    .koi-search-box input { flex: 1; }
    .koi-search-results { max-height: 240px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; margin-top: 8px; display: none; }
    .koi-search-results .search-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
    .koi-search-results .search-item:hover { background: #fff0f5; }
    .koi-search-results .search-item img { width: 50px; height: 34px; object-fit: cover; border-radius: 3px; }
    .koi-search-results .search-item .s-title { font-size: 13px; font-weight: 500; }
    .koi-search-results .search-item .s-date { font-size: 11px; color: #888; }
    .koi-ga-status { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; font-size: 13px; }
    .koi-ga-status.ok { background: #edfaef; border: 1px solid #b8e6c0; }
    .koi-ga-status.ng { background: #fef7e8; border: 1px solid #e6d5a8; }
    .koi-ga-box { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
    .koi-ga-box h3 { margin: 0 0 12px; }
    .koi-sync-result { margin-top: 12px; padding: 12px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; display: none; }
    .koi-sync-result pre { background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-size: 12px; overflow-x: auto; max-height: 300px; overflow-y: auto; white-space: pre-wrap; }
    .koi-pv-table { font-size: 13px; }
    .koi-pv-table td, .koi-pv-table th { padding: 6px 10px; }
    </style>

    <div class="wrap koi-popular-wrap">
        <h1>人気記事設定</h1>
        <p>トップページの「人気記事トップN」セクションの表示方法を設定します。</p>

        <!-- GA同期パネル -->
        <div class="koi-ga-box">
            <h3>Google Analytics 連携ステータス</h3>

            <?php if ($ga_status === 'ok' && $ga_time) : ?>
            <div class="koi-ga-status ok">
                <strong>同期済み</strong> — <?php echo esc_html($ga_time); ?>（<?php echo count($ga_pv_map); ?>記事マッチ）
            </div>
            <?php elseif (class_exists('Google\Site_Kit\Plugin')) : ?>
            <div class="koi-ga-status ng">
                <strong><?php echo $ga_status ? esc_html($ga_status) : '未同期'; ?></strong>
                — 管理画面を読み込むと6時間ごとに自動同期します
            </div>
            <?php else : ?>
            <div class="koi-ga-status ng">
                Site Kit プラグインが未インストールです。自動モードではAJAX閲覧数カウンターを使用します。
            </div>
            <?php endif; ?>

            <p>
                <button type="button" class="button button-primary" id="syncGaBtn">今すぐGA同期を実行</button>
                <span id="syncSpinner" class="spinner" style="float:none; vertical-align:middle;"></span>
            </p>

            <div class="koi-sync-result" id="syncResult"></div>

            <?php if (!empty($ga_pv_map)) : ?>
            <h4 style="margin-top: 16px;">現在の同期データ（上位<?php echo count($ga_pv_map); ?>記事）</h4>
            <table class="widefat striped koi-pv-table" style="max-width: 700px;">
                <thead><tr><th>順位</th><th>記事タイトル</th><th>PV数</th></tr></thead>
                <tbody>
                    <?php
                    arsort($ga_pv_map);
                    $rank = 1;
                    foreach ($ga_pv_map as $pid => $pv) :
                        $post_obj = get_post($pid);
                        if (!$post_obj) continue;
                    ?>
                    <tr>
                        <td><?php echo $rank++; ?></td>
                        <td><a href="<?php echo esc_url(get_edit_post_link($pid)); ?>"><?php echo esc_html($post_obj->post_title); ?></a></td>
                        <td><?php echo number_format($pv); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if (!empty($ga_debug['debug'])) : ?>
            <details style="margin-top: 12px;">
                <summary style="cursor: pointer; color: #666;">前回の同期デバッグログを表示</summary>
                <pre style="background: #f0f0f1; padding: 10px; font-size: 12px; margin-top: 6px; overflow-x: auto;"><?php
                    foreach ($ga_debug['debug'] as $line) {
                        echo esc_html($line) . "\n";
                    }
                ?></pre>
            </details>
            <?php endif; ?>
        </div>

        <form method="post" id="popularForm">
            <?php wp_nonce_field('koi_ria_save_popular', 'koi_ria_popular_nonce'); ?>

            <!-- 表示件数 -->
            <table class="form-table">
                <tr>
                    <th><label for="popular_count">表示件数</label></th>
                    <td>
                        <input type="number" id="popular_count" name="popular_count" value="<?php echo esc_attr($count); ?>" min="1" max="10" style="width: 70px;">
                        <span>件（1〜10）</span>
                    </td>
                </tr>
            </table>

            <!-- モード選択 -->
            <div class="koi-mode-box <?php echo $mode === 'auto' ? 'is-active' : ''; ?>" id="modeAuto">
                <h3>
                    <label>
                        <input type="radio" name="popular_mode" value="auto" class="mode-radio" <?php checked($mode, 'auto'); ?>>
                        自動モード（GA / 閲覧数ベース）
                    </label>
                </h3>
                <p style="margin: 0 0 12px; color: #666;">Google Analytics のPVデータまたはAJAX閲覧数カウンターから自動でランキングを生成します。</p>

                <table class="form-table" style="margin: 0;">
                    <tr>
                        <th><label for="priority_cat_id">優先カテゴリ</label></th>
                        <td>
                            <select id="priority_cat_id" name="priority_cat_id">
                                <option value="0">なし</option>
                                <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($priority_cat_id, $cat->term_id); ?>>
                                    <?php echo esc_html($cat->name); ?>（<?php echo esc_html($cat->slug); ?> / ID:<?php echo $cat->term_id; ?>）
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">このカテゴリの記事が優先的に上位表示されます</p>
                        </td>
                    </tr>
                    <tr>
                        <th>除外カテゴリ</th>
                        <td>
                            <?php foreach ($categories as $cat) : ?>
                            <label style="display: inline-block; margin-right: 14px; margin-bottom: 4px;">
                                <input type="checkbox" name="exclude_cats[]" value="<?php echo esc_attr($cat->term_id); ?>" <?php checked(in_array($cat->term_id, $exclude_cats)); ?>>
                                <?php echo esc_html($cat->name); ?>
                            </label>
                            <?php endforeach; ?>
                            <p class="description">チェックしたカテゴリの記事はランキングに含まれません</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="koi-mode-box <?php echo $mode === 'manual' ? 'is-active' : ''; ?>" id="modeManual">
                <h3>
                    <label>
                        <input type="radio" name="popular_mode" value="manual" class="mode-radio" <?php checked($mode, 'manual'); ?>>
                        手動モード（記事を自由に選択）
                    </label>
                </h3>
                <p style="margin: 0 0 12px; color: #666;">表示する記事と順番を手動で設定します。ドラッグで並び替え可能です。</p>

                <input type="hidden" name="manual_post_ids" id="manualPostIds" value="<?php echo esc_attr(implode(',', $manual_ids)); ?>">

                <div class="koi-manual-list" id="manualList">
                    <?php if (empty($manual_posts)) : ?>
                        <p style="color: #999; text-align: center; padding: 20px;" id="emptyMsg">記事が選択されていません。下の検索から追加してください。</p>
                    <?php else : ?>
                        <?php foreach ($manual_posts as $mp) :
                            $thumb = get_the_post_thumbnail_url($mp, 'thumbnail') ?: '';
                            $cats  = get_the_category($mp->ID);
                            $cat_label = $cats ? $cats[0]->name : '';
                            $pv = (int) get_post_meta($mp->ID, 'post_views_count', true);
                        ?>
                        <div class="koi-manual-item" data-id="<?php echo esc_attr($mp->ID); ?>">
                            <span class="drag-handle">&#9776;</span>
                            <?php if ($thumb) : ?>
                                <img src="<?php echo esc_url($thumb); ?>" class="item-thumb" alt="">
                            <?php else : ?>
                                <span class="item-thumb"></span>
                            <?php endif; ?>
                            <div class="item-info">
                                <div class="item-title"><?php echo esc_html($mp->post_title); ?></div>
                                <div class="item-meta">
                                    ID:<?php echo $mp->ID; ?>
                                    <?php if ($cat_label) echo ' / ' . esc_html($cat_label); ?>
                                    <?php if ($pv) echo ' / ' . number_format($pv) . ' PV'; ?>
                                    / <?php echo get_the_date('Y-m-d', $mp); ?>
                                </div>
                            </div>
                            <button type="button" class="item-remove" onclick="removeManualItem(this)" title="削除">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="koi-search-box">
                    <input type="text" id="postSearch" class="regular-text" placeholder="記事タイトルで検索...">
                    <button type="button" class="button" id="searchBtn">検索</button>
                </div>
                <div class="koi-search-results" id="searchResults"></div>
            </div>

            <?php submit_button('設定を保存'); ?>
        </form>
    </div>

    <script>
    (function(){
        // モード切り替え
        var radios = document.querySelectorAll('.mode-radio');
        radios.forEach(function(r) {
            r.addEventListener('change', function() {
                document.getElementById('modeAuto').classList.toggle('is-active', this.value === 'auto');
                document.getElementById('modeManual').classList.toggle('is-active', this.value === 'manual');
            });
        });

        // 手動記事リスト管理
        function updateManualIds() {
            var items = document.querySelectorAll('#manualList .koi-manual-item');
            var ids = [];
            items.forEach(function(el) { ids.push(el.dataset.id); });
            document.getElementById('manualPostIds').value = ids.join(',');
        }

        // 削除
        window.removeManualItem = function(btn) {
            btn.closest('.koi-manual-item').remove();
            updateManualIds();
            if (!document.querySelector('#manualList .koi-manual-item')) {
                document.getElementById('manualList').innerHTML = '<p style="color:#999;text-align:center;padding:20px;" id="emptyMsg">記事が選択されていません。</p>';
            }
        };

        // ドラッグ並び替え（シンプル実装）
        var dragSrc = null;
        document.getElementById('manualList').addEventListener('dragstart', function(e) {
            var item = e.target.closest('.koi-manual-item');
            if (!item) return;
            dragSrc = item;
            item.style.opacity = '0.4';
        });
        document.getElementById('manualList').addEventListener('dragover', function(e) {
            e.preventDefault();
            var item = e.target.closest('.koi-manual-item');
            if (item && item !== dragSrc) {
                var rect = item.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    item.parentNode.insertBefore(dragSrc, item);
                } else {
                    item.parentNode.insertBefore(dragSrc, item.nextSibling);
                }
            }
        });
        document.getElementById('manualList').addEventListener('dragend', function(e) {
            if (dragSrc) { dragSrc.style.opacity = '1'; dragSrc = null; }
            updateManualIds();
        });
        // Make items draggable
        document.querySelectorAll('.koi-manual-item').forEach(function(el) { el.draggable = true; });

        // 記事検索
        var searchInput = document.getElementById('postSearch');
        var searchBtn   = document.getElementById('searchBtn');
        var resultsDiv  = document.getElementById('searchResults');

        function doSearch() {
            var q = searchInput.value.trim();
            if (!q) return;
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = '<div style="padding:12px;color:#888;">検索中...</div>';

            var url = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            var fd = new FormData();
            fd.append('action', 'koi_ria_search_posts');
            fd.append('q', q);
            fd.append('nonce', '<?php echo wp_create_nonce('koi_ria_search_posts'); ?>');

            fetch(url, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success || !data.data.length) {
                        resultsDiv.innerHTML = '<div style="padding:12px;color:#888;">該当する記事がありません</div>';
                        return;
                    }
                    var html = '';
                    data.data.forEach(function(p) {
                        html += '<div class="search-item" data-id="' + p.id + '" data-title="' + escHtml(p.title) + '" data-thumb="' + (p.thumb || '') + '" data-cat="' + escHtml(p.cat) + '" data-pv="' + p.pv + '" data-date="' + p.date + '">';
                        html += p.thumb ? '<img src="' + p.thumb + '" alt="">' : '<span style="width:50px;height:34px;background:#eee;display:inline-block;border-radius:3px;"></span>';
                        html += '<div><div class="s-title">' + escHtml(p.title) + '</div><div class="s-date">ID:' + p.id + ' / ' + escHtml(p.cat) + ' / ' + p.date + '</div></div>';
                        html += '</div>';
                    });
                    resultsDiv.innerHTML = html;

                    resultsDiv.querySelectorAll('.search-item').forEach(function(item) {
                        item.addEventListener('click', function() {
                            addManualItem(item.dataset);
                        });
                    });
                });
        }

        function escHtml(s) {
            var d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function addManualItem(data) {
            // 重複チェック
            if (document.querySelector('#manualList .koi-manual-item[data-id="' + data.id + '"]')) {
                return;
            }
            var empty = document.getElementById('emptyMsg');
            if (empty) empty.remove();

            var el = document.createElement('div');
            el.className = 'koi-manual-item';
            el.draggable = true;
            el.dataset.id = data.id;
            el.innerHTML = '<span class="drag-handle">&#9776;</span>'
                + (data.thumb ? '<img src="' + data.thumb + '" class="item-thumb" alt="">' : '<span class="item-thumb"></span>')
                + '<div class="item-info"><div class="item-title">' + escHtml(data.title) + '</div>'
                + '<div class="item-meta">ID:' + data.id
                + (data.cat ? ' / ' + escHtml(data.cat) : '')
                + (data.pv > 0 ? ' / ' + Number(data.pv).toLocaleString() + ' PV' : '')
                + ' / ' + data.date + '</div></div>'
                + '<button type="button" class="item-remove" onclick="removeManualItem(this)" title="削除">&times;</button>';

            document.getElementById('manualList').appendChild(el);
            updateManualIds();
            resultsDiv.style.display = 'none';
            searchInput.value = '';
        }

        searchBtn.addEventListener('click', doSearch);
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
        });

        // 検索結果外クリックで閉じる
        document.addEventListener('click', function(e) {
            if (!resultsDiv.contains(e.target) && e.target !== searchInput && e.target !== searchBtn) {
                resultsDiv.style.display = 'none';
            }
        });

        // GA同期ボタン
        var syncBtn = document.getElementById('syncGaBtn');
        var syncSpinner = document.getElementById('syncSpinner');
        var syncResult = document.getElementById('syncResult');

        if (syncBtn) {
            syncBtn.addEventListener('click', function() {
                syncBtn.disabled = true;
                syncBtn.textContent = '同期中...';
                syncSpinner.classList.add('is-active');
                syncResult.style.display = 'block';
                syncResult.innerHTML = '<p>Google Analytics からPVデータを取得しています...</p>';

                var fd = new FormData();
                fd.append('action', 'koi_ria_sync_ga');
                fd.append('nonce', '<?php echo wp_create_nonce('koi_ria_sync_ga'); ?>');

                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(resp) {
                        syncBtn.disabled = false;
                        syncBtn.textContent = '今すぐGA同期を実行';
                        syncSpinner.classList.remove('is-active');

                        if (!resp.success) {
                            syncResult.innerHTML = '<p style="color:#d63638;"><strong>エラー:</strong> ' + escHtml(resp.data || '不明なエラー') + '</p>';
                            return;
                        }

                        var d = resp.data;
                        var html = '<p><strong>' + (d.success ? '同期成功' : '同期失敗') + ':</strong> ' + escHtml(d.message) + '</p>';
                        html += '<p>GA行数: ' + d.total_rows + ' / マッチ: ' + d.matched + ' / 未解決: ' + d.unmatched + '</p>';

                        if (d.debug && d.debug.length) {
                            html += '<details><summary style="cursor:pointer;">デバッグログ</summary><pre>';
                            d.debug.forEach(function(line) { html += escHtml(line) + '\n'; });
                            html += '</pre></details>';
                        }

                        if (d.success) {
                            html += '<p style="color:#00a32a; margin-top:8px;">ページをリロードすると同期データの一覧が更新されます。</p>';
                        }

                        syncResult.innerHTML = html;
                    })
                    .catch(function(err) {
                        syncBtn.disabled = false;
                        syncBtn.textContent = '今すぐGA同期を実行';
                        syncSpinner.classList.remove('is-active');
                        syncResult.innerHTML = '<p style="color:#d63638;">通信エラー: ' + err.message + '</p>';
                    });
            });
        }
    })();
    </script>
    <?php
}

/**
 * 表示設定ページ（フロントページセクション表示・並び順）
 */
function koi_ria_display_page(): void {
    // セクション定義
    $sections = [
        'hero_carousel'  => 'YouTubeスライダー',
        'search_bar'     => '検索バー',
        'weekly_schedule' => '今週の放送',
        'breaking_bar'   => '速報バー',
        'shows'          => '注目の番組',
        'popular_posts'  => '人気記事トップ3',
        'couple_tracker' => 'カップルその後',
        'poll'           => 'みんなの予想',
        'stories_cast'   => '話題の出演者',
        'column'         => '恋愛コラム',
        'news'           => '最新ニュース',
        'shindan'        => '番組診断バナー',
        'vod_search'     => 'VOD検索バナー',
        'affiliate'      => 'アフィリエイトバナー',
        'adsense'        => 'AdSenseスロット',
    ];

    // デフォルト設定
    $defaults = [];
    $order = 1;
    foreach ($sections as $key => $label) {
        $defaults[$key] = [
            'enabled' => true,
            'order'   => $order++,
        ];
    }

    // 保存処理
    if (isset($_POST['koi_ria_display_nonce']) && wp_verify_nonce($_POST['koi_ria_display_nonce'], 'koi_ria_save_display')) {
        $settings = [];
        foreach ($sections as $key => $label) {
            $settings[$key] = [
                'enabled' => !empty($_POST['section_enabled'][$key]),
                'order'   => intval($_POST['section_order'][$key] ?? $defaults[$key]['order']),
            ];
        }
        update_option('koi_ria_display_settings', $settings);
        echo '<div class="notice notice-success"><p>表示設定を保存しました。</p></div>';
    }

    $settings = get_option('koi_ria_display_settings', $defaults);
    // 新しいセクションが追加された場合のフォールバック
    foreach ($defaults as $key => $def) {
        if (!isset($settings[$key])) {
            $settings[$key] = $def;
        }
    }
    ?>
    <div class="wrap">
        <h1>表示設定</h1>
        <p>フロントページに表示するセクションの表示・非表示と表示順を設定します。</p>
        <form method="post">
            <?php wp_nonce_field('koi_ria_save_display', 'koi_ria_display_nonce'); ?>

            <table class="widefat striped" style="max-width: 700px; margin-top: 20px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">表示</th>
                        <th>セクション名</th>
                        <th style="width: 100px;">表示順</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $key => $label) :
                        $enabled = isset($settings[$key]['enabled']) ? $settings[$key]['enabled'] : true;
                        $ord     = isset($settings[$key]['order']) ? $settings[$key]['order'] : $defaults[$key]['order'];
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" name="section_enabled[<?php echo esc_attr($key); ?>]" value="1" <?php checked($enabled); ?>>
                        </td>
                        <td><strong><?php echo esc_html($label); ?></strong> <code style="font-size: 11px; color: #888;"><?php echo esc_html($key); ?></code></td>
                        <td>
                            <input type="number" name="section_order[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($ord); ?>" min="1" max="99" style="width: 70px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button('表示設定を保存'); ?>
        </form>
    </div>
    <?php
}

/**
 * ログ記録ヘルパー
 */
function koi_ria_log(string $source, string $message): void {
    $logs = get_option('koi_ria_cron_log', []);
    $logs[] = [
        'time'    => current_time('Y-m-d H:i:s'),
        'source'  => $source,
        'message' => $message,
    ];

    // 最大200件まで保持
    if (count($logs) > 200) {
        $logs = array_slice($logs, -200);
    }

    update_option('koi_ria_cron_log', $logs, false);
}
