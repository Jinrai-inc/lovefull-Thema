<?php
/**
 * トップページテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();

// 表示設定を取得
$koi_display = get_option('koi_ria_display_settings', []);

/**
 * セクションが有効かチェック（設定未保存時はデフォルトで表示）
 */
function koi_ria_section_enabled(string $key, array $settings): bool {
    if (empty($settings)) {
        return true;
    }
    if (!isset($settings[$key])) {
        return true;
    }
    return !empty($settings[$key]['enabled']);
}

/**
 * セクション定義（キー => レンダリング関数）
 * 表示順に従ってソートした後、順番にレンダリングする
 */
$koi_sections = [
    'hero_carousel'  => 'koi_ria_render_hero_carousel',
    'search_bar'     => 'koi_ria_render_search_bar',
    'weekly_schedule' => 'koi_ria_render_weekly_schedule',
    'breaking_bar'   => 'koi_ria_render_breaking_bar',
    'shows'          => 'koi_ria_render_shows',
    'popular_posts'  => 'koi_ria_render_popular_posts',
    'couple_tracker' => 'koi_ria_render_couple_tracker',
    'poll'           => 'koi_ria_render_poll',
    'stories_cast'   => 'koi_ria_render_stories_cast',
    'column'         => 'koi_ria_render_column',
    'news'           => 'koi_ria_render_news',
    'shindan'        => 'koi_ria_render_shindan',
    'vod_search'     => 'koi_ria_render_vod_search',
    'affiliate'      => 'koi_ria_render_affiliate',
    'adsense'        => 'koi_ria_render_adsense',
];

// 表示順でソート
$default_order = 1;
$koi_section_keys = array_keys($koi_sections);
usort($koi_section_keys, function ($a, $b) use ($koi_display, &$default_order) {
    static $defaults = null;
    if ($defaults === null) {
        $defaults = [
            'hero_carousel' => 1, 'search_bar' => 2, 'weekly_schedule' => 3,
            'breaking_bar' => 4, 'shows' => 5, 'popular_posts' => 6,
            'couple_tracker' => 7, 'poll' => 8, 'stories_cast' => 9,
            'column' => 10, 'news' => 11, 'shindan' => 12,
            'vod_search' => 13, 'affiliate' => 14, 'adsense' => 15,
        ];
    }
    $order_a = isset($koi_display[$a]['order']) ? (int) $koi_display[$a]['order'] : $defaults[$a];
    $order_b = isset($koi_display[$b]['order']) ? (int) $koi_display[$b]['order'] : $defaults[$b];
    return $order_a - $order_b;
});

// セクションをレンダリング
foreach ($koi_section_keys as $section_key) {
    if (!koi_ria_section_enabled($section_key, $koi_display)) {
        continue;
    }
    $render_fn = $koi_sections[$section_key];
    if (function_exists($render_fn)) {
        $render_fn();
    }
}

// --- セクションレンダリング関数 ---

function koi_ria_render_hero_carousel(): void {
    // 5-1. YouTubeヒーローカルーセル
    get_template_part('template-parts/hero-carousel');
}

function koi_ria_render_search_bar(): void {
    // 5-2. 検索バー
    ?>
    <section class="section section--search">
        <form class="search-bar" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search" aria-label="サイト内検索">
            <label for="top-search" class="screen-reader-text">検索キーワード</label>
            <span class="search-bar__icon"><?php echo koi_ria_icon('search', 18); ?></span>
            <input type="search" class="search-bar__input" id="top-search" name="s" placeholder="番組名・出演者を検索…" value="<?php echo get_search_query(); ?>">
        </form>
    </section>
    <?php
}

function koi_ria_render_weekly_schedule(): void {
    // 5-3. 今週の放送スケジュール
    get_template_part('template-parts/weekly-schedule');
}

function koi_ria_render_breaking_bar(): void {
    // 5-4. BREAKING（速報バー）
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
    <?php endif;
}

function koi_ria_render_shows(): void {
    // 5-5. 注目の番組
    ?>
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
    <?php
}

function koi_ria_render_popular_posts(): void {
    // 5-6. 人気記事トップ3（閲覧数順）
    get_template_part('template-parts/column-slider');
}

function koi_ria_render_couple_tracker(): void {
    // 5-7. カップルその後
    get_template_part('template-parts/couple-tracker');
}

function koi_ria_render_poll(): void {
    // 5-8. みんなの予想（投票）
    ?>
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
    <?php
}

function koi_ria_render_stories_cast(): void {
    // 5-9. 話題の出演者（ストーリーズ型横スクロール）
    get_template_part('template-parts/stories-cast');
}

function koi_ria_render_column(): void {
    // 5-10. 恋愛コラム
    ?>
    <section class="section section--column">
        <div class="section-header">
            <h2><?php echo koi_ria_icon('column', 22); ?> 恋愛コラム</h2>
            <?php $column_cat = get_category_by_slug('column'); ?>
            <?php if ($column_cat) : ?>
                <a href="<?php echo esc_url(get_category_link($column_cat->term_id)); ?>" class="section-header__more">もっと見る →</a>
            <?php endif; ?>
        </div>
        <div class="column-cards" style="padding: 0 var(--space-md);">
            <?php
            $columns = get_posts([
                'post_type'      => 'post',
                'posts_per_page' => 4,
                'category_name'  => 'column',
            ]);
            foreach ($columns as $post) :
                setup_postdata($post);
                $cats = get_the_category($post->ID);
                $cat_name = '';
                foreach ($cats as $c) {
                    if ($c->slug !== 'column') { $cat_name = $c->name; break; }
                }
                if (!$cat_name && $cats) $cat_name = $cats[0]->name;
            ?>
            <a href="<?php echo esc_url(get_permalink($post)); ?>" class="column-card">
                <div class="column-card__thumb-wrap">
                    <?php if (has_post_thumbnail($post)) : ?>
                        <?php echo get_the_post_thumbnail($post, 'show-card', ['class' => 'column-card__thumb', 'loading' => 'lazy']); ?>
                    <?php else : ?>
                        <div class="column-card__thumb column-card__thumb--empty"></div>
                    <?php endif; ?>
                    <?php if ($cat_name) : ?>
                        <span class="column-card__cat"><?php echo esc_html($cat_name); ?></span>
                    <?php endif; ?>
                </div>
                <div class="column-card__body">
                    <h3 class="column-card__title"><?php echo esc_html($post->post_title); ?></h3>
                    <p class="column-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt($post), 30, '…')); ?></p>
                    <time class="column-card__date" datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html(get_the_date('Y.m.d', $post)); ?></time>
                </div>
            </a>
            <?php
            endforeach;
            wp_reset_postdata();
            ?>
        </div>
    </section>
    <?php
}

function koi_ria_render_news(): void {
    // 5-11. 最新ニュース
    ?>
    <section class="section section--news">
        <div class="section-header">
            <h2><?php echo koi_ria_icon('news', 22); ?> 最新ニュース</h2>
            <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="section-header__more">もっと見る →</a>
        </div>
        <div>
            <?php
            $news_args = [
                'post_type'      => 'post',
                'posts_per_page' => 3,
                'category_name'  => 'news',
            ];
            $news = get_posts($news_args);
            foreach ($news as $post) :
                setup_postdata($post);
                get_template_part('template-parts/news-card', null, ['post' => $post]);
            endforeach;
            wp_reset_postdata();
            ?>
        </div>
    </section>
    <?php
}

function koi_ria_render_shindan(): void {
    // 5-12. 番組診断バナー
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
    <?php endif;
}

function koi_ria_render_vod_search(): void {
    // 5-13. VOD検索バナー
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
    <?php endif;
}

function koi_ria_render_affiliate(): void {
    // 5-14. アフィリエイトバナー
    get_template_part('template-parts/affiliate-banner');
}

function koi_ria_render_adsense(): void {
    // 5-15. AdSenseスロット
    get_template_part('template-parts/adsense-slot');
}

get_footer();
