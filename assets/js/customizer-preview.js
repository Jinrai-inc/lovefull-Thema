/**
 * カスタマイザーリアルタイムプレビュー
 */
(function ($) {
    // ロゴ設定
    wp.customize('koi_ria_logo_height_mobile', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--logo-height-mobile', newval + 'px');
        });
    });

    wp.customize('koi_ria_logo_height_pc', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--logo-height-pc', newval + 'px');
        });
    });

    wp.customize('koi_ria_logo_max_width', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--logo-max-width', newval + 'px');
        });
    });

    // 背景画像
    wp.customize('koi_ria_bg_image', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--bg-image', newval ? 'url(' + newval + ')' : 'none');
        });
    });

    // オーバーレイカラー
    wp.customize('koi_ria_bg_overlay_color', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--bg-overlay-color', newval);
        });
    });

    // オーバーレイ透明度
    wp.customize('koi_ria_bg_overlay_opacity', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--bg-overlay-opacity', (newval / 100).toFixed(2));
        });
    });

    // 背景ぼかし
    wp.customize('koi_ria_bg_blur', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--bg-blur', newval + 'px');
        });
    });
})(jQuery);
