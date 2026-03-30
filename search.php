<?php
/**
 * 検索結果テンプレート
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section">
    <div class="section-header">
        <h1>&#x1F50D; 「<?php echo esc_html(get_search_query()); ?>」の検索結果</h1>
    </div>

    <form class="search-bar" style="margin-bottom: var(--space-lg);" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search" aria-label="サイト内検索">
        <label for="search-input" class="screen-reader-text">検索キーワード</label>
        <span class="search-bar__icon">&#x1F50D;</span>
        <input type="search" class="search-bar__input" id="search-input" name="s" value="<?php echo get_search_query(); ?>" placeholder="番組名・出演者を検索…">
    </form>

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
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub);">「<?php echo esc_html(get_search_query()); ?>」に一致する結果はありませんでした</p>
    <?php endif; ?>
</section>

<?php
get_footer();
