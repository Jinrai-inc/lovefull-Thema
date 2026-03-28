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
$age              = get_field('age') ?: '';
$from_area        = get_field('from_area') ?: '';
$cast_status      = get_field('cast_status') ?: '';
$profile_image    = get_field('profile_image');
$ig_cache         = get_post_meta($cast_id, 'ig_profile_cache', true);

// 番組名取得
$show_name = '';
if ($show_id) {
    $show_post = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
    if ($show_post) {
        $show_name = get_field('short_name', $show_post->ID) ?: $show_post->post_title;
    }
}

// アバター画像URL
$avatar_url = '';
if ($profile_image && isset($profile_image['url'])) {
    $avatar_url = $profile_image['url'];
} elseif ($ig_cache) {
    $avatar_url = $ig_cache;
}
?>

<?php // 7-1. ヘッダー ?>
<section class="cast-profile-header" style="position: relative;">
    <a href="javascript:history.back();" class="cast-profile-header__back">&larr;</a>

    <div class="ig-avatar ig-avatar--lg" style="margin: 0 auto;">
        <?php if ($avatar_url) : ?>
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ig-avatar__img">
        <?php else : ?>
            <div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:2rem;background:#f0f0f0;">&#x1F464;</div>
        <?php endif; ?>
    </div>

    <h1 class="cast-profile-header__name"><?php echo esc_html($display_name); ?></h1>

    <p class="cast-profile-header__meta">
        <?php if ($show_name) echo esc_html($show_name); ?>
        <?php if ($role) echo ' / ' . esc_html($role); ?>
        <?php if ($age) echo ' / ' . esc_html($age) . '歳'; ?>
        <?php if ($from_area) echo ' / ' . esc_html($from_area); ?>
    </p>

    <?php if ($followers_count) : ?>
        <p class="cast-profile-header__followers">
            IG: <?php echo esc_html(number_format($followers_count)); ?> followers
            <?php if ($tiktok_followers) : ?>
                &nbsp;|&nbsp; TikTok: <?php echo esc_html(number_format($tiktok_followers)); ?>
            <?php endif; ?>
        </p>
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

<?php // 7-3. Instagram最新投稿グリッド（Phase1: プレースホルダー） ?>
<?php if ($ig_username) : ?>
<section class="section">
    <div class="section-header">
        <h2>Instagram</h2>
        <a href="https://instagram.com/<?php echo esc_attr($ig_username); ?>" class="section-header__more" target="_blank" rel="noopener">@<?php echo esc_html($ig_username); ?> →</a>
    </div>
    <div class="grid-3">
        <?php // Phase2で Instagram Graph API から自動取得予定 ?>
        <div style="aspect-ratio:1;background:#f0f0f0;border-radius:var(--radius-sm);"></div>
        <div style="aspect-ratio:1;background:#f0f0f0;border-radius:var(--radius-sm);"></div>
        <div style="aspect-ratio:1;background:#f0f0f0;border-radius:var(--radius-sm);"></div>
        <div style="aspect-ratio:1;background:#f0f0f0;border-radius:var(--radius-sm);"></div>
        <div style="aspect-ratio:1;background:#f0f0f0;border-radius:var(--radius-sm);"></div>
        <div style="aspect-ratio:1;background:#f0f0f0;border-radius:var(--radius-sm);"></div>
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
    <div class="scroll-x">
        <?php // Phase2で TikTok oEmbed から取得予定 ?>
        <div style="min-width:150px;aspect-ratio:9/16;background:#f0f0f0;border-radius:var(--radius-md);"></div>
        <div style="min-width:150px;aspect-ratio:9/16;background:#f0f0f0;border-radius:var(--radius-md);"></div>
        <div style="min-width:150px;aspect-ratio:9/16;background:#f0f0f0;border-radius:var(--radius-md);"></div>
    </div>
</section>
<?php endif; ?>

<?php // 7-5. 関連記事 ?>
<section class="section">
    <div class="section-header">
        <h2>関連記事</h2>
    </div>
    <?php
    $related = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'tag'            => sanitize_title($display_name),
    ]);
    if ($related) :
        foreach ($related as $post) :
            setup_postdata($post);
            get_template_part('template-parts/news-card', null, ['post' => $post]);
        endforeach;
        wp_reset_postdata();
    else :
    ?>
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub); font-size: 0.875rem;">関連記事はまだありません</p>
    <?php endif; ?>
</section>

<?php
get_footer();
