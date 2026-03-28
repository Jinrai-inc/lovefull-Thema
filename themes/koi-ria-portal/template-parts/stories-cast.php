<?php
/**
 * ストーリーズ型出演者横スクロール（Instagram風丸アバター）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$popular_cast = get_posts([
    'post_type'      => 'cast',
    'posts_per_page' => 15,
    'meta_key'       => 'followers_count',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

if (empty($popular_cast)) {
    // フォロワー数未設定の場合は最新の出演者を取得
    $popular_cast = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => 15,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

if (empty($popular_cast)) {
    return;
}
?>

<section class="section">
    <div class="section-header">
        <h2>&#x1F525; 話題の出演者</h2>
        <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>" class="section-header__more">もっと見る →</a>
    </div>
    <div class="scroll-x stories-scroll">
        <?php foreach ($popular_cast as $cast) :
            $display_name    = get_field('display_name', $cast->ID) ?: $cast->post_title;
            $ig_username     = get_field('ig_username', $cast->ID) ?: '';
            $tiktok_username = get_field('tiktok_username', $cast->ID) ?: '';
            $profile_image   = get_field('profile_image', $cast->ID);
            $ig_cache        = get_post_meta($cast->ID, 'ig_profile_cache', true);

            $avatar_url = '';
            if ($profile_image && isset($profile_image['url'])) {
                $avatar_url = $profile_image['url'];
            } elseif ($ig_cache) {
                $avatar_url = $ig_cache;
            }

            $show_id = get_field('show', $cast->ID);
            $show_name = '';
            if ($show_id) {
                $sp = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
                $show_name = $sp ? (get_field('short_name', $sp->ID) ?: '') : '';
            }
        ?>
        <a href="<?php echo esc_url(get_permalink($cast)); ?>" class="stories-item">
            <div class="ig-avatar">
                <?php if ($avatar_url) : ?>
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ig-avatar__img" loading="lazy">
                <?php else : ?>
                    <div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f0f0f0;">&#x1F464;</div>
                <?php endif; ?>
            </div>
            <span class="stories-item__name"><?php echo esc_html($display_name); ?></span>
            <span class="stories-item__sns">
                <?php if ($ig_username) : ?>
                    <span class="stories-item__sns-icon stories-item__sns-icon--ig" title="Instagram">IG</span>
                <?php endif; ?>
                <?php if ($tiktok_username) : ?>
                    <span class="stories-item__sns-icon stories-item__sns-icon--tiktok" title="TikTok">TT</span>
                <?php endif; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</section>
