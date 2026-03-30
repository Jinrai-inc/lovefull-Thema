<?php
/**
 * カップルその後 アーカイブテンプレート
 *
 * @package KoiRiaPortal
 */

get_header(); ?>

<section class="section">
    <div class="section-header">
        <h1>&#x1F491; カップルその後</h1>
    </div>

    <!-- Status filter -->
    <div class="pill-filters" style="margin-bottom: var(--space-md);">
        <button class="pill-filter is-active" data-status="all">全て</button>
        <button class="pill-filter" data-status="交際中">交際中</button>
        <button class="pill-filter" data-status="破局">破局</button>
        <button class="pill-filter" data-status="結婚">結婚</button>
    </div>

    <!-- Show filter -->
    <?php
    $shows = get_posts([
        'post_type'      => 'show',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    if ($shows) : ?>
    <div class="pill-filters" style="margin-bottom: var(--space-lg);">
        <button class="pill-filter is-active" data-show-id="all">全番組</button>
        <?php foreach ($shows as $show_post) :
            $show_label = get_field('short_name', $show_post->ID) ?: $show_post->post_title;
        ?>
            <button class="pill-filter" data-show-id="<?php echo esc_attr($show_post->ID); ?>"><?php echo esc_html($show_label); ?></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="couple-grid" id="coupleGrid">
        <?php while (have_posts()) : the_post();
            get_template_part('template-parts/couple-card', null, ['couple_id' => get_the_ID()]);
        endwhile; ?>
    </div>

    <?php the_posts_pagination(['prev_text' => '&larr;', 'next_text' => '&rarr;']); ?>
</section>

<?php get_footer(); ?>
