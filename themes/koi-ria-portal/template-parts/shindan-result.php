<?php
/**
 * 番組診断 結果テンプレート（サーバーサイドレンダリング）
 *
 * ?result=slug が指定されたときに OGP / SEO クローラー向けに
 * 結果を出力するためのテンプレートパーツ。
 *
 * 使い方: get_template_part('template-parts/shindan-result');
 */

defined('ABSPATH') || exit;

$shindan_results = [
    'kyousuki'    => ['title' => '今日、好きになりました。',       'emoji' => "\xF0\x9F\x92\x97", 'platform' => 'ABEMA',       'desc' => 'ピュアな恋を全力で応援したいあなたには、高校生たちの等身大の青春恋愛がぴったり！'],
    'ookami'      => ['title' => 'オオカミくんには騙されない',      'emoji' => "\xF0\x9F\x90\xBA", 'platform' => 'ABEMA',       'desc' => '駆け引きが好きなあなたにはオオカミくん！誰が嘘つきか推理しながら楽しめます。'],
    'ainosato'    => ['title' => 'あいの里',                       'emoji' => "\xF0\x9F\x8F\xA1", 'platform' => 'Netflix',     'desc' => '大人の深い恋を求めるあなたにぴったり。年齢を重ねたからこその価値観の一致が胸に響く。'],
    'bachelor'    => ['title' => 'バチェラー・ジャパン',            'emoji' => "\xF0\x9F\x8C\xB9", 'platform' => 'Prime Video', 'desc' => 'ゴージャスな世界観と本気の恋を楽しみたいあなたに。バラを受け取るのは誰？'],
    'loveblind'   => ['title' => 'ラブ イズ ブラインド JAPAN',     'emoji' => "\xF0\x9F\x92\x8D", 'platform' => 'Netflix',     'desc' => '見た目じゃなくて中身で恋する...そんな運命的な出会いを信じるあなたに。'],
    'shuffle'     => ['title' => 'シャッフルアイランド',            'emoji' => "\xF0\x9F\x8F\x9D", 'platform' => 'ABEMA',       'desc' => 'スリリングな展開が好きなあなたに！メンバーチェンジで予測不能な恋模様が楽しめます。'],
    'offlinelove' => ['title' => '未来日記',                       'emoji' => "\xF0\x9F\x93\x94", 'platform' => 'ABEMA',       'desc' => '脚本通りに進むデートから本当の恋は生まれるのか？ユニークな設定が魅力。'],
    'koiste'      => ['title' => '恋ステ',                         'emoji' => "\xF0\x9F\x8C\x9F", 'platform' => 'ABEMA',       'desc' => '等身大の高校生の恋に共感したいあなたに！素直な気持ちが一番。'],
    'boyfriend'   => ['title' => 'ボーイフレンド',                  'emoji' => "\xF0\x9F\xA7\xB3", 'platform' => 'ABEMA',       'desc' => '旅先での出会いが最高のスパイスに。奥手なあなたも一歩踏み出す勇気がもらえる。'],
    'lovetransit' => ['title' => 'ラブ トランジット',               'emoji' => "\xE2\x9C\x88",     'platform' => 'Prime Video', 'desc' => '元カレ・元カノとの再会...サバイバルな恋愛を楽しむあなたに。'],
    'lovejo'      => ['title' => '恋んトス',                       'emoji' => "\xF0\x9F\x8E\xB2", 'platform' => 'TBS',         'desc' => '笑いあり涙ありの恋愛バラエティ。エンタメ重視のあなたに。'],
];

$result_slug = isset($_GET['result']) ? sanitize_key($_GET['result']) : '';

if (empty($result_slug) || !isset($shindan_results[$result_slug])) {
    return;
}

$result = $shindan_results[$result_slug];
?>

<div class="shindan-result-card">
    <div class="shindan-result-card__emoji"><?php echo esc_html($result['emoji']); ?></div>
    <div class="shindan-result-card__title"><?php echo esc_html($result['title']); ?></div>
    <p style="font-size:0.75rem; color:var(--color-text-sub); margin-top:var(--space-xs);">
        <?php echo esc_html($result['platform']); ?> で配信中
    </p>
    <p class="shindan-result-card__desc"><?php echo esc_html($result['desc']); ?></p>
</div>
