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

// ACF PRO がインストールされている場合のみフィールド登録
if (function_exists('acf_add_local_field_group') || class_exists('ACF')) {
    add_action('acf/init', 'koi_ria_register_acf_fields');
}

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
                'instructions' => 'UCで始まるチャンネルID（例: UCxxxxxxxxxxxxxxxxxxxxxxx）。YouTubeチャンネルページのURLまたは「チャンネルについて」から取得できます。設定するとYouTube動画を自動取得します。',
            ],
            [
                'key'   => 'field_show_affiliate_url',
                'label' => 'アフィリエイトURL',
                'name'  => 'affiliate_url',
                'type'  => 'url',
                'instructions' => '番組の公式配信ページへのアフィリエイトリンク（A8.net、もしもアフィリエイト等）',
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
                'instructions' => 'サイト上に表示される名前。空欄の場合はタイトルが使用されます（例: あいり / たくや）',
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
                'instructions' => '@なしのユーザー名（例: airi_official）。設定するとプロフィールページにInstagramリンクが表示され、API設定済みの場合はフォロワー数も自動取得されます。',
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
                'instructions' => '出演者の個人YouTubeチャンネルURL（例: https://youtube.com/@username）。プロフィールページにリンクが表示されます。',
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
                'label'         => 'Instagramフォロワー数',
                'name'          => 'followers_count',
                'type'          => 'number',
                'default_value' => 0,
                'instructions'  => 'Instagram Graph API設定済みの場合は自動更新されます。手動入力も可能です。',
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
    // 番組: VOD配信情報（showに追加フィールド）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_show_vod',
        'title'    => 'VOD配信情報',
        'fields'   => [
            [
                'key'     => 'field_show_available_vods',
                'label'   => '配信VOD',
                'name'    => 'available_vods',
                'type'    => 'checkbox',
                'choices' => [
                    'ABEMA'        => 'ABEMA',
                    'Netflix'      => 'Netflix',
                    'Prime Video'  => 'Prime Video',
                    'U-NEXT'       => 'U-NEXT',
                    'Hulu'         => 'Hulu',
                    'Disney+'      => 'Disney+',
                    'TVer'         => 'TVer',
                    'Paravi'       => 'Paravi',
                ],
                'layout' => 'horizontal',
            ],
            [
                'key'        => 'field_show_vod_links',
                'label'      => 'VOD別URL',
                'name'       => 'vod_links',
                'type'       => 'repeater',
                'layout'     => 'table',
                'sub_fields' => [
                    [
                        'key'     => 'field_vod_link_name',
                        'label'   => 'VOD名',
                        'name'    => 'vod_name',
                        'type'    => 'select',
                        'choices' => [
                            'ABEMA'        => 'ABEMA',
                            'Netflix'      => 'Netflix',
                            'Prime Video'  => 'Prime Video',
                            'U-NEXT'       => 'U-NEXT',
                            'Hulu'         => 'Hulu',
                            'Disney+'      => 'Disney+',
                            'TVer'         => 'TVer',
                            'Paravi'       => 'Paravi',
                        ],
                    ],
                    [
                        'key'   => 'field_vod_link_url',
                        'label' => 'URL',
                        'name'  => 'url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_vod_link_is_free',
                        'label'         => '無料あり',
                        'name'          => 'is_free',
                        'type'          => 'true_false',
                        'default_value' => 0,
                        'ui'            => 1,
                    ],
                ],
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'show'],
            ],
        ],
        'menu_order' => 1,
        'position'   => 'normal',
        'style'      => 'default',
    ]);

    // -------------------------------------------------------
    // カップル情報（couple）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_couple_info',
        'title'    => 'カップル情報',
        'fields'   => [
            [
                'key'       => 'field_couple_member_a',
                'label'     => 'メンバーA',
                'name'      => 'member_a',
                'type'      => 'relationship',
                'post_type' => ['cast'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'       => 'field_couple_member_b',
                'label'     => 'メンバーB',
                'name'      => 'member_b',
                'type'      => 'relationship',
                'post_type' => ['cast'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'       => 'field_couple_show',
                'label'     => '番組',
                'name'      => 'show',
                'type'      => 'relationship',
                'post_type' => ['show'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'       => 'field_couple_season',
                'label'     => 'シーズン',
                'name'      => 'season',
                'type'      => 'relationship',
                'post_type' => ['season'],
                'max'       => 1,
                'required'  => 1,
                'return_format' => 'id',
            ],
            [
                'key'   => 'field_couple_name',
                'label' => 'カップル名',
                'name'  => 'couple_name',
                'type'  => 'text',
                'instructions' => '例: れんゆな / さとまる',
            ],
            [
                'key'     => 'field_couple_status',
                'label'   => '現在のステータス',
                'name'    => 'couple_status',
                'type'    => 'select',
                'choices' => [
                    '交際中' => '交際中',
                    '破局'   => '破局',
                    '結婚'   => '結婚',
                    '不明'   => '不明',
                ],
                'default_value' => '不明',
            ],
            [
                'key'        => 'field_couple_timeline',
                'label'      => 'タイムライン',
                'name'       => 'timeline',
                'type'       => 'repeater',
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'            => 'field_couple_event_date',
                        'label'          => '日付',
                        'name'           => 'event_date',
                        'type'           => 'date_picker',
                        'display_format' => 'Y.m',
                        'return_format'  => 'Y-m-d',
                    ],
                    [
                        'key'     => 'field_couple_event_type',
                        'label'   => 'イベント種別',
                        'name'    => 'event_type',
                        'type'    => 'select',
                        'choices' => [
                            '成立'     => '成立',
                            '交際報告' => '交際報告',
                            '破局報告' => '破局報告',
                            '結婚報告' => '結婚報告',
                            '目撃情報' => '目撃情報',
                            'SNS投稿'  => 'SNS投稿',
                        ],
                    ],
                    [
                        'key'   => 'field_couple_event_note',
                        'label' => 'メモ',
                        'name'  => 'event_note',
                        'type'  => 'text',
                        'instructions' => '例: Instagramで匂わせ投稿',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'couple'],
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

    // （注: カップル情報は group_couple_info で定義済み）

    // -------------------------------------------------------
    // YouTube動画（youtube_video）
    // -------------------------------------------------------
    acf_add_local_field_group([
        'key'      => 'group_youtube_video_info',
        'title'    => 'YouTube動画情報',
        'fields'   => [
            [
                'key'          => 'field_yt_video_id',
                'label'        => '動画ID',
                'name'         => 'video_id',
                'type'         => 'text',
                'instructions' => '⚠️ YouTube動画の11文字のID（例: <code>dQw4w9WgXcQ</code>）。チャンネル名ではありません！<br>動画URLが <code>https://www.youtube.com/watch?v=<strong>dQw4w9WgXcQ</strong></code> の場合、太字部分が動画IDです。',
                'maxlength'    => 11,
                'placeholder'  => '例: dQw4w9WgXcQ',
            ],
            [
                'key'   => 'field_yt_channel_name',
                'label' => 'チャンネル名',
                'name'  => 'channel_name',
                'type'  => 'text',
                'instructions' => '動画のチャンネル名（任意）。例: ABEMA 今日、好きになりました。【公式】',
                'placeholder'  => '例: ABEMA 今日、好きになりました。【公式】',
            ],
            [
                'key'     => 'field_yt_platform',
                'label'   => 'プラットフォーム',
                'name'    => 'platform',
                'type'    => 'select',
                'choices' => [
                    ''            => '-- 選択 --',
                    'ABEMA'       => 'ABEMA',
                    'Netflix'     => 'Netflix',
                    'Prime Video' => 'Prime Video',
                    'その他'      => 'その他',
                ],
            ],
            [
                'key'          => 'field_yt_thumbnail_url',
                'label'        => 'サムネイルURL',
                'name'         => 'thumbnail_url',
                'type'         => 'url',
                'instructions' => '空欄の場合、動画IDからYouTubeのサムネイルを自動使用します。手動設定は不要です。',
                'placeholder'  => '空欄で自動取得',
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
                'label'         => 'トップにピン留め',
                'name'          => 'is_pinned',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
                'instructions'  => 'ONにするとトップページのスライダー先頭に固定表示されます',
            ],
            [
                'key'           => 'field_yt_is_auto',
                'label'         => '自動取得',
                'name'          => 'is_auto',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
                'instructions'  => 'YouTube APIで自動取得された動画（手動登録の場合はOFFのまま）',
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
