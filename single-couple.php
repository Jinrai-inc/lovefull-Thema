<?php
/**
 * カップル詳細ページ
 *
 * @package KoiRiaPortal
 */

get_header();

while (have_posts()) : the_post();
    $couple_id   = get_the_ID();
    $member_a    = get_field('member_a', $couple_id);
    $member_b    = get_field('member_b', $couple_id);
    $show        = get_field('show', $couple_id);
    $season      = get_field('season', $couple_id);
    $couple_name = get_field('couple_name', $couple_id) ?: get_the_title();
    $status      = get_field('couple_status', $couple_id) ?: '不明';
    $timeline    = get_field('timeline', $couple_id) ?: [];

    // Get member data (relationship fields returning post IDs)
    $a_id   = is_array($member_a) ? $member_a[0] : $member_a;
    $b_id   = is_array($member_b) ? $member_b[0] : $member_b;
    $a_post = $a_id ? get_post($a_id) : null;
    $b_post = $b_id ? get_post($b_id) : null;

    // Member names
    $a_name = '';
    $b_name = '';
    $a_avatar = '';
    $b_avatar = '';

    if ($a_post) {
        $a_name = get_field('display_name', $a_post->ID) ?: $a_post->post_title;
        $a_img  = get_field('profile_image', $a_post->ID);
        $a_avatar = $a_img ? $a_img['sizes']['cast-avatar'] ?? $a_img['url'] : '';
    }
    if ($b_post) {
        $b_name = get_field('display_name', $b_post->ID) ?: $b_post->post_title;
        $b_img  = get_field('profile_image', $b_post->ID);
        $b_avatar = $b_img ? $b_img['sizes']['cast-avatar'] ?? $b_img['url'] : '';
    }

    // Show + Season info
    $show_id_val = is_array($show) ? ($show[0] ?? null) : $show;
    $season_id_val = is_array($season) ? ($season[0] ?? null) : $season;
    $show_name = '';
    $season_name = '';

    if ($show_id_val) {
        $show_post_obj = get_post($show_id_val);
        $show_name = $show_post_obj ? (get_field('short_name', $show_post_obj->ID) ?: $show_post_obj->post_title) : '';
    }
    if ($season_id_val) {
        $season_post_obj = get_post($season_id_val);
        $season_name = $season_post_obj ? (get_field('season_name', $season_post_obj->ID) ?: $season_post_obj->post_title) : '';
    }

    // Status badge class
    $status_class = '不明';
    if (in_array($status, ['交際中', '破局', '結婚'], true)) {
        $status_class = $status;
    }
?>

<section class="section">
    <!-- Couple Header -->
    <div class="couple-header">
        <div class="couple-header__avatars">
            <?php if ($a_post) : ?>
            <a href="<?php echo esc_url(get_permalink($a_post->ID)); ?>" class="ig-avatar ig-avatar--lg">
                <?php if ($a_avatar) : ?>
                    <img class="ig-avatar__img" src="<?php echo esc_url($a_avatar); ?>" alt="<?php echo esc_attr($a_name); ?>">
                <?php else : ?>
                    <span class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:2rem;background:#f0f0f0;">&#x1F464;</span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <span class="couple-header__heart">&#x2764;&#xFE0F;</span>

            <?php if ($b_post) : ?>
            <a href="<?php echo esc_url(get_permalink($b_post->ID)); ?>" class="ig-avatar ig-avatar--lg">
                <?php if ($b_avatar) : ?>
                    <img class="ig-avatar__img" src="<?php echo esc_url($b_avatar); ?>" alt="<?php echo esc_attr($b_name); ?>">
                <?php else : ?>
                    <span class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:2rem;background:#f0f0f0;">&#x1F464;</span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
        </div>

        <h1 style="font-size:1.25rem; margin-top:var(--space-md);"><?php echo esc_html($couple_name); ?></h1>

        <?php if ($show_name || $season_name) : ?>
            <p style="font-size:0.8125rem; color:var(--color-text-sub); margin-top:var(--space-xs);">
                <?php echo esc_html($show_name); ?>
                <?php if ($season_name) : ?> / <?php echo esc_html($season_name); ?><?php endif; ?>
            </p>
        <?php endif; ?>

        <span class="couple-card__status couple-card__status--<?php echo esc_attr($status_class); ?>">
            <?php echo esc_html($status); ?>
        </span>
    </div>

    <!-- Member names -->
    <div style="text-align:center; padding:0 var(--space-md); margin-bottom:var(--space-lg);">
        <?php if ($a_name && $b_name) : ?>
            <p style="font-size:0.9375rem; font-weight:500;">
                <?php echo esc_html($a_name); ?> &times; <?php echo esc_html($b_name); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Timeline -->
    <?php if ($timeline) : ?>
    <div style="padding: 0 var(--space-md);">
        <h2 style="font-size:1rem; font-weight:700; margin-bottom:var(--space-md);">&#x1F4C5; タイムライン</h2>
        <?php get_template_part('template-parts/couple-timeline', null, ['timeline' => $timeline]); ?>
    </div>
    <?php endif; ?>

    <!-- Content -->
    <?php if (get_the_content()) : ?>
    <div class="entry-content" style="padding: var(--space-lg) var(--space-md);">
        <?php the_content(); ?>
    </div>
    <?php endif; ?>
</section>

<?php endwhile;

get_footer(); ?>
