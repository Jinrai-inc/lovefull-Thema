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
$ig_cache        = get_post_meta($cast->ID, 'ig_profile_cache', true);

$avatar_url = '';
if ($profile_image && isset($profile_image['url'])) {
    $avatar_url = $profile_image['url'];
} elseif ($ig_cache) {
    $avatar_url = $ig_cache;
}

// 番組名取得（推しボタン用）
$_show_id   = get_field('show', $cast->ID);
$_show_title = '';
if ($_show_id) {
    $_show_post = is_array($_show_id) ? get_post($_show_id[0]) : get_post($_show_id);
    if ($_show_post) {
        $_show_title = get_field('short_name', $_show_post->ID) ?: $_show_post->post_title;
    }
}
?>

<a href="<?php echo esc_url(get_permalink($cast)); ?>" class="cast-card" style="position: relative;">
    <?php get_template_part('template-parts/fav-button', null, [
        'cast_id'    => $cast->ID,
        'name'       => $display_name,
        'ig'         => $ig_username,
        'show_title' => $_show_title,
    ]); ?>
    <div class="ig-avatar" style="margin: 0 auto;">
        <?php if ($avatar_url) : ?>
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ig-avatar__img" loading="lazy">
        <?php else : ?>
            <div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f0f0f0;">&#x1F464;</div>
        <?php endif; ?>
    </div>
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
