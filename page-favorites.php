<?php
/**
 * Template Name: 推しメンバー
 *
 * 推しページ — お気に入り出演者一覧 + フィード
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section" style="padding-top: var(--space-lg);">
    <div id="favorites-app">
        <!-- JS が LocalStorage を読み取って描画 -->
        <div class="favorites-empty">
            <div class="favorites-empty__icon">&#9825;</div>
            <p>読み込み中...</p>
        </div>
    </div>
</section>

<?php
get_footer();
