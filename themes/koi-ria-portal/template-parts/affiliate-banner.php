<?php
/**
 * アフィリエイトバナー
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$banners = [
    [
        'name'       => 'ABEMAプレミアム',
        'color'      => 'linear-gradient(135deg, #00B900, #00D900)',
        'cta'        => '2週間無料でお試し',
        'url'        => '#', // 管理画面から設定予定
    ],
    [
        'name'       => 'Netflix',
        'color'      => 'linear-gradient(135deg, #E50914, #B20710)',
        'cta'        => '今すぐ視聴する',
        'url'        => '#',
    ],
    [
        'name'       => 'Amazonプライム',
        'color'      => 'linear-gradient(135deg, #00A8E1, #0077B5)',
        'cta'        => '30日間無料体験',
        'url'        => '#',
    ],
];
?>

<section class="section">
    <?php foreach ($banners as $banner) : ?>
    <a href="<?php echo esc_url($banner['url']); ?>" class="affiliate-banner" style="background: <?php echo esc_attr($banner['color']); ?>;" target="_blank" rel="noopener sponsored">
        <div class="affiliate-banner__title"><?php echo esc_html($banner['name']); ?></div>
        <span class="affiliate-banner__cta"><?php echo esc_html($banner['cta']); ?></span>
        <small class="affiliate-banner__pr">PR</small>
    </a>
    <?php endforeach; ?>
</section>
