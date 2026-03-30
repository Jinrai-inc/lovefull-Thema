<?php
/**
 * VOD比較表テンプレート
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$affiliate_banners = get_option('koi_ria_affiliate_banners', []);

$vods = [
    'abema' => [
        'name'      => 'ABEMAプレミアム',
        'price'     => '960円',
        'trial'     => '2週間無料',
        'shows'     => '20+番組',
        'examples'  => '今日好き / オオカミくん',
        'color'     => 'var(--color-abema)',
        'url'       => $affiliate_banners['abema'] ?? '#',
    ],
    'netflix' => [
        'name'      => 'Netflix',
        'price'     => '790円〜',
        'trial'     => 'なし',
        'shows'     => '10+番組',
        'examples'  => 'あいの里 / ラブブラ',
        'color'     => 'var(--color-netflix)',
        'url'       => $affiliate_banners['netflix'] ?? '#',
    ],
    'prime' => [
        'name'      => 'Amazonプライム',
        'price'     => '600円',
        'trial'     => '30日間',
        'shows'     => '5+番組',
        'examples'  => 'バチェラー / ラブトラ',
        'color'     => 'var(--color-prime)',
        'url'       => $affiliate_banners['prime'] ?? '#',
    ],
    'unext' => [
        'name'      => 'U-NEXT',
        'price'     => '2,189円',
        'trial'     => '31日間',
        'shows'     => '5+番組',
        'examples'  => '恋のLast Vacation',
        'color'     => '#E6197B',
        'url'       => $affiliate_banners['unext'] ?? '#',
    ],
];

$rows = [
    'price'    => '月額料金',
    'trial'    => '無料体験',
    'shows'    => '恋リア番組数',
    'examples' => '代表番組',
];
?>

<section class="section" style="padding-top: var(--space-lg);">
    <div class="section-header">
        <h2>&#x1F4CA; VOD比較表</h2>
    </div>

    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 0 var(--space-md);">
        <table class="vod-compare">
            <thead>
                <tr>
                    <th style="background: transparent;"></th>
                    <?php foreach ($vods as $vod) : ?>
                        <th style="background: <?php echo esc_attr($vod['color']); ?>;">
                            <?php echo esc_html($vod['name']); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $key => $label) : ?>
                    <tr>
                        <td><?php echo esc_html($label); ?></td>
                        <?php foreach ($vods as $vod) : ?>
                            <td><?php echo esc_html($vod[$key]); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td></td>
                    <?php foreach ($vods as $vod) : ?>
                        <td>
                            <a href="<?php echo esc_url($vod['url']); ?>"
                               class="vod-compare__cta"
                               style="background: <?php echo esc_attr($vod['color']); ?>;"
                               target="_blank"
                               rel="noopener noreferrer sponsored">
                                無料で試す
                            </a>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</section>
