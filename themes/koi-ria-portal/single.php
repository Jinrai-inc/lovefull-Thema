<?php
/**
 * 汎用シングルテンプレート（フォールバック）
 *
 * single-{post_type}.php が存在しない投稿タイプ用
 *
 * @package KoiRiaPortal
 */

get_header();

while (have_posts()) :
    the_post();
?>

<article class="section">
    <div style="padding: 0 var(--space-md);">
        <a href="javascript:history.back();">&larr; 戻る</a>

        <h1 style="margin-top: var(--space-md); font-size: 1.25rem;"><?php the_title(); ?></h1>

        <?php if (has_post_thumbnail()) : ?>
            <div style="margin-top: var(--space-md); border-radius: var(--radius-md); overflow: hidden;">
                <?php the_post_thumbnail('large', ['style' => 'width:100%;height:auto;']); ?>
            </div>
        <?php endif; ?>

        <div class="entry-content" style="margin-top: var(--space-lg); line-height: 1.9; font-size: 0.9375rem;">
            <?php the_content(); ?>
        </div>
    </div>
</article>

<?php
endwhile;

get_footer();
