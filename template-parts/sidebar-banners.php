<?php
/**
 * サイドバー固定バナー表示
 *
 * 管理画面「広告設定」で登録したバナーをサイドバーに表示。
 * PC表示時のみ sticky で固定表示される。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$sidebar_banners = get_option('koi_ria_sidebar_banners', []);
if (empty($sidebar_banners)) {
    return;
}

$has_valid = false;
foreach ($sidebar_banners as $sb) {
    if (!empty($sb['image'])) {
        $has_valid = true;
        break;
    }
}
if (!$has_valid) {
    return;
}
?>
<aside class="sidebar-banners">
    <?php foreach ($sidebar_banners as $sb) :
        if (empty($sb['image'])) continue;
        $has_link = !empty($sb['url']);
    ?>
    <div class="sidebar-banner">
        <?php if ($has_link) : ?>
            <a href="<?php echo esc_url($sb['url']); ?>" target="_blank" rel="noopener sponsored" class="sidebar-banner__link">
        <?php endif; ?>
            <img src="<?php echo esc_url($sb['image']); ?>" alt="<?php echo esc_attr($sb['label'] ?: '広告'); ?>" class="sidebar-banner__img" loading="lazy">
        <?php if ($has_link) : ?>
            </a>
        <?php endif; ?>
        <?php if (!empty($sb['label'])) : ?>
            <span class="sidebar-banner__label"><?php echo esc_html($sb['label']); ?></span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</aside>
