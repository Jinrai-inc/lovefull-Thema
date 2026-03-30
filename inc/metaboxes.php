<?php
/**
 * ネイティブ WordPress メタボックス
 *
 * ACF PRO がインストールされていない場合に、カスタムフィールドを
 * 管理画面から編集できるようにする。ACF がある場合はスキップ。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// ACF PRO がアクティブなら、このファイルは何もしない
if (class_exists('ACF')) {
    return;
}

/* =========================================================
 * ヘルパー関数
 * ========================================================= */

/**
 * 指定した投稿タイプの公開済み投稿を取得して <option> タグ群を返す
 */
function koi_ria_post_options(string $post_type, $selected = ''): string {
    $posts = get_posts([
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    $html = '<option value="">-- 選択してください --</option>';
    foreach ($posts as $p) {
        $sel = selected($selected, $p->ID, false);
        $html .= sprintf('<option value="%d"%s>%s</option>', $p->ID, $sel, esc_html($p->post_title));
    }
    return $html;
}

/**
 * テキストフィールドを出力
 */
function koi_ria_text_field(int $post_id, string $key, string $label, string $instructions = '', string $type = 'text', string $class = 'regular-text'): void {
    $value = get_post_meta($post_id, $key, true);
    echo '<tr>';
    echo '<th><label for="koi_ria_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="' . esc_attr($type) . '" id="koi_ria_' . esc_attr($key) . '" name="koi_ria_' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="' . esc_attr($class) . '">';
    if ($instructions) {
        echo '<p class="description">' . esc_html($instructions) . '</p>';
    }
    echo '</td></tr>';
}

/**
 * セレクトフィールドを出力
 */
function koi_ria_select_field(int $post_id, string $key, string $label, array $choices, string $instructions = ''): void {
    $value = get_post_meta($post_id, $key, true);
    echo '<tr>';
    echo '<th><label for="koi_ria_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
    echo '<td><select id="koi_ria_' . esc_attr($key) . '" name="koi_ria_' . esc_attr($key) . '">';
    foreach ($choices as $k => $v) {
        echo '<option value="' . esc_attr($k) . '"' . selected($value, $k, false) . '>' . esc_html($v) . '</option>';
    }
    echo '</select>';
    if ($instructions) {
        echo '<p class="description">' . esc_html($instructions) . '</p>';
    }
    echo '</td></tr>';
}

/**
 * 投稿参照セレクトフィールドを出力
 */
function koi_ria_post_select_field(int $post_id, string $key, string $label, string $ref_post_type, string $instructions = ''): void {
    $value = get_post_meta($post_id, $key, true);
    echo '<tr>';
    echo '<th><label for="koi_ria_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
    echo '<td><select id="koi_ria_' . esc_attr($key) . '" name="koi_ria_' . esc_attr($key) . '">';
    echo koi_ria_post_options($ref_post_type, $value);
    echo '</select>';
    if ($instructions) {
        echo '<p class="description">' . esc_html($instructions) . '</p>';
    }
    echo '</td></tr>';
}

/**
 * チェックボックス（true/false）フィールドを出力
 */
function koi_ria_checkbox_field(int $post_id, string $key, string $label, string $instructions = ''): void {
    $value = get_post_meta($post_id, $key, true);
    echo '<tr>';
    echo '<th><label for="koi_ria_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="checkbox" id="koi_ria_' . esc_attr($key) . '" name="koi_ria_' . esc_attr($key) . '" value="1"' . checked($value, '1', false) . '>';
    if ($instructions) {
        echo '<p class="description">' . esc_html($instructions) . '</p>';
    }
    echo '</td></tr>';
}

/**
 * テキストエリアフィールドを出力
 */
function koi_ria_textarea_field(int $post_id, string $key, string $label, string $instructions = '', int $rows = 5): void {
    $value = get_post_meta($post_id, $key, true);
    echo '<tr>';
    echo '<th><label for="koi_ria_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<textarea id="koi_ria_' . esc_attr($key) . '" name="koi_ria_' . esc_attr($key) . '" rows="' . $rows . '" class="large-text">' . esc_textarea($value) . '</textarea>';
    if ($instructions) {
        echo '<p class="description">' . esc_html($instructions) . '</p>';
    }
    echo '</td></tr>';
}

/* =========================================================
 * メタボックス登録
 * ========================================================= */

add_action('add_meta_boxes', 'koi_ria_register_metaboxes');

function koi_ria_register_metaboxes(): void {
    // 番組 (show)
    add_meta_box('koi_ria_show_meta', '番組情報', 'koi_ria_show_metabox_cb', 'show', 'normal', 'high');

    // シーズン (season)
    add_meta_box('koi_ria_season_meta', 'シーズン情報', 'koi_ria_season_metabox_cb', 'season', 'normal', 'high');

    // 出演者 (cast)
    add_meta_box('koi_ria_cast_meta', '出演者情報', 'koi_ria_cast_metabox_cb', 'cast', 'normal', 'high');

    // YouTube動画 (youtube_video)
    add_meta_box('koi_ria_youtube_video_meta', 'YouTube動画情報', 'koi_ria_youtube_video_metabox_cb', 'youtube_video', 'normal', 'high');

    // 投票 (poll)
    add_meta_box('koi_ria_poll_meta', '投票設定', 'koi_ria_poll_metabox_cb', 'poll', 'normal', 'high');

    // カップル (couple)
    add_meta_box('koi_ria_couple_meta', 'カップル情報', 'koi_ria_couple_metabox_cb', 'couple', 'normal', 'high');

    // 相関図 (relation)
    add_meta_box('koi_ria_relation_meta', '相関図データ', 'koi_ria_relation_metabox_cb', 'relation', 'normal', 'high');
}

/* =========================================================
 * 番組 (show) メタボックス
 * ========================================================= */

function koi_ria_show_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_show_nonce_action', 'koi_ria_show_nonce');
    echo '<table class="form-table">';

    koi_ria_text_field($post->ID, 'short_name', '略称', '例: 今日好き');

    koi_ria_select_field($post->ID, 'platform', 'プラットフォーム', [
        ''             => '-- 選択 --',
        'ABEMA'        => 'ABEMA',
        'Netflix'      => 'Netflix',
        'Prime Video'  => 'Prime Video',
        'U-NEXT'       => 'U-NEXT',
        'Disney+'      => 'Disney+',
        'Paravi'       => 'Paravi',
        'その他'       => 'その他',
    ]);

    koi_ria_text_field($post->ID, 'platform_color', 'プラットフォームカラー', '例: #00B900', 'color');

    koi_ria_text_field($post->ID, 'youtube_channel_id', 'YouTubeチャンネルID', 'UCで始まるチャンネルID。設定するとYouTube動画を自動取得します。');

    koi_ria_text_field($post->ID, 'affiliate_url', 'アフィリエイトURL', '番組の公式配信ページへのアフィリエイトリンク', 'url');

    koi_ria_select_field($post->ID, 'show_status', '番組ステータス', [
        ''       => '-- 選択 --',
        '放送中' => '放送中',
        '配信中' => '配信中',
        '過去作' => '過去作',
        '制作中' => '制作中',
    ]);

    koi_ria_text_field($post->ID, 'genre', 'ジャンル', '例: 青春恋愛');
    koi_ria_text_field($post->ID, 'target', 'ターゲット層', '例: 中高生');
    koi_ria_text_field($post->ID, 'priority', '優先度', '小さい値ほど上位に表示', 'number');

    echo '</table>';
}

/* =========================================================
 * シーズン (season) メタボックス
 * ========================================================= */

function koi_ria_season_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_season_nonce_action', 'koi_ria_season_nonce');
    echo '<table class="form-table">';

    koi_ria_post_select_field($post->ID, 'show', '番組', 'show');
    koi_ria_text_field($post->ID, 'season_name', 'シーズン名', '例: テグ編 / Season 2');
    koi_ria_text_field($post->ID, 'year', '年', '例: 2026');

    koi_ria_select_field($post->ID, 'badge', 'バッジ', [
        ''       => 'なし',
        'ON AIR' => 'ON AIR',
        'NEW'    => 'NEW',
        '配信中' => '配信中',
        'COMING' => 'COMING',
    ]);

    koi_ria_text_field($post->ID, 'order', '表示順', '新しいシーズンほど小さい数字', 'number');

    echo '</table>';
}

/* =========================================================
 * 出演者 (cast) メタボックス
 * ========================================================= */

function koi_ria_cast_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_cast_nonce_action', 'koi_ria_cast_nonce');
    echo '<table class="form-table">';

    koi_ria_text_field($post->ID, 'display_name', '名前（表示用）', '空欄の場合はタイトルが使用されます');
    koi_ria_post_select_field($post->ID, 'show', '番組', 'show');
    koi_ria_post_select_field($post->ID, 'season', 'シーズン', 'season');
    koi_ria_text_field($post->ID, 'ig_username', 'Instagram ID', '@なしのユーザー名（例: airi_official）');
    koi_ria_text_field($post->ID, 'tiktok_username', 'TikTok ID', '@なし。空欄可');
    koi_ria_text_field($post->ID, 'x_username', 'X(Twitter) ID', '@なし。空欄可');
    koi_ria_text_field($post->ID, 'youtube_url', 'YouTube チャンネル', '例: https://youtube.com/@username', 'url');
    koi_ria_text_field($post->ID, 'followers_count', 'Instagramフォロワー数', 'API設定済みの場合は自動更新。手動入力も可能。', 'number');
    koi_ria_text_field($post->ID, 'tiktok_followers', 'TikTokフォロワー数', '', 'number');

    koi_ria_select_field($post->ID, 'role', '役割', [
        ''               => '-- 選択 --',
        '女子メンバー'   => '女子メンバー',
        '男子メンバー'   => '男子メンバー',
        '女性参加者'     => '女性参加者',
        '男性参加者'     => '男性参加者',
        '参加者'         => '参加者',
        'バチェラー'     => 'バチェラー',
        'バチェロレッテ' => 'バチェロレッテ',
    ]);

    koi_ria_select_field($post->ID, 'gender', '性別', [
        ''      => '-- 選択 --',
        'f'     => '女性',
        'm'     => '男性',
        'other' => 'その他',
    ]);

    koi_ria_text_field($post->ID, 'age', '年齢', '空欄可', 'number');
    koi_ria_text_field($post->ID, 'from_area', '出身', '例: 東京都');

    koi_ria_select_field($post->ID, 'cast_status', 'ステータス', [
        ''         => '-- 選択 --',
        '出演中'   => '出演中',
        '卒業'     => '卒業',
        'リタイア' => 'リタイア',
    ]);

    echo '</table>';
}

/* =========================================================
 * YouTube動画 (youtube_video) メタボックス
 * ========================================================= */

function koi_ria_youtube_video_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_youtube_video_nonce_action', 'koi_ria_youtube_video_nonce');

    $current_video_id = get_post_meta($post->ID, 'video_id', true);
    ?>
    <div style="background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:16px 20px;margin-bottom:16px;">
        <h4 style="margin:0 0 8px;font-size:14px;">📹 かんたん動画登録</h4>
        <p style="margin:0 0 12px;color:#666;font-size:13px;">YouTubeの動画URLを貼り付けるだけで登録できます。</p>

        <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" id="koi_ria_video_url" class="regular-text" style="flex:1;"
                placeholder="https://www.youtube.com/watch?v=... または https://youtu.be/..."
                <?php if ($current_video_id) : ?>
                value="https://www.youtube.com/watch?v=<?php echo esc_attr($current_video_id); ?>"
                <?php endif; ?>>
            <button type="button" id="koi_ria_extract_btn" class="button button-primary">動画を読み込む</button>
        </div>

        <div id="koi_ria_url_status" style="margin-top:8px;font-size:13px;"></div>

        <!-- プレビュー -->
        <div id="koi_ria_video_preview" style="margin-top:12px;">
            <?php if ($current_video_id) : ?>
            <iframe width="400" height="225" src="https://www.youtube.com/embed/<?php echo esc_attr($current_video_id); ?>" frameborder="0" allowfullscreen style="border-radius:6px;max-width:100%;"></iframe>
            <?php endif; ?>
        </div>
    </div>

    <table class="form-table">
    <?php
    koi_ria_text_field($post->ID, 'video_id', '動画ID', '上のURLから自動入力されます。直接入力も可能（11文字の英数字）');
    koi_ria_text_field($post->ID, 'channel_name', 'チャンネル名', '動画のチャンネル名（任意）');

    koi_ria_select_field($post->ID, 'platform', 'プラットフォーム', [
        ''            => '-- 選択 --',
        'ABEMA'       => 'ABEMA',
        'Netflix'     => 'Netflix',
        'Prime Video' => 'Prime Video',
        'その他'      => 'その他',
    ]);

    koi_ria_text_field($post->ID, 'thumbnail_url', 'サムネイルURL', 'URLから自動設定されます。空欄の場合はYouTubeデフォルトを使用', 'url');
    koi_ria_text_field($post->ID, 'published_at', '公開日', '', 'datetime-local');
    koi_ria_checkbox_field($post->ID, 'is_pinned', 'トップにピン留め', 'ONにするとトップページの先頭に固定表示されます');
    koi_ria_checkbox_field($post->ID, 'is_auto', '自動取得', 'APIで自動取得された動画');

    echo '</table>';

    // YouTube URL 自動抽出 JS
    ?>
    <script>
    (function() {
        var urlInput = document.getElementById('koi_ria_video_url');
        var extractBtn = document.getElementById('koi_ria_extract_btn');
        var statusEl = document.getElementById('koi_ria_url_status');
        var previewEl = document.getElementById('koi_ria_video_preview');
        var videoIdInput = document.getElementById('koi_ria_video_id');
        var thumbnailInput = document.getElementById('koi_ria_thumbnail_url');
        var titleInput = document.getElementById('title') || document.querySelector('input[name="post_title"]');

        function extractVideoId(url) {
            if (!url) return null;
            var patterns = [
                /(?:youtube\.com\/watch\?.*v=)([a-zA-Z0-9_-]{11})/,
                /(?:youtu\.be\/)([a-zA-Z0-9_-]{11})/,
                /(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
                /(?:youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/,
                /^([a-zA-Z0-9_-]{11})$/
            ];
            for (var i = 0; i < patterns.length; i++) {
                var match = url.trim().match(patterns[i]);
                if (match) return match[1];
            }
            return null;
        }

        function loadVideo() {
            var url = urlInput.value;
            var videoId = extractVideoId(url);

            if (!videoId) {
                statusEl.innerHTML = '<span style="color:#d63638;">❌ 有効なYouTube URLではありません。例: https://www.youtube.com/watch?v=xxxxxxxxxxx</span>';
                previewEl.innerHTML = '';
                return;
            }

            // 動画IDをセット
            videoIdInput.value = videoId;

            // サムネイルURLを自動設定
            if (thumbnailInput && !thumbnailInput.value) {
                thumbnailInput.value = 'https://img.youtube.com/vi/' + videoId + '/hqdefault.jpg';
            }

            // ステータス表示
            statusEl.innerHTML = '<span style="color:#00a32a;">✅ 動画ID: <strong>' + videoId + '</strong> を読み込みました</span>';

            // プレビュー表示
            previewEl.innerHTML = '<iframe width="400" height="225" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen style="border-radius:6px;max-width:100%;"></iframe>';

            // タイトルが空なら oEmbed APIで自動取得を試みる
            if (titleInput && (!titleInput.value || titleInput.value === '自動下書き')) {
                fetch('https://noembed.com/embed?url=https://www.youtube.com/watch?v=' + videoId)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.title) {
                            titleInput.value = data.title;
                            // Gutenbergの場合はinputイベントを発火
                            titleInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (data.author_name) {
                            var chInput = document.getElementById('koi_ria_channel_name');
                            if (chInput && !chInput.value) {
                                chInput.value = data.author_name;
                            }
                        }
                    })
                    .catch(function() { /* oEmbed取得失敗は無視 */ });
            }
        }

        if (extractBtn) {
            extractBtn.addEventListener('click', loadVideo);
        }

        // URLフィールドでEnterキーまたはペースト時にも自動実行
        if (urlInput) {
            urlInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); loadVideo(); }
            });
            urlInput.addEventListener('paste', function() {
                setTimeout(loadVideo, 100);
            });
        }
    })();
    </script>
    <?php
}

/* =========================================================
 * 投票 (poll) メタボックス
 * ========================================================= */

function koi_ria_poll_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_poll_nonce_action', 'koi_ria_poll_nonce');
    echo '<table class="form-table">';

    koi_ria_text_field($post->ID, 'question', '質問文');
    koi_ria_post_select_field($post->ID, 'show', '番組', 'show');
    koi_ria_checkbox_field($post->ID, 'is_active', '受付中');
    koi_ria_text_field($post->ID, 'end_date', '終了日', '', 'date');

    // 選択肢 (repeater の代替: 1行ごとに label|votes)
    koi_ria_textarea_field($post->ID, 'options', '選択肢', '1行に1つ。形式: ラベル|投票数（例: りんご|12）。投票数を省略すると0になります。', 6);

    echo '</table>';
}

/* =========================================================
 * カップル (couple) メタボックス
 * ========================================================= */

function koi_ria_couple_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_couple_nonce_action', 'koi_ria_couple_nonce');
    echo '<table class="form-table">';

    koi_ria_text_field($post->ID, 'couple_name', 'カップル名', '例: れんゆな / さとまる');
    koi_ria_post_select_field($post->ID, 'member_a', 'メンバーA', 'cast');
    koi_ria_post_select_field($post->ID, 'member_b', 'メンバーB', 'cast');
    koi_ria_post_select_field($post->ID, 'show', '番組', 'show');
    koi_ria_post_select_field($post->ID, 'season', 'シーズン', 'season');

    koi_ria_select_field($post->ID, 'couple_status', '現在のステータス', [
        ''       => '-- 選択 --',
        '交際中' => '交際中',
        '破局'   => '破局',
        '結婚'   => '結婚',
        '不明'   => '不明',
    ]);

    // タイムライン (repeater の代替: 1行ごとに date|event_type|note)
    koi_ria_textarea_field($post->ID, 'timeline', 'タイムライン', '1行に1イベント。形式: 日付|イベント種別|メモ（例: 2026-01-15|成立|最終回で成立）。種別: 成立/交際報告/破局報告/結婚報告/目撃情報/SNS投稿', 6);

    echo '</table>';
}

/* =========================================================
 * 相関図 (relation) メタボックス
 * ========================================================= */

function koi_ria_relation_metabox_cb(\WP_Post $post): void {
    wp_nonce_field('koi_ria_relation_nonce_action', 'koi_ria_relation_nonce');
    echo '<table class="form-table">';

    koi_ria_post_select_field($post->ID, 'season', 'シーズン', 'season');
    koi_ria_post_select_field($post->ID, 'from_cast', 'From（誰から）', 'cast');
    koi_ria_post_select_field($post->ID, 'to_cast', 'To（誰へ）', 'cast');

    koi_ria_select_field($post->ID, 'relation_type', '関係タイプ', [
        ''         => '-- 選択 --',
        'love'     => 'love',
        'rival'    => 'rival',
        'couple'   => 'couple',
        'interest' => 'interest',
    ]);

    koi_ria_text_field($post->ID, 'relation_label', 'ラベル', '例: 両思い / 片思い / 三角関係 / 成立！');

    echo '</table>';
}

/* =========================================================
 * 保存処理
 * ========================================================= */

/**
 * 共通の保存ヘルパー: nonce を検証し、指定キーの meta を保存する
 */
function koi_ria_save_meta_fields(int $post_id, string $nonce_name, string $nonce_action, array $fields): void {
    // nonce 検証
    if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action)) {
        return;
    }
    // 自動保存はスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    foreach ($fields as $field) {
        $form_key = 'koi_ria_' . $field['key'];
        if ($field['type'] === 'checkbox') {
            $value = isset($_POST[$form_key]) ? '1' : '0';
        } else {
            $value = isset($_POST[$form_key]) ? sanitize_text_field(wp_unslash($_POST[$form_key])) : '';
        }
        // textarea はサニタイズを変える
        if ($field['type'] === 'textarea' && isset($_POST[$form_key])) {
            $value = sanitize_textarea_field(wp_unslash($_POST[$form_key]));
        }
        update_post_meta($post_id, $field['key'], $value);
    }
}

// --- 番組 (show) ---
add_action('save_post_show', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_show_nonce', 'koi_ria_show_nonce_action', [
        ['key' => 'short_name',         'type' => 'text'],
        ['key' => 'platform',           'type' => 'text'],
        ['key' => 'platform_color',     'type' => 'text'],
        ['key' => 'youtube_channel_id', 'type' => 'text'],
        ['key' => 'affiliate_url',      'type' => 'text'],
        ['key' => 'show_status',        'type' => 'text'],
        ['key' => 'genre',              'type' => 'text'],
        ['key' => 'target',             'type' => 'text'],
        ['key' => 'priority',           'type' => 'text'],
    ]);
});

// --- シーズン (season) ---
add_action('save_post_season', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_season_nonce', 'koi_ria_season_nonce_action', [
        ['key' => 'show',        'type' => 'text'],
        ['key' => 'season_name', 'type' => 'text'],
        ['key' => 'year',        'type' => 'text'],
        ['key' => 'badge',       'type' => 'text'],
        ['key' => 'order',       'type' => 'text'],
    ]);
});

// --- 出演者 (cast) ---
add_action('save_post_cast', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_cast_nonce', 'koi_ria_cast_nonce_action', [
        ['key' => 'display_name',     'type' => 'text'],
        ['key' => 'show',             'type' => 'text'],
        ['key' => 'season',           'type' => 'text'],
        ['key' => 'ig_username',      'type' => 'text'],
        ['key' => 'tiktok_username',  'type' => 'text'],
        ['key' => 'x_username',       'type' => 'text'],
        ['key' => 'youtube_url',      'type' => 'text'],
        ['key' => 'followers_count',  'type' => 'text'],
        ['key' => 'tiktok_followers', 'type' => 'text'],
        ['key' => 'role',             'type' => 'text'],
        ['key' => 'gender',           'type' => 'text'],
        ['key' => 'age',              'type' => 'text'],
        ['key' => 'from_area',        'type' => 'text'],
        ['key' => 'cast_status',      'type' => 'text'],
    ]);
});

// --- YouTube動画 (youtube_video) ---
add_action('save_post_youtube_video', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_youtube_video_nonce', 'koi_ria_youtube_video_nonce_action', [
        ['key' => 'video_id',      'type' => 'text'],
        ['key' => 'channel_name',  'type' => 'text'],
        ['key' => 'platform',      'type' => 'text'],
        ['key' => 'thumbnail_url', 'type' => 'text'],
        ['key' => 'published_at',  'type' => 'text'],
        ['key' => 'is_pinned',     'type' => 'checkbox'],
        ['key' => 'is_auto',       'type' => 'checkbox'],
    ]);
});

// --- 投票 (poll) ---
add_action('save_post_poll', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_poll_nonce', 'koi_ria_poll_nonce_action', [
        ['key' => 'question',  'type' => 'text'],
        ['key' => 'show',      'type' => 'text'],
        ['key' => 'is_active', 'type' => 'checkbox'],
        ['key' => 'end_date',  'type' => 'text'],
        ['key' => 'options',   'type' => 'textarea'],
    ]);
});

// --- カップル (couple) ---
add_action('save_post_couple', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_couple_nonce', 'koi_ria_couple_nonce_action', [
        ['key' => 'couple_name',   'type' => 'text'],
        ['key' => 'member_a',      'type' => 'text'],
        ['key' => 'member_b',      'type' => 'text'],
        ['key' => 'show',          'type' => 'text'],
        ['key' => 'season',        'type' => 'text'],
        ['key' => 'couple_status', 'type' => 'text'],
        ['key' => 'timeline',      'type' => 'textarea'],
    ]);
});

// --- 相関図 (relation) ---
add_action('save_post_relation', function (int $post_id): void {
    koi_ria_save_meta_fields($post_id, 'koi_ria_relation_nonce', 'koi_ria_relation_nonce_action', [
        ['key' => 'season',        'type' => 'text'],
        ['key' => 'from_cast',     'type' => 'text'],
        ['key' => 'to_cast',       'type' => 'text'],
        ['key' => 'relation_type', 'type' => 'text'],
        ['key' => 'relation_label','type' => 'text'],
    ]);
});
