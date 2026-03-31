<?php
/**
 * ブロックスタイル登録（register_block_style）
 *
 * WordPressエディターのサイドバーからスタイルを選択可能にする。
 * core/heading, core/group, core/list, core/quote, core/paragraph に対応。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    // ── 見出しスタイル ──
    $heading_styles = [
        ['name' => 'koi-ribbon',    'label' => 'リボン'],
        ['name' => 'koi-heart',     'label' => 'ハート'],
        ['name' => 'koi-gradient',  'label' => 'グラデーション'],
        ['name' => 'koi-bubble',    'label' => '吹き出し'],
        ['name' => 'koi-star',      'label' => 'キラキラ'],
        ['name' => 'koi-stripe',    'label' => 'ストライプ'],
        ['name' => 'koi-bracket',   'label' => 'カギ括弧'],
        ['name' => 'koi-underline', 'label' => 'ドット下線'],
        ['name' => 'koi-balloon',   'label' => 'バルーン'],
        ['name' => 'koi-label',     'label' => 'ラベル'],
    ];
    foreach ($heading_styles as $style) {
        register_block_style('core/heading', $style);
    }

    // ── ボックス（グループ）スタイル ──
    $group_styles = [
        ['name' => 'koi-box-pink',    'label' => 'ピンクボックス'],
        ['name' => 'koi-box-point',   'label' => 'POINTボックス'],
        ['name' => 'koi-box-caution', 'label' => '注意ボックス'],
        ['name' => 'koi-box-check',   'label' => 'チェックボックス'],
        ['name' => 'koi-box-quote',   'label' => '引用ボックス'],
        ['name' => 'koi-box-ranking', 'label' => 'ランキング'],
        ['name' => 'koi-box-memo',    'label' => 'メモ'],
        ['name' => 'koi-box-love',    'label' => 'ラブ'],
        ['name' => 'koi-box-step',    'label' => 'ステップ'],
        ['name' => 'koi-box-profile', 'label' => 'プロフィール'],
        ['name' => 'koi-balloon-left',  'label' => '左フキダシ'],
        ['name' => 'koi-balloon-right', 'label' => '右フキダシ'],
        ['name' => 'koi-balloon-think', 'label' => '考え中フキダシ'],
    ];
    foreach ($group_styles as $style) {
        register_block_style('core/group', $style);
    }

    // ── リストスタイル ──
    $list_styles = [
        ['name' => 'koi-check',   'label' => 'チェック'],
        ['name' => 'koi-heart',   'label' => 'ハート'],
        ['name' => 'koi-star',    'label' => 'スター'],
        ['name' => 'koi-number',  'label' => 'ナンバー'],
    ];
    foreach ($list_styles as $style) {
        register_block_style('core/list', $style);
    }

    // ── 引用スタイル ──
    register_block_style('core/quote', [
        'name'  => 'koi-cute',
        'label' => 'かわいい引用',
    ]);

    // ── 段落スタイル ──
    $paragraph_styles = [
        ['name' => 'koi-marker-pink',     'label' => 'ピンクマーカー'],
        ['name' => 'koi-marker-yellow',   'label' => '黄色マーカー'],
        ['name' => 'koi-marker-gradient', 'label' => 'グラデマーカー'],
        ['name' => 'koi-large',           'label' => '大きめ'],
        ['name' => 'koi-small',           'label' => '小さめ'],
    ];
    foreach ($paragraph_styles as $style) {
        register_block_style('core/paragraph', $style);
    }
});
