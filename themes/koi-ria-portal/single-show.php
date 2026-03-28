<?php
/**
 * 番組詳細テンプレート
 *
 * @package KoiRiaPortal
 */

get_header();

$show_id       = get_the_ID();
$short_name    = get_field('short_name') ?: get_the_title();
$platform      = get_field('platform') ?: '';
$platform_color = get_field('platform_color') ?: '#A855F7';
$emoji         = get_field('emoji') ?: '📺';
$status        = get_field('show_status') ?: '';
$genre         = get_field('genre') ?: '';
$affiliate_url = get_field('affiliate_url') ?: '';
?>

<section class="section">
    <div style="padding: 0 var(--space-md);">
        <a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>">&larr; 番組一覧に戻る</a>
    </div>

    <div style="text-align: center; padding: var(--space-lg) var(--space-md);">
        <span style="font-size: 3rem;"><?php echo esc_html($emoji); ?></span>
        <h1 style="margin-top: var(--space-sm);"><?php the_title(); ?></h1>
        <div style="margin-top: var(--space-sm); display: flex; justify-content: center; gap: var(--space-sm); flex-wrap: wrap;">
            <?php if ($platform) : ?>
                <span class="badge" style="background: <?php echo esc_attr($platform_color); ?>; color: #fff;"><?php echo esc_html($platform); ?></span>
            <?php endif; ?>
            <?php if ($status) : ?>
                <span class="badge badge--on-air"><?php echo esc_html($status); ?></span>
            <?php endif; ?>
            <?php if ($genre) : ?>
                <span class="badge" style="background: rgba(168,85,247,0.1); color: var(--color-primary);"><?php echo esc_html($genre); ?></span>
            <?php endif; ?>
        </div>
        <?php if ($affiliate_url) : ?>
            <a href="<?php echo esc_url($affiliate_url); ?>" class="btn btn--primary" style="margin-top: var(--space-md);" target="_blank" rel="noopener sponsored">視聴する</a>
        <?php endif; ?>
    </div>

    <?php if (get_the_content()) : ?>
    <div style="padding: 0 var(--space-md);">
        <?php the_content(); ?>
    </div>
    <?php endif; ?>
</section>

<?php // シーズン一覧 + メンバー ?>
<section class="section">
    <div class="section-header">
        <h2>シーズン</h2>
    </div>

    <?php
    $seasons = get_posts([
        'post_type'      => 'season',
        'posts_per_page' => -1,
        'meta_query'     => [
            ['key' => 'show', 'value' => $show_id, 'compare' => '='],
        ],
        'meta_key'       => 'order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);

    if ($seasons) :
        foreach ($seasons as $season) :
            $season_name = get_field('season_name', $season->ID) ?: $season->post_title;
            $badge       = get_field('badge', $season->ID) ?: '';
            $year        = get_field('year', $season->ID) ?: '';

            $members = get_posts([
                'post_type'      => 'cast',
                'posts_per_page' => -1,
                'meta_query'     => [
                    ['key' => 'season', 'value' => $season->ID, 'compare' => '='],
                ],
            ]);
    ?>
    <div class="accordion" style="margin: 0 var(--space-md) var(--space-sm);">
        <button class="accordion__trigger" aria-expanded="false">
            <?php echo esc_html($season_name); ?>
            <?php if ($year) : ?><small>(<?php echo esc_html($year); ?>)</small><?php endif; ?>
            <?php if ($badge) : ?><span class="badge badge--on-air"><?php echo esc_html($badge); ?></span><?php endif; ?>
            <small><?php echo count($members); ?>名</small>
        </button>
        <div class="accordion__content">
            <?php if ($members) : ?>
            <div class="scroll-x">
                <?php foreach ($members as $cast) :
                    get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
                endforeach; ?>
            </div>
            <?php else : ?>
                <p style="color: var(--color-text-sub); font-size: 0.875rem;">メンバーデータはまだ登録されていません</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
        endforeach;
    else :
    ?>
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub); font-size: 0.875rem;">シーズンデータはまだ登録されていません</p>
    <?php endif; ?>
</section>

<?php
get_footer();
