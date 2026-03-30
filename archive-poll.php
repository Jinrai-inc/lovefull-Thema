<?php
/**
 * 投票一覧テンプレート
 *
 * @package KoiRiaPortal
 */

get_header();
?>

<section class="section">
    <div class="section-header">
        <h1>&#x1F5F3; みんなの予想</h1>
    </div>

    <?php
    $polls = get_posts([
        'post_type'      => 'poll',
        'posts_per_page' => 20,
        'meta_key'       => 'is_active',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    if ($polls) :
        foreach ($polls as $poll) :
            get_template_part('template-parts/poll-card', null, ['poll' => $poll]);
            echo '<div style="height: var(--space-md);"></div>';
        endforeach;
    else :
    ?>
        <p style="padding: 0 var(--space-md); color: var(--color-text-sub);">投票はまだありません</p>
    <?php endif; ?>
</section>

<?php
get_footer();
