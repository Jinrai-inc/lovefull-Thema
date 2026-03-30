<?php
/**
 * CSVインポート管理画面（改良版）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// インポート処理
$results = null;
if (isset($_POST['koi_ria_csv_nonce']) && wp_verify_nonce($_POST['koi_ria_csv_nonce'], 'koi_ria_csv_import')) {
    $handler = KOI_RIA_DIR . '/admin/csv-import-handler.php';
    if (file_exists($handler)) {
        require_once $handler;
        $results = koi_ria_handle_csv_import();
    }
}

$template_base = get_template_directory_uri() . '/data/';
?>
<style>
.koi-csv-wrap { max-width: 1100px; }
.koi-csv-wrap h1 { margin-bottom: 20px; }
.koi-section { margin-bottom: 30px; }
.koi-section h2 {
    font-size: 1.3em;
    padding-bottom: 8px;
    border-bottom: 2px solid #2271b1;
    margin-bottom: 16px;
}
.koi-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.koi-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
.koi-card .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: #2271b1;
    margin-bottom: 10px;
}
.koi-card h3 { margin: 0 0 8px; font-size: 1.05em; }
.koi-card p { color: #646970; font-size: 13px; margin: 0 0 14px; }
.koi-card .button { width: 100%; text-align: center; }
.koi-steps {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px 24px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
.koi-steps ol { margin: 0; padding-left: 20px; }
.koi-steps ol li {
    padding: 6px 0;
    font-size: 14px;
    line-height: 1.6;
}
.koi-warning {
    background: #fcf9e8;
    border-left: 4px solid #dba617;
    padding: 12px 16px;
    margin-top: 16px;
    font-size: 13px;
}
.koi-warning strong { color: #6e4e00; }
.koi-import-form {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 24px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    max-width: 650px;
}
.koi-import-form .form-table th { padding-left: 0; width: 140px; }
.koi-format-section { margin-top: 12px; }
.koi-format-section h3 {
    font-size: 1em;
    margin: 18px 0 8px;
    padding: 8px 12px;
    background: #f0f0f1;
    border-left: 3px solid #2271b1;
}
.koi-format-section .widefat { margin-bottom: 6px; }
.koi-format-section .widefat th,
.koi-format-section .widefat td { font-size: 13px; padding: 6px 10px; }
.koi-format-section .widefat .required { color: #d63638; font-weight: bold; }
.koi-format-section .widefat .optional { color: #646970; }
.koi-result-box { margin-bottom: 20px; }
</style>

<div class="wrap koi-csv-wrap">
    <h1><span class="dashicons dashicons-upload" style="font-size:24px;vertical-align:middle;margin-right:6px;"></span>恋リアデータ管理 - CSVインポート</h1>

    <?php if ($results) : ?>
    <div class="koi-result-box notice <?php echo ($results['error'] > 0) ? 'notice-warning' : 'notice-success'; ?> is-dismissible">
        <p>
            <strong>インポート完了:</strong>
            新規 <strong><?php echo intval($results['created'] ?? 0); ?></strong>件 /
            更新 <strong><?php echo intval($results['updated'] ?? 0); ?></strong>件 /
            スキップ <strong><?php echo intval($results['skipped'] ?? 0); ?></strong>件 /
            エラー <strong><?php echo intval($results['error'] ?? 0); ?></strong>件
        </p>
        <?php if (!empty($results['errors'])) : ?>
        <details>
            <summary style="cursor:pointer;color:#d63638;">エラー詳細を表示</summary>
            <ul style="margin-top:8px;">
                <?php foreach ($results['errors'] as $err) : ?>
                    <li><?php echo esc_html($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </details>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Section 1: テンプレートダウンロード -->
    <div class="koi-section">
        <h2><span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:4px;"></span>テンプレートダウンロード</h2>
        <p>以下のテンプレートCSVをダウンロードし、データを入力してからインポートしてください。</p>
        <div class="koi-cards">
            <div class="koi-card">
                <div class="dashicons dashicons-video-alt3"></div>
                <h3>番組</h3>
                <p>番組名・プラットフォーム・ジャンル等の基本情報</p>
                <a href="<?php echo esc_url($template_base . 'template-shows.csv'); ?>" class="button button-primary" download>ダウンロード</a>
            </div>
            <div class="koi-card">
                <div class="dashicons dashicons-calendar-alt"></div>
                <h3>シーズン</h3>
                <p>各番組のシーズン・放送年・バッジ情報</p>
                <a href="<?php echo esc_url($template_base . 'template-seasons.csv'); ?>" class="button button-primary" download>ダウンロード</a>
            </div>
            <div class="koi-card">
                <div class="dashicons dashicons-groups"></div>
                <h3>出演者</h3>
                <p>出演者名・SNSアカウント・年齢・出身地等</p>
                <a href="<?php echo esc_url($template_base . 'template-cast.csv'); ?>" class="button button-primary" download>ダウンロード</a>
            </div>
            <div class="koi-card">
                <div class="dashicons dashicons-heart"></div>
                <h3>相関図</h3>
                <p>出演者間の関係性（両思い・片思い等）</p>
                <a href="<?php echo esc_url($template_base . 'template-relations.csv'); ?>" class="button button-primary" download>ダウンロード</a>
            </div>
            <div class="koi-card">
                <div class="dashicons dashicons-youtube"></div>
                <h3>YouTube動画</h3>
                <p>YouTube動画URL・タイトル・チャンネル名</p>
                <a href="<?php echo esc_url($template_base . 'template-youtube.csv'); ?>" class="button button-primary" download>ダウンロード</a>
            </div>
        </div>
    </div>

    <!-- Section 2: インポート手順 -->
    <div class="koi-section">
        <h2><span class="dashicons dashicons-editor-ol" style="vertical-align:middle;margin-right:4px;"></span>インポート手順</h2>
        <div class="koi-steps">
            <ol>
                <li>上のボタンからテンプレートCSVをダウンロード</li>
                <li>Excel / Google スプレッドシートで編集し、<strong>UTF-8</strong> で保存</li>
                <li>下のフォームからCSVファイルをアップロード</li>
                <li><strong>「番組 → シーズン → 出演者 → 相関図」</strong>の順にインポート（依存関係があるため）</li>
            </ol>
            <div class="koi-warning">
                <strong>インポート順序に注意:</strong>
                番組 → シーズン → 出演者 → 相関図 の順序で登録してください。シーズンは番組に、出演者はシーズンに依存します。
            </div>
        </div>
    </div>

    <!-- Section 3: CSVインポート実行 -->
    <div class="koi-section">
        <h2><span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span>CSVインポート実行</h2>
        <div class="koi-import-form">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('koi_ria_csv_import', 'koi_ria_csv_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="import_type">インポート対象</label></th>
                        <td>
                            <select id="import_type" name="import_type" required style="min-width:280px;">
                                <option value="">-- 選択してください --</option>
                                <option value="show">番組（show）</option>
                                <option value="season">シーズン（season）</option>
                                <option value="cast">出演者（cast）</option>
                                <option value="relation">相関図（relation）</option>
                                <option value="youtube_video">YouTube動画（youtube_video）</option>
                            </select>
                            <p class="description">インポートするデータの種類を選択します</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="import_mode">取り込みモード</label></th>
                        <td>
                            <select id="import_mode" name="import_mode" style="min-width:280px;">
                                <option value="upsert">上書き（UPSERT）: 既存データを更新、なければ新規追加</option>
                                <option value="add">追加のみ: 既存をスキップし新規のみ追加</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="csv_file">CSVファイル</label></th>
                        <td>
                            <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                            <p class="description">UTF-8エンコードのCSVファイルをアップロードしてください（BOM付きも対応）</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('インポート実行', 'primary large', 'submit', true); ?>
            </form>
        </div>
    </div>

    <!-- Section 4: CSVフォーマット詳細 -->
    <div class="koi-section">
        <h2><span class="dashicons dashicons-editor-table" style="vertical-align:middle;margin-right:4px;"></span>CSVフォーマット詳細</h2>

        <div class="koi-format-section">
            <h3>番組CSV（show）</h3>
            <table class="widefat striped">
                <thead><tr><th>カラム名</th><th>必須/任意</th><th>説明</th><th>値の例</th></tr></thead>
                <tbody>
                    <tr><td><code>slug</code></td><td class="required">必須</td><td>番組のスラッグ（URLに使用、重複判定キー）</td><td>kyou-suki</td></tr>
                    <tr><td><code>title</code></td><td class="required">必須</td><td>番組の正式名称</td><td>今日、好きになりました。</td></tr>
                    <tr><td><code>short_name</code></td><td class="optional">任意</td><td>番組の略称</td><td>今日好き</td></tr>
                    <tr><td><code>platform</code></td><td class="optional">任意</td><td>配信プラットフォーム名</td><td>ABEMA</td></tr>
                    <tr><td><code>youtube_channel_id</code></td><td class="optional">任意</td><td>YouTubeチャンネルID</td><td>UCxxxxxxxxxx</td></tr>
                    <tr><td><code>affiliate_url</code></td><td class="optional">任意</td><td>アフィリエイトリンクURL</td><td>https://...</td></tr>
                    <tr><td><code>show_status</code></td><td class="optional">任意</td><td>放送状態</td><td>放送中 / 配信中 / 過去作</td></tr>
                    <tr><td><code>genre</code></td><td class="optional">任意</td><td>ジャンル</td><td>青春恋愛</td></tr>
                    <tr><td><code>target</code></td><td class="optional">任意</td><td>ターゲット層</td><td>中高生</td></tr>
                    <tr><td><code>priority</code></td><td class="optional">任意</td><td>表示優先度（数値、小さい方が優先）</td><td>1</td></tr>
                </tbody>
            </table>

            <h3>シーズンCSV（season）</h3>
            <table class="widefat striped">
                <thead><tr><th>カラム名</th><th>必須/任意</th><th>説明</th><th>値の例</th></tr></thead>
                <tbody>
                    <tr><td><code>show_slug</code></td><td class="required">必須</td><td>紐づく番組のスラッグ</td><td>kyou-suki</td></tr>
                    <tr><td><code>season_name</code></td><td class="required">必須</td><td>シーズン名（重複判定キー）</td><td>グアム編</td></tr>
                    <tr><td><code>year</code></td><td class="optional">任意</td><td>放送年</td><td>2026</td></tr>
                    <tr><td><code>badge</code></td><td class="optional">任意</td><td>バッジラベル</td><td>ON AIR / NEW</td></tr>
                    <tr><td><code>order</code></td><td class="optional">任意</td><td>表示順（数値）</td><td>1</td></tr>
                </tbody>
            </table>

            <h3>出演者CSV（cast）</h3>
            <table class="widefat striped">
                <thead><tr><th>カラム名</th><th>必須/任意</th><th>説明</th><th>値の例</th></tr></thead>
                <tbody>
                    <tr><td><code>show_slug</code></td><td class="required">必須</td><td>紐づく番組のスラッグ</td><td>kyou-suki</td></tr>
                    <tr><td><code>season_name</code></td><td class="required">必須</td><td>紐づくシーズン名</td><td>グアム編</td></tr>
                    <tr><td><code>name</code></td><td class="required">必須</td><td>出演者のフルネーム（重複判定キー）</td><td>山田あいり</td></tr>
                    <tr><td><code>display_name</code></td><td class="optional">任意</td><td>表示名（省略時はnameを使用）</td><td>あいり</td></tr>
                    <tr><td><code>ig_username</code></td><td class="optional">任意</td><td>Instagramユーザー名</td><td>airi_yamada</td></tr>
                    <tr><td><code>tiktok_username</code></td><td class="optional">任意</td><td>TikTokユーザー名</td><td>airi_tk</td></tr>
                    <tr><td><code>x_username</code></td><td class="optional">任意</td><td>X（旧Twitter）ユーザー名</td><td>airi_y</td></tr>
                    <tr><td><code>youtube_url</code></td><td class="optional">任意</td><td>YouTubeチャンネルURL</td><td>https://youtube.com/...</td></tr>
                    <tr><td><code>role</code></td><td class="optional">任意</td><td>出演者の役割</td><td>女子メンバー</td></tr>
                    <tr><td><code>gender</code></td><td class="optional">任意</td><td>性別（m/f）</td><td>f</td></tr>
                    <tr><td><code>age</code></td><td class="optional">任意</td><td>年齢（数値）</td><td>17</td></tr>
                    <tr><td><code>from_area</code></td><td class="optional">任意</td><td>出身地</td><td>東京都</td></tr>
                    <tr><td><code>cast_status</code></td><td class="optional">任意</td><td>出演状態</td><td>出演中 / 卒業</td></tr>
                </tbody>
            </table>

            <h3>相関図CSV（relation）</h3>
            <table class="widefat striped">
                <thead><tr><th>カラム名</th><th>必須/任意</th><th>説明</th><th>値の例</th></tr></thead>
                <tbody>
                    <tr><td><code>show_slug</code></td><td class="required">必須</td><td>紐づく番組のスラッグ</td><td>kyou-suki</td></tr>
                    <tr><td><code>season_name</code></td><td class="required">必須</td><td>紐づくシーズン名</td><td>グアム編</td></tr>
                    <tr><td><code>from_name</code></td><td class="required">必須</td><td>関係元の出演者名（display_name）</td><td>あいり</td></tr>
                    <tr><td><code>to_name</code></td><td class="required">必須</td><td>関係先の出演者名（display_name）</td><td>たくや</td></tr>
                    <tr><td><code>type</code></td><td class="optional">任意</td><td>関係タイプ</td><td>love / interest / rival</td></tr>
                    <tr><td><code>label</code></td><td class="optional">任意</td><td>関係のラベル</td><td>両思い / 片思い</td></tr>
                </tbody>
            </table>

            <h3>YouTube動画CSV（youtube_video）</h3>
            <table class="widefat striped">
                <thead><tr><th>カラム名</th><th>必須/任意</th><th>説明</th><th>値の例</th></tr></thead>
                <tbody>
                    <tr><td><code>url</code></td><td class="required">必須*</td><td>YouTube動画のURL（video_idが無い場合は必須）</td><td>https://www.youtube.com/watch?v=xxxxx</td></tr>
                    <tr><td><code>video_id</code></td><td class="optional">任意</td><td>YouTube動画ID（URLから自動取得可）</td><td>dQw4w9WgXcQ</td></tr>
                    <tr><td><code>title</code></td><td class="optional">任意</td><td>動画タイトル</td><td>【今日好き】第1話</td></tr>
                    <tr><td><code>channel_name</code></td><td class="optional">任意</td><td>チャンネル名</td><td>今日好き公式</td></tr>
                    <tr><td><code>thumbnail_url</code></td><td class="optional">任意</td><td>サムネイルURL（省略時は自動生成）</td><td>https://img.youtube.com/vi/.../hqdefault.jpg</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <hr>

    <div class="koi-section">
        <h2><span class="dashicons dashicons-info" style="vertical-align:middle;margin-right:4px;"></span>重複判定キー</h2>
        <table class="widefat" style="max-width: 600px;">
            <thead>
                <tr><th>対象</th><th>判定キー</th></tr>
            </thead>
            <tbody>
                <tr><td>番組</td><td><code>slug</code></td></tr>
                <tr><td>シーズン</td><td><code>show_slug</code> + <code>season_name</code></td></tr>
                <tr><td>出演者</td><td><code>show_slug</code> + <code>season_name</code> + <code>name</code></td></tr>
                <tr><td>相関図</td><td><code>show_slug</code> + <code>season_name</code> + <code>from_name</code> + <code>to_name</code></td></tr>
                <tr><td>YouTube動画</td><td><code>video_id</code>（URLから自動抽出）</td></tr>
            </tbody>
        </table>
    </div>
</div>
