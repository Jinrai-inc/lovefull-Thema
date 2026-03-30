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
        update_option('koi_ria_ig_access_token', sanitize_text_field($_POST['ig_access_token'] ?? ''));
        update_option('koi_ria_ig_user_id', sanitize_text_field($_POST['ig_user_id'] ?? ''));

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
    $ig_token       = get_option('koi_ria_ig_access_token', '');
    $ig_user_id     = get_option('koi_ria_ig_user_id', '');
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

            <h2 class="title">Instagram Graph API</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ig_access_token">Access Token</label></th>
                    <td>
                        <input type="text" id="ig_access_token" name="ig_access_token" value="<?php echo esc_attr($ig_token); ?>" class="regular-text">
                        <p class="description">Instagram Graph API のアクセストークン（Business Discovery用）</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ig_user_id">Business Account ID</label></th>
                    <td>
                        <input type="text" id="ig_user_id" name="ig_user_id" value="<?php echo esc_attr($ig_user_id); ?>" class="regular-text">
                        <p class="description">自サイトのIGビジネスアカウントのユーザーID</p>
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
    $ig_last    = get_option('koi_ria_ig_last_run', null);
    $ig_img_last = get_option('koi_ria_ig_images_last_run', null);

    // Cronの次回実行時刻
    $yt_next   = wp_next_scheduled('koi_ria_fetch_youtube');
    $news_next = wp_next_scheduled('koi_ria_fetch_news');
    $ig_next   = wp_next_scheduled('koi_ria_update_instagram');

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
                <?php // Instagram ?>
                <tr>
                    <td><strong>Instagram更新</strong></td>
                    <td>毎日</td>
                    <td><?php echo $ig_last ? esc_html($ig_last['time']) : '—'; ?></td>
                    <td>
                        <?php if ($ig_last && isset($ig_last['summary'])) :
                            $s = $ig_last['summary']; ?>
                            更新: <?php echo intval($s['updated']); ?> /
                            スキップ: <?php echo intval($s['skipped']); ?> /
                            エラー: <?php echo intval($s['errors']); ?>
                            <?php if (isset($ig_last['api_calls'])) : ?>
                                (API: <?php echo intval($ig_last['api_calls']); ?>回)
                            <?php endif; ?>
                        <?php else : ?>—<?php endif; ?>
                    </td>
                    <td><?php echo $ig_next ? esc_html(date('Y-m-d H:i', $ig_next)) : '未設定'; ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin-post.php?action=koi_ria_manual_instagram&_wpnonce=' . wp_create_nonce('koi_ria_manual_cron'))); ?>" class="button button-secondary">実行</a>
                    </td>
                </tr>
                <?php // IG画像 ?>
                <tr>
                    <td><strong>IG画像キャッシュ</strong></td>
                    <td>週1回</td>
                    <td><?php echo $ig_img_last ? esc_html($ig_img_last['time']) : '—'; ?></td>
                    <td>
                        <?php if ($ig_img_last && isset($ig_img_last['summary'])) :
                            $s = $ig_img_last['summary']; ?>
                            更新: <?php echo intval($s['updated']); ?> /
                            スキップ: <?php echo intval($s['skipped']); ?> /
                            エラー: <?php echo intval($s['errors']); ?>
                        <?php else : ?>—<?php endif; ?>
                    </td>
                    <td>—</td>
                    <td>—</td>
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
