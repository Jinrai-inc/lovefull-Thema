<?php
/**
 * 推しボタン（Favorite Button）
 *
 * @package KoiRiaPortal
 *
 * Expected $args:
 *   cast_id    — int
 *   name       — string
 *   ig         — string (Instagram username)
 *   show_title — string
 */

defined('ABSPATH') || exit;

$cast_id    = $args['cast_id']    ?? 0;
$name       = $args['name']       ?? '';
$ig         = $args['ig']         ?? '';
$show_title = $args['show_title'] ?? '';

if ( ! $cast_id ) return;
?>
<button class="fav-btn"
        data-cast-id="<?php echo esc_attr( $cast_id ); ?>"
        data-name="<?php echo esc_attr( $name ); ?>"
        data-ig="<?php echo esc_attr( $ig ); ?>"
        data-show-title="<?php echo esc_attr( $show_title ); ?>"
        aria-label="推しに登録">
    <span class="fav-heart">&#9825;</span>
    <span class="fav-heart active">&#9829;</span>
</button>
