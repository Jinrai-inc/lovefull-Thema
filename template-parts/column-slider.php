<?php
/**
 * 特集コラムスライダー
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$columns = get_posts([
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'category_name'  => 'column',
]);

if (empty($columns)) {
    return;
}
?>

<section class="section column-slider-section">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('star', 22); ?> 特集コラム</h2>
        <a href="<?php echo esc_url(get_category_link(get_cat_ID('column'))); ?>" class="section-header__more">もっと見る →</a>
    </div>

    <div class="column-slider" id="columnSlider">
        <?php foreach ($columns as $i => $post) :
            setup_postdata($post);
            $thumb_url = get_the_post_thumbnail_url($post, 'medium_large') ?: '';
            $cats = get_the_category($post->ID);
            $cat_name = $cats ? $cats[0]->name : 'コラム';
            $read_time = ceil(mb_strlen(strip_tags(get_the_content(null, false, $post))) / 600);
        ?>
        <a href="<?php echo esc_url(get_permalink($post)); ?>" class="column-slide <?php echo $i === 0 ? 'column-slide--featured' : ''; ?>">
            <div class="column-thumb" style="background-image: url('<?php echo esc_url($thumb_url); ?>');"></div>
            <div class="column-body">
                <span class="column-cat"><?php echo esc_html($cat_name); ?></span>
                <h3><?php echo esc_html($post->post_title); ?></h3>
                <span class="column-meta">読了時間: <?php echo esc_html($read_time); ?>分</span>
            </div>
        </a>
        <?php endforeach; wp_reset_postdata(); ?>
    </div>

    <div class="slider-dots" id="columnDots"></div>
</section>
