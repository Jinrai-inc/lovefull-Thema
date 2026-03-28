<?php
/**
 * 放送スケジュール（今週の放送）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// ACFオプションページ or ハードコードのスケジュール
// Phase2以降でACFオプションページから動的管理に変更予定
$days = ['月', '火', '水', '木', '金', '土', '日'];
$today_index = (int) date('N') - 1; // 0=月, 6=日
?>

<section class="section">
    <div class="section-header">
        <h2>&#x1F4C5; 今週の放送</h2>
    </div>
    <div class="scroll-x">
        <?php foreach ($days as $i => $day) :
            $is_today = ($i === $today_index);
        ?>
        <div class="schedule-card <?php echo $is_today ? 'is-today' : ''; ?>">
            <div class="schedule-card__day"><?php echo esc_html($day); ?></div>
            <div class="schedule-card__time">--:--</div>
            <div class="schedule-card__show">-</div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
