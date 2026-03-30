<?php
/**
 * 404ページテンプレート
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section page-404">
    <div class="page-404__emoji"><?php echo koi_ria_icon('heart', 48); ?></div>
    <h1 class="page-404__title">ページが見つかりません</h1>
    <p class="page-404__text">お探しのページは移動または削除された可能性があります。</p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary" style="margin-top: var(--space-lg);">トップに戻る</a>
</section>

<?php
get_footer();
