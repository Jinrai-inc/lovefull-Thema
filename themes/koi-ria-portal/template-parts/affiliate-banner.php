<?php
/**
 * アフィリエイトバナー
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$banners = get_option('koi_ria_affiliate_banners', []);

// デフォルトバナー（管理画面未設定時）
if (empty($banners)) {
    $banners = [
        [
            'name'  => 'ABEMAプレミアム',
            'color' => 'linear-gradient(135deg, #00B900, #00D900)',
            'cta'   => '2週間無料でお試し',
            'url'   => '#',
        ],
        [
            'name'  => 'Netflix',
            'color' => 'linear-gradient(135deg, #E50914, #B20710)',
            'cta'   => '今すぐ視聴する',
            'url'   => '#',
        ],
        [
            'name'  => 'Amazonプライム',
            'color' => 'linear-gradient(135deg, #00A8E1, #0077B5)',
            'cta'   => '30日間無料体験',
            'url'   => '#',
        ],
    ];
}

// URLが設定されているバナーのみ表示
$active_banners = array_filter($banners, function ($b) {
    return !empty($b['url']) && $b['url'] !== '#';
});

// 全バナーがURL未設定の場合はデフォルト表示
if (empty($active_banners)) {
    $active_banners = $banners;
}
?>

<section class="section">
    <?php foreach ($active_banners as $banner) :
        $color = $banner['color'] ?: 'linear-gradient(135deg, #667eea, #764ba2)';
    ?>
    <a href="<?php echo esc_url($banner['url']); ?>" class="affiliate-banner" style="background: <?php echo esc_attr($color); ?>;" target="_blank" rel="noopener sponsored">
        <div class="affiliate-banner__title"><?php echo esc_html($banner['name']); ?></div>
        <span class="affiliate-banner__cta"><?php echo esc_html($banner['cta']); ?></span>
        <small class="affiliate-banner__pr">PR</small>
    </a>
    <?php endforeach; ?>
</section>
