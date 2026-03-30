<?php
/**
 * はじめにガイド - Getting Started Page
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$acf_active = class_exists('ACF');
?>
<style>
    .koi-ria-guide .step-card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-left: 4px solid #e91e63;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .koi-ria-guide .step-card h2 {
        margin: 0 0 12px 0;
        padding: 0;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .koi-ria-guide .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e91e63;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .koi-ria-guide .step-card p,
    .koi-ria-guide .step-card ul {
        margin-left: 38px;
    }
    .koi-ria-guide .step-card ul {
        list-style: none;
        padding: 0;
    }
    .koi-ria-guide .step-card ul li {
        padding: 4px 0;
        line-height: 1.6;
    }
    .koi-ria-guide .step-card ul li .dashicons {
        color: #e91e63;
        margin-right: 4px;
        vertical-align: text-bottom;
    }
    .koi-ria-guide .field-table {
        margin-left: 38px;
        border-collapse: collapse;
        width: calc(100% - 38px);
        max-width: 780px;
    }
    .koi-ria-guide .field-table th,
    .koi-ria-guide .field-table td {
        border: 1px solid #ddd;
        padding: 8px 12px;
        text-align: left;
        vertical-align: top;
    }
    .koi-ria-guide .field-table th {
        background: #f6f7f7;
        white-space: nowrap;
        width: 180px;
    }
    .koi-ria-guide .status-ok {
        color: #00a32a;
    }
    .koi-ria-guide .status-ng {
        color: #d63638;
    }
    .koi-ria-guide .action-link {
        display: inline-block;
        margin: 8px 0 0 38px;
        text-decoration: none;
    }
    .koi-ria-guide .page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    .koi-ria-guide .page-header .dashicons {
        font-size: 28px;
        width: 28px;
        height: 28px;
        color: #e91e63;
    }
    .koi-ria-guide code {
        background: #f0f0f1;
        padding: 2px 6px;
        border-radius: 3px;
    }
    .koi-ria-guide .slug-table {
        margin-left: 38px;
        border-collapse: collapse;
        max-width: 600px;
    }
    .koi-ria-guide .slug-table th,
    .koi-ria-guide .slug-table td {
        border: 1px solid #ddd;
        padding: 8px 12px;
        text-align: left;
    }
    .koi-ria-guide .slug-table th {
        background: #f6f7f7;
    }
</style>

<div class="wrap koi-ria-guide">
    <div class="page-header">
        <span class="dashicons dashicons-heart"></span>
        <h1>恋リアポータル - はじめにガイド</h1>
    </div>
    <p>恋リアポータルテーマのセットアップ手順です。上から順番に進めてください。</p>

    <hr style="margin: 20px 0;">

    <!-- Step 1 -->
    <div class="step-card">
        <h2><span class="step-number">1</span> 必要プラグインのインストール</h2>
        <p>このテーマにはカスタムフィールド管理のため、以下のプラグインが必要です。</p>
        <ul>
            <li>
                <?php if ($acf_active) : ?>
                    <span class="dashicons dashicons-yes-alt status-ok"></span>
                    <strong>ACF PRO (Advanced Custom Fields PRO)</strong> - <span class="status-ok">有効化済み</span>
                <?php else : ?>
                    <span class="dashicons dashicons-dismiss status-ng"></span>
                    <strong>ACF PRO (Advanced Custom Fields PRO)</strong> - <span class="status-ng">未インストールまたは無効</span>
                <?php endif; ?>
            </li>
        </ul>
        <p><em>ACF PRO はカスタム投稿タイプ（番組・出演者等）のフィールド管理に必須です。</em></p>
        <?php if (!$acf_active) : ?>
            <a href="<?php echo esc_url(admin_url('plugins.php')); ?>" class="button button-primary action-link">
                <span class="dashicons dashicons-admin-plugins" style="vertical-align:text-bottom; margin-right:4px;"></span>
                プラグイン画面へ
            </a>
        <?php endif; ?>
    </div>

    <!-- Step 2 -->
    <div class="step-card">
        <h2><span class="step-number">2</span> 番組を登録する</h2>
        <p><strong>管理画面 &rarr; 番組 &rarr; 新規追加</strong> から番組を登録します。</p>
        <table class="field-table">
            <tr>
                <th>タイトル</th>
                <td>番組名（例: <code>今日、好きになりました。</code>）</td>
            </tr>
            <tr>
                <th>略称</th>
                <td>短い名前（例: <code>今日好き</code>）- 一覧表示やタグに使用</td>
            </tr>
            <tr>
                <th>プラットフォーム</th>
                <td>ABEMA, Netflix, Amazon Prime Video 等の配信元</td>
            </tr>
            <tr>
                <th>YouTubeチャンネルID</th>
                <td>
                    YouTube APIで動画を自動取得するためのID<br>
                    <strong>取得方法:</strong> YouTubeチャンネルのURLから <code>UC...</code> で始まるIDをコピー<br>
                    例: <code>UCxxxxxxxxxxxxxxxxxxxxxxx</code>
                </td>
            </tr>
            <tr>
                <th>VOD配信情報</th>
                <td>配信先VODサービスと各URLを設定（ABEMA, Netflix, Hulu等）</td>
            </tr>
        </table>
        <?php if (post_type_exists('show')) : ?>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=show')); ?>" class="button button-primary action-link">
                <span class="dashicons dashicons-plus-alt" style="vertical-align:text-bottom; margin-right:4px;"></span>
                番組を新規追加
            </a>
        <?php endif; ?>
    </div>

    <!-- Step 3 -->
    <div class="step-card">
        <h2><span class="step-number">3</span> シーズンを登録する</h2>
        <p>番組ごとにシーズン（期・編）を作成し、コンテンツを整理します。</p>
        <ul>
            <li><span class="dashicons dashicons-arrow-right-alt2"></span> <strong>管理画面 &rarr; シーズン &rarr; 新規追加</strong> からシーズンを作成</li>
            <li><span class="dashicons dashicons-arrow-right-alt2"></span> 「番組」フィールドで紐付け先の番組を選択</li>
            <li><span class="dashicons dashicons-arrow-right-alt2"></span> シーズン名（例: <code>第35弾</code>）と放送期間を設定</li>
        </ul>
        <?php if (post_type_exists('season')) : ?>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=season')); ?>" class="button button-primary action-link">
                <span class="dashicons dashicons-plus-alt" style="vertical-align:text-bottom; margin-right:4px;"></span>
                シーズンを新規追加
            </a>
        <?php endif; ?>
    </div>

    <!-- Step 4 -->
    <div class="step-card">
        <h2><span class="step-number">4</span> 出演者（メンバー）を登録する</h2>
        <p><strong>管理画面 &rarr; 出演者 &rarr; 新規追加</strong> から出演者を登録します。</p>
        <table class="field-table">
            <tr>
                <th>タイトル</th>
                <td>本名またはニックネーム</td>
            </tr>
            <tr>
                <th>名前（表示用）</th>
                <td>サイト上に表示される名前</td>
            </tr>
            <tr>
                <th>番組・シーズン</th>
                <td>紐付け先の番組とシーズンを選択</td>
            </tr>
            <tr>
                <th>Instagram ID</th>
                <td>
                    @なしのユーザー名（例: <code>username</code>）<br>
                    自動フォロワー数取得に使用されます
                </td>
            </tr>
            <tr>
                <th>プロフィール画像</th>
                <td>手動アップロード、または Instagram API で自動取得</td>
            </tr>
        </table>
        <?php if (post_type_exists('member')) : ?>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=member')); ?>" class="button button-primary action-link">
                <span class="dashicons dashicons-plus-alt" style="vertical-align:text-bottom; margin-right:4px;"></span>
                出演者を新規追加
            </a>
        <?php endif; ?>
    </div>

    <!-- Step 5 -->
    <div class="step-card">
        <h2><span class="step-number">5</span> CSVで一括登録</h2>
        <p>大量のデータを登録する場合は、CSVファイルで一括インポートできます。</p>
        <ul>
            <li><span class="dashicons dashicons-arrow-right-alt2"></span> <strong>管理画面 &rarr; データ管理 &rarr; CSVインポート</strong></li>
        </ul>
        <p><strong>CSV形式の例（出演者）:</strong></p>
        <pre style="margin-left:38px; background:#f6f7f7; padding:12px; border:1px solid #ddd; max-width:780px; overflow-x:auto;">name,display_name,show,season,instagram_id
"山田太郎","たろう","今日好き","第35弾","taro_yamada"
"鈴木花子","はなこ","今日好き","第35弾","hanako_suzuki"</pre>
        <a href="<?php echo esc_url(admin_url('admin.php?page=koi-ria-import')); ?>" class="button button-primary action-link">
            <span class="dashicons dashicons-upload" style="vertical-align:text-bottom; margin-right:4px;"></span>
            CSVインポート画面へ
        </a>
    </div>

    <!-- Step 6 -->
    <div class="step-card">
        <h2><span class="step-number">6</span> API設定（自動取得）</h2>
        <p>外部サービスのAPIを設定して、コンテンツの自動取得を有効にします。</p>
        <table class="field-table">
            <tr>
                <th>YouTube Data API v3</th>
                <td>
                    Google Cloud Console で API キーを取得し設定<br>
                    番組に登録した YouTube チャンネルの動画を自動取得します
                </td>
            </tr>
            <tr>
                <th>Instagram Graph API</th>
                <td>
                    Meta for Developers でアクセストークンを取得し設定<br>
                    出演者のフォロワー数やプロフィール画像を自動更新します
                </td>
            </tr>
        </table>
        <p style="margin-left:38px;"><strong>設定場所:</strong> 管理画面 &rarr; データ管理 &rarr; API設定</p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=koi-ria-settings')); ?>" class="button button-primary action-link">
            <span class="dashicons dashicons-admin-network" style="vertical-align:text-bottom; margin-right:4px;"></span>
            API設定画面へ
        </a>
    </div>

    <!-- Step 7 -->
    <div class="step-card">
        <h2><span class="step-number">7</span> 固定ページを作成</h2>
        <p>以下の固定ページを作成し、対応するテンプレートを選択してください。</p>
        <table class="slug-table">
            <thead>
                <tr>
                    <th>スラッグ</th>
                    <th>ページ名</th>
                    <th>テンプレート</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>favorites</code></td>
                    <td>推しメンバー</td>
                    <td>推しメンバー</td>
                </tr>
                <tr>
                    <td><code>shindan</code></td>
                    <td>番組診断</td>
                    <td>番組診断</td>
                </tr>
                <tr>
                    <td><code>vod-search</code></td>
                    <td>VOD検索</td>
                    <td>VOD検索</td>
                </tr>
            </tbody>
        </table>
        <p style="margin-left:38px;"><em>固定ページ作成後、「ページ属性」でテンプレートを選択してください。</em></p>
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" class="button button-primary action-link">
            <span class="dashicons dashicons-admin-page" style="vertical-align:text-bottom; margin-right:4px;"></span>
            固定ページを新規追加
        </a>
    </div>

    <hr style="margin: 24px 0;">
    <p style="color:#646970;">
        <span class="dashicons dashicons-info" style="vertical-align:text-bottom; color:#646970;"></span>
        ご不明な点がある場合は、各ステップのリンクから該当ページへ直接アクセスできます。
    </p>
</div>
