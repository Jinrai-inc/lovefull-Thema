<?php
/**
 * 通常記事テンプレート（ニュース/コラム 自動判定）
 *
 * @package KoiRiaPortal
 */

get_header();

while (have_posts()) :
    the_post();

    // カテゴリからコラムかニュースかを判定
    $is_column = has_category('column');
    $is_news   = has_category('news');

    // デフォルトアイキャッチ画像URL（カスタマイザーで設定）
    $default_eyecatch = get_theme_mod('koi_ria_default_eyecatch', '');

    // アイキャッチURL取得（個別 → デフォルト）
    $has_own_eyecatch = has_post_thumbnail(get_the_ID());
    $eyecatch_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
    if (!$eyecatch_url) {
        $eyecatch_url = $default_eyecatch;
    }
?>

<div class="article-layout">
<div class="article-layout__main">

<?php if ($is_news) : ?>
<!-- ========== ニュース記事テンプレート ========== -->

<?php if ($eyecatch_url) : ?>
<?php if ($has_own_eyecatch) : ?>
<div class="eyecatch-hero eyecatch-hero--plain">
    <img src="<?php echo esc_url($eyecatch_url); ?>" alt="" class="eyecatch-hero__img">
</div>
<?php else : ?>
<div class="eyecatch-hero">
    <img src="<?php echo esc_url($eyecatch_url); ?>" alt="" class="eyecatch-hero__img">
    <div class="eyecatch-hero__overlay"></div>
    <div class="eyecatch-hero__title-box">
        <h1><?php the_title(); ?></h1>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<article class="section news-article">
    <div style="padding: 0 var(--space-md);">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="back-link"><?php echo koi_ria_icon('arrow-right', 14); ?> トップに戻る</a>

        <div class="news-article__header">
            <div class="news-article__badges">
                <span class="badge badge--news"><?php echo koi_ria_icon('news', 12); ?> ニュース</span>
                <?php
                $tags = get_the_tags();
                if ($tags) :
                    foreach (array_slice($tags, 0, 3) as $tag) :
                ?>
                    <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="badge badge--tag"><?php echo esc_html($tag->name); ?></a>
                <?php
                    endforeach;
                endif;
                ?>
            </div>

            <?php if ($has_own_eyecatch || !$eyecatch_url) : ?>
            <h1 class="news-article__title"><?php the_title(); ?></h1>
            <?php endif; ?>

            <div class="news-article__meta">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo koi_ria_icon('calendar', 14); ?> <?php echo get_the_date('Y年n月j日 H:i'); ?>
                </time>
                <?php if (get_the_date('c') !== get_the_modified_date('c')) : ?>
                    <span class="news-article__updated">(更新: <?php echo get_the_modified_date('Y年n月j日'); ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="entry-content" style="margin-top: var(--space-lg); line-height: 1.9; font-size: 0.9375rem;">
            <?php the_content(); ?>
        </div>

        <?php if ($tags) : ?>
        <div class="news-article__tags">
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="pill-filter">#<?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/article-cta'); ?>
    </div>
</article>

<?php // 関連ニュース ?>
<section class="section">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('news', 20); ?> 関連ニュース</h2>
    </div>
    <?php
    $news_cat_id = get_cat_ID('news');
    $related_news = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'post__not_in'   => [get_the_ID()],
        'category'       => $news_cat_id,
    ]);
    if ($related_news) :
        foreach ($related_news as $post) :
            setup_postdata($post);
            get_template_part('template-parts/news-card', null, ['post' => $post]);
        endforeach;
        wp_reset_postdata();
    else :
    ?>
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub); font-size: 0.875rem;">関連ニュースはまだありません</p>
    <?php endif; ?>
</section>

<?php else : ?>
<!-- ========== 恋愛コラム記事テンプレート ========== -->

<?php if ($eyecatch_url) : ?>
<?php if ($has_own_eyecatch) : ?>
<div class="eyecatch-hero eyecatch-hero--plain">
    <img src="<?php echo esc_url($eyecatch_url); ?>" alt="" class="eyecatch-hero__img">
</div>
<?php else : ?>
<div class="eyecatch-hero">
    <img src="<?php echo esc_url($eyecatch_url); ?>" alt="" class="eyecatch-hero__img">
    <div class="eyecatch-hero__overlay"></div>
    <div class="eyecatch-hero__title-box">
        <h1><?php the_title(); ?></h1>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<article class="section column-article">
    <div style="padding: 0 var(--space-md);">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="back-link"><?php echo koi_ria_icon('arrow-right', 14); ?> トップに戻る</a>

        <div class="column-article__header">
            <div class="column-article__badges">
                <span class="badge badge--column"><?php echo koi_ria_icon('column', 12); ?> 恋愛コラム</span>
                <?php
                $categories = get_the_category();
                foreach ($categories as $cat) :
                    if ($cat->slug === 'column') continue;
                ?>
                    <span class="badge badge--cat"><?php echo esc_html($cat->name); ?></span>
                <?php endforeach; ?>
            </div>

            <?php if ($has_own_eyecatch || !$eyecatch_url) : ?>
            <h1 class="column-article__title"><?php the_title(); ?></h1>
            <?php endif; ?>

            <div class="column-article__meta">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo koi_ria_icon('calendar', 14); ?> <?php echo get_the_date('Y年n月j日'); ?>
                </time>
                <?php if (get_the_date('c') !== get_the_modified_date('c')) : ?>
                    <span class="column-article__updated">(更新: <?php echo get_the_modified_date('Y.m.d'); ?>)</span>
                <?php endif; ?>
                <span class="column-article__readtime">
                    <?php echo koi_ria_icon('clock', 14); ?> <?php echo esc_html(ceil(mb_strlen(strip_tags(get_the_content())) / 600)); ?>分で読める
                </span>
            </div>
        </div>

        <div class="entry-content" style="margin-top: var(--space-lg); line-height: 1.9; font-size: 0.9375rem;">
            <?php the_content(); ?>
        </div>

        <?php
        $tags = get_the_tags();
        if ($tags) :
        ?>
        <div class="column-article__tags">
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="pill-filter">#<?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/article-cta'); ?>
    </div>
</article>

<?php // 関連コラム ?>
<section class="section">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('column', 20); ?> 関連コラム</h2>
    </div>
    <div class="column-cards" style="padding: 0 var(--space-md);">
        <?php
        $related_columns = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => 4,
            'post__not_in'   => [get_the_ID()],
            'category_name'  => 'column',
        ]);
        foreach ($related_columns as $post) :
            setup_postdata($post);
            $cats = get_the_category($post->ID);
            $cat_name = '';
            foreach ($cats as $c) {
                if ($c->slug !== 'column') { $cat_name = $c->name; break; }
            }
            if (!$cat_name && $cats) $cat_name = $cats[0]->name;
        ?>
        <?php
            $card_thumb = get_the_post_thumbnail_url($post, 'show-card');
            if (!$card_thumb) $card_thumb = get_theme_mod('koi_ria_default_eyecatch', '');
        ?>
        <a href="<?php echo esc_url(get_permalink($post)); ?>" class="column-card">
            <div class="column-card__thumb-wrap">
                <?php if ($card_thumb) : ?>
                    <img src="<?php echo esc_url($card_thumb); ?>" alt="" class="column-card__thumb" loading="lazy">
                <?php else : ?>
                    <div class="column-card__thumb column-card__thumb--empty"></div>
                <?php endif; ?>
                <div class="column-card__overlay"></div>
                <span class="column-card__overlay-title"><?php echo esc_html($post->post_title); ?></span>
            </div>
            <div class="column-card__body">
                <?php if ($cat_name) : ?>
                    <span class="column-card__cat"><?php echo esc_html($cat_name); ?></span>
                <?php endif; ?>
                <time class="column-card__date" datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html(get_the_date('Y.m.d', $post)); ?></time>
                <h3 class="column-card__title"><?php echo esc_html($post->post_title); ?></h3>
            </div>
        </a>
        <?php
        endforeach;
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php endif; ?>

</div><!-- /.article-layout__main -->

<?php get_template_part('template-parts/article-sidebar'); ?>

</div><!-- /.article-layout -->

<?php
endwhile;

get_footer();
