<?php
/**
 * 人気記事トップ3（Google Analytics PV数連携）
 * ※恋愛コラム（column カテゴリ）は除外
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$column_cat_id  = get_cat_ID('column');
$exclude_cats   = $column_cat_id ? [$column_cat_id] : [];
$popular_posts  = function_exists('koi_ria_get_popular_posts')
    ? koi_ria_get_popular_posts(3, $exclude_cats)
    : [];

if (empty($popular_posts)) {
    return;
}

$pv_map   = get_option('koi_ria_ga_popular_pv_map', []);
$has_ga   = !empty($pv_map);
?>

<section class="section section--popular">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('fire', 22); ?> 人気記事トップ3</h2>
    </div>

    <div class="popular-ranking" style="padding: 0 var(--space-md);">
        <?php foreach ($popular_posts as $i => $post) :
            setup_postdata($post);
            $rank = $i + 1;

            // GA PVデータ優先、なければAJAXカウンター値
            if ($has_ga && isset($pv_map[$post->ID])) {
                $views = (int) $pv_map[$post->ID];
            } else {
                $views = (int) get_post_meta($post->ID, 'post_views_count', true);
            }

            $thumb_url = get_the_post_thumbnail_url($post, 'medium') ?: '';
            $cats      = get_the_category($post->ID);
            $cat_name  = $cats ? $cats[0]->name : '';
        ?>
        <a href="<?php echo esc_url(get_permalink($post)); ?>" class="popular-item">
            <span class="popular-item__rank popular-item__rank--<?php echo esc_attr($rank); ?>"><?php echo esc_html($rank); ?></span>
            <?php if ($thumb_url) : ?>
                <img src="<?php echo esc_url($thumb_url); ?>" alt="" class="popular-item__thumb" loading="lazy">
            <?php else : ?>
                <span class="popular-item__thumb popular-item__thumb--empty"></span>
            <?php endif; ?>
            <div class="popular-item__body">
                <?php if ($cat_name) : ?>
                    <span class="popular-item__cat"><?php echo esc_html($cat_name); ?></span>
                <?php endif; ?>
                <h3 class="popular-item__title"><?php echo esc_html($post->post_title); ?></h3>
                <?php if ($views > 0) : ?>
                    <span class="popular-item__views"><?php echo esc_html(number_format($views)); ?> PV</span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; wp_reset_postdata(); ?>
    </div>

    <?php if (!$has_ga && class_exists('Google\Site_Kit\Plugin')) : ?>
        <!-- GA同期は管理画面アクセス時に自動実行されます -->
    <?php endif; ?>
</section>
