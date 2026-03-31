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
     * セクション: SEO / サイト基本情報
     * ======================================== */
    $wp_customize->add_section('koi_ria_seo', [
        'title'       => 'SEO / サイト基本情報',
        'description' => 'サイトタイトル・メタディスクリプションなど、検索結果に表示される情報を設定します。',
        'priority'    => 20,
    ]);

    // --- サイトタイトル（WordPress標準設定を移動） ---
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->get_control('blogname')->section  = 'koi_ria_seo';
    $wp_customize->get_control('blogname')->priority  = 1;
    $wp_customize->get_control('blogname')->label      = 'サイトタイトル';
    $wp_customize->get_control('blogname')->description = '検索結果やブラウザタブに表示されるサイト名です。';

    // --- キャッチフレーズ（WordPress標準設定を移動） ---
    $wp_customize->get_setting('blogdescription')->transport = 'postMessage';
    $wp_customize->get_control('blogdescription')->section  = 'koi_ria_seo';
    $wp_customize->get_control('blogdescription')->priority  = 2;
    $wp_customize->get_control('blogdescription')->label      = 'キャッチフレーズ';
    $wp_customize->get_control('blogdescription')->description = 'タイトルタグに「サイト名 | キャッチフレーズ」として表示されます。';

    // --- フロントページ meta description ---
    $wp_customize->add_setting('koi_ria_meta_description', [
        'default'           => '恋愛リアリティ番組の最新ニュース・出演者情報・番組まとめをお届けするポータルサイト。今日好き、あいのり、バチェラーなど人気番組を網羅。',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('koi_ria_meta_description', [
        'label'       => 'メタディスクリプション（トップページ）',
        'description' => 'Google検索結果に表示される説明文です（160文字以内推奨）。',
        'section'     => 'koi_ria_seo',
        'type'        => 'textarea',
        'priority'    => 3,
        'input_attrs' => ['rows' => 3, 'maxlength' => 200],
    ]);

    // --- OGP デフォルト画像 ---
    $wp_customize->add_setting('koi_ria_ogp_image', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'koi_ria_ogp_image', [
        'label'       => 'OGPデフォルト画像',
        'description' => 'SNSでシェアされた際に表示されるデフォルト画像です。推奨: 1200x630px',
        'section'     => 'koi_ria_seo',
        'priority'    => 4,
    ]));

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
        'description' => '記事にアイキャッチ画像が未設定の場合に表示されるデフォルト画像です。',
        'priority'    => 36,
    ]);

    // --- デフォルトアイキャッチ画像 ---
    $wp_customize->add_setting('koi_ria_default_eyecatch', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'koi_ria_default_eyecatch', [
        'label'       => 'デフォルトアイキャッチ画像',
        'description' => 'アイキャッチ未設定の記事に自動表示されます。推奨サイズ: 1792×1024px（16:9横長）',
        'section'     => 'koi_ria_eyecatch',
    ]));
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
