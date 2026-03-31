/**
 * カスタマイザーリアルタイムプレビュー
 */
(function ($) {
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
})(jQuery);
