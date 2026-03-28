<?php
/**
 * 番組一覧テンプレート
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section">
    <div class="section-header">
        <h1>&#x1F4FA; 番組一覧</h1>
    </div>

    <?php // プラットフォームフィルター ?>
    <div class="pill-filters" style="margin-bottom: var(--space-lg);">
        <button class="pill-filter is-active" data-platform="all">すべて</button>
        <button class="pill-filter" data-platform="ABEMA">ABEMA</button>
        <button class="pill-filter" data-platform="Netflix">Netflix</button>
        <button class="pill-filter" data-platform="Prime Video">Prime Video</button>
        <button class="pill-filter" data-platform="other">その他</button>
    </div>

    <div class="grid-2" id="showGrid">
        <?php
        $shows = get_posts([
            'post_type'      => 'show',
            'posts_per_page' => -1,
            'meta_key'       => 'priority',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ]);
        foreach ($shows as $show) :
            $platform = get_field('platform', $show->ID) ?: '';
            $emoji = get_field('emoji', $show->ID) ?: '📺';
            $short_name = get_field('short_name', $show->ID) ?: $show->post_title;
            $status = get_field('show_status', $show->ID) ?: '';
        ?>
        <a href="<?php echo esc_url(get_permalink($show)); ?>" class="show-card" data-platform="<?php echo esc_attr($platform); ?>">
            <div class="show-card__emoji"><?php echo esc_html($emoji); ?></div>
            <div class="show-card__name"><?php echo esc_html($short_name); ?></div>
            <?php if ($platform) : ?>
                <span class="badge badge--<?php echo esc_attr(sanitize_title($platform)); ?>"><?php echo esc_html($platform); ?></span>
            <?php endif; ?>
            <?php if ($status) : ?>
                <span class="badge" style="margin-top: 4px;"><?php echo esc_html($status); ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
get_footer();
