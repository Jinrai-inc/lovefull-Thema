<?php
/**
 * Instagram プロフィール画像取得（unavatar.io + ローカルキャッシュ方式）
 *
 * Facebook API連携不要。unavatar.ioを経由してIG画像を取得し、
 * wp-content/uploads/ig-cache/ にローカル保存する。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// デフォルト値。管理画面の設定で上書き可能。
if (!defined('KOI_RIA_AVATAR_CACHE_DAYS')) {
    define('KOI_RIA_AVATAR_CACHE_DAYS', (int) get_option('koi_ria_avatar_cache_days', 7));
}

/**
 * IGアバターのURLを取得する（キャッシュ優先）
 */
function koi_ria_get_ig_avatar(string $ig_username, int $size = 200): string {
    if (empty($ig_username)) {
        return koi_ria_get_fallback_avatar($ig_username, $size);
    }

    $upload_dir = wp_upload_dir();
    $cache_dir  = $upload_dir['basedir'] . '/ig-cache/';
    $cache_url  = $upload_dir['baseurl'] . '/ig-cache/';
    $filename   = sanitize_file_name($ig_username) . '.jpg';
    $filepath   = $cache_dir . $filename;

    // キャッシュが有効期間内ならローカル画像を返す
    if (file_exists($filepath) && (time() - filemtime($filepath)) < KOI_RIA_AVATAR_CACHE_DAYS * DAY_IN_SECONDS) {
        return $cache_url . $filename;
    }

    // unavatar.ioから取得してローカルに保存
    $fetched = koi_ria_fetch_and_cache_avatar($ig_username, $filepath);

    if ($fetched) {
        return $cache_url . $filename;
    }

    // 取得失敗: 古いキャッシュがあればそれを返す
    if (file_exists($filepath)) {
        return $cache_url . $filename;
    }

    return koi_ria_get_fallback_avatar($ig_username, $size);
}

/**
 * unavatar.ioから画像を取得してローカルに保存する
 */
function koi_ria_fetch_and_cache_avatar(string $ig_username, string $filepath): bool {
    $url = 'https://unavatar.io/instagram/' . urlencode($ig_username);

    // 有料プランのAPIキーがあれば付与
    $api_key = get_option('koi_ria_unavatar_api_key', '');
    if ($api_key) {
        $url = add_query_arg('apiKey', $api_key, $url);
    }

    $response = wp_remote_get($url, [
        'timeout'   => 15,
        'sslverify' => true,
        'headers'   => [
            'User-Agent' => 'KoiRiaPortal/1.0 (WordPress)',
        ],
    ]);

    if (is_wp_error($response)) {
        error_log('[KoiRia] Avatar fetch error for @' . $ig_username . ': ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        error_log('[KoiRia] Avatar fetch HTTP ' . $code . ' for @' . $ig_username);
        return false;
    }

    $body = wp_remote_retrieve_body($response);

    if (strlen($body) < 1024) {
        error_log('[KoiRia] Avatar too small for @' . $ig_username . ' (' . strlen($body) . ' bytes)');
        return false;
    }

    $dir = dirname($filepath);
    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }

    $result = file_put_contents($filepath, $body);

    if ($result === false) {
        error_log('[KoiRia] Failed to write avatar file: ' . $filepath);
        return false;
    }

    return true;
}

/**
 * フォールバックアバター（名前のイニシャル画像）
 */
function koi_ria_get_fallback_avatar(string $name = '', int $size = 200): string {
    $display = !empty($name) ? $name : '?';
    return 'https://ui-avatars.com/api/?name=' . urlencode($display)
         . '&background=FF3B6F&color=fff&size=' . intval($size)
         . '&font-size=0.4&bold=true';
}

/**
 * テンプレート用ヘルパー: <img>タグを出力
 */
function koi_ria_ig_avatar(string $ig_username, int $size = 80, string $class = 'ig-avatar'): void {
    $url = koi_ria_get_ig_avatar($ig_username, $size);
    $alt = esc_attr($ig_username);
    $fallback = esc_url(koi_ria_get_fallback_avatar($ig_username, $size));
    echo '<img src="' . esc_url($url) . '" alt="@' . $alt . '" '
       . 'width="' . intval($size) . '" height="' . intval($size) . '" '
       . 'class="' . esc_attr($class) . '" loading="lazy" '
       . 'onerror="this.src=\'' . $fallback . '\'" />';
}
