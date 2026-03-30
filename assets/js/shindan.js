/**
 * 番組診断 (Show Diagnosis)
 */
(function () {
  'use strict';

  const QUESTIONS = [
    {
      q: "恋愛で一番大事なのは？",
      options: [
        { text: "ピュアな気持ち", tags: ["kyousuki", "koiste"] },
        { text: "駆け引きのスリル", tags: ["ookami", "shuffle"] },
        { text: "大人の価値観の一致", tags: ["ainosato", "bachelor"] },
        { text: "運命的な出会い", tags: ["loveblind", "offlinelove"] },
      ]
    },
    {
      q: "好きな旅先は？",
      options: [
        { text: "韓国・アジアリゾート", tags: ["kyousuki", "shuffle"] },
        { text: "ヨーロッパ", tags: ["offlinelove", "loveblind"] },
        { text: "国内の落ち着く場所", tags: ["ainosato", "boyfriend"] },
        { text: "ゴージャスなリゾート", tags: ["bachelor", "lovetransit"] },
      ]
    },
    {
      q: "自分に近いのは？",
      options: [
        { text: "現役学生 or 学生気分", tags: ["kyousuki", "koiste"] },
        { text: "恋に奥手、でも本気", tags: ["ainosato", "boyfriend"] },
        { text: "恋のサバイバー", tags: ["ookami", "lovetransit"] },
        { text: "結婚を意識してる", tags: ["bachelor", "loveblind"] },
      ]
    },
    {
      q: "番組に求めるのは？",
      options: [
        { text: "胸キュン・青春感", tags: ["kyousuki", "koiste"] },
        { text: "ハラハラ・心理戦", tags: ["ookami", "shuffle", "lovetransit"] },
        { text: "感動・涙", tags: ["ainosato", "loveblind"] },
        { text: "笑い・エンタメ", tags: ["offlinelove", "lovejo"] },
      ]
    },
    {
      q: "推しになりやすいのは？",
      options: [
        { text: "一途で真っ直ぐな子", tags: ["kyousuki", "ainosato"] },
        { text: "顔面最強のビジュアル担", tags: ["shuffle", "bachelor"] },
        { text: "ギャップがある子", tags: ["ookami", "lovetransit"] },
        { text: "自分を持ってる大人な人", tags: ["loveblind", "boyfriend"] },
      ]
    },
  ];

  const RESULTS = {
    kyousuki: { title: "今日、好きになりました。", emoji: "\uD83D\uDC97", platform: "ABEMA", desc: "ピュアな恋を全力で応援したいあなたには、高校生たちの等身大の青春恋愛がぴったり！", recs: ["テグ編（放送中）", "グアム編（伝説の神弾）", "金木犀編（人気No.2）"] },
    ookami: { title: "オオカミくんには騙されない", emoji: "\uD83D\uDC3A", platform: "ABEMA", desc: "駆け引きが好きなあなたにはオオカミくん！誰が嘘つきか推理しながら楽しめます。", recs: ["シーズン7", "シーズン5"] },
    ainosato: { title: "あいの里", emoji: "\uD83C\uDFE1", platform: "Netflix", desc: "大人の深い恋を求めるあなたにぴったり。年齢を重ねたからこその価値観の一致が胸に響く。", recs: ["シーズン2（配信中）", "シーズン1"] },
    bachelor: { title: "バチェラー・ジャパン", emoji: "\uD83C\uDF39", platform: "Prime Video", desc: "ゴージャスな世界観と本気の恋を楽しみたいあなたに。バラを受け取るのは誰？", recs: ["シーズン5", "シーズン4"] },
    loveblind: { title: "ラブ イズ ブラインド JAPAN", emoji: "\uD83D\uDC8D", platform: "Netflix", desc: "見た目じゃなくて中身で恋する...そんな運命的な出会いを信じるあなたに。", recs: ["シーズン1"] },
    shuffle: { title: "シャッフルアイランド", emoji: "\uD83C\uDFDD\uFE0F", platform: "ABEMA", desc: "スリリングな展開が好きなあなたに！メンバーチェンジで予測不能な恋模様が楽しめます。", recs: ["シーズン3", "シーズン2"] },
    offlinelove: { title: "未来日記", emoji: "\uD83D\uDCD4", platform: "ABEMA", desc: "脚本通りに進むデートから本当の恋は生まれるのか？ユニークな設定が魅力。", recs: ["シーズン2"] },
    koiste: { title: "恋ステ", emoji: "\uD83C\uDF1F", platform: "ABEMA", desc: "等身大の高校生の恋に共感したいあなたに！素直な気持ちが一番。", recs: ["最新シーズン"] },
    boyfriend: { title: "ボーイフレンド", emoji: "\uD83E\uDDF3", platform: "ABEMA", desc: "旅先での出会いが最高のスパイスに。奥手なあなたも一歩踏み出す勇気がもらえる。", recs: ["韓国編"] },
    lovetransit: { title: "ラブ トランジット", emoji: "\u2708\uFE0F", platform: "Prime Video", desc: "元カレ・元カノとの再会...サバイバルな恋愛を楽しむあなたに。", recs: ["シーズン2", "シーズン1"] },
    lovejo: { title: "恋んトス", emoji: "\uD83C\uDFB2", platform: "TBS", desc: "笑いあり涙ありの恋愛バラエティ。エンタメ重視のあなたに。", recs: ["シーズン8"] },
  };

  /* --- State --- */
  let currentQ = 0;
  let answers = [];

  /* --- DOM refs --- */
  const startBtn       = document.getElementById('shindanStart');
  const progressFill   = document.getElementById('shindanProgress');
  const progressText   = document.getElementById('shindanProgressText');
  const questionArea   = document.getElementById('shindanQuestion');
  const resultArea     = document.getElementById('shindanResult');

  if (!startBtn) return; // Not on shindan page

  const steps = document.querySelectorAll('.shindan-step');

  /* --- Helpers --- */
  function showStep(name) {
    steps.forEach(function (el) {
      el.classList.remove('is-active');
      el.style.display = 'none';
    });
    var target = document.querySelector('[data-step="' + name + '"]');
    if (target) {
      target.style.display = 'block';
      target.classList.add('is-active');
    }
  }

  function renderQuestion(index) {
    var q = QUESTIONS[index];
    progressFill.style.width = ((index + 1) / QUESTIONS.length * 100) + '%';
    progressText.textContent = 'Q' + (index + 1) + ' / ' + QUESTIONS.length;

    var html = '<p class="shindan-question">' + q.q + '</p>';
    html += '<div class="shindan-options">';
    q.options.forEach(function (opt, i) {
      html += '<button class="shindan-option" data-index="' + i + '">' + opt.text + '</button>';
    });
    html += '</div>';

    questionArea.innerHTML = html;

    // Bind option clicks
    questionArea.querySelectorAll('.shindan-option').forEach(function (btn) {
      btn.addEventListener('click', function () {
        handleAnswer(parseInt(btn.getAttribute('data-index'), 10));
      });
    });
  }

  function handleAnswer(optionIndex) {
    var selected = QUESTIONS[currentQ].options[optionIndex];
    answers.push(selected);

    // Highlight selected
    questionArea.querySelectorAll('.shindan-option').forEach(function (btn) {
      btn.classList.remove('is-selected');
    });
    questionArea.querySelector('[data-index="' + optionIndex + '"]').classList.add('is-selected');

    // Short delay for feedback, then advance
    setTimeout(function () {
      currentQ++;
      if (currentQ < QUESTIONS.length) {
        renderQuestion(currentQ);
      } else {
        showResult();
      }
    }, 300);
  }

  function calculateResult() {
    var tagCount = {};
    answers.forEach(function (ans) {
      ans.tags.forEach(function (tag) {
        tagCount[tag] = (tagCount[tag] || 0) + 1;
      });
    });

    var maxTag = '';
    var maxCount = 0;
    Object.keys(tagCount).forEach(function (tag) {
      if (tagCount[tag] > maxCount) {
        maxCount = tagCount[tag];
        maxTag = tag;
      }
    });

    return maxTag;
  }

  function showResult() {
    var slug = calculateResult();
    var r = RESULTS[slug];
    if (!r) return;

    // Update URL for sharing / OGP
    if (window.history && window.history.replaceState) {
      var url = new URL(window.location);
      url.searchParams.set('result', slug);
      window.history.replaceState(null, '', url.toString());
    }

    var shareText = encodeURIComponent('私にぴったりの恋リアは「' + r.title + '」でした！ #恋リア診断');
    var shareUrl = encodeURIComponent(window.location.href);

    var html = '<div class="shindan-result-card">';
    html += '<div class="shindan-result-card__emoji">' + r.emoji + '</div>';
    html += '<div class="shindan-result-card__title">' + r.title + '</div>';
    html += '<p style="font-size:0.75rem; color:var(--color-text-sub); margin-top:var(--space-xs);">' + r.platform + ' で配信中</p>';
    html += '<p class="shindan-result-card__desc">' + r.desc + '</p>';

    if (r.recs && r.recs.length) {
      html += '<div class="shindan-result-card__recs">';
      html += '<p style="font-size:0.8125rem; font-weight:700; margin-bottom:var(--space-xs);">おすすめシーズン</p>';
      html += '<ul>';
      r.recs.forEach(function (rec) {
        html += '<li>' + rec + '</li>';
      });
      html += '</ul>';
      html += '</div>';
    }

    html += '</div>'; // end result-card

    // Share buttons
    html += '<div class="shindan-share">';
    html += '<a class="shindan-share__btn shindan-share__btn--x" href="https://twitter.com/intent/tweet?text=' + shareText + '&url=' + shareUrl + '" target="_blank" rel="noopener noreferrer">X でシェア</a>';
    html += '<a class="shindan-share__btn shindan-share__btn--line" href="https://social-plugins.line.me/lineit/share?url=' + shareUrl + '" target="_blank" rel="noopener noreferrer">LINE でシェア</a>';
    html += '</div>';

    // Retry button
    html += '<div style="margin-top:var(--space-lg);">';
    html += '<button class="btn btn--outline" id="shindanRetry">もう一度診断する</button>';
    html += '</div>';

    resultArea.innerHTML = html;
    showStep('result');

    // Retry handler
    document.getElementById('shindanRetry').addEventListener('click', function () {
      currentQ = 0;
      answers = [];
      // Remove result param from URL
      if (window.history && window.history.replaceState) {
        var url = new URL(window.location);
        url.searchParams.delete('result');
        window.history.replaceState(null, '', url.toString());
      }
      showStep('intro');
    });
  }

  /* --- Init --- */
  startBtn.addEventListener('click', function () {
    currentQ = 0;
    answers = [];
    showStep('questions');
    renderQuestion(0);
  });

  // If ?result=slug, show result directly
  var params = new URLSearchParams(window.location.search);
  var preResult = params.get('result');
  if (preResult && RESULTS[preResult]) {
    // Simulate all answers pointing to this result so showResult works
    var r = RESULTS[preResult];
    // Build fake answers to produce this slug
    answers = [];
    for (var i = 0; i < QUESTIONS.length; i++) {
      // Find option with matching tag or use first
      var found = QUESTIONS[i].options[0];
      QUESTIONS[i].options.forEach(function (opt) {
        if (opt.tags.indexOf(preResult) !== -1) {
          found = opt;
        }
      });
      answers.push(found);
    }
    showResult();
  }
})();
