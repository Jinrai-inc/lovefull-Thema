<?php
/**
 * VOD自動挿入ウィジェット
 * 記事下に「この番組を見るなら」を表示
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// Auto-detect show from tags if not passed
$show_id = $args['show_id'] ?? 0;
if (!$show_id) {
    $tags = get_the_tags();
    if ($tags) {
        foreach ($tags as $tag) {
            $found = get_posts([
                'post_type'      => 'show',
                'posts_per_page' => 1,
                'meta_query'     => [
                    [
                        'key'   => 'short_name',
                        'value' => $tag->name,
                    ],
                ],
            ]);
            if ($found) {
                $show_id = $found[0]->ID;
                break;
            }
        }
    }
}

if (!$show_id) return;

$show_title    = get_the_title($show_id);
$vod_links     = get_field('vod_links', $show_id) ?: [];
$platform      = get_field('platform', $show_id) ?: '';
$affiliate_url = get_field('affiliate_url', $show_id) ?: '';

// VODプラットフォームの表示設定
$platform_map = [
    'abema'   => ['label' => 'ABEMA', 'class' => 'badge--abema'],
    'netflix' => ['label' => 'Netflix', 'class' => 'badge--netflix'],
    'prime'   => ['label' => 'Prime', 'class' => 'badge--prime'],
    'unext'   => ['label' => 'U-NEXT', 'class' => 'badge--unext'],
];
?>

<div class="vod-auto-insert">
    <p class="vod-auto-insert__title"><?php echo koi_ria_icon('tv', 20); ?> 「<?php echo esc_html($show_title); ?>」を見るなら</p>

    <div class="vod-badges" style="margin-bottom: var(--space-md);">
        <?php if ($platform && isset($platform_map[$platform])) : ?>
            <span class="badge <?php echo esc_attr($platform_map[$platform]['class']); ?>">
                <?php echo esc_html($platform_map[$platform]['label']); ?>
            </span>
        <?php endif; ?>

        <?php if (is_array($vod_links)) : ?>
            <?php foreach ($vod_links as $vod_link) :
                $vod_key   = $vod_link['platform'] ?? '';
                $vod_url   = $vod_link['url'] ?? '';
                if (!$vod_key || !isset($platform_map[$vod_key])) continue;
            ?>
                <span class="badge <?php echo esc_attr($platform_map[$vod_key]['class']); ?>">
                    <?php echo esc_html($platform_map[$vod_key]['label']); ?>
                </span>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="vod-auto-insert__links">
        <?php if ($affiliate_url) : ?>
            <a href="<?php echo esc_url($affiliate_url); ?>" class="btn btn--primary btn--sm" target="_blank" rel="noopener noreferrer sponsored">
                今すぐ見る
            </a>
        <?php endif; ?>

        <?php if (is_array($vod_links)) : ?>
            <?php foreach ($vod_links as $vod_link) :
                $vod_key = $vod_link['platform'] ?? '';
                $vod_url = $vod_link['url'] ?? '';
                if (!$vod_key || !$vod_url || !isset($platform_map[$vod_key])) continue;
            ?>
                <a href="<?php echo esc_url($vod_url); ?>"
                   class="btn btn--sm btn--outline"
                   target="_blank"
                   rel="noopener noreferrer sponsored">
                    <?php echo esc_html($platform_map[$vod_key]['label']); ?>で見る
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?php echo esc_url(home_url('/vod-search/')); ?>" class="btn btn--sm btn--outline">
            VOD比較表を見る
        </a>
    </div>
</div>
