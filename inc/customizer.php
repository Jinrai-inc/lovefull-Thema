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

    /* ========================================
     * セクション: アイキャッチ設定
     * ======================================== */
    $wp_customize->add_section('koi_ria_eyecatch', [
        'title'       => 'アイキャッチ設定',
        'description' => '記事にアイキャッチ画像が未設定の場合に使用されるデフォルト画像と、タイトルオーバーレイの設定です。',
        'priority'    => 36,
    ]);

    // --- デフォルトアイキャッチ画像 ---
    $wp_customize->add_setting('koi_ria_default_eyecatch', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'koi_ria_default_eyecatch', [
        'label'       => 'デフォルトアイキャッチ画像',
        'description' => '全記事共通の背景画像。アイキャッチ未設定の記事に自動適用されます。推奨: 1792x1024px（16:9横長）',
        'section'     => 'koi_ria_eyecatch',
    ]));

    // --- オーバーレイカラー ---
    $wp_customize->add_setting('koi_ria_eyecatch_overlay_color', [
        'default'           => '#2D1B33',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'koi_ria_eyecatch_overlay_color', [
        'label'       => 'オーバーレイカラー',
        'description' => 'アイキャッチ画像の上に重ねる色',
        'section'     => 'koi_ria_eyecatch',
    ]));

    // --- オーバーレイ透明度 ---
    $wp_customize->add_setting('koi_ria_eyecatch_overlay_opacity', [
        'default'           => 45,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('koi_ria_eyecatch_overlay_opacity', [
        'label'       => 'オーバーレイ透明度（%）',
        'description' => '0 = 完全透明、100 = 完全不透明。タイトルが読みやすいよう40〜60%推奨',
        'section'     => 'koi_ria_eyecatch',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 0,
            'max'  => 100,
            'step' => 5,
        ],
    ]);
});

/**
 * カスタマイザー用プレビューJS（リアルタイム反映）
 */
add_action('customize_preview_init', function () {
    $js_url = get_template_directory_uri() . '/assets/js/customizer-preview.js';
    wp_enqueue_script('koi-ria-customizer-preview', $js_url, ['customize-preview', 'jquery'], '1.2', true);
});

/**
 * カスタマイザーコントロール用JS（スライダー数値表示）
 */
add_action('customize_controls_enqueue_scripts', function () {
    wp_add_inline_script('customize-controls', "
        var rangeIds = [
            {id:'koi_ria_logo_height_mobile', unit:'px'},
            {id:'koi_ria_logo_height_pc', unit:'px'},
            {id:'koi_ria_logo_max_width', unit:'px'},
            {id:'koi_ria_eyecatch_overlay_opacity', unit:'%'}
        ];
        rangeIds.forEach(function(item) {
            wp.customize.control(item.id, function(control) {
                control.container.on('input', 'input[type=range]', function() {
                    jQuery(this).siblings('span.value-display').remove();
                    jQuery(this).after('<span class=\"value-display\" style=\"font-weight:bold;margin-left:8px;\">' + this.value + item.unit + '</span>');
                });
            });
        });
    ");
});
