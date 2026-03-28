<?php
/**
 * ACF フィールドグループ登録
 *
 * ACF PRO の acf_add_local_field_group() でフィールドを定義する。
 * ACF PRO がインストールされていない場合はスキップ。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('acf/init', 'koi_ria_register_acf_fields');

function koi_ria_register_acf_fields(): void {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    // -------------------------------------------------------
    // 番組情報（show）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_show_info',
        'title'    => '番組情報',
        'fields'   => [
            [
                'key'   => 'field_show_short_name',
                'label' => '略称',
                'name'  => 'short_name',
                'type'  => 'text',
                'instructions' => '例: 今日好き',
            ],
            [
                'key'     => 'field_show_platform',
                'label'   => 'プラットフォーム',
                'name'    => 'platform',
                'type'    => 'select',
                'choices' => [
                    'ABEMA'        => 'ABEMA',
                    'Netflix'      => 'Netflix',
                    'Prime Video'  => 'Prime Video',
                    'U-NEXT'       => 'U-NEXT',
                    'Disney+'      => 'Disney+',
                    'Paravi'       => 'Paravi',
                    'その他'       => 'その他',
                ],
                'default_value' => 'ABEMA',
            ],
            [
                'key'   => 'field_show_platform_color',
                'label' => 'プラットフォームカラー',
                'name'  => 'platform_color',
                'type'  => 'color_picker',
                'default_value' => '#00B900',
            ],
            [
                'key'   => 'field_show_emoji',
                'label' => '絵文字アイコン',
                'name'  => 'emoji',
                'type'  => 'text',
                'instructions' => '例: 💗',
            ],
            [
                'key'   => 'field_show_youtube_channel_id',
                'label' => 'YouTubeチャンネルID',
                'name'  => 'youtube_channel_id',
                'type'  => 'text',
                'instructions' => 'YouTube API自動取得用',
            ],
            [
                'key'   => 'field_show_affiliate_url',
                'label' => 'アフィリエイトURL',
                'name'  => 'affiliate_url',
                'type'  => 'url',
            ],
            [
                'key'     => 'field_show_status',
                'label'   => '番組ステータス',
                'name'    => 'show_status',
                'type'    => 'select',
                'choices' => [
                    '放送中' => '放送中',
                    '配信中' => '配信中',
                    '過去作' => '過去作',
                    '制作中' => '制作中',
                ],
            ],
            [
                'key'   => 'field_show_genre',
                'label' => 'ジャンル',
                'name'  => 'genre',
                'type'  => 'text',
                'instructions' => '例: 青春恋愛',
            ],
            [
                'key'   => 'field_show_target',
                'label' => 'ターゲット層',
                'name'  => 'target',
                'type'  => 'text',
                'instructions' => '例: 中高生',
            ],
            [
                'key'           => 'field_show_priority',
                'label'         => '優先度',
                'name'          => 'priority',
                'type'          => 'number',
                'default_value' => 10,
                'instructions'  => '小さい値ほど上位に表示',
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'show'],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
    ]);

    // -------------------------------------------------------
    // シーズン情報（season）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_season_info',
        'title'    => 'シーズン情報',
        'fields'   => [
            [
                'key'       => 'field_season_show',
                'label'     => '番組',
                'name'      => 'show',
                'type'      => 'relationship',
                'post_type' => ['show'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'   => 'field_season_name',
                'label' => 'シーズン名',
                'name'  => 'season_name',
                'type'  => 'text',
                'instructions' => '例: テグ編 / Season 2',
            ],
            [
                'key'   => 'field_season_year',
                'label' => '年',
                'name'  => 'year',
                'type'  => 'text',
                'instructions' => '例: 2026',
            ],
            [
                'key'     => 'field_season_badge',
                'label'   => 'バッジ',
                'name'    => 'badge',
                'type'    => 'select',
                'choices' => [
                    ''       => 'なし',
                    'ON AIR' => 'ON AIR',
                    'NEW'    => 'NEW',
                    '配信中'   => '配信中',
                    'COMING' => 'COMING',
                ],
                'allow_null' => 1,
            ],
            [
                'key'           => 'field_season_order',
                'label'         => '表示順',
                'name'          => 'order',
                'type'          => 'number',
                'default_value' => 1,
                'instructions'  => '新しいシーズンほど小さい数字',
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'season'],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
    ]);

    // -------------------------------------------------------
    // 出演者情報（cast）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_cast_info',
        'title'    => '出演者情報',
        'fields'   => [
            [
                'key'   => 'field_cast_display_name',
                'label' => '名前（表示用）',
                'name'  => 'display_name',
                'type'  => 'text',
                'instructions' => 'ひらがな等',
            ],
            [
                'key'       => 'field_cast_show',
                'label'     => '番組',
                'name'      => 'show',
                'type'      => 'relationship',
                'post_type' => ['show'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'       => 'field_cast_season',
                'label'     => 'シーズン',
                'name'      => 'season',
                'type'      => 'relationship',
                'post_type' => ['season'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'   => 'field_cast_ig_username',
                'label' => 'Instagram ID',
                'name'  => 'ig_username',
                'type'  => 'text',
                'instructions' => '@なし。例: airi_official',
            ],
            [
                'key'   => 'field_cast_tiktok_username',
                'label' => 'TikTok ID',
                'name'  => 'tiktok_username',
                'type'  => 'text',
                'instructions' => '@なし。空欄可',
            ],
            [
                'key'   => 'field_cast_x_username',
                'label' => 'X(Twitter) ID',
                'name'  => 'x_username',
                'type'  => 'text',
                'instructions' => '@なし。空欄可',
            ],
            [
                'key'   => 'field_cast_youtube_url',
                'label' => 'YouTube チャンネル',
                'name'  => 'youtube_url',
                'type'  => 'url',
                'instructions' => '個人チャンネルがある場合',
            ],
            [
                'key'          => 'field_cast_profile_image',
                'label'        => 'プロフィール画像',
                'name'         => 'profile_image',
                'type'         => 'image',
                'return_format' => 'array',
                'preview_size' => 'cast-avatar',
            ],
            [
                'key'           => 'field_cast_followers_count',
                'label'         => 'フォロワー数',
                'name'          => 'followers_count',
                'type'          => 'number',
                'default_value' => 0,
                'instructions'  => 'IG APIで自動更新',
            ],
            [
                'key'           => 'field_cast_tiktok_followers',
                'label'         => 'TikTokフォロワー数',
                'name'          => 'tiktok_followers',
                'type'          => 'number',
                'default_value' => 0,
            ],
            [
                'key'     => 'field_cast_role',
                'label'   => '役割',
                'name'    => 'role',
                'type'    => 'select',
                'choices' => [
                    '女子メンバー'   => '女子メンバー',
                    '男子メンバー'   => '男子メンバー',
                    '女性参加者'     => '女性参加者',
                    '男性参加者'     => '男性参加者',
                    '参加者'         => '参加者',
                    'バチェラー'     => 'バチェラー',
                    'バチェロレッテ' => 'バチェロレッテ',
                ],
            ],
            [
                'key'     => 'field_cast_gender',
                'label'   => '性別',
                'name'    => 'gender',
                'type'    => 'select',
                'choices' => [
                    'f'     => '女性',
                    'm'     => '男性',
                    'other' => 'その他',
                ],
            ],
            [
                'key'   => 'field_cast_age',
                'label' => '年齢',
                'name'  => 'age',
                'type'  => 'number',
                'instructions' => '空欄可',
            ],
            [
                'key'   => 'field_cast_from_area',
                'label' => '出身',
                'name'  => 'from_area',
                'type'  => 'text',
                'instructions' => '例: 東京都',
            ],
            [
                'key'     => 'field_cast_status',
                'label'   => 'ステータス',
                'name'    => 'cast_status',
                'type'    => 'select',
                'choices' => [
                    '出演中'   => '出演中',
                    '卒業'     => '卒業',
                    'リタイア' => 'リタイア',
                ],
                'default_value' => '出演中',
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'cast'],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
    ]);

    // -------------------------------------------------------
    // 相関図（relation）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_relation_info',
        'title'    => '相関図データ',
        'fields'   => [
            [
                'key'       => 'field_relation_season',
                'label'     => 'シーズン',
                'name'      => 'season',
                'type'      => 'relationship',
                'post_type' => ['season'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'       => 'field_relation_from_cast',
                'label'     => 'From（誰から）',
                'name'      => 'from_cast',
                'type'      => 'relationship',
                'post_type' => ['cast'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'       => 'field_relation_to_cast',
                'label'     => 'To（誰へ）',
                'name'      => 'to_cast',
                'type'      => 'relationship',
                'post_type' => ['cast'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'     => 'field_relation_type',
                'label'   => '関係タイプ',
                'name'    => 'relation_type',
                'type'    => 'select',
                'choices' => [
                    'love'     => 'love',
                    'rival'    => 'rival',
                    'couple'   => 'couple',
                    'interest' => 'interest',
                ],
            ],
            [
                'key'   => 'field_relation_label',
                'label' => 'ラベル',
                'name'  => 'relation_label',
                'type'  => 'text',
                'instructions' => '例: 両思い / 片思い / 三角関係 / 成立！',
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'relation'],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
    ]);

    // -------------------------------------------------------
    // 投票（poll）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_poll_info',
        'title'    => '投票設定',
        'fields'   => [
            [
                'key'      => 'field_poll_question',
                'label'    => '質問文',
                'name'     => 'question',
                'type'     => 'text',
                'required' => 1,
            ],
            [
                'key'       => 'field_poll_show',
                'label'     => '番組',
                'name'      => 'show',
                'type'      => 'relationship',
                'post_type' => ['show'],
                'max'       => 1,
                'return_format' => 'id',
            ],
            [
                'key'        => 'field_poll_options',
                'label'      => '選択肢',
                'name'       => 'options',
                'type'       => 'repeater',
                'layout'     => 'table',
                'sub_fields' => [
                    [
                        'key'   => 'field_poll_option_label',
                        'label' => 'ラベル',
                        'name'  => 'option_label',
                        'type'  => 'text',
                    ],
                    [
                        'key'           => 'field_poll_option_votes',
                        'label'         => '投票数',
                        'name'          => 'option_votes',
                        'type'          => 'number',
                        'default_value' => 0,
                    ],
                ],
            ],
            [
                'key'           => 'field_poll_is_active',
                'label'         => '受付中',
                'name'          => 'is_active',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'   => 'field_poll_end_date',
                'label' => '終了日',
                'name'  => 'end_date',
                'type'  => 'date_picker',
                'display_format' => 'Y/m/d',
                'return_format'  => 'Y-m-d',
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'poll'],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
    ]);

    // -------------------------------------------------------
    // YouTube動画（youtube_video）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_youtube_video_info',
        'title'    => 'YouTube動画情報',
        'fields'   => [
            [
                'key'   => 'field_yt_video_id',
                'label' => '動画ID',
                'name'  => 'video_id',
                'type'  => 'text',
                'instructions' => 'YouTube動画ID',
            ],
            [
                'key'   => 'field_yt_channel_name',
                'label' => 'チャンネル名',
                'name'  => 'channel_name',
                'type'  => 'text',
            ],
            [
                'key'     => 'field_yt_platform',
                'label'   => 'プラットフォーム',
                'name'    => 'platform',
                'type'    => 'select',
                'choices' => [
                    'ABEMA'   => 'ABEMA',
                    'Netflix' => 'Netflix',
                    'その他'  => 'その他',
                ],
            ],
            [
                'key'   => 'field_yt_thumbnail_url',
                'label' => 'サムネイルURL',
                'name'  => 'thumbnail_url',
                'type'  => 'url',
                'instructions' => 'YouTube APIから自動取得',
            ],
            [
                'key'            => 'field_yt_published_at',
                'label'          => '公開日',
                'name'           => 'published_at',
                'type'           => 'date_time_picker',
                'display_format' => 'Y/m/d H:i',
                'return_format'  => 'Y-m-d H:i:s',
            ],
            [
                'key'           => 'field_yt_is_pinned',
                'label'         => 'ピン留め',
                'name'          => 'is_pinned',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
                'instructions'  => 'ヒーロー固定表示用',
            ],
            [
                'key'           => 'field_yt_is_auto',
                'label'         => '自動取得',
                'name'          => 'is_auto',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
                'instructions'  => 'API自動取得かどうか',
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'youtube_video'],
            ],
        ],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
    ]);
}
