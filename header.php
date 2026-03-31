<?php
/**
 * ヘッダーテンプレート
 *
 * 構造: ロゴバー（非追尾） → ナビバー（追尾sticky）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content">コンテンツへスキップ</a>

<header class="site-header" role="banner">
    <?php // ── ロゴエリア（センター配置・非追尾） ── ?>
    <div class="site-logo-bar">
        <?php if (has_custom_logo()) : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
                <?php
                $custom_logo_id = get_theme_mod('custom_logo');
                echo wp_get_attachment_image($custom_logo_id, 'full', false, [
                    'class' => 'site-logo__img',
                    'alt'   => get_bloginfo('name'),
                ]);
                ?>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
                <span class="site-logo__icon"><?php echo koi_ria_icon('heart-filled', 28); ?></span>
                <span class="site-logo__text"><?php bloginfo('name'); ?></span>
            </a>
        <?php endif; ?>
    </div>

    <?php // ── ナビバー（追尾sticky） ── ?>
    <div class="nav-bar" id="navBar">
        <div class="nav-bar__inner">
            <nav class="nav-bar__links" aria-label="メインナビゲーション">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="header-nav__link header-nav__link--home">
                    <span class="header-nav__icon"><?php echo koi_ria_icon('heart-filled', 14); ?></span>ホーム
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>" class="header-nav__link header-nav__link--show">
                    <span class="header-nav__icon"><?php echo koi_ria_icon('play', 14); ?></span>番組一覧
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>" class="header-nav__link header-nav__link--cast">
                    <span class="header-nav__icon"><?php echo koi_ria_icon('cast', 14); ?></span>出演者
                </a>
                <?php
                $column_cat = get_category_by_slug('column');
                if ($column_cat) : ?>
                    <a href="<?php echo esc_url(get_category_link($column_cat->term_id)); ?>" class="header-nav__link header-nav__link--column">
                        <span class="header-nav__icon"><?php echo koi_ria_icon('pen', 14); ?></span>恋愛コラム
                    </a>
                <?php endif; ?>
                <?php
                $news_cat = get_category_by_slug('news');
                if ($news_cat) : ?>
                    <a href="<?php echo esc_url(get_category_link($news_cat->term_id)); ?>" class="header-nav__link header-nav__link--news">
                        <span class="header-nav__icon"><?php echo koi_ria_icon('fire', 14); ?></span>ニュース
                    </a>
                <?php endif; ?>
                <a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>#relation" class="header-nav__link header-nav__link--relation">
                    <span class="header-nav__icon"><?php echo koi_ria_icon('sparkles', 14); ?></span>相関図
                </a>
                <a href="<?php echo esc_url(home_url('/shindan/')); ?>" class="header-nav__link header-nav__link--shindan">
                    <span class="header-nav__icon"><?php echo koi_ria_icon('poll', 14); ?></span>番組診断
                </a>
            </nav>

            <button class="hamburger" id="menuToggle" aria-label="メニューを開く" aria-expanded="false">
                <span class="hamburger__icon hamburger__icon--menu"><?php echo koi_ria_icon('menu', 24); ?></span>
                <span class="hamburger__icon hamburger__icon--close" style="display:none;"><?php echo koi_ria_icon('close', 24); ?></span>
            </button>
        </div>

        <?php // ── ハンバーガーメニュー（ナビと同じリンク） ── ?>
        <nav class="global-nav" id="globalNav" aria-label="グローバルナビゲーション">
            <ul class="global-nav__list">
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php echo koi_ria_icon('heart-filled', 18); ?> ホーム</a></li>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('show')); ?>"><?php echo koi_ria_icon('play', 18); ?> 番組一覧</a></li>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>"><?php echo koi_ria_icon('cast', 18); ?> 出演者</a></li>
                <?php if ($column_cat) : ?>
                    <li><a href="<?php echo esc_url(get_category_link($column_cat->term_id)); ?>"><?php echo koi_ria_icon('pen', 18); ?> 恋愛コラム</a></li>
                <?php endif; ?>
                <?php if ($news_cat) : ?>
                    <li><a href="<?php echo esc_url(get_category_link($news_cat->term_id)); ?>"><?php echo koi_ria_icon('fire', 18); ?> ニュース</a></li>
                <?php endif; ?>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('cast')); ?>#relation"><?php echo koi_ria_icon('sparkles', 18); ?> 相関図</a></li>
                <li><a href="<?php echo esc_url(home_url('/shindan/')); ?>"><?php echo koi_ria_icon('poll', 18); ?> 番組診断</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="site-main" id="content" role="main">
