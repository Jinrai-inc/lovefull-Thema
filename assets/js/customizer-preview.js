/**
 * カスタマイザーリアルタイムプレビュー
 */
(function ($) {
    wp.customize('koi_ria_logo_height', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--logo-height', newval + 'px');
        });
    });

    wp.customize('koi_ria_logo_max_width', function (value) {
        value.bind(function (newval) {
            document.documentElement.style.setProperty('--logo-max-width', newval + 'px');
        });
    });
})(jQuery);
