<?php
/**
 * ストーリーズ型出演者横スクロール（Instagram風丸アバター）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$popular_cast = get_posts([
    'post_type'      => 'cast',
    'posts_per_page' => 8,
    'meta_key'       => 'followers_count',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

if (empty($popular_cast)) {
    // フォロワー数未設定の場合は最新の出演者を取得
    $popular_cast = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => 8,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

if (empty($popular_cast)) {
    return;
}
?>

<section class="section section--cast">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('fire', 22); ?> 話題の出演者</h2>
        <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>" class="section-header__more">もっと見る →</a>
    </div>
    <div class="scroll-x stories-scroll">
        <?php foreach ($popular_cast as $cast) :
            $display_name    = get_field('display_name', $cast->ID) ?: $cast->post_title;
            $ig_username     = get_field('ig_username', $cast->ID) ?: '';
            $tiktok_username = get_field('tiktok_username', $cast->ID) ?: '';
            $profile_image   = get_field('profile_image', $cast->ID);

            // アバターURL: 手動アップロード > 番組ロゴ > フォールバック
            $avatar_url = '';
            $_use_show_logo = false;
            if ($profile_image && isset($profile_image['url'])) {
                $avatar_url = $profile_image['url'];
            }

            $show_id = get_field('show', $cast->ID);
            $show_name = '';
            $_show_logo_url = '';
            if ($show_id) {
                $_sid = $show_id;
                if (is_array($_sid)) $_sid = $_sid[0];
                if (is_object($_sid)) $_sid = $_sid->ID ?? 0;
                $_sid = intval($_sid);
                $sp = $_sid ? get_post($_sid) : null;
                $show_name = $sp ? (get_field('short_name', $sp->ID) ?: '') : '';
                $_show_logo_url = $sp ? get_the_post_thumbnail_url($sp->ID, 'medium') : '';
            }

            if (!$avatar_url && $_show_logo_url) {
                $avatar_url = $_show_logo_url;
                $_use_show_logo = true;
            }
        ?>
        <a href="<?php echo esc_url(get_permalink($cast)); ?>" class="stories-item">
            <?php if ($_use_show_logo) : ?>
                <div class="stories-item__show-logo">
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($show_name); ?>" class="stories-item__show-logo-img" loading="lazy">
                </div>
            <?php elseif ($avatar_url) : ?>
                <div class="ig-avatar">
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ig-avatar__img" loading="lazy">
                </div>
            <?php else : ?>
                <div class="ig-avatar">
                    <div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f0f0f0;"><?php echo koi_ria_icon('users', 24); ?></div>
                </div>
            <?php endif; ?>
            <span class="stories-item__name"><?php echo esc_html($display_name); ?></span>
            <?php if ($show_name && $sp) :
                $show_platform = get_field('platform', $sp->ID) ?: '';
                $show_color = $show_platform ? koi_ria_get_platform_color($show_platform) : '#E8619A';
            ?>
                <span class="stories-item__show" style="--show-color: <?php echo esc_attr($show_color); ?>;"><?php echo esc_html($show_name); ?></span>
            <?php endif; ?>
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
