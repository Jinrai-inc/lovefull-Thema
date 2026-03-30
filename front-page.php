<?php
/**
 * トップページテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<?php // 5-1. YouTubeヒーローカルーセル ?>
<?php get_template_part('template-parts/hero-carousel'); ?>

<?php // 5-2. 検索バー ?>
<section class="section section--search">
    <form class="search-bar" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search" aria-label="サイト内検索">
        <label for="top-search" class="screen-reader-text">検索キーワード</label>
        <span class="search-bar__icon"><?php echo koi_ria_icon('search', 18); ?></span>
        <input type="search" class="search-bar__input" id="top-search" name="s" placeholder="番組名・出演者を検索…" value="<?php echo get_search_query(); ?>">
    </form>
</section>

<?php // 5-3. 今週の放送スケジュール ?>
<?php get_template_part('template-parts/weekly-schedule'); ?>

<?php // 5-4. BREAKING（速報バー） ?>
<?php
$breaking = get_posts([
    'post_type'      => 'post',
    'posts_per_page' => 1,
    'tag'            => 'breaking',
]);
if ($breaking) :
    $b = $breaking[0];
?>
<section class="section">
    <a href="<?php echo esc_url(get_permalink($b)); ?>" class="breaking-bar">
        <span class="badge badge--live">LIVE</span>
        <span class="breaking-bar__title"><?php echo esc_html($b->post_title); ?></span>
    </a>
</section>
<?php endif; ?>

<?php // 5-5. 注目の番組 ?>
<section class="section section--shows">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('tv', 22); ?> 注目の番組</h2>
        <a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>" class="section-header__more">もっと見る →</a>
    </div>
    <div class="scroll-x">
        <?php
        $shows = get_posts([
            'post_type'      => 'show',
            'posts_per_page' => 6,
            'meta_key'       => 'priority',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ]);
        foreach ($shows as $show) :
            $args = ['show' => $show];
            get_template_part('template-parts/show-card', null, $args);
        endforeach;
        ?>
    </div>
</section>

<?php // 5-6. 特集コラム（スライダー） ?>
<?php get_template_part('template-parts/column-slider'); ?>

<?php // 5-7. カップルその後 ?>
<?php get_template_part('template-parts/couple-tracker'); ?>

<?php // 5-8. みんなの予想（投票） ?>
<section class="section section--poll">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('vote', 22); ?> みんなの予想</h2>
        <a href="<?php echo esc_url(get_post_type_archive_link('poll')); ?>" class="section-header__more">もっと見る →</a>
    </div>
    <?php
    $active_poll = get_posts([
        'post_type'      => 'poll',
        'posts_per_page' => 1,
        'meta_query'     => [
            ['key' => 'is_active', 'value' => '1', 'compare' => '='],
        ],
    ]);
    if ($active_poll) :
        $args = ['poll' => $active_poll[0]];
        get_template_part('template-parts/poll-card', null, $args);
    else :
    ?>
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub); font-size: 0.875rem;">現在受付中の投票はありません</p>
    <?php endif; ?>
</section>

<?php // 5-9. 話題の出演者（ストーリーズ型横スクロール） ?>
<?php get_template_part('template-parts/stories-cast'); ?>

<?php // 5-10. 恋愛コラム ?>
<section class="section section--column">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('column', 22); ?> 恋愛コラム</h2>
        <?php $column_cat = get_category_by_slug('column'); ?>
        <?php if ($column_cat) : ?>
            <a href="<?php echo esc_url(get_category_link($column_cat->term_id)); ?>" class="section-header__more">もっと見る →</a>
        <?php endif; ?>
    </div>
    <div class="grid-2" style="padding: 0 var(--space-md);">
        <?php
        $columns = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => 4,
            'category_name'  => 'column',
        ]);
        foreach ($columns as $post) :
            setup_postdata($post);
        ?>
        <a href="<?php echo esc_url(get_permalink($post)); ?>" class="card" style="text-decoration: none; color: inherit;">
            <?php if (has_post_thumbnail($post)) : ?>
                <?php echo get_the_post_thumbnail($post, 'show-card', ['class' => 'card__thumb', 'loading' => 'lazy']); ?>
            <?php endif; ?>
            <div class="card__body">
                <div style="font-size: 0.875rem; font-weight: 600; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo esc_html($post->post_title); ?></div>
                <p style="font-size: 0.75rem; color: var(--color-text-sub); margin-top: var(--space-xs); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo esc_html(wp_trim_words(get_the_excerpt($post), 40, '…')); ?></p>
            </div>
        </a>
        <?php
        endforeach;
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php // 5-11. 最新ニュース ?>
<section class="section section--news">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('news', 22); ?> 最新ニュース</h2>
        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="section-header__more">もっと見る →</a>
    </div>
    <div>
        <?php
        $column_cat_id = get_cat_ID('column');
        $news_args = [
            'post_type'      => 'post',
            'posts_per_page' => 3,
        ];
        if ($column_cat_id) {
            $news_args['category__not_in'] = [$column_cat_id];
        }
        $news = get_posts($news_args);
        foreach ($news as $post) :
            setup_postdata($post);
            get_template_part('template-parts/news-card', null, ['post' => $post]);
        endforeach;
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php // 5-12. 番組診断バナー ?>
<?php
$shindan_page = get_page_by_path('shindan');
if ($shindan_page) :
?>
<section class="section section--shindan">
    <a href="<?php echo esc_url(get_permalink($shindan_page)); ?>" class="promo-banner" style="background: var(--color-gradient); color: #fff; display: block; border-radius: var(--radius-lg); padding: var(--space-lg); text-decoration: none; text-align: center; margin: 0 var(--space-md);">
        <span style="font-size: 2rem; display: block;"><?php echo koi_ria_icon('sparkle', 32); ?></span>
        <div style="font-weight: 700; font-size: 1.125rem; margin-top: var(--space-xs);">あなたにぴったりの恋リアは？</div>
        <p style="font-size: 0.8125rem; opacity: 0.9; margin-top: var(--space-xs);">5つの質問で診断！→</p>
    </a>
</section>
<?php endif; ?>

<?php // 5-13. VOD検索バナー ?>
<?php
$vod_page = get_page_by_path('vod-search');
if ($vod_page) :
?>
<section class="section">
    <a href="<?php echo esc_url(get_permalink($vod_page)); ?>" class="promo-banner" style="background: linear-gradient(135deg, #00B900, #0077B5); color: #fff; display: block; border-radius: var(--radius-lg); padding: var(--space-lg); text-decoration: none; text-align: center; margin: 0 var(--space-md);">
        <span style="font-size: 2rem; display: block;"><?php echo koi_ria_icon('compass', 32); ?></span>
        <div style="font-weight: 700; font-size: 1.125rem; margin-top: var(--space-xs);">どのVODで見れる？</div>
        <p style="font-size: 0.8125rem; opacity: 0.9; margin-top: var(--space-xs);">番組の配信先を検索 →</p>
    </a>
</section>
<?php endif; ?>

<?php // 5-14. アフィリエイトバナー ?>
<?php get_template_part('template-parts/affiliate-banner'); ?>

<?php // 5-15. AdSenseスロット ?>
<?php get_template_part('template-parts/adsense-slot'); ?>

<?php
get_footer();
