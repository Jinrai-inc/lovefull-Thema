<?php
/**
 * WordPress カスタマイザー設定
 *
 * 外観 → カスタマイズ からリアルタイムプレビュー付きで調整可能。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {

    /* ========================================
     * セクション: ヘッダー設定
     * ======================================== */
    $wp_customize->add_section('koi_ria_header', [
        'title'    => 'ヘッダー設定',
        'priority' => 30,
    ]);

    // --- ロゴの高さ ---
    $wp_customize->add_setting('koi_ria_logo_height', [
        'default'           => 40,
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('koi_ria_logo_height', [
        'label'       => 'ロゴの高さ（px）',
        'description' => 'ヘッダーに表示するロゴ画像の高さを調整します。',
        'section'     => 'koi_ria_header',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 20,
            'max'  => 120,
            'step' => 5,
        ],
    ]);

    // --- ロゴ最大幅 ---
    $wp_customize->add_setting('koi_ria_logo_max_width', [
        'default'           => 200,
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('koi_ria_logo_max_width', [
        'label'       => 'ロゴの最大幅（px）',
        'description' => 'ロゴ画像の横幅の上限を設定します。',
        'section'     => 'koi_ria_header',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 80,
            'max'  => 400,
            'step' => 10,
        ],
    ]);

});

/**
 * カスタマイザー用プレビューJS（リアルタイム反映）
 */
add_action('customize_preview_init', function () {
    $js_url = get_template_directory_uri() . '/assets/js/customizer-preview.js';
    wp_enqueue_script('koi-ria-customizer-preview', $js_url, ['customize-preview', 'jquery'], '1.0', true);
});

/**
 * カスタマイザーコントロール用JS（スライダー数値表示）
 */
add_action('customize_controls_enqueue_scripts', function () {
    wp_add_inline_script('customize-controls', "
        wp.customize.control('koi_ria_logo_height', function(control) {
            control.container.on('input', 'input[type=range]', function() {
                jQuery(this).next('.customize-control-description, span.value-display').remove();
                jQuery(this).after('<span class=\"value-display\" style=\"font-weight:bold;margin-left:8px;\">' + this.value + 'px</span>');
            });
        });
        wp.customize.control('koi_ria_logo_max_width', function(control) {
            control.container.on('input', 'input[type=range]', function() {
                jQuery(this).next('span.value-display').remove();
                jQuery(this).after('<span class=\"value-display\" style=\"font-weight:bold;margin-left:8px;\">' + this.value + 'px</span>');
            });
        });
    ");
});
