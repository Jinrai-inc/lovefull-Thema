<?php
/**
 * 放送スケジュール（今週の放送）
 *
 * 番組CPTの broadcast_day フィールドから曜日別に表示。
 * broadcast_day 未設定の番組はスケジュールに表示されない。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$days_ja     = ['月', '火', '水', '木', '金', '土', '日'];
$today_index = (int) date('N') - 1; // 0=月, 6=日

// 放送中・配信中の番組を取得
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

// 曜日別に番組をグループ化（broadcast_day フィールドを使用）
$shows_by_day = array_fill(0, 7, []);

foreach ($on_air_shows as $show) {
    $broadcast_day = get_field('broadcast_day', $show->ID);
    if ($broadcast_day === '' || $broadcast_day === null || $broadcast_day === false) {
        continue; // 放送曜日未設定 → スケジュール非表示
    }
    $day_index = intval($broadcast_day);
    if ($day_index >= 0 && $day_index <= 6) {
        $shows_by_day[$day_index][] = $show;
    }
}

// スケジュールデータ生成
$schedule_data = [];
foreach ($days_ja as $i => $day) {
    $day_shows = $shows_by_day[$i];
    if (!empty($day_shows)) {
        // 最優先の番組を表示（複数ある場合は priority 順で最初の1つ）
        $show = $day_shows[0];
        $schedule_data[] = [
            'day'       => $day,
            'time'      => get_field('broadcast_time', $show->ID) ?: '',
            'show_name' => get_field('short_name', $show->ID) ?: $show->post_title,
            'platform'  => get_field('platform', $show->ID) ?: '',
            'show_id'   => $show->ID,
        ];
    } else {
        $schedule_data[] = [
            'day'       => $day,
            'time'      => '',
            'show_name' => '',
            'platform'  => '',
            'show_id'   => 0,
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
