<?php
/**
 * SNSリンクボタン
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$ig_username     = $args['ig_username'] ?? '';
$tiktok_username = $args['tiktok_username'] ?? '';
$x_username      = $args['x_username'] ?? '';
$youtube_url     = $args['youtube_url'] ?? '';
?>

<?php if ($ig_username) : ?>
<a href="https://instagram.com/<?php echo esc_attr($ig_username); ?>" class="btn btn--ig" target="_blank" rel="noopener" style="width: 100%;">
    <?php echo koi_ria_icon('instagram', 18); ?> @<?php echo esc_html($ig_username); ?>
</a>
<?php endif; ?>

<?php if ($tiktok_username) : ?>
<a href="https://tiktok.com/@<?php echo esc_attr($tiktok_username); ?>" class="btn btn--tiktok" target="_blank" rel="noopener" style="width: 100%;">
    <?php echo koi_ria_icon('tiktok', 18); ?> @<?php echo esc_html($tiktok_username); ?>
</a>
<?php endif; ?>

<?php if ($x_username) : ?>
<a href="https://x.com/<?php echo esc_attr($x_username); ?>" class="btn btn--x" target="_blank" rel="noopener" style="width: 100%;">
    <?php echo koi_ria_icon('twitter', 18); ?> @<?php echo esc_html($x_username); ?>
</a>
<?php endif; ?>

<?php if ($youtube_url) : ?>
<a href="<?php echo esc_url($youtube_url); ?>" class="btn btn--outline" target="_blank" rel="noopener" style="width: 100%; border-color: #FF0000; color: #FF0000;">
    <?php echo koi_ria_icon('youtube', 18); ?> YouTube
</a>
<?php endif; ?>
