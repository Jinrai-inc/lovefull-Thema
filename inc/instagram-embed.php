<?php
/**
 * Instagram URL 自動埋め込み
 *
 * 記事本文中の Instagram URL を自動的に埋め込みウィジェットに変換する。
 * 対応パターン:
 *   - プロフィール: https://www.instagram.com/username/
 *   - 投稿: https://www.instagram.com/p/XXXXX/
 *   - リール: https://www.instagram.com/reel/XXXXX/
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * the_content フィルタで Instagram URL を埋め込みに変換
 */
add_filter('the_content', 'koi_ria_embed_instagram_urls', 8);

function koi_ria_embed_instagram_urls(string $content): string {
    if (empty($content) || !is_singular('post')) {
        return $content;
    }

    // <a> タグ内のURLは除外し、ベアURL（行単独）のみ対象
    // パターン: 行頭のInstagram URL（前後にHTMLタグがない）
    $pattern = '#(?:<p>)?\s*(https?://(?:www\.)?instagram\.com/((?:p|reel)/[\w\-]+|[\w][\w.\-]*[\w])/?(?:\?[^\s<]*)?)\s*(?:</p>)?#i';

    $content = preg_replace_callback($pattern, function ($matches) {
        $url  = esc_url($matches[1]);
        $path = rtrim($matches[2], '/');

        // <a>タグ内にある場合はスキップ（前後のコンテキストチェック）
        if (strpos($matches[0], 'href=') !== false) {
            return $matches[0];
        }

        // プロフィールか投稿/リールかを判定
        if (preg_match('#^(p|reel)/#', $path)) {
            // 投稿・リール埋め込み
            return koi_ria_ig_post_embed($url);
        } else {
            // プロフィール埋め込み
            $username = $path;
            return koi_ria_ig_profile_embed($username, $url);
        }
    }, $content);

    return $content;
}

/**
 * Instagram 投稿/リール 埋め込み HTML
 */
function koi_ria_ig_post_embed(string $url): string {
    $embed_url = rtrim($url, '/') . '/embed/';
    return '<div class="ig-embed ig-embed--post">'
        . '<iframe src="' . esc_url($embed_url) . '" frameborder="0" scrolling="no" allowtransparency="true" loading="lazy"></iframe>'
        . '</div>';
}

/**
 * Instagram プロフィール リンクカード HTML
 * ※ Instagramはプロフィールのiframe埋め込みを制限しているため、スタイリッシュなリンクカードで表示
 */
function koi_ria_ig_profile_embed(string $username, string $url): string {
    $username = sanitize_text_field($username);

    return '<div class="ig-embed ig-embed--profile">'
        . '<a href="' . esc_url($url) . '" target="_blank" rel="noopener nofollow" class="ig-profile-card">'
        . '<div class="ig-profile-card__icon">' . koi_ria_icon('instagram', 36) . '</div>'
        . '<div class="ig-profile-card__info">'
        . '<span class="ig-profile-card__username">@' . esc_html($username) . '</span>'
        . '<span class="ig-profile-card__label">Instagramプロフィール</span>'
        . '</div>'
        . '<span class="ig-profile-card__btn">Instagramで見る</span>'
        . '</a>'
        . '</div>';
}
