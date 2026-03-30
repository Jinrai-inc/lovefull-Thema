/**
 * VOD検索 — デバウンス付き検索 + 結果表示
 *
 * @package KoiRiaPortal
 */
(function () {
    'use strict';

    var input      = document.getElementById('vod-search-input');
    var form       = document.getElementById('vodSearchForm');
    var resultsBox = document.getElementById('vodSearchResults');

    if (!input || !resultsBox) return;

    var debounceTimer = null;
    var apiBase       = (window.koiRia && window.koiRia.ajaxUrl) || '/wp-json/koi-ria/v1/';

    // プラットフォームカラー
    var platformColors = {
        abema:   'var(--color-abema)',
        netflix: 'var(--color-netflix)',
        prime:   'var(--color-prime)',
        unext:   '#E6197B'
    };

    var platformLabels = {
        abema:   'ABEMA',
        netflix: 'Netflix',
        prime:   'Prime',
        unext:   'U-NEXT'
    };

    // フォームのsubmitを抑止
    form.addEventListener('submit', function (e) {
        e.preventDefault();
    });

    // デバウンス付き入力監視
    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var query = input.value.trim();

        if (query.length < 1) {
            resultsBox.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function () {
            fetchResults(query);
        }, 300);
    });

    function fetchResults(query) {
        resultsBox.innerHTML = '<p style="text-align:center;color:var(--color-text-sub);">検索中…</p>';

        fetch(apiBase + 'vod-search?q=' + encodeURIComponent(query))
            .then(function (res) {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(function (data) {
                renderResults(data);
            })
            .catch(function () {
                resultsBox.innerHTML = '<p style="text-align:center;color:var(--color-danger);">検索エラーが発生しました</p>';
            });
    }

    function renderResults(items) {
        if (!items || items.length === 0) {
            resultsBox.innerHTML = '<p style="text-align:center;color:var(--color-text-sub);padding:var(--space-lg) 0;">検索結果なし</p>';
            return;
        }

        var html = '<div style="padding: 0 var(--space-md);">';

        items.forEach(function (item) {
            html += '<div class="vod-result-card">';
            html += '<div class="vod-result-card__title">' + escapeHtml(item.title) + '</div>';

            // VODバッジ
            if (item.platforms && item.platforms.length > 0) {
                html += '<div class="vod-badges">';
                item.platforms.forEach(function (p) {
                    var color = platformColors[p.key] || '#9CA3AF';
                    var label = platformLabels[p.key] || p.key;
                    html += '<span class="badge" style="background:' + color + ';color:#fff;">' + escapeHtml(label) + '</span>';
                });
                html += '</div>';
            }

            // アフィリエイトリンク
            if (item.links && item.links.length > 0) {
                html += '<div style="display:flex;flex-wrap:wrap;gap:var(--space-xs);">';
                item.links.forEach(function (link) {
                    var color = platformColors[link.key] || '#9CA3AF';
                    var label = platformLabels[link.key] || link.key;
                    html += '<a href="' + escapeHtml(link.url) + '" class="btn btn--sm" style="background:' + color + ';color:#fff;" target="_blank" rel="noopener noreferrer sponsored">';
                    html += escapeHtml(label) + 'で見る</a>';
                });
                html += '</div>';
            }

            // 番組詳細リンク
            if (item.url) {
                html += '<a href="' + escapeHtml(item.url) + '" style="display:inline-block;margin-top:var(--space-sm);font-size:0.8125rem;color:var(--color-primary);">番組詳細 &rarr;</a>';
            }

            html += '</div>';
        });

        html += '</div>';
        resultsBox.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
})();
