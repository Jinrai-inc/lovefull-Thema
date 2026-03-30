<?php
/**
 * YouTubeヒーローカルーセル
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// ピン留め動画 or 最新5件を取得
$pinned = get_posts([
    'post_type'      => 'youtube_video',
    'posts_per_page' => 5,
    'meta_query'     => [
        ['key' => 'is_pinned', 'value' => '1', 'compare' => '='],
    ],
]);

if (empty($pinned)) {
    $pinned = get_posts([
        'post_type'      => 'youtube_video',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

if (empty($pinned)) {
    return;
}
?>

<section class="hero-carousel" id="heroCarousel">
    <div class="hero-carousel__track" id="heroTrack">
        <?php foreach ($pinned as $video) :
            $video_id      = get_field('video_id', $video->ID) ?: '';
            $thumbnail_url = get_field('thumbnail_url', $video->ID) ?: '';
            $platform      = get_field('platform', $video->ID) ?: '';
            $is_auto       = get_field('is_auto', $video->ID);
        ?>
        <div class="hero-carousel__slide" data-video-id="<?php echo esc_attr($video_id); ?>">
            <?php if ($thumbnail_url) : ?>
                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($video->post_title); ?>" class="hero-carousel__thumb" loading="lazy">
            <?php else : ?>
                <div class="hero-carousel__thumb" style="background:#1F2937;display:flex;align-items:center;justify-content:center;color:#fff;font-size:3rem;">&#x25B6;</div>
            <?php endif; ?>
            <div class="hero-carousel__overlay">
                <div style="display: flex; gap: 6px; margin-bottom: 4px;">
                    <?php if ($platform) : ?>
                        <span class="badge badge--<?php echo esc_attr(sanitize_title($platform)); ?>"><?php echo esc_html($platform); ?></span>
                    <?php endif; ?>
                    <?php if ($is_auto) : ?>
                        <span class="badge badge--new">AUTO</span>
                    <?php endif; ?>
                </div>
                <p class="hero-carousel__title"><?php echo esc_html($video->post_title); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="hero-carousel__dots" id="heroDots">
        <?php foreach ($pinned as $i => $video) : ?>
            <button class="hero-carousel__dot <?php echo $i === 0 ? 'is-active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="スライド<?php echo $i + 1; ?>"></button>
        <?php endforeach; ?>
    </div>
</section>
