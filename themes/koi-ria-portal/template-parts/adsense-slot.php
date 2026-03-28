<?php
/**
 * AdSenseスロット
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$adsense_client = get_option('koi_ria_adsense_client_id', '');
$slot_type      = $args['slot_type'] ?? 'top';

$slot_id = '';
switch ($slot_type) {
    case 'top':
        $slot_id = get_option('koi_ria_adsense_slot_top', '');
        break;
    case 'article':
        $slot_id = get_option('koi_ria_adsense_slot_article', '');
        break;
    case 'sidebar':
        $slot_id = get_option('koi_ria_adsense_slot_sidebar', '');
        break;
}
?>

<section class="section adsense-slot">
    <?php if ($adsense_client && $slot_id) : ?>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="<?php echo esc_attr($adsense_client); ?>"
             data-ad-slot="<?php echo esc_attr($slot_id); ?>"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    <?php else : ?>
        <div style="background: #f0f0f0; border-radius: var(--radius-md); padding: var(--space-xl); text-align: center; color: var(--color-text-sub); font-size: 0.75rem;">
            広告スペース
        </div>
    <?php endif; ?>
</section>
