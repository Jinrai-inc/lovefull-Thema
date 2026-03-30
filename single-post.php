<?php
/**
 * 通常記事テンプレート（ニュース/コラム）
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

        <div style="margin-top: var(--space-md);">
            <?php
            $categories = get_the_category();
            if ($categories) :
                foreach ($categories as $cat) :
            ?>
                <span class="badge" style="background: rgba(168,85,247,0.1); color: var(--color-primary);"><?php echo esc_html($cat->name); ?></span>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <h1 style="margin-top: var(--space-sm); font-size: 1.25rem;"><?php the_title(); ?></h1>

        <p style="color: var(--color-text-sub); font-size: 0.75rem; margin-top: var(--space-xs);">
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
            <?php if (get_the_date('c') !== get_the_modified_date('c')) : ?>
                <span>(更新: <time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>"><?php echo get_the_modified_date('Y.m.d'); ?></time>)</span>
            <?php endif; ?>
            &nbsp;|&nbsp; <?php echo esc_html(ceil(mb_strlen(strip_tags(get_the_content())) / 600)); ?>分で読める
        </p>

        <?php if (has_post_thumbnail()) : ?>
            <div style="margin-top: var(--space-md); border-radius: var(--radius-md); overflow: hidden;">
                <?php the_post_thumbnail('large', ['style' => 'width:100%;height:auto;']); ?>
            </div>
        <?php endif; ?>

        <div class="entry-content" style="margin-top: var(--space-lg); line-height: 1.9; font-size: 0.9375rem;">
            <?php the_content(); ?>
        </div>

        <?php // タグ表示 ?>
        <?php
        $tags = get_the_tags();
        if ($tags) :
        ?>
        <div style="margin-top: var(--space-lg); display: flex; flex-wrap: wrap; gap: var(--space-sm);">
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="pill-filter">#<?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</article>

<?php // 関連記事 ?>
<section class="section">
    <div class="section-header">
        <h2>関連記事</h2>
    </div>
    <?php
    $related = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'post__not_in'   => [get_the_ID()],
        'category__in'   => wp_get_post_categories(get_the_ID()),
    ]);
    foreach ($related as $post) :
        setup_postdata($post);
        get_template_part('template-parts/news-card', null, ['post' => $post]);
    endforeach;
    wp_reset_postdata();
    ?>
</section>

<?php
endwhile;

get_footer();
