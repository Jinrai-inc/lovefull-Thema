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
        'API設定',
        'API設定',
        'manage_options',
        'koi-ria-settings',
        'koi_ria_settings_page'
    );
}

/**
 * CSVインポートページ
 */
function koi_ria_import_page(): void {
    $import_page = KOI_RIA_DIR . '/admin/csv-import-page.php';
    if (file_exists($import_page)) {
        require_once $import_page;
    } else {
        echo '<div class="wrap"><h1>恋リアデータ管理</h1><p>CSVインポート機能は Phase2 で実装予定です。</p></div>';
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
        echo '<div class="notice notice-success"><p>設定を保存しました。</p></div>';
    }

    $youtube_key = get_option('koi_ria_youtube_api_key', '');
    $ig_token    = get_option('koi_ria_ig_access_token', '');
    $ig_user_id  = get_option('koi_ria_ig_user_id', '');
    ?>
    <div class="wrap">
        <h1>API設定</h1>
        <form method="post">
            <?php wp_nonce_field('koi_ria_save_settings', 'koi_ria_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="youtube_api_key">YouTube Data API Key</label></th>
                    <td><input type="text" id="youtube_api_key" name="youtube_api_key" value="<?php echo esc_attr($youtube_key); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ig_access_token">Instagram Access Token</label></th>
                    <td><input type="text" id="ig_access_token" name="ig_access_token" value="<?php echo esc_attr($ig_token); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ig_user_id">Instagram User ID</label></th>
                    <td><input type="text" id="ig_user_id" name="ig_user_id" value="<?php echo esc_attr($ig_user_id); ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('設定を保存'); ?>
        </form>
    </div>
    <?php
}
