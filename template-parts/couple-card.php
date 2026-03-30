<?php
/**
 * カップルカード コンポーネント
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$couple_id   = $args['couple_id'] ?? get_the_ID();
$member_a    = get_field('member_a', $couple_id);
$member_b    = get_field('member_b', $couple_id);
$show        = get_field('show', $couple_id);
$season      = get_field('season', $couple_id);
$couple_name = get_field('couple_name', $couple_id) ?: get_the_title($couple_id);
$status      = get_field('couple_status', $couple_id) ?: '不明';
$timeline    = get_field('timeline', $couple_id) ?: [];

// Member data
$a_id   = is_array($member_a) ? ($member_a[0] ?? null) : $member_a;
$b_id   = is_array($member_b) ? ($member_b[0] ?? null) : $member_b;
$a_post = $a_id ? get_post($a_id) : null;
$b_post = $b_id ? get_post($b_id) : null;

$a_name   = '';
$b_name   = '';
$a_avatar = '';
$b_avatar = '';

if ($a_post) {
    $a_name = get_field('display_name', $a_post->ID) ?: $a_post->post_title;
    $a_img  = get_field('profile_image', $a_post->ID);
    $a_avatar = $a_img ? ($a_img['sizes']['cast-avatar'] ?? $a_img['url']) : '';
}
if ($b_post) {
    $b_name = get_field('display_name', $b_post->ID) ?: $b_post->post_title;
    $b_img  = get_field('profile_image', $b_post->ID);
    $b_avatar = $b_img ? ($b_img['sizes']['cast-avatar'] ?? $b_img['url']) : '';
}

// Show info
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

// Status class
$status_class = '不明';
if (in_array($status, ['交際中', '破局', '結婚'], true)) {
    $status_class = $status;
}

// Last 3 timeline events
$recent_events = array_slice($timeline, -3);
?>

<a href="<?php echo esc_url(get_permalink($couple_id)); ?>"
   class="couple-card"
   data-status="<?php echo esc_attr($status); ?>"
   data-show-id="<?php echo esc_attr($show_id_val ?: ''); ?>">

    <div class="couple-card__avatars">
        <span class="ig-avatar">
            <?php if ($a_avatar) : ?>
                <img class="ig-avatar__img" src="<?php echo esc_url($a_avatar); ?>" alt="<?php echo esc_attr($a_name); ?>">
            <?php else : ?>
                <span class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f0f0f0;">&#x1F464;</span>
            <?php endif; ?>
        </span>
        <span class="couple-card__heart">&#x2764;&#xFE0F;</span>
        <span class="ig-avatar">
            <?php if ($b_avatar) : ?>
                <img class="ig-avatar__img" src="<?php echo esc_url($b_avatar); ?>" alt="<?php echo esc_attr($b_name); ?>">
            <?php else : ?>
                <span class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f0f0f0;">&#x1F464;</span>
            <?php endif; ?>
        </span>
    </div>

    <div class="couple-card__name"><?php echo esc_html($couple_name); ?></div>

    <?php if ($show_name || $season_name) : ?>
        <div class="couple-card__show">
            <?php echo esc_html($show_name); ?>
            <?php if ($season_name) : ?> / <?php echo esc_html($season_name); ?><?php endif; ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;">
        <span class="couple-card__status couple-card__status--<?php echo esc_attr($status_class); ?>">
            <?php echo esc_html($status); ?>
        </span>
    </div>

    <?php if ($recent_events) : ?>
    <div class="couple-timeline" style="margin-top:var(--space-sm); font-size:0.75rem;">
        <?php foreach ($recent_events as $event) :
            $event_date = $event['date'] ?? '';
            $event_type = $event['event_type'] ?? '';
            $event_note = $event['event_note'] ?? '';
        ?>
        <div class="timeline-event" data-type="<?php echo esc_attr($event_type); ?>">
            <div class="timeline-event__date"><?php echo esc_html($event_date); ?></div>
            <div class="timeline-event__type"><?php echo esc_html($event_type); ?></div>
            <?php if ($event_note) : ?>
                <div class="timeline-event__note"><?php echo esc_html($event_note); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</a>
