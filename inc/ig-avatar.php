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

    static $upload_dir_cache = null;
    if ($upload_dir_cache === null) {
        $upload_dir_cache = wp_upload_dir();
    }

    $cache_dir  = $upload_dir_cache['basedir'] . '/ig-cache/';
    $cache_url  = $upload_dir_cache['baseurl'] . '/ig-cache/';
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
 *
 * 取得順序:
 * 1. unavatar.io/instagram/{username} — Instagram直接
 * 2. unavatar.io/{username} — 全プラットフォーム自動検索（フォールバック）
 */
function koi_ria_fetch_and_cache_avatar(string $ig_username, string $filepath): bool {
    $api_key = get_option('koi_ria_unavatar_api_key', '');

    // 試行するURLリスト（Instagram専用 → 汎用フォールバック）
    $urls = [
        'https://unavatar.io/instagram/' . urlencode($ig_username),
        'https://unavatar.io/' . urlencode($ig_username),
    ];

    foreach ($urls as $url) {
        if ($api_key) {
            $url = add_query_arg('apiKey', $api_key, $url);
        }

        $body = koi_ria_download_avatar_image($url, $ig_username);
        if ($body !== false) {
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
    }

    return false;
}

/**
 * URLから画像データをダウンロード＆検証する
 *
 * @return string|false 有効な画像データ or false
 */
function koi_ria_download_avatar_image(string $url, string $ig_username) {
    $response = wp_remote_get($url, [
        'timeout'     => 15,
        'sslverify'   => true,
        'decompress'  => true,
        'redirection' => 5,
        'headers'     => [
            'User-Agent'      => 'KoiRiaPortal/1.0 (WordPress)',
            'Accept'          => 'image/jpeg, image/png, image/webp, image/gif, image/*;q=0.9',
            'Accept-Encoding' => 'identity',
        ],
    ]);

    if (is_wp_error($response)) {
        error_log('[KoiRia] Avatar fetch error for @' . $ig_username . ' (' . $url . '): ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        error_log('[KoiRia] Avatar fetch HTTP ' . $code . ' for @' . $ig_username . ' (' . $url . ')');
        return false;
    }

    $body = wp_remote_retrieve_body($response);

    // gzip圧縮されたデータを自動解凍
    $body = koi_ria_maybe_decompress($body);

    // 画像データの検証
    if (strlen($body) < 1024) {
        error_log('[KoiRia] Avatar too small for @' . $ig_username . ' (' . strlen($body) . ' bytes)');
        return false;
    }

    // 画像ヘッダーチェック（JPEG/PNG/GIF/WebP）
    if (!koi_ria_is_valid_image($body)) {
        $magic = substr($body, 0, 4);
        error_log('[KoiRia] Avatar data is not a valid image for @' . $ig_username . ' (magic: ' . bin2hex($magic) . ', size: ' . strlen($body) . ')');
        return false;
    }

    return $body;
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
 * 管理画面: 個別ユーザー名テスト（AJAX）
 */
add_action('wp_ajax_koi_ria_test_avatar', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('権限がありません');
    }
    check_ajax_referer('koi_ria_test_avatar');

    $username = sanitize_text_field($_POST['username'] ?? '');
    if (empty($username)) {
        wp_send_json_error('ユーザー名を入力してください');
    }

    $upload_dir = wp_upload_dir();
    $filepath   = $upload_dir['basedir'] . '/ig-cache/' . sanitize_file_name($username) . '.jpg';

    $result = koi_ria_fetch_and_cache_avatar($username, $filepath);

    if ($result) {
        $cache_url = $upload_dir['baseurl'] . '/ig-cache/' . sanitize_file_name($username) . '.jpg';
        wp_send_json_success([
            'message' => '@' . $username . ' のアバターを取得・保存しました',
            'url'     => $cache_url . '?t=' . time(),
            'size'    => filesize($filepath),
        ]);
    } else {
        wp_send_json_error('@' . $username . ' のアバター取得に失敗しました。エラーログを確認してください。');
    }
});

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

/**
 * 圧縮データを検出して自動解凍する
 *
 * gzip / ZIP でラップされた画像データを展開して画像本体を返す。
 * 既に画像データならそのまま返す。
 */
function koi_ria_maybe_decompress(string $data): string {
    if (strlen($data) < 4) {
        return $data;
    }

    // 既に有効な画像ならそのまま返す
    if (koi_ria_is_valid_image($data)) {
        return $data;
    }

    // gzip (magic: 1f 8b)
    if (substr($data, 0, 2) === "\x1F\x8B" && function_exists('gzdecode')) {
        $decoded = @gzdecode($data);
        if ($decoded !== false && koi_ria_is_valid_image($decoded)) {
            return $decoded;
        }
    }

    // zlib / deflate (magic: 78 01, 78 9c, 78 da)
    $zlib_byte = ord($data[0]);
    if ($zlib_byte === 0x78 && function_exists('gzuncompress')) {
        $decoded = @gzuncompress($data);
        if ($decoded !== false && koi_ria_is_valid_image($decoded)) {
            return $decoded;
        }
    }

    // PKzip (magic: PK\x03\x04) — ZIPアーカイブ内の最初のファイルを展開
    if (substr($data, 0, 4) === "PK\x03\x04" && class_exists('ZipArchive')) {
        $tmp = wp_tempnam('avatar_zip_');
        file_put_contents($tmp, $data);

        $zip = new ZipArchive();
        if ($zip->open($tmp) === true && $zip->numFiles > 0) {
            $contents = $zip->getFromIndex(0);
            $zip->close();
            @unlink($tmp);
            if ($contents !== false && koi_ria_is_valid_image($contents)) {
                return $contents;
            }
        } else {
            @unlink($tmp);
        }
    }

    return $data;
}

/**
 * バイナリデータが有効な画像かチェック
 */
function koi_ria_is_valid_image(string $data): bool {
    if (strlen($data) < 4) {
        return false;
    }
    return (
        substr($data, 0, 2) === "\xFF\xD8" ||         // JPEG
        substr($data, 0, 8) === "\x89PNG\r\n\x1A\n" || // PNG
        substr($data, 0, 4) === "GIF8" ||               // GIF
        substr($data, 0, 4) === "RIFF"                  // WebP
    );
}
