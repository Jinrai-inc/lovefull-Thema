<?php
/**
 * カスタム投稿タイプ一括登録
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('init', 'koi_ria_register_post_types');

function koi_ria_register_post_types(): void {
    // 番組（show）
    register_post_type('show', [
        'label'        => '番組',
        'labels'       => [
            'name'               => '番組',
            'singular_name'      => '番組',
            'add_new'            => '新規追加',
            'add_new_item'       => '番組を追加',
            'edit_item'          => '番組を編集',
            'new_item'           => '新しい番組',
            'view_item'          => '番組を表示',
            'search_items'       => '番組を検索',
            'not_found'          => '番組が見つかりません',
            'not_found_in_trash' => 'ゴミ箱に番組はありません',
            'all_items'          => 'すべての番組',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-video-alt3',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'show'],
        'show_in_rest' => true,
    ]);

    // シーズン（season）
    register_post_type('season', [
        'label'        => 'シーズン',
        'labels'       => [
            'name'               => 'シーズン',
            'singular_name'      => 'シーズン',
            'add_new'            => '新規追加',
            'add_new_item'       => 'シーズンを追加',
            'edit_item'          => 'シーズンを編集',
            'new_item'           => '新しいシーズン',
            'view_item'          => 'シーズンを表示',
            'search_items'       => 'シーズンを検索',
            'not_found'          => 'シーズンが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にシーズンはありません',
            'all_items'          => 'すべてのシーズン',
        ],
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => ['title'],
        'rewrite'      => ['slug' => 'season'],
        'show_in_rest' => true,
    ]);

    // 出演者（cast）
    register_post_type('cast', [
        'label'        => '出演者',
        'labels'       => [
            'name'               => '出演者',
            'singular_name'      => '出演者',
            'add_new'            => '新規追加',
            'add_new_item'       => '出演者を追加',
            'edit_item'          => '出演者を編集',
            'new_item'           => '新しい出演者',
            'view_item'          => '出演者を表示',
            'search_items'       => '出演者を検索',
            'not_found'          => '出演者が見つかりません',
            'not_found_in_trash' => 'ゴミ箱に出演者はありません',
            'all_items'          => 'すべての出演者',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'cast'],
        'show_in_rest' => true,
    ]);

    // 相関図データ（relation）
    register_post_type('relation', [
        'label'        => '相関図',
        'labels'       => [
            'name'               => '相関図',
            'singular_name'      => '相関図',
            'add_new'            => '新規追加',
            'add_new_item'       => '相関図を追加',
            'edit_item'          => '相関図を編集',
            'new_item'           => '新しい相関図',
            'search_items'       => '相関図を検索',
            'not_found'          => '相関図が見つかりません',
            'all_items'          => 'すべての相関図',
        ],
        'public'       => false,
        'show_ui'      => true,
        'menu_icon'    => 'dashicons-heart',
        'supports'     => ['title'],
        'show_in_rest' => true,
    ]);

    // 投票（poll）
    register_post_type('poll', [
        'label'        => '投票',
        'labels'       => [
            'name'               => '投票',
            'singular_name'      => '投票',
            'add_new'            => '新規追加',
            'add_new_item'       => '投票を追加',
            'edit_item'          => '投票を編集',
            'new_item'           => '新しい投票',
            'view_item'          => '投票を表示',
            'search_items'       => '投票を検索',
            'not_found'          => '投票が見つかりません',
            'not_found_in_trash' => 'ゴミ箱に投票はありません',
            'all_items'          => 'すべての投票',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-chart-bar',
        'supports'     => ['title'],
        'rewrite'      => ['slug' => 'vote'],
        'show_in_rest' => true,
    ]);

    // YouTube動画（youtube_video）
    register_post_type('youtube_video', [
        'label'        => 'YouTube動画',
        'labels'       => [
            'name'               => 'YouTube動画',
            'singular_name'      => 'YouTube動画',
            'add_new'            => '新規追加',
            'add_new_item'       => 'YouTube動画を追加',
            'edit_item'          => 'YouTube動画を編集',
            'new_item'           => '新しいYouTube動画',
            'search_items'       => 'YouTube動画を検索',
            'not_found'          => 'YouTube動画が見つかりません',
            'all_items'          => 'すべてのYouTube動画',
        ],
        'public'       => false,
        'show_ui'      => true,
        'menu_icon'    => 'dashicons-youtube',
        'supports'     => ['title'],
        'show_in_rest' => true,
    ]);

    // カップル（couple）
    register_post_type('couple', [
        'label'        => 'カップル',
        'labels'       => [
            'name'               => 'カップル',
            'singular_name'      => 'カップル',
            'add_new'            => '新規追加',
            'add_new_item'       => 'カップルを追加',
            'edit_item'          => 'カップルを編集',
            'new_item'           => '新しいカップル',
            'view_item'          => 'カップルを表示',
            'search_items'       => 'カップルを検索',
            'not_found'          => 'カップルが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にカップルはありません',
            'all_items'          => 'すべてのカップル',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-heart',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'couple'],
        'show_in_rest' => true,
    ]);
}
