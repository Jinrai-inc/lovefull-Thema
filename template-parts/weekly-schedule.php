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

// Platform emoji fallback map
$platform_emoji = [
    'ABEMA'       => "\u{1F4FA}",
    'Netflix'     => "\u{1F3AC}",
    'Prime Video' => "\u{1F4E6}",
    'U-NEXT'      => "\u{1F3A5}",
    'Hulu'        => "\u{1F30A}",
    'Disney+'     => "\u{2728}",
    'TVer'        => "\u{1F4F1}",
];
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
            $platform_color = $platform ? koi_ria_get_platform_color($platform) : '#E8619A';
            $has_thumbnail = $show_id && has_post_thumbnail($show_id);
            $emoji = $platform_emoji[$platform] ?? "\u{1F4FA}";
            $card_tag = $show_id ? 'a' : 'div';
            $card_href = $show_id ? ' href="' . esc_url(get_permalink($show_id)) . '"' : '';
        ?>
        <<?php echo $card_tag; ?><?php echo $card_href; ?>
            class="schedule-card <?php echo $is_today ? 'is-today' : ''; ?>"
            style="--platform-color: <?php echo esc_attr($platform_color); ?>;">

            <?php if ($is_today && $show_name) : ?>
                <span class="schedule-card__onair">
                    <span class="schedule-card__onair-dot"></span>ON AIR
                </span>
            <?php endif; ?>

            <div class="schedule-card__day-label">
                <span class="schedule-card__day-kanji"><?php echo esc_html($day); ?></span>
                <span class="schedule-card__day-suffix">曜日</span>
            </div>

            <?php if ($time) : ?>
                <div class="schedule-card__time"><?php echo esc_html($time); ?></div>
            <?php else : ?>
                <div class="schedule-card__time schedule-card__time--empty">--:--</div>
            <?php endif; ?>

            <?php if ($show_name) : ?>
                <div class="schedule-card__thumb">
                    <?php if ($has_thumbnail) : ?>
                        <?php echo get_the_post_thumbnail($show_id, 'thumbnail', ['class' => 'schedule-card__img', 'loading' => 'lazy']); ?>
                    <?php else : ?>
                        <span class="schedule-card__emoji"><?php echo $emoji; ?></span>
                    <?php endif; ?>
                </div>

                <div class="schedule-card__show"><?php echo esc_html($show_name); ?></div>

                <?php if ($platform) : ?>
                    <span class="schedule-card__platform" style="--platform-color: <?php echo esc_attr($platform_color); ?>;">
                        <?php echo esc_html($platform); ?>
                    </span>
                <?php endif; ?>
            <?php else : ?>
                <div class="schedule-card__thumb">
                    <span class="schedule-card__emoji" style="opacity: 0.3;">&#x1F4FA;</span>
                </div>
                <div class="schedule-card__show schedule-card__show--empty">---</div>
            <?php endif; ?>

        </<?php echo $card_tag; ?>>
        <?php endforeach; ?>
    </div>
</section>
