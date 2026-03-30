<?php
/**
 * 相関図表示 — キャスト間の関係をカード型レイアウトで可視化
 *
 * single-show.php から get_template_part('template-parts/correlation-chart') で読み込まれる。
 * グローバル $post から番組IDを取得し、最新シーズンのキャスト＆リレーションを描画する。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

$show_id = get_the_ID();
if ( ! $show_id ) {
    return;
}

// --- 最新シーズン取得（order 昇順 = 最小値が最新） ---
$seasons = get_posts([
    'post_type'      => 'season',
    'posts_per_page' => 1,
    'meta_query'     => [
        ['key' => 'show', 'value' => $show_id, 'compare' => '='],
    ],
    'meta_key'  => 'order',
    'orderby'   => 'meta_value_num',
    'order'     => 'ASC',
]);

if ( empty($seasons) ) {
    // シーズンが無ければ何も出力しない
    return;
}

$season    = $seasons[0];
$season_id = $season->ID;

// --- キャスト一覧 ---
$cast_posts = get_posts([
    'post_type'      => 'cast',
    'posts_per_page' => -1,
    'meta_query'     => [
        ['key' => 'season', 'value' => $season_id, 'compare' => '='],
    ],
]);

// --- リレーション一覧 ---
$relations = get_posts([
    'post_type'      => 'relation',
    'posts_per_page' => -1,
    'meta_query'     => [
        ['key' => 'season', 'value' => $season_id, 'compare' => '='],
    ],
]);

// --- 関係タイプ定義 ---
$type_map = [
    'love'     => ['icon' => "\u{1F497}", 'color_class' => 'corr-rel--love',     'label' => '恋愛'],
    'couple'   => ['icon' => "\u{1F491}", 'color_class' => 'corr-rel--couple',   'label' => 'カップル'],
    'rival'    => ['icon' => "\u{26A1}",  'color_class' => 'corr-rel--rival',    'label' => 'ライバル'],
    'interest' => ['icon' => "\u{1F440}", 'color_class' => 'corr-rel--interest', 'label' => '気になる'],
];
?>

<section class="section correlation-chart">
    <div class="section-header">
        <h2>&#x1F495; 相関図</h2>
    </div>

    <?php if ( empty($relations) ) : ?>
        <div class="corr-empty">
            <p class="corr-empty__icon">&#x1F495;</p>
            <p class="corr-empty__text">相関図データはまだ登録されていません</p>
            <?php if ( current_user_can('edit_posts') ) : ?>
                <p class="corr-empty__hint">管理画面の「リレーション」から相関データを追加してください。</p>
            <?php endif; ?>
        </div>
    <?php else : ?>

        <?php // --- キャストアバターグリッド --- ?>
        <?php if ( $cast_posts ) : ?>
        <div class="corr-cast-grid">
            <?php foreach ( $cast_posts as $m ) :
                $m_name   = get_field('display_name', $m->ID) ?: $m->post_title;
                $m_img    = get_field('profile_image', $m->ID);
                $m_ig     = get_field('ig_username', $m->ID);
                $m_cache  = get_post_meta($m->ID, 'ig_profile_cache', true);
                $m_avatar = ($m_img && isset($m_img['url'])) ? $m_img['url'] : ($m_cache ?: '');
                $m_gender = get_field('gender', $m->ID) ?: '';
                $gender_class = ($m_gender === 'f') ? 'corr-avatar--female' : 'corr-avatar--male';
            ?>
            <div class="corr-cast-item">
                <div class="corr-avatar <?php echo esc_attr($gender_class); ?>">
                    <?php if ( $m_avatar ) : ?>
                        <img src="<?php echo esc_url($m_avatar); ?>" alt="<?php echo esc_attr($m_name); ?>" class="corr-avatar__img" loading="lazy">
                    <?php else : ?>
                        <span class="corr-avatar__fallback">&#x1F464;</span>
                    <?php endif; ?>
                </div>
                <span class="corr-cast-item__name"><?php echo esc_html($m_name); ?></span>
                <?php if ( $m_ig ) : ?>
                    <span class="corr-cast-item__ig">@<?php echo esc_html($m_ig); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php // --- 凡例 --- ?>
        <div class="corr-legend">
            <?php foreach ( $type_map as $key => $info ) : ?>
                <span class="corr-legend__item <?php echo esc_attr($info['color_class']); ?>">
                    <?php echo $info['icon']; ?> <?php echo esc_html($info['label']); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <?php // --- リレーションリスト --- ?>
        <ul class="corr-relations">
            <?php foreach ( $relations as $rel ) :
                $from_id = get_field('from_cast', $rel->ID);
                $to_id   = get_field('to_cast', $rel->ID);
                $r_type  = get_field('relation_type', $rel->ID) ?: '';
                $r_label = get_field('relation_label', $rel->ID) ?: '';

                // from_cast の名前解決
                $from_name = '';
                if ( $from_id ) {
                    $fp = is_array($from_id) ? get_post($from_id[0]) : get_post($from_id);
                    $from_name = $fp ? (get_field('display_name', $fp->ID) ?: $fp->post_title) : '';
                }

                // to_cast の名前解決
                $to_name = '';
                if ( $to_id ) {
                    $tp = is_array($to_id) ? get_post($to_id[0]) : get_post($to_id);
                    $to_name = $tp ? (get_field('display_name', $tp->ID) ?: $tp->post_title) : '';
                }

                $info        = $type_map[$r_type] ?? ['icon' => '❓', 'color_class' => 'corr-rel--unknown', 'label' => ''];
                $color_class = $info['color_class'];
                $icon        = $info['icon'];
            ?>
            <li class="corr-rel <?php echo esc_attr($color_class); ?>">
                <span class="corr-rel__from"><?php echo esc_html($from_name); ?></span>
                <span class="corr-rel__arrow">
                    <span class="corr-rel__line"></span>
                    <span class="corr-rel__icon"><?php echo $icon; ?></span>
                    <span class="corr-rel__line"></span>
                </span>
                <span class="corr-rel__to"><?php echo esc_html($to_name); ?></span>
                <?php if ( $r_label ) : ?>
                    <span class="corr-rel__badge"><?php echo esc_html($r_label); ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

    <?php endif; ?>
</section>
