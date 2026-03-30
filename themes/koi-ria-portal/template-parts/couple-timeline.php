<?php
/**
 * カップルタイムライン コンポーネント
 *
 * Expects $args['timeline'] — array of repeater rows.
 * Each row: date, event_type, event_note
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$timeline = $args['timeline'] ?? [];

if (empty($timeline)) {
    return;
}
?>

<div class="couple-timeline">
    <?php foreach ($timeline as $event) :
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
