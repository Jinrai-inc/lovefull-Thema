<?php
/**
 * 出演者データベース（アコーディオン構造）
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section">
    <div class="section-header">
        <h1>&#x1F465; 出演者データベース</h1>
    </div>

    <?php // プラットフォームフィルター ?>
    <div class="pill-filters" style="margin-bottom: var(--space-md);">
        <button class="pill-filter is-active" data-platform="all">全番組</button>
        <button class="pill-filter" data-platform="ABEMA">ABEMA</button>
        <button class="pill-filter" data-platform="Netflix">Netflix</button>
        <button class="pill-filter" data-platform="Prime Video">Prime Video</button>
    </div>

    <?php // 検索バー ?>
    <form class="search-bar" style="margin-bottom: var(--space-lg);" action="<?php echo esc_url(home_url('/')); ?>" method="get">
        <span class="search-bar__icon">&#x1F50D;</span>
        <input type="search" class="search-bar__input" name="s" placeholder="出演者名で検索…">
        <input type="hidden" name="post_type" value="cast">
    </form>

    <?php // アコーディオン: 番組 → シーズン → メンバー ?>
    <?php get_template_part('template-parts/cast-accordion'); ?>
</section>

<?php
get_footer();
