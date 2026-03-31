<?php
/**
 * カスタムウィジェット
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * バナー広告ウィジェット
 *
 * 画像・リンク・ラベルを設定してサイドバーにバナーを表示する。
 */
class Koi_Ria_Banner_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_banner',
            'バナー広告',
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

/**
 * 広告コードウィジェット
 *
 * AdSenseやアフィリエイトのHTMLコードをそのまま貼り付けて表示する。
 */
class Koi_Ria_Ad_Code_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'koi_ria_ad_code',
            '広告コード（HTML）',
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
        // 広告コードはそのまま出力（管理者が設定するため）
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
            <small class="description">AdSense、A8.net、もしもアフィリエイト等の広告コードをそのまま貼り付けてください。</small>
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
            'ad_code' => $new_instance['ad_code'] ?? '', // HTMLコードはサニタイズしない（管理者のみ設定）
            'label'   => sanitize_text_field($new_instance['label'] ?? ''),
        ];
    }
}

/**
 * ウィジェット用メディアアップローダースクリプト（管理画面）
 */
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
