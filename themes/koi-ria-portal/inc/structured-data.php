<?php
/**
 * Schema.org 構造化データ出力（SEO / GEO 最適化版）
 *
 * 対応スキーマ:
 * - Organization (サイト全体: エンティティ定義)
 * - WebSite (トップページ: サイト名 + SearchAction)
 * - CollectionPage + ItemList (アーカイブページ)
 * - TVSeries (番組詳細: シーズン・キャスト・放送情報)
 * - Person (出演者詳細: SNS・出演歴・フォロワー)
 * - NewsArticle (記事: 著者・画像・Speakable)
 * - VideoObject (YouTube動画)
 * - FAQPage (番組・出演者のFAQ)
 * - BroadcastEvent (放送スケジュール)
 * - BreadcrumbList (全ページ)
 * - SiteNavigationElement (グローバルナビ)
 *
 * GEO最適化:
 * - エンティティ間の @id リンクでナレッジグラフ構築
 * - Speakable 対応で音声検索 / AI抽出に最適化
 * - FAQ構造化データでAI要約に直接回答を提供
 * - ItemList で一覧ページの構造を明示
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

add_action('wp_head', 'koi_ria_structured_data', 5);
add_action('wp_head', 'koi_ria_breadcrumb_schema', 5);
add_action('wp_head', 'koi_ria_site_navigation_schema', 5);

/**
 * サイト全体の Organization スキーマ（全ページ共通）
 */
add_action('wp_head', function (): void {
    // Organization は全ページで1回だけ出力
    static $output = false;
    if ($output) return;
    $output = true;

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        '@id'      => home_url('/#organization'),
        'name'     => get_bloginfo('name'),
        'legalName' => '株式会社仁頼',
        'alternateName' => ['Jinrai Co., Ltd.', '恋リアポータル'],
        'url'      => home_url('/'),
        'logo'     => [
            '@type' => 'ImageObject',
            'url'   => koi_ria_get_site_logo_url(),
        ],
        'description' => get_bloginfo('description'),
        'foundingDate' => '2022-09',
        'address'      => [
            '@type'           => 'PostalAddress',
            'addressCountry'  => 'JP',
            'postalCode'      => '221-0001',
            'addressRegion'   => '神奈川県',
            'addressLocality' => '横浜市神奈川区',
            'streetAddress'   => '西寺尾4丁目6番6-3号',
        ],
        'parentOrganization' => [
            '@type'    => 'Organization',
            'name'     => '株式会社仁頼',
            'url'      => 'https://jinrai.co.jp',
            'legalName' => '株式会社仁頼',
        ],
    ];

    $social_urls = koi_ria_get_site_social_urls();
    if ($social_urls) {
        $schema['sameAs'] = $social_urls;
    }

    koi_ria_output_schema($schema);
}, 4);

/**
 * ページ種別に応じた構造化データ出力
 */
function koi_ria_structured_data(): void {
    if (is_front_page()) {
        koi_ria_website_schema();
        koi_ria_front_page_itemlist();
    }

    if (is_singular('cast')) {
        koi_ria_cast_schema();
    } elseif (is_singular('show')) {
        koi_ria_show_schema();
    } elseif (is_singular('post')) {
        koi_ria_article_schema();
    }

    // アーカイブページ: ItemList
    if (is_post_type_archive('show')) {
        koi_ria_archive_itemlist('show', '番組一覧');
    } elseif (is_post_type_archive('cast')) {
        koi_ria_archive_itemlist('cast', '出演者データベース');
    }
}

/* =========================================================================
 * WebSite スキーマ（トップページ）
 * ========================================================================= */
function koi_ria_website_schema(): void {
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        '@id'      => home_url('/#website'),
        'name'     => get_bloginfo('name'),
        'alternateName' => '恋リアポータル',
        'url'      => home_url('/'),
        'description' => get_bloginfo('description'),
        'inLanguage'   => 'ja',
        'publisher'    => ['@id' => home_url('/#organization')],
        'potentialAction' => [
            [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => home_url('/?s={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ];

    koi_ria_output_schema($schema);
}

/* =========================================================================
 * トップページ ItemList（注目番組・人気出演者）
 * ========================================================================= */
function koi_ria_front_page_itemlist(): void {
    // 注目番組リスト
    $shows = get_posts([
        'post_type'      => 'show',
        'posts_per_page' => 10,
        'meta_key'       => 'priority',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);

    if ($shows) {
        $items = [];
        foreach ($shows as $i => $show) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'url'      => get_permalink($show),
                'name'     => $show->post_title,
            ];
        }

        koi_ria_output_schema([
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => '注目の恋愛リアリティ番組',
            'description'     => '今注目の恋愛リアリティ番組ランキング',
            'numberOfItems'   => count($items),
            'itemListElement' => $items,
        ]);
    }
}

/* =========================================================================
 * アーカイブページ ItemList + CollectionPage
 * ========================================================================= */
function koi_ria_archive_itemlist(string $post_type, string $name): void {
    global $wp_query;

    $items = [];
    if (have_posts()) {
        $position = 1;
        foreach ($wp_query->posts as $post) {
            $item = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'url'      => get_permalink($post),
                'name'     => $post->post_title,
            ];
            $items[] = $item;
        }
    }

    if ($items) {
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'CollectionPage',
            'name'            => $name,
            'url'             => koi_ria_current_archive_url(),
            'description'     => get_bloginfo('name') . 'の' . $name,
            'isPartOf'        => ['@id' => home_url('/#website')],
            'mainEntity'      => [
                '@type'           => 'ItemList',
                'numberOfItems'   => count($items),
                'itemListElement' => $items,
            ],
        ];
        koi_ria_output_schema($schema);
    }
}

/* =========================================================================
 * TVSeries スキーマ（番組詳細）
 * ========================================================================= */
function koi_ria_show_schema(): void {
    $show_id  = get_the_ID();
    $platform = get_field('platform', $show_id) ?: '';
    $genre    = get_field('genre', $show_id) ?: '';
    $status   = get_field('show_status', $show_id) ?: '';
    $target   = get_field('target', $show_id) ?: '';
    $show_url = get_permalink();

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'TVSeries',
        '@id'         => $show_url . '#tvseries',
        'name'        => get_the_title(),
        'url'         => $show_url,
        'description' => koi_ria_get_clean_excerpt(120),
        'inLanguage'  => 'ja',
    ];

    if ($genre) {
        $schema['genre'] = $genre;
    }

    if ($target) {
        $schema['audience'] = [
            '@type'        => 'Audience',
            'audienceType' => $target,
        ];
    }

    if ($platform) {
        $schema['productionCompany'] = [
            '@type' => 'Organization',
            'name'  => $platform,
        ];
    }

    // 放送ステータス
    if ($status) {
        $status_map = [
            'on_air'   => 'http://schema.org/InProgress',
            'ended'    => 'http://schema.org/Completed',
            'upcoming' => 'http://schema.org/PreProduction',
        ];
        if (isset($status_map[$status])) {
            $schema['productionStatus'] = $status_map[$status];
        }
    }

    if (has_post_thumbnail()) {
        $thumb_id  = get_post_thumbnail_id();
        $thumb_url = get_the_post_thumbnail_url($show_id, 'large');
        $meta      = wp_get_attachment_metadata($thumb_id);
        $schema['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $thumb_url,
            'width'  => $meta['width'] ?? 1200,
            'height' => $meta['height'] ?? 630,
        ];
    }

    // シーズン情報
    $seasons = get_posts([
        'post_type'      => 'season',
        'posts_per_page' => -1,
        'meta_query'     => [['key' => 'show', 'value' => $show_id]],
        'meta_key'       => 'order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);

    if ($seasons) {
        $schema['containsSeason'] = [];
        foreach ($seasons as $season) {
            $s_name = get_field('season_name', $season->ID) ?: $season->post_title;
            $s_year = get_field('year', $season->ID) ?: '';
            $season_data = [
                '@type'      => 'TVSeason',
                '@id'        => $show_url . '#season-' . $season->ID,
                'name'       => $s_name,
                'partOfSeries' => ['@id' => $show_url . '#tvseries'],
            ];
            if ($s_year) {
                $season_data['datePublished'] = $s_year;
            }

            // シーズンの出演者
            $season_casts = get_posts([
                'post_type'      => 'cast',
                'posts_per_page' => -1,
                'meta_query'     => [['key' => 'season', 'value' => $season->ID]],
            ]);
            if ($season_casts) {
                $actors = [];
                foreach ($season_casts as $cast) {
                    $actors[] = [
                        '@type' => 'Person',
                        'name'  => get_field('display_name', $cast->ID) ?: $cast->post_title,
                        'url'   => get_permalink($cast),
                    ];
                }
                $season_data['actor'] = $actors;
            }

            $schema['containsSeason'][] = $season_data;
        }
        $schema['numberOfSeasons'] = count($seasons);
    }

    // 関連YouTube動画を VideoObject として追加
    $videos = get_posts([
        'post_type'      => 'youtube_video',
        'posts_per_page' => 5,
        'meta_query'     => [
            ['key' => 'platform', 'value' => $platform],
        ],
    ]);

    if ($videos) {
        $video_objects = [];
        foreach ($videos as $video) {
            $video_id    = get_field('video_id', $video->ID);
            $published   = get_field('published_at', $video->ID);
            $thumb       = get_field('thumbnail_url', $video->ID);
            if (!$video_id) continue;

            $vo = [
                '@type'        => 'VideoObject',
                'name'         => $video->post_title,
                'description'  => $video->post_title,
                'thumbnailUrl' => $thumb ?: "https://i.ytimg.com/vi/{$video_id}/hqdefault.jpg",
                'contentUrl'   => "https://www.youtube.com/watch?v={$video_id}",
                'embedUrl'     => "https://www.youtube.com/embed/{$video_id}",
            ];
            if ($published) {
                $vo['uploadDate'] = date('c', strtotime($published));
            }
            $video_objects[] = $vo;
        }
        if ($video_objects) {
            $schema['video'] = $video_objects;
        }
    }

    koi_ria_output_schema($schema);

    // FAQ: 番組に関するよくある質問（GEO最適化）
    koi_ria_show_faq_schema($show_id, $schema);
}

/**
 * 番組FAQ構造化データ（GEO: AI検索用の直接回答）
 */
function koi_ria_show_faq_schema(int $show_id, array $show_data): void {
    $show_name = get_the_title();
    $platform  = get_field('platform', $show_id) ?: '';
    $genre     = get_field('genre', $show_id) ?: '';
    $status    = get_field('show_status', $show_id) ?: '';
    $target    = get_field('target', $show_id) ?: '';

    $status_labels = [
        'on_air'   => '現在放送中',
        'ended'    => '放送終了',
        'upcoming' => '放送予定',
    ];

    $faq_items = [];

    // Q1: 番組概要
    $desc = koi_ria_get_clean_excerpt(200);
    if ($desc) {
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "「{$show_name}」はどんな番組ですか？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $desc,
            ],
        ];
    }

    // Q2: 配信プラットフォーム
    if ($platform) {
        $answer = "「{$show_name}」は{$platform}で";
        $answer .= isset($status_labels[$status]) ? $status_labels[$status] . 'です。' : '配信されています。';
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "「{$show_name}」はどこで見れますか？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $answer,
            ],
        ];
    }

    // Q3: シーズン数
    $num_seasons = $show_data['numberOfSeasons'] ?? 0;
    if ($num_seasons > 0) {
        $season_names = [];
        foreach (($show_data['containsSeason'] ?? []) as $s) {
            $season_names[] = $s['name'];
        }
        $answer = "「{$show_name}」は現在{$num_seasons}シーズンあります。";
        if ($season_names) {
            $answer .= '（' . implode('、', array_slice($season_names, 0, 5)) . '）';
        }
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "「{$show_name}」は何シーズンありますか？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $answer,
            ],
        ];
    }

    // Q4: ジャンル・ターゲット
    if ($genre || $target) {
        $answer = "「{$show_name}」は";
        if ($genre) $answer .= "{$genre}ジャンルの";
        $answer .= '恋愛リアリティ番組です。';
        if ($target) $answer .= "主な視聴者層は{$target}です。";
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "「{$show_name}」のジャンルは何ですか？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $answer,
            ],
        ];
    }

    if ($faq_items) {
        koi_ria_output_schema([
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faq_items,
        ]);
    }
}

/* =========================================================================
 * Person スキーマ（出演者詳細）
 * ========================================================================= */
function koi_ria_cast_schema(): void {
    $cast_id         = get_the_ID();
    $display_name    = get_field('display_name', $cast_id) ?: get_the_title();
    $ig_username     = get_field('ig_username', $cast_id) ?: '';
    $tiktok_username = get_field('tiktok_username', $cast_id) ?: '';
    $x_username      = get_field('x_username', $cast_id) ?: '';
    $from_area       = get_field('from_area', $cast_id) ?: '';
    $age             = get_field('age', $cast_id) ?: '';
    $birthday        = get_field('birthday', $cast_id) ?: '';
    $gender          = get_field('gender', $cast_id) ?: '';
    $blood_type      = get_field('blood_type', $cast_id) ?: '';
    $hobby           = get_field('hobby', $cast_id) ?: '';
    $profile_image   = get_field('profile_image', $cast_id);
    $ig_cache        = get_post_meta($cast_id, 'ig_profile_cache', true);
    $followers_count = get_field('followers_count', $cast_id) ?: 0;
    $cast_url        = get_permalink();

    $same_as = [];
    if ($ig_username) {
        $same_as[] = "https://instagram.com/{$ig_username}";
    }
    if ($tiktok_username) {
        $same_as[] = "https://tiktok.com/@{$tiktok_username}";
    }
    if ($x_username) {
        $same_as[] = "https://x.com/{$x_username}";
    }

    // 所属番組
    $show_id = get_field('show', $cast_id);
    $show_name = '';
    $show_url  = '';
    if ($show_id) {
        $show_post = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
        if ($show_post) {
            $show_name = $show_post->post_title;
            $show_url  = get_permalink($show_post);
        }
    }

    // シーズン情報
    $season_id = get_field('season', $cast_id);
    $season_name = '';
    if ($season_id) {
        $season_post = is_array($season_id) ? get_post($season_id[0]) : get_post($season_id);
        $season_name = $season_post ? (get_field('season_name', $season_post->ID) ?: $season_post->post_title) : '';
    }

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Person',
        '@id'         => $cast_url . '#person',
        'name'        => $display_name,
        'url'         => $cast_url,
        'description' => koi_ria_get_clean_excerpt(160),
    ];

    if ($same_as) {
        $schema['sameAs'] = $same_as;
    }

    if ($gender) {
        $schema['gender'] = ($gender === 'f') ? 'Female' : 'Male';
    }

    if ($birthday) {
        $schema['birthDate'] = $birthday;
    }

    if ($from_area) {
        $schema['homeLocation'] = [
            '@type' => 'Place',
            'name'  => $from_area,
        ];
        $schema['nationality'] = [
            '@type' => 'Country',
            'name'  => '日本',
        ];
    }

    // 画像
    $image_url = '';
    if ($profile_image && isset($profile_image['url'])) {
        $image_url = $profile_image['url'];
    } elseif ($ig_cache) {
        $image_url = $ig_cache;
    }
    if ($image_url) {
        $schema['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $image_url,
            'width'  => 400,
            'height' => 400,
        ];
    }

    // 出演歴（performerIn）
    if ($show_name) {
        $performer_in = [
            '@type' => 'TVSeries',
            'name'  => $show_name,
        ];
        if ($show_url) {
            $performer_in['@id'] = $show_url . '#tvseries';
            $performer_in['url'] = $show_url;
        }
        $schema['performerIn'] = $performer_in;

        // memberOf はシーズン単位
        if ($season_name) {
            $schema['memberOf'] = [
                '@type' => 'TVSeason',
                'name'  => $season_name,
                'partOfSeries' => [
                    '@type' => 'TVSeries',
                    'name'  => $show_name,
                ],
            ];
        }
    }

    // SNS フォロワー数（InteractionCounter）
    if ($followers_count > 0 && $ig_username) {
        $schema['interactionStatistic'] = [
            '@type'                => 'InteractionCounter',
            'interactionType'      => 'https://schema.org/FollowAction',
            'userInteractionCount' => intval($followers_count),
            'interactionService'   => [
                '@type' => 'WebSite',
                'name'  => 'Instagram',
                'url'   => 'https://instagram.com',
            ],
        ];
    }

    koi_ria_output_schema($schema);

    // 出演者FAQ（GEO最適化）
    koi_ria_cast_faq_schema($cast_id, $display_name, [
        'show_name'  => $show_name,
        'season'     => $season_name,
        'age'        => $age,
        'from_area'  => $from_area,
        'ig'         => $ig_username,
        'tiktok'     => $tiktok_username,
        'followers'  => $followers_count,
        'blood_type' => $blood_type,
        'hobby'      => $hobby,
    ]);
}

/**
 * 出演者FAQ構造化データ（GEO: AI検索最適化）
 */
function koi_ria_cast_faq_schema(int $cast_id, string $name, array $info): void {
    $faq_items = [];

    // Q1: 基本プロフィール
    $profile_parts = [];
    if ($info['age']) $profile_parts[] = "年齢は{$info['age']}歳";
    if ($info['from_area']) $profile_parts[] = "出身は{$info['from_area']}";
    if ($info['blood_type']) $profile_parts[] = "血液型は{$info['blood_type']}型";
    if ($profile_parts) {
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "{$name}のプロフィールは？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => "{$name}は" . implode('、', $profile_parts) . 'です。'
                    . ($info['show_name'] ? "「{$info['show_name']}」" . ($info['season'] ? "（{$info['season']}）" : '') . 'に出演しています。' : ''),
            ],
        ];
    }

    // Q2: SNSアカウント
    $sns_parts = [];
    if ($info['ig']) $sns_parts[] = "Instagram: @{$info['ig']}";
    if ($info['tiktok']) $sns_parts[] = "TikTok: @{$info['tiktok']}";
    if ($sns_parts) {
        $answer = "{$name}のSNSアカウントは" . implode('、', $sns_parts) . 'です。';
        if ($info['followers'] > 0) {
            $answer .= 'Instagramのフォロワー数は約' . number_format($info['followers']) . '人です。';
        }
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "{$name}のSNS（インスタ・TikTok）は？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $answer,
            ],
        ];
    }

    // Q3: 出演番組
    if ($info['show_name']) {
        $answer = "{$name}は恋愛リアリティ番組「{$info['show_name']}」";
        if ($info['season']) {
            $answer .= "の{$info['season']}";
        }
        $answer .= 'に出演しています。';
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "{$name}の出演番組は何ですか？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $answer,
            ],
        ];
    }

    // Q4: 趣味
    if ($info['hobby']) {
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => "{$name}の趣味は？",
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => "{$name}の趣味は{$info['hobby']}です。",
            ],
        ];
    }

    if ($faq_items) {
        koi_ria_output_schema([
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faq_items,
        ]);
    }
}

/* =========================================================================
 * Article / NewsArticle スキーマ（記事）
 * ========================================================================= */
function koi_ria_article_schema(): void {
    $post_id = get_the_ID();
    $content = get_the_content();

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'NewsArticle',
        '@id'           => get_permalink() . '#article',
        'headline'      => get_the_title(),
        'url'           => get_permalink(),
        'datePublished' => get_the_date('c'),
        'dateModified'  => get_the_modified_date('c'),
        'inLanguage'    => 'ja',
        'isPartOf'      => ['@id' => home_url('/#website')],
        'author'        => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
            '@id'   => home_url('/#organization'),
        ],
        'publisher'     => [
            '@id' => home_url('/#organization'),
        ],
        'description'   => koi_ria_get_clean_excerpt(160),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => get_permalink(),
        ],
    ];

    if (has_post_thumbnail()) {
        $thumb_id  = get_post_thumbnail_id();
        $thumb_url = get_the_post_thumbnail_url($post_id, 'large');
        $meta      = wp_get_attachment_metadata($thumb_id);
        $alt       = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
        $schema['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $thumb_url,
            'width'  => $meta['width'] ?? 1200,
            'height' => $meta['height'] ?? 630,
        ];
        if ($alt) {
            $schema['image']['caption'] = $alt;
        }
    }

    // 記事本文の文字数
    if ($content) {
        $schema['wordCount']    = mb_strlen(strip_tags($content));
        $schema['articleBody']  = mb_substr(wp_strip_all_tags($content), 0, 500);
    }

    // カテゴリ・タグ → articleSection / keywords
    $cats = get_the_category();
    if ($cats) {
        $schema['articleSection'] = $cats[0]->name;
    }
    $tags = get_the_tags();
    if ($tags) {
        $schema['keywords'] = implode(', ', wp_list_pluck($tags, 'name'));
    }

    // Speakable（GEO: 音声検索・AI抽出最適化）
    $schema['speakable'] = [
        '@type'       => 'SpeakableSpecification',
        'cssSelector' => ['.entry-content h1', '.entry-content h2', '.entry-content p:first-of-type'],
    ];

    koi_ria_output_schema($schema);
}

/* =========================================================================
 * パンくずリスト スキーマ
 * ========================================================================= */
function koi_ria_breadcrumb_schema(): void {
    if (is_front_page()) {
        return;
    }

    $items = [];
    $position = 1;

    // ホーム
    $items[] = [
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => 'ホーム',
        'item'     => home_url('/'),
    ];

    if (is_singular('show')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '番組一覧',
            'item'     => get_post_type_archive_link('show'),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
        ];
    } elseif (is_singular('cast')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '出演者データベース',
            'item'     => get_post_type_archive_link('cast'),
        ];
        // 番組名を中間パンくずに
        $show_id = get_field('show');
        if ($show_id) {
            $show_post = is_array($show_id) ? get_post($show_id[0]) : get_post($show_id);
            if ($show_post) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $show_post->post_title,
                    'item'     => get_permalink($show_post),
                ];
            }
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_field('display_name') ?: get_the_title(),
        ];
    } elseif (is_singular('post')) {
        $cats = get_the_category();
        if ($cats) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $cats[0]->name,
                'item'     => get_category_link($cats[0]),
            ];
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
        ];
    } elseif (is_post_type_archive()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => post_type_archive_title('', false),
        ];
    } elseif (is_category() || is_tag()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => single_term_title('', false),
        ];
    } elseif (is_search()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '検索結果: ' . get_search_query(),
        ];
    }

    if (count($items) < 2) {
        return;
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    koi_ria_output_schema($schema);
}

/* =========================================================================
 * SiteNavigationElement（グローバルナビ構造化）
 * ========================================================================= */
function koi_ria_site_navigation_schema(): void {
    $nav_items = [
        ['name' => 'ホーム', 'url' => home_url('/')],
        ['name' => '番組一覧', 'url' => get_post_type_archive_link('show')],
        ['name' => '出演者データベース', 'url' => get_post_type_archive_link('cast')],
        ['name' => 'みんなの予想', 'url' => get_post_type_archive_link('poll')],
    ];

    $elements = [];
    foreach ($nav_items as $item) {
        $elements[] = [
            '@type' => 'SiteNavigationElement',
            'name'  => $item['name'],
            'url'   => $item['url'],
        ];
    }

    koi_ria_output_schema([
        '@context'  => 'https://schema.org',
        '@type'     => 'ItemList',
        'name'      => 'サイトナビゲーション',
        'itemListElement' => $elements,
    ]);
}

/* =========================================================================
 * ヘルパー関数
 * ========================================================================= */

/**
 * JSON-LD出力ヘルパー
 */
function koi_ria_output_schema(array $schema): void {
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
}

/**
 * クリーンなエクサープト取得（HTMLタグ除去・改行除去）
 */
function koi_ria_get_clean_excerpt(int $max_length = 160): string {
    $excerpt = get_the_excerpt();
    $excerpt = wp_strip_all_tags($excerpt);
    $excerpt = preg_replace('/\s+/', ' ', trim($excerpt));
    if (mb_strlen($excerpt) > $max_length) {
        $excerpt = mb_substr($excerpt, 0, $max_length) . '...';
    }
    return $excerpt;
}

/**
 * サイトロゴURL取得
 */
function koi_ria_get_site_logo_url(): string {
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($url) return $url;
    }
    return KOI_RIA_URI . '/assets/images/ogp-default.png';
}

/**
 * サイトのSNS URL一覧（カスタマイザーから取得）
 */
function koi_ria_get_site_social_urls(): array {
    $urls = [];
    $options = [
        'koi_ria_twitter_url',
        'koi_ria_instagram_url',
        'koi_ria_youtube_url',
        'koi_ria_tiktok_url',
    ];
    foreach ($options as $key) {
        $val = get_option($key, '');
        if ($val) {
            $urls[] = $val;
        }
    }
    return $urls;
}

/**
 * 現在のアーカイブURL
 */
function koi_ria_current_archive_url(): string {
    if (is_post_type_archive()) {
        return get_post_type_archive_link(get_query_var('post_type'));
    }
    global $wp;
    return home_url(add_query_arg([], $wp->request));
}
