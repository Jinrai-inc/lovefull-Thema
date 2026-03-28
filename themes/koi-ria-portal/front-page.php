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
<section class="section">
    <form class="search-bar" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search" aria-label="サイト内検索">
        <label for="top-search" class="screen-reader-text">検索キーワード</label>
        <span class="search-bar__icon">&#x1F50D;</span>
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
<section class="section">
    <div class="section-header">
        <h2>&#x1F4FA; 注目の番組</h2>
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
<section class="section">
    <div class="section-header">
        <h2>&#x1F5F3; みんなの予想</h2>
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

<?php // 5-10. 最新ニュース ?>
<section class="section">
    <div class="section-header">
        <h2>&#x1F4F0; 最新ニュース</h2>
        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="section-header__more">もっと見る →</a>
    </div>
    <div>
        <?php
        $news = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => 3,
        ]);
        foreach ($news as $post) :
            setup_postdata($post);
            get_template_part('template-parts/news-card', null, ['post' => $post]);
        endforeach;
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php // 5-11. HOW TO・ガイド ?>
<section class="section">
    <div class="section-header">
        <h2>&#x1F4D6; ガイド・HOW TO</h2>
    </div>
    <div>
        <?php
        $guides = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => 5,
            'category_name'  => 'guide',
        ]);
        foreach ($guides as $post) :
            setup_postdata($post);
            get_template_part('template-parts/news-card', null, ['post' => $post]);
        endforeach;
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php // 5-12. アフィリエイトバナー ?>
<?php get_template_part('template-parts/affiliate-banner'); ?>

<?php // 5-13. AdSenseスロット ?>
<?php get_template_part('template-parts/adsense-slot'); ?>

<?php
get_footer();
