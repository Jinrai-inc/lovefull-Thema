<?php
/**
 * Instagram プロフィール画像取得（unavatar.io + ローカルキャッシュ方式）
 *
 * Facebook API連携不要。unavatar.ioを経由してIG画像を取得し、
 * wp-content/uploads/ig-cache/ にローカル保存する。
 *
 * 重要: ページ表示時はローカルキャッシュ or フォールバックのみ。
 * 外部APIへのリクエストはCron（バックグラウンド）でのみ行う。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

if (!defined('KOI_RIA_AVATAR_CACHE_DAYS')) {
    define('KOI_RIA_AVATAR_CACHE_DAYS', (int) get_option('koi_ria_avatar_cache_days', 7));
}

/**
 * IGアバターのURLを取得する（キャッシュのみ参照、外部リクエストしない）
 *
 * キャッシュがなければフォールバック画像を即座に返す。
 * 実際の画像取得はCronまたは手動実行で行う。
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

    // ローカルキャッシュがあればそのまま返す（期限切れでも表示優先）
    if (file_exists($filepath) && filesize($filepath) > 1024) {
        return $cache_url . $filename;
    }

    // キャッシュなし → フォールバック画像を即座に返す
    return koi_ria_get_fallback_avatar($ig_username, $size);
}

/**
 * unavatar.ioから画像を取得してローカルに保存する
 *
 * ※ この関数はCronからのみ呼ぶこと（ページ表示中に呼ばない）
 */
function koi_ria_fetch_and_cache_avatar(string $ig_username, string $filepath): bool {
    $url = 'https://unavatar.io/instagram/' . urlencode($ig_username);

    $api_key = get_option('koi_ria_unavatar_api_key', '');
    if ($api_key) {
        $url = add_query_arg('apiKey', $api_key, $url);
    }

    $response = wp_remote_get($url, [
        'timeout'     => 15,
        'sslverify'   => true,
        'decompress'  => true,
        'headers'     => [
            'User-Agent'      => 'KoiRiaPortal/1.0 (WordPress)',
            'Accept'          => 'image/*',
            'Accept-Encoding' => 'identity',
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
    $content_type = wp_remote_retrieve_header($response, 'content-type');

    // ZIP/圧縮ファイルを除外
    if ($content_type && (
        strpos($content_type, 'zip') !== false ||
        strpos($content_type, 'octet-stream') !== false ||
        strpos($content_type, 'gzip') !== false
    )) {
        error_log('[KoiRia] Avatar returned non-image content-type for @' . $ig_username . ': ' . $content_type);
        return false;
    }

    // 画像データの検証
    if (strlen($body) < 1024) {
        error_log('[KoiRia] Avatar too small for @' . $ig_username . ' (' . strlen($body) . ' bytes)');
        return false;
    }

    // 画像ヘッダーチェック（JPEG/PNG/GIF/WebP）
    $magic = substr($body, 0, 4);
    $is_image = (
        substr($body, 0, 2) === "\xFF\xD8" ||         // JPEG
        substr($body, 0, 8) === "\x89PNG\r\n\x1A\n" || // PNG
        substr($body, 0, 4) === "GIF8" ||               // GIF
        substr($body, 0, 4) === "RIFF"                  // WebP
    );

    if (!$is_image) {
        error_log('[KoiRia] Avatar data is not a valid image for @' . $ig_username . ' (magic: ' . bin2hex($magic) . ')');
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
