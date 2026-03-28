<?php
/**
 * CSVインポート管理画面
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
?>
<div class="wrap">
    <h1>恋リアデータ管理 - CSVインポート</h1>

    <?php if ($results) : ?>
    <div class="notice notice-info">
        <p>
            インポート結果:
            新規 <strong><?php echo intval($results['created'] ?? 0); ?></strong>件 /
            更新 <strong><?php echo intval($results['updated'] ?? 0); ?></strong>件 /
            スキップ <strong><?php echo intval($results['skipped'] ?? 0); ?></strong>件 /
            エラー <strong><?php echo intval($results['error'] ?? 0); ?></strong>件
        </p>
        <?php if (!empty($results['errors'])) : ?>
        <details>
            <summary>エラー詳細</summary>
            <ul>
                <?php foreach ($results['errors'] as $err) : ?>
                    <li><?php echo esc_html($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </details>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="max-width: 600px;">
        <?php wp_nonce_field('koi_ria_csv_import', 'koi_ria_csv_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="import_type">インポート対象</label></th>
                <td>
                    <select id="import_type" name="import_type" required>
                        <option value="show">番組</option>
                        <option value="season">シーズン</option>
                        <option value="cast">出演者</option>
                        <option value="relation">相関図</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="import_mode">取り込みモード</label></th>
                <td>
                    <select id="import_mode" name="import_mode">
                        <option value="upsert">上書き（UPSERT）: 既存データを更新、なければ新規追加</option>
                        <option value="add">追加のみ: 既存をスキップし新規のみ追加</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="csv_file">CSVファイル</label></th>
                <td>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    <p class="description">UTF-8エンコードのCSVファイルをアップロードしてください</p>
                </td>
            </tr>
        </table>

        <?php submit_button('インポート実行'); ?>
    </form>

    <hr>

    <h2>CSVフォーマット</h2>

    <h3>番組CSV</h3>
    <code>slug,title,short_name,platform,emoji,youtube_channel_id,affiliate_url</code>

    <h3>シーズンCSV</h3>
    <code>show_slug,season_name,year,badge,order</code>

    <h3>出演者CSV</h3>
    <code>show_slug,season_name,name,ig_username,tiktok_username,role,gender,age,from_area</code>

    <h3>相関図CSV</h3>
    <code>show_slug,season_name,from_name,to_name,type,label</code>

    <hr>

    <h2>重複判定キー</h2>
    <table class="widefat" style="max-width: 600px;">
        <thead>
            <tr><th>対象</th><th>判定キー</th></tr>
        </thead>
        <tbody>
            <tr><td>番組</td><td>slug</td></tr>
            <tr><td>シーズン</td><td>show_slug + season_name</td></tr>
            <tr><td>出演者</td><td>show_slug + season_name + name</td></tr>
            <tr><td>相関図</td><td>show_slug + season_name + from_name + to_name</td></tr>
        </tbody>
    </table>
</div>
