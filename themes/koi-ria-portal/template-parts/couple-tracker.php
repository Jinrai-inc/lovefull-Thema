<?php
/**
 * カップルその後カード
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// 相関図データから couple タイプを取得
$couples = get_posts([
    'post_type'      => 'relation',
    'posts_per_page' => 10,
    'meta_query'     => [
        ['key' => 'relation_type', 'value' => 'couple', 'compare' => '='],
    ],
]);

if (empty($couples)) {
    return;
}
?>

<section class="section">
    <div class="section-header">
        <h2>&#x1F491; カップルその後</h2>
    </div>

    <div style="background: var(--color-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); margin: 0 var(--space-md); overflow: hidden;">
        <?php foreach ($couples as $rel) :
            $from_id    = get_field('from_cast', $rel->ID);
            $to_id      = get_field('to_cast', $rel->ID);
            $label      = get_field('relation_label', $rel->ID) ?: '';
            $season_id  = get_field('season', $rel->ID);

            $from_name = '';
            $to_name   = '';
            $show_name = '';

            if ($from_id) {
                $from_post = is_array($from_id) ? get_post($from_id[0]) : get_post($from_id);
                $from_name = $from_post ? (get_field('display_name', $from_post->ID) ?: $from_post->post_title) : '';
            }
            if ($to_id) {
                $to_post = is_array($to_id) ? get_post($to_id[0]) : get_post($to_id);
                $to_name = $to_post ? (get_field('display_name', $to_post->ID) ?: $to_post->post_title) : '';
            }
            if ($season_id) {
                $season_post = is_array($season_id) ? get_post($season_id[0]) : get_post($season_id);
                if ($season_post) {
                    $show_id_val = get_field('show', $season_post->ID);
                    if ($show_id_val) {
                        $show_post = is_array($show_id_val) ? get_post($show_id_val[0]) : get_post($show_id_val);
                        $show_name = $show_post ? (get_field('short_name', $show_post->ID) ?: $show_post->post_title) : '';
                    }
                }
            }

            // ステータスバッジ判定
            $badge_class = 'badge--unknown';
            $badge_text  = $label;
            if (mb_strpos($label, '交際') !== false) {
                $badge_class = 'badge--active';
            } elseif (mb_strpos($label, '破局') !== false) {
                $badge_class = 'badge--ended';
            } elseif (mb_strpos($label, '結婚') !== false) {
                $badge_class = 'badge--married';
            }
        ?>
        <div class="couple-item">
            <span class="couple-item__icon">&#x1F496;</span>
            <div class="couple-item__info">
                <div class="couple-item__names"><?php echo esc_html($from_name); ?> &times; <?php echo esc_html($to_name); ?></div>
                <?php if ($show_name) : ?>
                    <div class="couple-item__show"><?php echo esc_html($show_name); ?></div>
                <?php endif; ?>
            </div>
            <?php if ($badge_text) : ?>
                <span class="badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
