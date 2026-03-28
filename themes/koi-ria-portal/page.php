<?php
/**
 * 固定ページテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();

while (have_posts()) :
    the_post();
?>

<section class="section">
    <div style="padding: 0 var(--space-md);">
        <h1 style="margin-bottom: var(--space-lg);"><?php the_title(); ?></h1>
        <div class="entry-content" style="line-height: 1.9; font-size: 0.9375rem;">
            <?php the_content(); ?>
        </div>
    </div>
</section>

<?php
endwhile;

get_footer();
