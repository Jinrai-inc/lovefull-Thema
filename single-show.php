<?php
/**
 * 番組詳細テンプレート
 *
 * タブUI: シーズン&メンバー / 相関図 / YouTube / 関連記事
 *
 * @package KoiRiaPortal
 */

get_header();

$show_id        = get_the_ID();
$short_name     = get_field('short_name') ?: get_the_title();
$platform       = get_field('platform') ?: '';
$platform_color = get_field('platform_color') ?: '#A855F7';
$emoji          = get_field('emoji') ?: '📺';
$status         = get_field('show_status') ?: '';
$genre          = get_field('genre') ?: '';
$target         = get_field('target') ?: '';
$affiliate_url  = get_field('affiliate_url') ?: '';

// シーズン取得
$seasons = get_posts([
    'post_type'      => 'season',
    'posts_per_page' => -1,
    'meta_query'     => [
        ['key' => 'show', 'value' => $show_id, 'compare' => '='],
    ],
    'meta_key'  => 'order',
    'orderby'   => 'meta_value_num',
    'order'     => 'ASC',
]);

// YouTube動画
$videos = get_posts([
    'post_type'      => 'youtube_video',
    'posts_per_page' => 6,
    'meta_query'     => [
        ['key' => 'platform', 'value' => $platform, 'compare' => '='],
    ],
    'orderby' => 'date',
    'order'   => 'DESC',
]);

// 関連記事（番組名タグ）
$related_posts = get_posts([
    'post_type'      => 'post',
    'posts_per_page' => 5,
    'tag'            => sanitize_title($short_name),
]);
?>

<?php // ヘッダー ?>
<section class="show-detail-header" style="text-align: center; padding: var(--space-lg) var(--space-md) var(--space-md); background: linear-gradient(180deg, rgba(168,85,247,0.06) 0%, transparent 100%);">
    <div style="margin-bottom: var(--space-sm);">
        <a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>" style="font-size: 0.8125rem; color: var(--color-text-sub);">&larr; 番組一覧</a>
    </div>

    <span style="font-size: 3rem; display: block;"><?php echo esc_html($emoji); ?></span>
    <h1 style="margin-top: var(--space-sm); font-size: 1.375rem;"><?php the_title(); ?></h1>

    <div style="margin-top: var(--space-sm); display: flex; justify-content: center; gap: var(--space-xs); flex-wrap: wrap;">
        <?php if ($platform) : ?>
            <span class="badge" style="background: <?php echo esc_attr($platform_color); ?>; color: #fff;"><?php echo esc_html($platform); ?></span>
        <?php endif; ?>
        <?php if ($status) : ?>
            <span class="badge badge--on-air"><?php echo esc_html($status); ?></span>
        <?php endif; ?>
        <?php if ($genre) : ?>
            <span class="badge" style="background: rgba(168,85,247,0.1); color: var(--color-primary);"><?php echo esc_html($genre); ?></span>
        <?php endif; ?>
        <?php if ($target) : ?>
            <span class="badge" style="background: rgba(255,59,111,0.1); color: var(--color-accent);"><?php echo esc_html($target); ?></span>
        <?php endif; ?>
    </div>

    <?php if ($affiliate_url) : ?>
        <a href="<?php echo esc_url($affiliate_url); ?>" class="btn btn--primary" style="margin-top: var(--space-md);" target="_blank" rel="noopener sponsored">
            <?php echo esc_html($platform); ?>で視聴する
        </a>
        <small style="display: block; margin-top: var(--space-xs); color: var(--color-text-sub); font-size: 0.625rem;">PR</small>
    <?php endif; ?>

    <?php if (get_the_content()) : ?>
    <div style="margin-top: var(--space-md); text-align: left; font-size: 0.875rem; line-height: 1.8; color: var(--color-text);">
        <?php the_content(); ?>
    </div>
    <?php endif; ?>
</section>

<?php // タブナビゲーション ?>
<nav class="show-tabs" style="position: sticky; top: var(--header-height); z-index: 50; background: var(--color-bg); border-bottom: 1px solid #E5E7EB;" aria-label="番組情報タブ">
    <div class="scroll-x" style="gap: 0; padding: 0;" role="tablist">
        <button class="show-tab is-active" data-tab="members" role="tab" aria-selected="true" aria-controls="tab-members" id="tab-btn-members"><?php echo koi_ria_icon('users', 18); ?> メンバー</button>
        <?php if ($seasons) : ?>
        <button class="show-tab" data-tab="chart" role="tab" aria-selected="false" aria-controls="tab-chart" id="tab-btn-chart"><?php echo koi_ria_icon('couple', 18); ?> 相関図</button>
        <?php endif; ?>
        <?php if ($videos) : ?>
        <button class="show-tab" data-tab="videos" role="tab" aria-selected="false" aria-controls="tab-videos" id="tab-btn-videos"><?php echo koi_ria_icon('play', 18); ?> 動画</button>
        <?php endif; ?>
        <?php if ($related_posts) : ?>
        <button class="show-tab" data-tab="articles" role="tab" aria-selected="false" aria-controls="tab-articles" id="tab-btn-articles"><?php echo koi_ria_icon('news', 18); ?> 記事</button>
        <?php endif; ?>
    </div>
</nav>

<?php // タブ: メンバー（シーズンアコーディオン） ?>
<div class="show-tab-content is-active" id="tab-members" role="tabpanel" aria-labelledby="tab-btn-members">
    <section class="section">
        <?php if ($seasons) :
            foreach ($seasons as $season) :
                $season_name = get_field('season_name', $season->ID) ?: $season->post_title;
                $badge       = get_field('badge', $season->ID) ?: '';
                $year        = get_field('year', $season->ID) ?: '';

                $members = get_posts([
                    'post_type'      => 'cast',
                    'posts_per_page' => -1,
                    'meta_query'     => [
                        ['key' => 'season', 'value' => $season->ID, 'compare' => '='],
                    ],
                ]);

                $girls = array_filter($members, fn($m) => get_field('gender', $m->ID) === 'f');
                $boys  = array_filter($members, fn($m) => get_field('gender', $m->ID) !== 'f');
        ?>
        <div class="accordion" style="margin: 0 var(--space-md) var(--space-xs);">
            <button class="accordion__trigger <?php echo $badge ? 'is-open' : ''; ?>" aria-expanded="<?php echo $badge ? 'true' : 'false'; ?>">
                <span style="display: flex; align-items: center; gap: var(--space-sm); flex: 1;">
                    <?php echo esc_html($season_name); ?>
                    <?php if ($year) : ?><small style="color: var(--color-text-sub);">(<?php echo esc_html($year); ?>)</small><?php endif; ?>
                    <?php if ($badge) : ?><span class="badge badge--on-air"><?php echo esc_html($badge); ?></span><?php endif; ?>
                </span>
                <small style="color: var(--color-text-sub);"><?php echo count($members); ?>名</small>
            </button>
            <div class="accordion__content <?php echo $badge ? 'is-open' : ''; ?>">
                <?php if ($members) : ?>
                    <?php if ($girls) : ?>
                    <div style="margin-bottom: var(--space-md);">
                        <small style="display:block; padding: 0 var(--space-sm); margin-bottom: var(--space-xs); color: var(--color-accent); font-weight: 700;">GIRLS (<?php echo count($girls); ?>)</small>
                        <div class="scroll-x">
                            <?php foreach ($girls as $cast) :
                                get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($boys) : ?>
                    <div>
                        <small style="display:block; padding: 0 var(--space-sm); margin-bottom: var(--space-xs); color: var(--color-primary); font-weight: 700;">BOYS (<?php echo count($boys); ?>)</small>
                        <div class="scroll-x">
                            <?php foreach ($boys as $cast) :
                                get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (empty($girls) && empty($boys)) : ?>
                    <div class="scroll-x">
                        <?php foreach ($members as $cast) :
                            get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
                        endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else : ?>
                    <p style="color: var(--color-text-sub); font-size: 0.875rem;">メンバーデータはまだ登録されていません</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
            endforeach;
        else :
        ?>
            <p style="padding: 0 var(--space-md); color: var(--color-text-sub); font-size: 0.875rem;">シーズンデータはまだ登録されていません</p>
        <?php endif; ?>
    </section>
</div>

<?php // タブ: 相関図 ?>
<?php if ($seasons) : ?>
<div class="show-tab-content" id="tab-chart" role="tabpanel" aria-labelledby="tab-btn-chart" style="display: none;">
    <section class="section">
        <?php // シーズン選択 ?>
        <div class="pill-filters" style="margin-bottom: var(--space-md);">
            <?php foreach ($seasons as $si => $season) :
                $s_name = get_field('season_name', $season->ID) ?: $season->post_title;
                $s_badge = get_field('badge', $season->ID) ?: '';
            ?>
            <button class="pill-filter <?php echo $si === 0 ? 'is-active' : ''; ?>" data-season-id="<?php echo esc_attr($season->ID); ?>">
                <?php echo esc_html($s_name); ?>
                <?php if ($s_badge) : ?><span class="badge badge--on-air" style="margin-left: 4px; font-size: 0.5rem;"><?php echo esc_html($s_badge); ?></span><?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php // 各シーズンの相関図 ?>
        <?php foreach ($seasons as $si => $season) :
            $relations = get_posts([
                'post_type'      => 'relation',
                'posts_per_page' => -1,
                'meta_query'     => [
                    ['key' => 'season', 'value' => $season->ID, 'compare' => '='],
                ],
            ]);

            $season_members = get_posts([
                'post_type'      => 'cast',
                'posts_per_page' => -1,
                'meta_query'     => [
                    ['key' => 'season', 'value' => $season->ID, 'compare' => '='],
                ],
            ]);
        ?>
        <div class="correlation-season" data-season-id="<?php echo esc_attr($season->ID); ?>" style="<?php echo $si > 0 ? 'display: none;' : ''; ?>">
            <?php if ($relations) : ?>
                <?php // メンバーアバター一覧 ?>
                <div class="scroll-x" style="margin-bottom: var(--space-lg);">
                    <?php foreach ($season_members as $m) :
                        $m_name = get_field('display_name', $m->ID) ?: $m->post_title;
                        $m_img  = get_field('profile_image', $m->ID);
                        $m_ig = get_field('ig_username', $m->ID);
                        $m_avatar = ($m_img && isset($m_img['url'])) ? $m_img['url'] : (koi_ria_get_ig_avatar($m_ig, 200) ?: '');
                        $m_gender = get_field('gender', $m->ID) ?: '';
                    ?>
                    <div style="text-align: center; min-width: 70px;">
                        <div class="ig-avatar" style="margin: 0 auto; padding: 2px; <?php echo $m_gender === 'f' ? 'background: linear-gradient(45deg, #FF3B6F, #FF8BA7);' : 'background: linear-gradient(45deg, #3B82F6, #A855F7);'; ?>">
                            <?php if ($m_avatar) : ?>
                                <img src="<?php echo esc_url($m_avatar); ?>" alt="" class="ig-avatar__img" style="width: 50px; height: 50px;" loading="lazy">
                            <?php else : ?>
                                <div class="ig-avatar__img" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;background:#f0f0f0;"><?php echo koi_ria_icon('users', 20); ?></div>
                            <?php endif; ?>
                        </div>
                        <small style="display: block; margin-top: 4px; font-size: 0.6875rem; font-weight: 500;"><?php echo esc_html($m_name); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php // リレーション一覧 ?>
                <div style="padding: 0 var(--space-md);">
                    <?php foreach ($relations as $rel) :
                        $from_id = get_field('from_cast', $rel->ID);
                        $to_id   = get_field('to_cast', $rel->ID);
                        $r_type  = get_field('relation_type', $rel->ID) ?: '';
                        $r_label = get_field('relation_label', $rel->ID) ?: '';

                        $from_name = '';
                        if ($from_id) {
                            $fp = is_array($from_id) ? get_post($from_id[0]) : get_post($from_id);
                            $from_name = $fp ? (get_field('display_name', $fp->ID) ?: $fp->post_title) : '';
                        }
                        $to_name = '';
                        if ($to_id) {
                            $tp = is_array($to_id) ? get_post($to_id[0]) : get_post($to_id);
                            $to_name = $tp ? (get_field('display_name', $tp->ID) ?: $tp->post_title) : '';
                        }

                        $rel_icon = match($r_type) {
                            'love'     => koi_ria_icon('heart-filled', 20),
                            'rival'    => koi_ria_icon('fire', 20),
                            'couple'   => koi_ria_icon('couple', 20),
                            'interest' => koi_ria_icon('search', 20),
                            default    => '？',
                        };
                        $rel_color = match($r_type) {
                            'love'     => 'var(--color-accent)',
                            'rival'    => '#F59E0B',
                            'couple'   => 'var(--color-success)',
                            'interest' => 'var(--color-primary)',
                            default    => 'var(--color-text-sub)',
                        };
                    ?>
                    <div class="relation-line" style="display: flex; align-items: center; gap: var(--space-sm); padding: var(--space-sm) 0; border-bottom: 1px solid #F3F4F6;">
                        <span style="font-weight: 700; font-size: 0.8125rem; min-width: 50px; text-align: right;"><?php echo esc_html($from_name); ?></span>
                        <span style="display: flex; align-items: center; gap: 4px; flex: 1; justify-content: center;">
                            <span style="height: 2px; flex: 1; background: <?php echo $rel_color; ?>; opacity: 0.4;"></span>
                            <span style="font-size: 1.2rem;"><?php echo $rel_icon; ?></span>
                            <span style="height: 2px; flex: 1; background: <?php echo $rel_color; ?>; opacity: 0.4;"></span>
                        </span>
                        <span style="font-weight: 700; font-size: 0.8125rem; min-width: 50px;"><?php echo esc_html($to_name); ?></span>
                    </div>
                    <?php if ($r_label) : ?>
                    <div style="text-align: center; margin-top: -4px; margin-bottom: var(--space-xs);">
                        <span style="font-size: 0.6875rem; color: <?php echo $rel_color; ?>; font-weight: 500;"><?php echo esc_html($r_label); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div style="padding: var(--space-2xl) var(--space-md); text-align: center; color: var(--color-text-sub);">
                    <p style="font-size: 2rem; margin-bottom: var(--space-sm);"><?php echo koi_ria_icon('couple', 32); ?></p>
                    <p style="font-size: 0.875rem;">このシーズンの相関図データはまだ登録されていません</p>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </section>
</div>
<?php endif; ?>

<?php // タブ: YouTube動画 ?>
<?php if ($videos) : ?>
<div class="show-tab-content" id="tab-videos" role="tabpanel" aria-labelledby="tab-btn-videos" style="display: none;">
    <section class="section">
        <div style="padding: 0 var(--space-md); display: flex; flex-direction: column; gap: var(--space-md);">
            <?php foreach ($videos as $video) :
                $vid = get_field('video_id', $video->ID) ?: '';
                $thumb = get_field('thumbnail_url', $video->ID) ?: '';
            ?>
            <div class="card" data-video-id="<?php echo esc_attr($vid); ?>" style="cursor: pointer;">
                <div style="position: relative;">
                    <?php if ($thumb) : ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($video->post_title); ?>" class="card__thumb" loading="lazy">
                    <?php else : ?>
                        <div class="card__thumb" style="display:flex;align-items:center;justify-content:center;background:#1F2937;color:#fff;font-size:2rem;"><?php echo koi_ria_icon('play', 20); ?></div>
                    <?php endif; ?>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:48px;height:48px;border-radius:50%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;">
                        <span style="color:#fff;font-size:1.2rem;margin-left:3px;"><?php echo koi_ria_icon('play', 20); ?></span>
                    </div>
                </div>
                <div class="card__body">
                    <p style="font-size: 0.8125rem; font-weight: 500; line-height: 1.4;"><?php echo esc_html($video->post_title); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?php endif; ?>

<?php // タブ: 関連記事 ?>
<?php if ($related_posts) : ?>
<div class="show-tab-content" id="tab-articles" role="tabpanel" aria-labelledby="tab-btn-articles" style="display: none;">
    <section class="section">
        <?php foreach ($related_posts as $post) :
            setup_postdata($post);
            get_template_part('template-parts/news-card', null, ['post' => $post]);
        endforeach;
        wp_reset_postdata(); ?>
    </section>
</div>
<?php endif; ?>

<?php // アフィリエイト ?>
<?php if ($affiliate_url) : ?>
<section class="section">
    <a href="<?php echo esc_url($affiliate_url); ?>" class="affiliate-banner" style="background: linear-gradient(135deg, <?php echo esc_attr($platform_color); ?>, <?php echo esc_attr($platform_color); ?>CC);" target="_blank" rel="noopener sponsored">
        <div class="affiliate-banner__title"><?php echo esc_html($short_name); ?>を<?php echo esc_html($platform); ?>で見る</div>
        <span class="affiliate-banner__cta">今すぐ視聴</span>
        <small class="affiliate-banner__pr">PR</small>
    </a>
</section>
<?php endif; ?>

<?php
get_footer();
