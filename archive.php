<?php
/**
 * カテゴリ/タグアーカイブテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();

$default_eyecatch = get_theme_mod('koi_ria_default_eyecatch', '');
?>

<section class="section">
    <div class="section-header" style="padding: 0 var(--space-md);">
        <h1><?php the_archive_title(); ?></h1>
    </div>

    <?php if (have_posts()) : ?>
        <div class="archive-cards">
            <?php while (have_posts()) : the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'show-card');
                if (!$thumb) $thumb = $default_eyecatch;
                $cats = get_the_category();
                $cat_name = $cats ? $cats[0]->name : '';
            ?>
            <a href="<?php the_permalink(); ?>" class="column-card">
                <div class="column-card__thumb-wrap">
                    <?php if ($thumb) : ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="" class="column-card__thumb" loading="lazy">
                    <?php else : ?>
                        <div class="column-card__thumb column-card__thumb--empty"></div>
                    <?php endif; ?>
                    <div class="column-card__overlay"></div>
                    <span class="column-card__overlay-title"><?php the_title(); ?></span>
                </div>
                <div class="column-card__body">
                    <?php if ($cat_name) : ?>
                        <span class="column-card__cat"><?php echo esc_html($cat_name); ?></span>
                    <?php endif; ?>
                    <time class="column-card__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
                    <h3 class="column-card__title"><?php the_title(); ?></h3>
                </div>
            </a>
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
