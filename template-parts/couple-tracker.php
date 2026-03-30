<?php
/**
 * カップルその後セクション（トップページ用）
 *
 * couple CPT から最新3組を表示 + relation CPTからのフォールバック
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// 新couple CPTから取得
$couple_posts = get_posts([
    'post_type'      => 'couple',
    'posts_per_page' => 3,
    'orderby'        => 'modified',
    'order'          => 'DESC',
]);

// couple CPTにデータがない場合はrelationから従来の表示
if (empty($couple_posts)) {
    $couples = get_posts([
        'post_type'      => 'relation',
        'posts_per_page' => 5,
        'meta_query'     => [
            ['key' => 'relation_type', 'value' => 'couple', 'compare' => '='],
        ],
    ]);

    if (empty($couples)) return;
    ?>
    <section class="section section--couple">
        <div class="section-header">
            <h2><?php echo koi_ria_icon('couple', 22); ?> カップルその後</h2>
        </div>
        <div style="background: var(--color-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); margin: 0 var(--space-md); overflow: hidden;">
            <?php foreach ($couples as $rel) :
                $from_id   = get_field('from_cast', $rel->ID);
                $to_id     = get_field('to_cast', $rel->ID);
                $label     = get_field('relation_label', $rel->ID) ?: '';
                $season_id = get_field('season', $rel->ID);
                $from_name = '';
                $to_name   = '';
                $show_name = '';
                if ($from_id) {
                    $fp = is_array($from_id) ? get_post($from_id[0]) : get_post($from_id);
                    $from_name = $fp ? (get_field('display_name', $fp->ID) ?: $fp->post_title) : '';
                }
                if ($to_id) {
                    $tp = is_array($to_id) ? get_post($to_id[0]) : get_post($to_id);
                    $to_name = $tp ? (get_field('display_name', $tp->ID) ?: $tp->post_title) : '';
                }
                if ($season_id) {
                    $sp = is_array($season_id) ? get_post($season_id[0]) : get_post($season_id);
                    if ($sp) {
                        $sid = get_field('show', $sp->ID);
                        if ($sid) {
                            $shp = is_array($sid) ? get_post($sid[0]) : get_post($sid);
                            $show_name = $shp ? (get_field('short_name', $shp->ID) ?: $shp->post_title) : '';
                        }
                    }
                }
            ?>
            <div class="couple-item">
                <span class="couple-item__icon"><?php echo koi_ria_icon('heart-filled', 20); ?></span>
                <div class="couple-item__info">
                    <div class="couple-item__names"><?php echo esc_html($from_name); ?> &times; <?php echo esc_html($to_name); ?></div>
                    <?php if ($show_name) : ?>
                        <div class="couple-item__show"><?php echo esc_html($show_name); ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($label) : ?>
                    <span class="badge"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return;
}

// 新couple CPTの表示
?>
<section class="section section--couple">
    <div class="section-header">
        <h2><?php echo koi_ria_icon('couple', 22); ?> カップルその後</h2>
        <a href="<?php echo esc_url(get_post_type_archive_link('couple')); ?>" class="section-header__more">もっと見る →</a>
    </div>

    <div style="display: flex; flex-direction: column; gap: var(--space-md); padding: 0 var(--space-md);">
        <?php foreach ($couple_posts as $cp) :
            $member_a = get_field('member_a', $cp->ID);
            $member_b = get_field('member_b', $cp->ID);
            $status   = get_field('couple_status', $cp->ID) ?: '不明';
            $c_name   = get_field('couple_name', $cp->ID) ?: $cp->post_title;
            $show_id  = get_field('show', $cp->ID);

            $a_id = is_array($member_a) ? ($member_a[0] ?? 0) : ($member_a ?: 0);
            $b_id = is_array($member_b) ? ($member_b[0] ?? 0) : ($member_b ?: 0);

            $a_name = $a_id ? (get_field('display_name', $a_id) ?: get_the_title($a_id)) : '';
            $b_name = $b_id ? (get_field('display_name', $b_id) ?: get_the_title($b_id)) : '';

            $show_name = '';
            if ($show_id) {
                $sp = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
                $show_name = $sp ? (get_field('short_name', $sp->ID) ?: $sp->post_title) : '';
            }

            // アバター取得
            $a_avatar = '';
            $b_avatar = '';
            if ($a_id) {
                $img = get_field('profile_image', $a_id);
                $a_avatar = ($img && isset($img['sizes']['cast-avatar'])) ? $img['sizes']['cast-avatar'] : (koi_ria_get_ig_avatar(get_field('ig_username', $a_id), 200) ?: '');
            }
            if ($b_id) {
                $img = get_field('profile_image', $b_id);
                $b_avatar = ($img && isset($img['sizes']['cast-avatar'])) ? $img['sizes']['cast-avatar'] : (koi_ria_get_ig_avatar(get_field('ig_username', $b_id), 200) ?: '');
            }

            $status_class = 'couple-card__status--' . $status;
        ?>
        <a href="<?php echo esc_url(get_permalink($cp)); ?>" class="couple-card">
            <div class="couple-card__avatars">
                <div class="ig-avatar" style="width: 50px; height: 50px;">
                    <?php if ($a_avatar) : ?>
                        <img src="<?php echo esc_url($a_avatar); ?>" alt="<?php echo esc_attr($a_name); ?>" class="ig-avatar__img" style="width:50px;height:50px;" loading="lazy">
                    <?php else : ?>
                        <div class="ig-avatar__img" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;"><?php echo koi_ria_icon('users', 20); ?></div>
                    <?php endif; ?>
                </div>
                <span class="couple-card__heart"><?php echo koi_ria_icon('heart', 18); ?></span>
                <div class="ig-avatar" style="width: 50px; height: 50px;">
                    <?php if ($b_avatar) : ?>
                        <img src="<?php echo esc_url($b_avatar); ?>" alt="<?php echo esc_attr($b_name); ?>" class="ig-avatar__img" style="width:50px;height:50px;" loading="lazy">
                    <?php else : ?>
                        <div class="ig-avatar__img" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;"><?php echo koi_ria_icon('users', 20); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="couple-card__name"><?php echo esc_html($c_name); ?></div>
            <?php if ($show_name) : ?>
                <div class="couple-card__show"><?php echo esc_html($show_name); ?></div>
            <?php endif; ?>
            <div style="text-align: center;">
                <span class="couple-card__status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status); ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
