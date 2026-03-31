<?php
/**
 * 番組カード（横スクロール用）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$show       = $args['show'] ?? null;
if (!$show) return;

$emoji      = get_field('emoji', $show->ID) ?: '📺';
$short_name = get_field('short_name', $show->ID) ?: $show->post_title;
$platform   = get_field('platform', $show->ID) ?: '';
$status     = get_field('show_status', $show->ID) ?: '';
$has_thumb  = has_post_thumbnail($show->ID);
?>

<a href="<?php echo esc_url(get_permalink($show)); ?>" class="show-card">
    <div class="show-card__logo">
        <?php if ($has_thumb) : ?>
            <?php echo get_the_post_thumbnail($show->ID, 'show-card', ['class' => 'show-card__logo-img', 'loading' => 'lazy']); ?>
        <?php else : ?>
            <span class="show-card__emoji"><?php echo esc_html($emoji); ?></span>
        <?php endif; ?>
    </div>
    <div class="show-card__name"><?php echo esc_html($short_name); ?></div>
    <?php if ($platform) : ?>
        <span class="badge badge--<?php echo esc_attr(sanitize_title($platform)); ?>" style="font-size: 0.5625rem;"><?php echo esc_html($platform); ?></span>
    <?php endif; ?>
    <?php if ($status) : ?>
        <div style="margin-top: 4px;">
            <span class="badge badge--on-air" style="font-size: 0.5625rem;"><?php echo esc_html($status); ?></span>
        </div>
    <?php endif; ?>
</a>
