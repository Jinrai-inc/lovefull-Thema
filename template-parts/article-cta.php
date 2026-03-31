<?php
/**
 * 記事下アフィリエイトCTA
 *
 * 投稿のカスタムフィールド cta_type (abema|prime) に応じてCTAバナーを表示。
 * cta_url_override があればそのURLを、なければ管理画面のデフォルトURLを使用。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$cta_type = get_post_meta(get_the_ID(), 'cta_type', true);
if (!$cta_type) {
    return;
}

$cta_url_override = get_post_meta(get_the_ID(), 'cta_url_override', true);

$cta_config = [
    'abema' => [
        'name'    => 'ABEMA',
        'cta'     => 'ABEMAプレミアムで今すぐ視聴',
        'color'   => 'linear-gradient(135deg, #00B900, #00D900)',
        'default' => get_option('koi_ria_cta_url_abema', 'https://abema.tv/subscription/lp/183c3ec2-c6d8-409e-80b6-caf5a8012f8a'),
    ],
    'prime' => [
        'name'    => 'Amazonプライム・ビデオ',
        'cta'     => 'プライム・ビデオで今すぐ視聴',
        'color'   => 'linear-gradient(135deg, #00A8E1, #0077B5)',
        'default' => get_option('koi_ria_cta_url_prime', ''),
    ],
];

if (!isset($cta_config[$cta_type])) {
    return;
}

$config = $cta_config[$cta_type];
$url    = $cta_url_override ?: $config['default'];

if (!$url) {
    return;
}
?>

<div class="article-cta" style="margin-top: var(--space-xl); padding: 0 var(--space-md);">
    <a href="<?php echo esc_url($url); ?>" class="affiliate-banner" style="background: <?php echo esc_attr($config['color']); ?>;" target="_blank" rel="noopener sponsored">
        <div class="affiliate-banner__title"><?php echo esc_html($config['name']); ?></div>
        <span class="affiliate-banner__cta"><?php echo esc_html($config['cta']); ?></span>
        <small class="affiliate-banner__pr">PR</small>
    </a>
</div>
