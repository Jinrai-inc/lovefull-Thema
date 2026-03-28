<?php
/**
 * カテゴリ/タグアーカイブテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section">
    <div class="section-header">
        <h1><?php the_archive_title(); ?></h1>
    </div>

    <?php if (have_posts()) : ?>
        <div>
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/news-card', null, ['post' => $post]); ?>
            <?php endwhile; ?>
        </div>

        <div style="padding: var(--space-lg) var(--space-md); text-align: center;">
            <?php the_posts_pagination([
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
            ]); ?>
        </div>
    <?php else : ?>
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub);">記事が見つかりませんでした</p>
    <?php endif; ?>
</section>

<?php
get_footer();
