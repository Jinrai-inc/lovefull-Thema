/**
 * 推しメンバー登録（Favorites）
 *
 * @package KoiRiaPortal
 */

const STORAGE_KEY = 'koi_ria_favorites';
const MAX_FAVS = 50;

/* ---------- LocalStorage helpers ---------- */

function getFavs() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
  } catch (e) {
    return [];
  }
}

function saveFavs(favs) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(favs));
}

function isFav(castId) {
  return getFavs().some(function (f) {
    return String(f.cast_id) === String(castId);
  });
}

function toggleFav(castId, name, ig, showTitle) {
  var favs = getFavs();
  var idx = favs.findIndex(function (f) {
    return String(f.cast_id) === String(castId);
  });

  if (idx !== -1) {
    favs.splice(idx, 1);
  } else {
    if (favs.length >= MAX_FAVS) {
      alert('推しメンバーは最大' + MAX_FAVS + '人までです。');
      return false;
    }
    favs.push({
      cast_id: String(castId),
      name: name || '',
      ig: ig || '',
      show_title: showTitle || ''
    });
  }

  saveFavs(favs);
  return true;
}

/* ---------- DOM updates ---------- */

function updateFavButtons() {
  document.querySelectorAll('.fav-btn').forEach(function (btn) {
    var castId = btn.getAttribute('data-cast-id');
    if (isFav(castId)) {
      btn.classList.add('is-fav');
      btn.setAttribute('aria-label', '推しを解除');
    } else {
      btn.classList.remove('is-fav');
      btn.setAttribute('aria-label', '推しに登録');
    }
  });
}

/* ---------- Button click handler ---------- */

document.addEventListener('click', function (e) {
  var btn = e.target.closest('.fav-btn');
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  var castId   = btn.getAttribute('data-cast-id');
  var name     = btn.getAttribute('data-name');
  var ig       = btn.getAttribute('data-ig');
  var showTitle = btn.getAttribute('data-show-title');

  if (toggleFav(castId, name, ig, showTitle)) {
    updateFavButtons();
    renderFavoritesPage();
  }
});

/* ---------- Favorites page rendering ---------- */

function renderFavoritesPage() {
  var container = document.getElementById('favorites-app');
  if (!container) return;

  var favs = getFavs();

  if (favs.length === 0) {
    container.innerHTML =
      '<div class="favorites-empty">' +
        '<div class="favorites-empty__icon">\u2661</div>' +
        '<p>まだ推しメンバーがいません。</p>' +
        '<p style="font-size:0.875rem;margin-top:var(--space-sm);">出演者ページの <span style="color:#FF3B6F;">\u2665</span> ボタンで登録できます。</p>' +
      '</div>';
    return;
  }

  // Avatar grid
  var html = '<div class="section-header"><h2>\u2665 推しメンバー (' + favs.length + ')</h2></div>';
  html += '<div class="favorites-grid">';
  favs.forEach(function (fav) {
    var initial = fav.name ? fav.name.charAt(0) : '?';
    html +=
      '<div class="stories-item" style="position:relative;">' +
        '<div class="ig-avatar" style="margin:0 auto;">' +
          '<div class="ig-avatar__img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f0f0f0;">\uD83D\uDC64</div>' +
        '</div>' +
        '<span class="stories-item__name">' + escapeHtml(fav.name) + '</span>' +
        (fav.show_title ? '<span style="font-size:0.5625rem;color:var(--color-text-sub);">' + escapeHtml(fav.show_title) + '</span>' : '') +
        '<button class="fav-btn is-fav" data-cast-id="' + escapeAttr(fav.cast_id) + '" data-name="' + escapeAttr(fav.name) + '" data-ig="' + escapeAttr(fav.ig) + '" data-show-title="' + escapeAttr(fav.show_title) + '" aria-label="\u63A8\u3057\u3092\u89E3\u9664" style="position:absolute;top:0;right:0;width:24px;height:24px;font-size:0.75rem;">' +
          '<span class="fav-heart" style="display:none;">\u2661</span>' +
          '<span class="fav-heart active" style="display:inline;">\u2665</span>' +
        '</button>' +
      '</div>';
  });
  html += '</div>';

  // Feed container
  html += '<div class="favorites-feed" id="favorites-feed">' +
    '<div class="section-header" style="margin-top:var(--space-lg);"><h2>\uD83D\uDCF0 推しの最新ニュース</h2></div>' +
    '<div id="favorites-feed-list" style="text-align:center;padding:var(--space-md);color:var(--color-text-sub);">読み込み中...</div>' +
  '</div>';

  container.innerHTML = html;

  // Load feed via REST API
  loadFavoritesFeed(favs);
}

function loadFavoritesFeed(favs) {
  var feedList = document.getElementById('favorites-feed-list');
  if (!feedList) return;

  var castIds = favs.map(function (f) { return f.cast_id; }).join(',');
  if (!castIds) {
    feedList.innerHTML = '<p>フィードはありません。</p>';
    return;
  }

  var url = '/wp-json/koi-ria/v1/favorites-feed?cast_ids=' + encodeURIComponent(castIds);

  fetch(url)
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(function (posts) {
      if (!posts || posts.length === 0) {
        feedList.innerHTML = '<p>まだニュースがありません。</p>';
        return;
      }
      var html = '';
      posts.forEach(function (post) {
        html +=
          '<a href="' + escapeAttr(post.url) + '" style="display:block;padding:var(--space-sm) 0;border-bottom:1px solid #f0f0f0;text-decoration:none;color:inherit;">' +
            '<div style="font-size:0.75rem;color:var(--color-text-sub);">' + escapeHtml(post.date || '') + '</div>' +
            '<div style="font-weight:500;">' + escapeHtml(post.title || '') + '</div>' +
          '</a>';
      });
      feedList.innerHTML = html;
    })
    .catch(function () {
      feedList.innerHTML = '<p style="color:var(--color-text-sub);">フィードを取得できませんでした。</p>';
    });
}

/* ---------- Utility ---------- */

function escapeHtml(str) {
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}

function escapeAttr(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

/* ---------- Init ---------- */

document.addEventListener('DOMContentLoaded', function () {
  updateFavButtons();
  renderFavoritesPage();
});
