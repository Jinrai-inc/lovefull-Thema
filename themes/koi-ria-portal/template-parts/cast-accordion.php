<?php
/**
 * 出演者アコーディオン（番組→シーズン→メンバー）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$shows = get_posts([
    'post_type'      => 'show',
    'posts_per_page' => -1,
    'meta_key'       => 'priority',
    'orderby'        => 'meta_value_num',
    'order'          => 'ASC',
]);

if (empty($shows)) :
?>
    <p style="padding: 0 var(--space-md); color: var(--color-text-sub);">番組データはまだ登録されていません</p>
<?php
    return;
endif;

foreach ($shows as $show) :
    $emoji      = get_field('emoji', $show->ID) ?: '📺';
    $short_name = get_field('short_name', $show->ID) ?: $show->post_title;
    $platform   = get_field('platform', $show->ID) ?: '';

    $seasons = get_posts([
        'post_type'      => 'season',
        'posts_per_page' => -1,
        'meta_query'     => [
            ['key' => 'show', 'value' => $show->ID, 'compare' => '='],
        ],
        'meta_key'       => 'order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);
?>

<div class="accordion" data-platform="<?php echo esc_attr($platform); ?>" style="margin: 0 var(--space-md) var(--space-xs);">
    <button class="accordion__trigger" aria-expanded="false">
        <span><?php echo esc_html($emoji); ?> <?php echo esc_html($short_name); ?></span>
    </button>
    <div class="accordion__content">
        <?php if ($seasons) :
            foreach ($seasons as $season) :
                $season_name = get_field('season_name', $season->ID) ?: $season->post_title;
                $badge       = get_field('badge', $season->ID) ?: '';

                $members = get_posts([
                    'post_type'      => 'cast',
                    'posts_per_page' => -1,
                    'meta_query'     => [
                        ['key' => 'season', 'value' => $season->ID, 'compare' => '='],
                    ],
                ]);

                // 性別でグループ分け
                $girls = [];
                $boys  = [];
                foreach ($members as $m) {
                    $gender = get_field('gender', $m->ID) ?: '';
                    if ($gender === 'f') {
                        $girls[] = $m;
                    } else {
                        $boys[] = $m;
                    }
                }
        ?>
        <div style="margin-bottom: var(--space-md);">
            <button class="accordion__sub-trigger" aria-expanded="false">
                <span>
                    <?php echo esc_html($season_name); ?>
                    <?php if ($badge) : ?><span class="badge badge--on-air" style="margin-left: 6px;"><?php echo esc_html($badge); ?></span><?php endif; ?>
                </span>
                <small><?php echo count($members); ?>名</small>
            </button>
            <div class="accordion__content">
                <?php if ($girls) : ?>
                <div style="margin-bottom: var(--space-sm);">
                    <small style="display:block; padding: 0 var(--space-sm); margin-bottom: var(--space-xs); color: var(--color-text-sub);">GIRLS (<?php echo count($girls); ?>)</small>
                    <div class="scroll-x">
                        <?php foreach ($girls as $cast) :
                            get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
                        endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($boys) : ?>
                <div>
                    <small style="display:block; padding: 0 var(--space-sm); margin-bottom: var(--space-xs); color: var(--color-text-sub);">BOYS (<?php echo count($boys); ?>)</small>
                    <div class="scroll-x">
                        <?php foreach ($boys as $cast) :
                            get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
                        endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (empty($members)) : ?>
                    <p style="color: var(--color-text-sub); font-size: 0.8125rem; padding: var(--space-sm);">メンバー未登録</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
            endforeach;
        else :
        ?>
            <p style="color: var(--color-text-sub); font-size: 0.8125rem;">シーズン未登録</p>
        <?php endif; ?>
    </div>
</div>

<?php endforeach; ?>
