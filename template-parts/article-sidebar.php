<?php
/**
 * 記事サイドバー
 *
 * ウィジェットエリア + 従来のサイドバーバナーを表示。
 * PC表示時に記事の右側にスティッキー表示される。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$has_widgets = is_active_sidebar('article-sidebar');
$sidebar_banners = get_option('koi_ria_sidebar_banners', []);
$has_banners = false;
if (!empty($sidebar_banners)) {
    foreach ($sidebar_banners as $sb) {
        if (!empty($sb['image'])) {
            $has_banners = true;
            break;
        }
    }
}

if (!$has_widgets && !$has_banners) return;
?>

<aside class="article-sidebar">
    <div class="article-sidebar__inner">
        <?php if ($has_widgets) : ?>
            <?php dynamic_sidebar('article-sidebar'); ?>
        <?php endif; ?>

        <?php if ($has_banners) : ?>
            <?php foreach ($sidebar_banners as $sb) :
                if (empty($sb['image'])) continue;
                $has_link = !empty($sb['url']);
            ?>
            <div class="sidebar-widget">
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
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</aside>
