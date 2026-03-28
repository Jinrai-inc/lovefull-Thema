<?php
/**
 * ニュースカード
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$post = $args['post'] ?? null;
if (!$post) return;

$tags = get_the_tags($post->ID);
?>

<a href="<?php echo esc_url(get_permalink($post)); ?>" class="news-card">
    <div class="news-card__body">
        <?php if ($tags) : ?>
            <span class="news-card__tag"><?php echo esc_html($tags[0]->name); ?></span>
        <?php endif; ?>
        <div class="news-card__title"><?php echo esc_html($post->post_title); ?></div>
        <div class="news-card__time"><?php echo esc_html(human_time_diff(get_the_time('U', $post), current_time('timestamp'))); ?>前</div>
    </div>
    <?php if (has_post_thumbnail($post)) : ?>
        <?php echo get_the_post_thumbnail($post, 'thumbnail', ['class' => 'news-card__thumb', 'loading' => 'lazy']); ?>
    <?php endif; ?>
</a>
