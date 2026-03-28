<?php
/**
 * 投票カード
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$poll = $args['poll'] ?? null;
if (!$poll) return;

$question  = get_field('question', $poll->ID) ?: $poll->post_title;
$options   = get_field('options', $poll->ID) ?: [];
$is_active = get_field('is_active', $poll->ID);

// 合計投票数
$total = 0;
foreach ($options as $opt) {
    $total += intval($opt['option_votes'] ?? 0);
}

// Cookie で投票済み判定
$voted = isset($_COOKIE['koi_ria_voted_' . $poll->ID]);
?>

<div class="poll-card" data-poll-id="<?php echo esc_attr($poll->ID); ?>">
    <p class="poll-card__question"><?php echo esc_html($question); ?></p>

    <?php foreach ($options as $i => $opt) :
        $votes = intval($opt['option_votes'] ?? 0);
        $pct   = $total > 0 ? round($votes / $total * 100) : 0;
    ?>
    <div class="poll-option <?php echo $voted ? '' : 'is-votable'; ?>" data-index="<?php echo $i; ?>">
        <span class="poll-option__label"><?php echo esc_html($opt['option_label'] ?? ''); ?></span>
        <?php if ($voted || !$is_active) : ?>
            <span class="poll-option__pct"><?php echo esc_html($pct); ?>%</span>
        <?php endif; ?>
    </div>
    <?php if ($voted || !$is_active) : ?>
    <div class="poll-option__bar">
        <div class="poll-option__bar-fill" style="width: <?php echo esc_attr($pct); ?>%;"></div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($total > 0) : ?>
    <p style="text-align: right; font-size: 0.6875rem; color: var(--color-text-sub); margin-top: var(--space-sm);">
        <?php echo esc_html(number_format($total)); ?> 票
    </p>
    <?php endif; ?>

    <?php if (!$is_active) : ?>
    <p style="text-align: center; font-size: 0.75rem; color: var(--color-text-sub); margin-top: var(--space-sm);">この投票は終了しました</p>
    <?php endif; ?>
</div>
