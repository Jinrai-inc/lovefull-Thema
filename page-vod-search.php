<?php
/**
 * VOD検索ページ
 * Template Name: VOD検索
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section" style="padding-top: var(--space-lg);">
    <div class="section-header">
        <h1><?php echo koi_ria_icon('compass', 24); ?> どのVODで見れる？</h1>
    </div>

    <form class="search-bar" id="vodSearchForm" role="search" aria-label="VOD検索">
        <label for="vod-search-input" class="screen-reader-text">番組名を入力</label>
        <span class="search-bar__icon"><?php echo koi_ria_icon('search', 18); ?></span>
        <input type="search" class="search-bar__input" id="vod-search-input" placeholder="番組名を入力…" autocomplete="off">
    </form>

    <div id="vodSearchResults" style="margin-top: var(--space-lg);"></div>
</section>

<?php get_template_part('template-parts/vod-compare-table'); ?>

<?php // 記事下にも呼べるアフィリエイトCTA ?>

<?php get_footer(); ?>
