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
        'title'    => 'ヘッダー・ロゴ設定',
        'priority' => 30,
    ]);

    // --- モバイル ロゴの高さ ---
    $wp_customize->add_setting('koi_ria_logo_height_mobile', [
        'default'           => 50,
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('koi_ria_logo_height_mobile', [
        'label'       => 'ロゴの高さ — モバイル（px）',
        'description' => 'スマートフォンで表示するロゴの高さ',
        'section'     => 'koi_ria_header',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 20,
            'max'  => 150,
            'step' => 5,
        ],
    ]);

    // --- PC ロゴの高さ ---
    $wp_customize->add_setting('koi_ria_logo_height_pc', [
        'default'           => 70,
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('koi_ria_logo_height_pc', [
        'label'       => 'ロゴの高さ — PC（px）',
        'description' => 'デスクトップで表示するロゴの高さ',
        'section'     => 'koi_ria_header',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 30,
            'max'  => 200,
            'step' => 5,
        ],
    ]);

    // --- ロゴ最大幅 ---
    $wp_customize->add_setting('koi_ria_logo_max_width', [
        'default'           => 300,
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('koi_ria_logo_max_width', [
        'label'       => 'ロゴの最大幅（px）',
        'description' => 'ロゴ画像の横幅の上限',
        'section'     => 'koi_ria_header',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 100,
            'max'  => 600,
            'step' => 10,
        ],
    ]);
});

/**
 * カスタマイザー用プレビューJS（リアルタイム反映）
 */
add_action('customize_preview_init', function () {
    $js_url = get_template_directory_uri() . '/assets/js/customizer-preview.js';
    wp_enqueue_script('koi-ria-customizer-preview', $js_url, ['customize-preview', 'jquery'], '1.1', true);
});

/**
 * カスタマイザーコントロール用JS（スライダー数値表示）
 */
add_action('customize_controls_enqueue_scripts', function () {
    wp_add_inline_script('customize-controls', "
        ['koi_ria_logo_height_mobile', 'koi_ria_logo_height_pc', 'koi_ria_logo_max_width'].forEach(function(id) {
            wp.customize.control(id, function(control) {
                control.container.on('input', 'input[type=range]', function() {
                    jQuery(this).siblings('span.value-display').remove();
                    jQuery(this).after('<span class=\"value-display\" style=\"font-weight:bold;margin-left:8px;\">' + this.value + 'px</span>');
                });
            });
        });
    ");
});
