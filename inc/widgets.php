<?php
/**
 * カスタムウィジェット
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/* =========================================================================
 * 1. バナー広告ウィジェット
 * ========================================================================= */
class Koi_Ria_Banner_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_banner',
            '🎀 バナー広告',
            ['description' => '画像バナー広告を表示します。画像URL・リンク先・ラベルを設定できます。']
        );
    }

    public function widget($args, $instance): void {
        $image = $instance['image'] ?? '';
        $url   = $instance['url'] ?? '';
        $label = $instance['label'] ?? '';
        $title = $instance['title'] ?? '';

        if (empty($image)) return;

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        echo '<div class="sidebar-banner">';
        if ($url) {
            echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener sponsored" class="sidebar-banner__link">';
        }
        echo '<img src="' . esc_url($image) . '" alt="' . esc_attr($label ?: '広告') . '" class="sidebar-banner__img" loading="lazy">';
        if ($url) {
            echo '</a>';
        }
        if ($label) {
            echo '<span class="sidebar-banner__label">' . esc_html($label) . '</span>';
        }
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title = $instance['title'] ?? '';
        $image = $instance['image'] ?? '';
        $url   = $instance['url'] ?? '';
        $label = $instance['label'] ?? '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル（任意）:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>" placeholder="例: おすすめ">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('image')); ?>">画像URL:</label>
            <input class="widefat koi-widget-image-url" id="<?php echo esc_attr($this->get_field_id('image')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('image')); ?>"
                   type="url" value="<?php echo esc_attr($image); ?>" placeholder="https://example.com/banner.jpg">
            <button type="button" class="button koi-widget-media-upload" style="margin-top:4px;">画像を選択</button>
        </p>
        <?php if ($image) : ?>
        <p><img src="<?php echo esc_url($image); ?>" style="max-width:100%;height:auto;border-radius:6px;"></p>
        <?php endif; ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('url')); ?>">リンク先URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('url')); ?>"
                   type="url" value="<?php echo esc_attr($url); ?>" placeholder="https://...">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('label')); ?>">ラベル（任意）:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('label')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('label')); ?>"
                   type="text" value="<?php echo esc_attr($label); ?>" placeholder="PR">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title' => sanitize_text_field($new_instance['title'] ?? ''),
            'image' => esc_url_raw($new_instance['image'] ?? ''),
            'url'   => esc_url_raw($new_instance['url'] ?? ''),
            'label' => sanitize_text_field($new_instance['label'] ?? ''),
        ];
    }
}

/* =========================================================================
 * 2. 広告コードウィジェット
 * ========================================================================= */
class Koi_Ria_Ad_Code_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_ad_code',
            '🎀 広告コード（HTML）',
            ['description' => 'AdSenseやアフィリエイトのHTMLコードを貼り付けて表示します。']
        );
    }

    public function widget($args, $instance): void {
        $title   = $instance['title'] ?? '';
        $ad_code = $instance['ad_code'] ?? '';
        $label   = $instance['label'] ?? '';

        if (empty($ad_code)) return;

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        echo '<div class="sidebar-ad-code">';
        echo $ad_code;
        if ($label) {
            echo '<span class="sidebar-banner__label">' . esc_html($label) . '</span>';
        }
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title   = $instance['title'] ?? '';
        $ad_code = $instance['ad_code'] ?? '';
        $label   = $instance['label'] ?? '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル（任意）:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>" placeholder="例: スポンサー">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('ad_code')); ?>">広告コード（HTML）:</label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('ad_code')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('ad_code')); ?>"
                      rows="8" placeholder="AdSenseやアフィリエイトのHTMLコードを貼り付け"><?php echo esc_textarea($ad_code); ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('label')); ?>">ラベル（任意）:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('label')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('label')); ?>"
                   type="text" value="<?php echo esc_attr($label); ?>" placeholder="PR / 広告">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title'   => sanitize_text_field($new_instance['title'] ?? ''),
            'ad_code' => $new_instance['ad_code'] ?? '',
            'label'   => sanitize_text_field($new_instance['label'] ?? ''),
        ];
    }
}

/* =========================================================================
 * 3. 人気記事ウィジェット
 * ========================================================================= */
class Koi_Ria_Popular_Posts_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_popular_posts',
            '🎀 人気記事ランキング',
            ['description' => 'PV数の多い記事をランキング形式で表示します。']
        );
    }

    public function widget($args, $instance): void {
        $title = $instance['title'] ?? '人気記事';
        $count = intval($instance['count'] ?? 5);
        $days  = intval($instance['days'] ?? 30);

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        $posts = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'meta_key'       => 'koi_ria_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'date_query'     => [['after' => $days . ' days ago']],
        ]);

        // PVデータがない場合はコメント数順にフォールバック
        if (empty($posts)) {
            $posts = get_posts([
                'post_type'      => 'post',
                'posts_per_page' => $count,
                'orderby'        => 'comment_count',
                'order'          => 'DESC',
            ]);
        }

        if ($posts) :
            echo '<ul class="sw-popular-list">';
            foreach ($posts as $i => $post) :
                $thumb = get_the_post_thumbnail_url($post, 'thumbnail');
                if (!$thumb) $thumb = get_theme_mod('koi_ria_default_eyecatch', '');
                $rank = $i + 1;
            ?>
                <li class="sw-popular-item">
                    <a href="<?php echo esc_url(get_permalink($post)); ?>" class="sw-popular-link">
                        <span class="sw-popular-rank sw-popular-rank--<?php echo $rank <= 3 ? $rank : 'other'; ?>"><?php echo $rank; ?></span>
                        <div class="sw-popular-thumb">
                            <?php if ($thumb) : ?>
                                <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                            <?php else : ?>
                                <span class="sw-popular-thumb--empty"></span>
                            <?php endif; ?>
                        </div>
                        <div class="sw-popular-info">
                            <span class="sw-popular-title"><?php echo esc_html($post->post_title); ?></span>
                            <time class="sw-popular-date"><?php echo get_the_date('Y.m.d', $post); ?></time>
                        </div>
                    </a>
                </li>
            <?php
            endforeach;
            echo '</ul>';
        else :
            echo '<p class="sw-empty">記事がまだありません</p>';
        endif;

        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title = $instance['title'] ?? '人気記事';
        $count = $instance['count'] ?? 5;
        $days  = $instance['days'] ?? 30;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">表示件数:</label>
            <input id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>"
                   type="number" value="<?php echo esc_attr($count); ?>" min="1" max="20" style="width:60px;">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('days')); ?>">集計期間（日）:</label>
            <input id="<?php echo esc_attr($this->get_field_id('days')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('days')); ?>"
                   type="number" value="<?php echo esc_attr($days); ?>" min="1" max="365" style="width:60px;">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title' => sanitize_text_field($new_instance['title'] ?? '人気記事'),
            'count' => intval($new_instance['count'] ?? 5),
            'days'  => intval($new_instance['days'] ?? 30),
        ];
    }
}

/* =========================================================================
 * 4. 関連記事ウィジェット
 * ========================================================================= */
class Koi_Ria_Related_Posts_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_related_posts',
            '🎀 関連記事',
            ['description' => '現在の記事と同じカテゴリの記事を自動表示します。']
        );
    }

    public function widget($args, $instance): void {
        if (!is_singular('post')) return;

        $title = $instance['title'] ?? '関連記事';
        $count = intval($instance['count'] ?? 5);

        $categories = get_the_category();
        if (empty($categories)) return;

        $cat_ids = wp_list_pluck($categories, 'term_id');

        $posts = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'post__not_in'   => [get_the_ID()],
            'category__in'   => $cat_ids,
            'orderby'        => 'rand',
        ]);

        if (empty($posts)) return;

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        echo '<ul class="sw-related-list">';
        foreach ($posts as $post) :
            $thumb = get_the_post_thumbnail_url($post, 'thumbnail');
            if (!$thumb) $thumb = get_theme_mod('koi_ria_default_eyecatch', '');
        ?>
            <li class="sw-related-item">
                <a href="<?php echo esc_url(get_permalink($post)); ?>" class="sw-related-link">
                    <div class="sw-related-thumb">
                        <?php if ($thumb) : ?>
                            <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                        <?php else : ?>
                            <span class="sw-related-thumb--empty"></span>
                        <?php endif; ?>
                    </div>
                    <div class="sw-related-info">
                        <span class="sw-related-title"><?php echo esc_html($post->post_title); ?></span>
                        <time class="sw-related-date"><?php echo get_the_date('Y.m.d', $post); ?></time>
                    </div>
                </a>
            </li>
        <?php
        endforeach;
        echo '</ul>';
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title = $instance['title'] ?? '関連記事';
        $count = $instance['count'] ?? 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">表示件数:</label>
            <input id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>"
                   type="number" value="<?php echo esc_attr($count); ?>" min="1" max="10" style="width:60px;">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title' => sanitize_text_field($new_instance['title'] ?? '関連記事'),
            'count' => intval($new_instance['count'] ?? 5),
        ];
    }
}

/* =========================================================================
 * 5. 番組ランキングウィジェット
 * ========================================================================= */
class Koi_Ria_Show_Ranking_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_show_ranking',
            '🎀 注目の番組',
            ['description' => '番組をロゴ付きで一覧表示します。']
        );
    }

    public function widget($args, $instance): void {
        $title = $instance['title'] ?? '注目の番組';
        $count = intval($instance['count'] ?? 5);

        $shows = get_posts([
            'post_type'      => 'show',
            'posts_per_page' => $count,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ]);

        if (empty($shows)) return;

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        echo '<ul class="sw-show-list">';
        foreach ($shows as $show) :
            $logo = get_the_post_thumbnail_url($show, 'thumbnail');
            $platform = get_field('platform', $show->ID) ?: '';
        ?>
            <li class="sw-show-item">
                <a href="<?php echo esc_url(get_permalink($show)); ?>" class="sw-show-link">
                    <div class="sw-show-logo">
                        <?php if ($logo) : ?>
                            <img src="<?php echo esc_url($logo); ?>" alt="" loading="lazy">
                        <?php else : ?>
                            <span class="sw-show-logo--empty"><?php echo koi_ria_icon('tv', 18); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="sw-show-info">
                        <span class="sw-show-name"><?php echo esc_html($show->post_title); ?></span>
                        <?php if ($platform) : ?>
                            <span class="sw-show-platform"><?php echo esc_html($platform); ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            </li>
        <?php
        endforeach;
        echo '</ul>';
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title = $instance['title'] ?? '注目の番組';
        $count = $instance['count'] ?? 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">表示件数:</label>
            <input id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>"
                   type="number" value="<?php echo esc_attr($count); ?>" min="1" max="15" style="width:60px;">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title' => sanitize_text_field($new_instance['title'] ?? '注目の番組'),
            'count' => intval($new_instance['count'] ?? 5),
        ];
    }
}

/* =========================================================================
 * 6. かわいい検索ウィジェット
 * ========================================================================= */
class Koi_Ria_Search_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_search',
            '🎀 検索（かわいい）',
            ['description' => 'テーマに合ったかわいいデザインの検索窓です。']
        );
    }

    public function widget($args, $instance): void {
        $title       = $instance['title'] ?? '';
        $placeholder = $instance['placeholder'] ?? '番組名・出演者を検索...';

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        ?>
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="sw-search">
            <div class="sw-search__box">
                <?php echo koi_ria_icon('search', 18); ?>
                <input type="search" class="sw-search__input" name="s"
                       placeholder="<?php echo esc_attr($placeholder); ?>"
                       value="<?php echo get_search_query(); ?>"
                       aria-label="検索">
            </div>
            <button type="submit" class="sw-search__btn">検索</button>
        </form>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title       = $instance['title'] ?? '';
        $placeholder = $instance['placeholder'] ?? '番組名・出演者を検索...';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル（任意）:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('placeholder')); ?>">プレースホルダー:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('placeholder')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('placeholder')); ?>"
                   type="text" value="<?php echo esc_attr($placeholder); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title'       => sanitize_text_field($new_instance['title'] ?? ''),
            'placeholder' => sanitize_text_field($new_instance['placeholder'] ?? '番組名・出演者を検索...'),
        ];
    }
}

/* =========================================================================
 * 7. プロフィールカードウィジェット
 * ========================================================================= */
class Koi_Ria_Profile_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_profile',
            '🎀 サイト紹介カード',
            ['description' => 'サイトの紹介文とSNSリンクを表示するプロフィールカードです。']
        );
    }

    public function widget($args, $instance): void {
        $title   = $instance['title'] ?? get_bloginfo('name');
        $desc    = $instance['description'] ?? get_bloginfo('description');
        $twitter = $instance['twitter'] ?? '';
        $insta   = $instance['instagram'] ?? '';

        echo $args['before_widget'];
        ?>
        <div class="sw-profile">
            <?php
            $logo_id = get_theme_mod('custom_logo');
            if ($logo_id) :
                $logo_url = wp_get_attachment_image_url($logo_id, 'medium');
            ?>
                <div class="sw-profile__logo">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                </div>
            <?php endif; ?>
            <h3 class="sw-profile__name"><?php echo esc_html($title); ?></h3>
            <?php if ($desc) : ?>
                <p class="sw-profile__desc"><?php echo esc_html($desc); ?></p>
            <?php endif; ?>
            <?php if ($twitter || $insta) : ?>
            <div class="sw-profile__sns">
                <?php if ($twitter) : ?>
                    <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener" class="sw-profile__sns-link sw-profile__sns-link--x" aria-label="X (Twitter)">𝕏</a>
                <?php endif; ?>
                <?php if ($insta) : ?>
                    <a href="<?php echo esc_url($insta); ?>" target="_blank" rel="noopener" class="sw-profile__sns-link sw-profile__sns-link--ig" aria-label="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title   = $instance['title'] ?? get_bloginfo('name');
        $desc    = $instance['description'] ?? get_bloginfo('description');
        $twitter = $instance['twitter'] ?? '';
        $insta   = $instance['instagram'] ?? '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">サイト名:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('description')); ?>">紹介文:</label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('description')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('description')); ?>"
                      rows="3"><?php echo esc_textarea($desc); ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('twitter')); ?>">X (Twitter) URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('twitter')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('twitter')); ?>"
                   type="url" value="<?php echo esc_attr($twitter); ?>" placeholder="https://x.com/...">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('instagram')); ?>">Instagram URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('instagram')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('instagram')); ?>"
                   type="url" value="<?php echo esc_attr($insta); ?>" placeholder="https://www.instagram.com/...">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title'       => sanitize_text_field($new_instance['title'] ?? ''),
            'description' => sanitize_text_field($new_instance['description'] ?? ''),
            'twitter'     => esc_url_raw($new_instance['twitter'] ?? ''),
            'instagram'   => esc_url_raw($new_instance['instagram'] ?? ''),
        ];
    }
}

/* =========================================================================
 * 8. カテゴリ一覧ウィジェット（かわいい）
 * ========================================================================= */
class Koi_Ria_Categories_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_categories',
            '🎀 カテゴリ一覧',
            ['description' => 'テーマに合ったかわいいデザインのカテゴリ一覧です。']
        );
    }

    public function widget($args, $instance): void {
        $title = $instance['title'] ?? 'カテゴリ';

        $categories = get_categories([
            'orderby' => 'count',
            'order'   => 'DESC',
            'hide_empty' => true,
        ]);

        if (empty($categories)) return;

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        echo '<div class="sw-categories">';
        foreach ($categories as $cat) :
        ?>
            <a href="<?php echo esc_url(get_category_link($cat)); ?>" class="sw-category-tag">
                <span class="sw-category-tag__name"><?php echo esc_html($cat->name); ?></span>
                <span class="sw-category-tag__count"><?php echo intval($cat->count); ?></span>
            </a>
        <?php
        endforeach;
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance): void {
        $title = $instance['title'] ?? 'カテゴリ';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">タイトル:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array {
        return [
            'title' => sanitize_text_field($new_instance['title'] ?? 'カテゴリ'),
        ];
    }
}

/* =========================================================================
 * ウィジェット用メディアアップローダースクリプト（管理画面）
 * ========================================================================= */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'widgets.php' && strpos($hook, 'customize') === false) return;

    wp_enqueue_media();
    wp_add_inline_script('media-upload', "
        jQuery(document).on('click', '.koi-widget-media-upload', function(e) {
            e.preventDefault();
            var btn = jQuery(this);
            var input = btn.siblings('.koi-widget-image-url');
            var frame = wp.media({
                title: 'バナー画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                input.val(attachment.url).trigger('change');
            });
            frame.open();
        });
    ");
});
