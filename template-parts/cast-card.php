<?php
/**
 * 出演者カード
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$cast = $args['cast'] ?? null;
if (!$cast) return;

$display_name    = get_field('display_name', $cast->ID) ?: $cast->post_title;
$ig_username     = get_field('ig_username', $cast->ID) ?: '';
$tiktok_username = get_field('tiktok_username', $cast->ID) ?: '';
$followers_count = get_field('followers_count', $cast->ID) ?: 0;
$profile_image   = get_field('profile_image', $cast->ID);

$avatar_url = '';
if ($profile_image && isset($profile_image['url'])) {
    $avatar_url = $profile_image['url'];
}

// 番組情報取得（推しボタン用 + ロゴ画像）- 静的キャッシュで同一showの重複クエリ防止
static $_show_cache = [];
$_show_id   = get_field('show', $cast->ID);
$_show_title = '';
$_show_logo_url = '';
if ($_show_id) {
    $_sid = is_array($_show_id) ? $_show_id[0] : $_show_id;
    if (is_object($_sid)) $_sid = $_sid->ID ?? 0;
    if (!isset($_show_cache[$_sid])) {
        $_show_post = get_post($_sid);
        $_show_cache[$_sid] = [
            'title' => $_show_post ? (get_field('short_name', $_show_post->ID) ?: $_show_post->post_title) : '',
            'logo'  => $_show_post ? get_the_post_thumbnail_url($_show_post->ID, 'cast-avatar') : '',
        ];
    }
    $_show_title = $_show_cache[$_sid]['title'];
    $_show_logo_url = $_show_cache[$_sid]['logo'];
}

// アバター: プロフィール画像 → 番組ロゴ → フォールバック
if (!$avatar_url && $_show_logo_url) {
    $avatar_url = $_show_logo_url;
}
?>

<a href="<?php echo esc_url(get_permalink($cast)); ?>" class="cast-card" style="position: relative;">
    <?php get_template_part('template-parts/fav-button', null, [
        'cast_id'    => $cast->ID,
        'name'       => $display_name,
        'ig'         => $ig_username,
        'show_title' => $_show_title,
    ]); ?>
    <?php if ($avatar_url && !$profile_image && $_show_logo_url) : ?>
        <div class="cast-card__show-logo">
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($_show_title); ?>" class="cast-card__show-logo-img" loading="lazy">
        </div>
    <?php elseif ($avatar_url) : ?>
        <div class="ig-avatar" style="margin: 0 auto;">
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ig-avatar__img" loading="lazy">
        </div>
    <?php else : ?>
        <div class="ig-avatar" style="margin: 0 auto;">
            <div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;background:#f0f0f0;"><?php echo koi_ria_icon('users', 24); ?></div>
        </div>
    <?php endif; ?>
    <div class="cast-card__name"><?php echo esc_html($display_name); ?></div>
    <?php if ($followers_count) : ?>
        <div class="cast-card__followers"><?php echo esc_html(number_format($followers_count)); ?> followers</div>
    <?php endif; ?>
    <div class="cast-card__sns">
        <?php if ($ig_username) : ?>
            <span class="btn btn--ig btn--sm" onclick="event.preventDefault(); window.open('https://instagram.com/<?php echo esc_attr($ig_username); ?>', '_blank');">IG</span>
        <?php endif; ?>
        <?php if ($tiktok_username) : ?>
            <span class="btn btn--tiktok btn--sm" onclick="event.preventDefault(); window.open('https://tiktok.com/@<?php echo esc_attr($tiktok_username); ?>', '_blank');">TikTok</span>
        <?php endif; ?>
    </div>
</a>
