<?php
/**
 * 放送スケジュール（今週の放送）
 *
 * ACFオプションページから取得。未設定時は番組CPTの show_status=放送中 から動的生成。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$days_ja    = ['月', '火', '水', '木', '金', '土', '日'];
$today_index = (int) date('N') - 1; // 0=月, 6=日

// ACFオプションページからスケジュールデータを取得（設定されている場合）
$schedule_data = function_exists('get_field') ? get_field('weekly_schedule', 'option') : null;

// ACFオプションページ未設定時: 放送中番組から自動生成
if (empty($schedule_data)) {
    $on_air_shows = get_posts([
        'post_type'      => 'show',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'show_status',
                'value'   => ['放送中', '配信中'],
                'compare' => 'IN',
            ],
        ],
        'meta_key' => 'priority',
        'orderby'  => 'meta_value_num',
        'order'    => 'ASC',
    ]);

    // 番組を曜日に割り当て（ラウンドロビン）
    $schedule_data = [];
    foreach ($days_ja as $i => $day) {
        $show_index = $i % max(count($on_air_shows), 1);
        $show = $on_air_shows[$show_index] ?? null;
        $schedule_data[] = [
            'day'       => $day,
            'time'      => $show ? '21:00' : '',
            'show_name' => $show ? (get_field('short_name', $show->ID) ?: $show->post_title) : '',
            'platform'  => $show ? (get_field('platform', $show->ID) ?: '') : '',
            'show_id'   => $show ? $show->ID : 0,
        ];
    }
}

if (empty($schedule_data)) {
    return;
}
?>

<section class="section section--schedule">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('calendar', 22); ?> 今週の放送</h2>
    </div>
    <div class="scroll-x">
        <?php foreach ($schedule_data as $i => $entry) :
            $day       = $entry['day'] ?? $days_ja[$i] ?? '';
            $time      = $entry['time'] ?? '';
            $show_name = $entry['show_name'] ?? '';
            $platform  = $entry['platform'] ?? '';
            $show_id   = $entry['show_id'] ?? 0;
            $is_today  = ($i === $today_index);
        ?>
        <div class="schedule-card <?php echo $is_today ? 'is-today' : ''; ?>">
            <div class="schedule-card__day"><?php echo esc_html($day); ?></div>
            <?php if ($time) : ?>
                <div class="schedule-card__time"><?php echo esc_html($time); ?></div>
            <?php else : ?>
                <div class="schedule-card__time" style="opacity: 0.4;">—</div>
            <?php endif; ?>
            <?php if ($show_name) : ?>
                <?php if ($show_id) : ?>
                    <a href="<?php echo esc_url(get_permalink($show_id)); ?>" class="schedule-card__show"><?php echo esc_html($show_name); ?></a>
                <?php else : ?>
                    <div class="schedule-card__show"><?php echo esc_html($show_name); ?></div>
                <?php endif; ?>
                <?php if ($platform && !$is_today) : ?>
                    <span class="badge badge--<?php echo esc_attr(sanitize_title($platform)); ?>" style="margin-top: 4px; font-size: 0.5rem;"><?php echo esc_html($platform); ?></span>
                <?php endif; ?>
            <?php else : ?>
                <div class="schedule-card__show" style="opacity: 0.4;">—</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
