<?php
/**
 * 出演者プロフィールテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();

$cast_id          = get_the_ID();
$display_name     = get_field('display_name') ?: get_the_title();
$show_id          = get_field('show');
$season_id        = get_field('season');
$ig_username      = get_field('ig_username') ?: '';
$tiktok_username  = get_field('tiktok_username') ?: '';
$x_username       = get_field('x_username') ?: '';
$youtube_url      = get_field('youtube_url') ?: '';
$followers_count  = get_field('followers_count') ?: 0;
$tiktok_followers = get_field('tiktok_followers') ?: 0;
$role             = get_field('role') ?: '';
$gender           = get_field('gender') ?: '';
$age              = get_field('age') ?: '';
$from_area        = get_field('from_area') ?: '';
$cast_status      = get_field('cast_status') ?: '';
$profile_image    = get_field('profile_image');
$ig_cache         = get_post_meta($cast_id, 'ig_profile_cache', true);

// 番組情報取得
$show_name = '';
$show_post = null;
if ($show_id) {
    $show_post = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
    if ($show_post) {
        $show_name = get_field('short_name', $show_post->ID) ?: $show_post->post_title;
    }
}

// シーズン名
$season_name = '';
if ($season_id) {
    $season_post = is_array($season_id) ? get_post($season_id[0]) : get_post($season_id);
    if ($season_post) {
        $season_name = get_field('season_name', $season_post->ID) ?: $season_post->post_title;
    }
}

// アバター画像URL
$avatar_url = '';
if ($profile_image && isset($profile_image['url'])) {
    $avatar_url = $profile_image['url'];
} elseif ($ig_cache) {
    $avatar_url = $ig_cache;
}

// 同シーズンの相関図データ
$relations = [];
if ($season_id) {
    $s_id = is_array($season_id) ? $season_id[0] : $season_id;
    $relations = get_posts([
        'post_type'      => 'relation',
        'posts_per_page' => -1,
        'meta_query'     => [
            'relation' => 'AND',
            ['key' => 'season', 'value' => $s_id, 'compare' => '='],
            [
                'relation' => 'OR',
                ['key' => 'from_cast', 'value' => $cast_id, 'compare' => '='],
                ['key' => 'to_cast', 'value' => $cast_id, 'compare' => '='],
            ],
        ],
    ]);
}
?>

<?php // 7-1. プロフィールヘッダー ?>
<section class="cast-profile-header" style="position: relative;">
    <a href="javascript:history.back();" class="cast-profile-header__back">&larr;</a>

    <div class="ig-avatar ig-avatar--lg" style="margin: 0 auto;">
        <?php if ($avatar_url) : ?>
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ig-avatar__img">
        <?php else : ?>
            <div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:2rem;background:#f0f0f0;"><?php echo koi_ria_icon('users', 32); ?></div>
        <?php endif; ?>
    </div>

    <div style="display: flex; align-items: center; justify-content: center; gap: var(--space-sm);">
        <h1 class="cast-profile-header__name" style="margin: 0;"><?php echo esc_html($display_name); ?></h1>
        <?php get_template_part('template-parts/fav-button', null, [
            'cast_id'    => $cast_id,
            'name'       => $display_name,
            'ig'         => $ig_username,
            'show_title' => $show_name,
        ]); ?>
    </div>

    <p class="cast-profile-header__meta">
        <?php if ($show_name) : ?>
            <a href="<?php echo esc_url(get_permalink($show_post)); ?>" style="color: #fff; text-decoration: underline; text-underline-offset: 2px;"><?php echo esc_html($show_name); ?></a>
        <?php endif; ?>
        <?php if ($season_name) echo ' / ' . esc_html($season_name); ?>
        <?php if ($role) echo ' / ' . esc_html($role); ?>
    </p>

    <?php // プロフィール詳細 ?>
    <div style="display: flex; justify-content: center; gap: var(--space-lg); margin-top: var(--space-md);">
        <?php if ($age) : ?>
        <div style="text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 700;"><?php echo esc_html($age); ?></div>
            <div style="font-size: 0.625rem; opacity: 0.8;">歳</div>
        </div>
        <?php endif; ?>
        <?php if ($from_area) : ?>
        <div style="text-align: center;">
            <div style="font-size: 0.875rem; font-weight: 700;"><?php echo esc_html($from_area); ?></div>
            <div style="font-size: 0.625rem; opacity: 0.8;">出身</div>
        </div>
        <?php endif; ?>
        <?php if ($followers_count) : ?>
        <div style="text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 700;"><?php echo esc_html(koi_ria_format_number($followers_count)); ?></div>
            <div style="font-size: 0.625rem; opacity: 0.8;">IG followers</div>
        </div>
        <?php endif; ?>
        <?php if ($tiktok_followers) : ?>
        <div style="text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 700;"><?php echo esc_html(koi_ria_format_number($tiktok_followers)); ?></div>
            <div style="font-size: 0.625rem; opacity: 0.8;">TikTok</div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($cast_status) : ?>
    <div style="margin-top: var(--space-sm);">
        <span class="badge <?php echo $cast_status === '出演中' ? 'badge--active' : ($cast_status === 'リタイア' ? 'badge--ended' : 'badge--unknown'); ?>"><?php echo esc_html($cast_status); ?></span>
    </div>
    <?php endif; ?>
</section>

<?php // 7-2. SNSリンク ?>
<section class="section">
    <div style="display: flex; flex-direction: column; gap: var(--space-sm); padding: 0 var(--space-md);">
        <?php get_template_part('template-parts/sns-buttons', null, [
            'ig_username'     => $ig_username,
            'tiktok_username' => $tiktok_username,
            'x_username'      => $x_username,
            'youtube_url'     => $youtube_url,
        ]); ?>
    </div>
</section>

<?php // 相関図（この出演者に関連するリレーション） ?>
<?php if ($relations) : ?>
<section class="section">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('couple', 22); ?> 相関図</h2>
    </div>
    <div style="background: var(--color-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); margin: 0 var(--space-md); padding: var(--space-md); overflow: hidden;">
        <?php foreach ($relations as $rel) :
            $from_id = get_field('from_cast', $rel->ID);
            $to_id   = get_field('to_cast', $rel->ID);
            $r_type  = get_field('relation_type', $rel->ID) ?: '';
            $r_label = get_field('relation_label', $rel->ID) ?: '';

            // 相手の名前を特定
            $other_id = null;
            if ((is_array($from_id) ? $from_id[0] : $from_id) == $cast_id) {
                $other_id = is_array($to_id) ? $to_id[0] : $to_id;
            } else {
                $other_id = is_array($from_id) ? $from_id[0] : $from_id;
            }
            $other_post = $other_id ? get_post($other_id) : null;
            $other_name = $other_post ? (get_field('display_name', $other_post->ID) ?: $other_post->post_title) : '？';

            $rel_icon = match($r_type) {
                'love'     => koi_ria_icon('heart-filled', 20),
                'rival'    => koi_ria_icon('fire', 20),
                'couple'   => koi_ria_icon('couple', 20),
                'interest' => koi_ria_icon('search', 20),
                default    => '？',
            };
        ?>
        <a href="<?php echo $other_post ? esc_url(get_permalink($other_post)) : '#'; ?>" class="couple-item" style="border-color: #F3F4F6;">
            <span class="couple-item__icon"><?php echo $rel_icon; ?></span>
            <div class="couple-item__info">
                <div class="couple-item__names"><?php echo esc_html($other_name); ?></div>
                <?php if ($r_label) : ?>
                    <div class="couple-item__show"><?php echo esc_html($r_label); ?></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php // 7-3. Instagram最新投稿グリッド ?>
<?php if ($ig_username) : ?>
<section class="section">
    <div class="section-header">
        <h2>Instagram</h2>
        <a href="https://instagram.com/<?php echo esc_attr($ig_username); ?>" class="section-header__more" target="_blank" rel="noopener">@<?php echo esc_html($ig_username); ?> →</a>
    </div>
    <div class="sns-follow-card sns-follow-card--ig">
        <div class="sns-follow-card__profile">
            <?php if ($avatar_url) : ?>
                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($ig_username); ?>" class="sns-follow-card__avatar">
            <?php else : ?>
                <div class="sns-follow-card__avatar sns-follow-card__avatar--placeholder"><?php echo koi_ria_icon('instagram', 24); ?></div>
            <?php endif; ?>
            <div class="sns-follow-card__info">
                <div class="sns-follow-card__username">@<?php echo esc_html($ig_username); ?></div>
                <?php if ($followers_count) : ?>
                    <div class="sns-follow-card__followers"><?php echo esc_html(koi_ria_format_number($followers_count)); ?> フォロワー</div>
                <?php endif; ?>
            </div>
        </div>
        <a href="https://instagram.com/<?php echo esc_attr($ig_username); ?>" class="btn btn--ig sns-follow-card__cta" target="_blank" rel="noopener">Instagramをフォロー</a>
        <div class="sns-follow-card__note">最新投稿はInstagramでチェック →</div>
    </div>
</section>
<?php endif; ?>

<?php // 7-4. TikTok最新動画 ?>
<?php if ($tiktok_username) : ?>
<section class="section">
    <div class="section-header">
        <h2>TikTok</h2>
        <a href="https://tiktok.com/@<?php echo esc_attr($tiktok_username); ?>" class="section-header__more" target="_blank" rel="noopener">@<?php echo esc_html($tiktok_username); ?> →</a>
    </div>
    <div class="sns-follow-card sns-follow-card--tiktok">
        <div class="sns-follow-card__profile">
            <div class="sns-follow-card__tiktok-icon"><?php echo koi_ria_icon('tiktok', 24); ?></div>
            <div class="sns-follow-card__info">
                <div class="sns-follow-card__username">@<?php echo esc_html($tiktok_username); ?></div>
                <?php if ($tiktok_followers) : ?>
                    <div class="sns-follow-card__followers"><?php echo esc_html(koi_ria_format_number($tiktok_followers)); ?> フォロワー</div>
                <?php endif; ?>
            </div>
        </div>
        <a href="https://tiktok.com/@<?php echo esc_attr($tiktok_username); ?>" class="btn btn--tiktok sns-follow-card__cta" target="_blank" rel="noopener">TikTokをフォロー</a>
        <div class="sns-follow-card__note">最新動画はTikTokでチェック →</div>
    </div>
</section>
<?php endif; ?>

<?php // 7-5. 関連記事 ?>
<?php
$related = get_posts([
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'tag'            => sanitize_title($display_name),
]);
if ($related) :
?>
<section class="section">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('news', 22); ?> 関連記事</h2>
    </div>
    <?php
    foreach ($related as $post) :
        setup_postdata($post);
        get_template_part('template-parts/news-card', null, ['post' => $post]);
    endforeach;
    wp_reset_postdata();
    ?>
</section>
<?php endif; ?>

<?php // 同シーズンの他メンバー ?>
<?php
$s_id = is_array($season_id) ? ($season_id[0] ?? 0) : ($season_id ?: 0);
if ($s_id) :
    $same_season_members = get_posts([
        'post_type'      => 'cast',
        'posts_per_page' => 10,
        'post__not_in'   => [$cast_id],
        'meta_query'     => [
            ['key' => 'season', 'value' => $s_id, 'compare' => '='],
        ],
    ]);
    if ($same_season_members) :
?>
<section class="section">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('users', 22); ?> 同じシーズンのメンバー</h2>
    </div>
    <div class="scroll-x">
        <?php foreach ($same_season_members as $cast) :
            get_template_part('template-parts/cast-card', null, ['cast' => $cast]);
        endforeach; ?>
    </div>
</section>
<?php
    endif;
endif;
?>

<?php
get_footer();
