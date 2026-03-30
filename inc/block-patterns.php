<?php
/**
 * ブロックパターン登録
 * 恋リアポータル向けカスタムCSSクラスを使ったパターン
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * パターンカテゴリとパターンを登録
 */
add_action('init', function () {
    // カテゴリ登録
    register_block_pattern_category('koi-ria-portal', [
        'label' => '恋リアポータル',
    ]);

    // --- 見出しパターン ---

    register_block_pattern('koi-ria/heading-ribbon', [
        'title'       => 'リボン見出し',
        'description' => 'リボン風デザインのピンク見出し',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:heading {"className":"heading--ribbon"} --><h2 class="wp-block-heading heading--ribbon">リボン見出しのサンプル</h2><!-- /wp:heading -->',
    ]);

    register_block_pattern('koi-ria/heading-heart', [
        'title'       => 'ハート見出し',
        'description' => 'ハートアイコン付きの見出し',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:heading {"className":"heading--heart"} --><h2 class="wp-block-heading heading--heart">ハート見出しのサンプル</h2><!-- /wp:heading -->',
    ]);

    register_block_pattern('koi-ria/heading-gradient', [
        'title'       => 'グラデーション見出し',
        'description' => 'グラデーション背景の見出し',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:heading {"className":"heading--gradient"} --><h2 class="wp-block-heading heading--gradient">グラデーション見出しのサンプル</h2><!-- /wp:heading -->',
    ]);

    register_block_pattern('koi-ria/heading-bubble', [
        'title'       => '吹き出し見出し',
        'description' => '吹き出し型の見出し',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:heading {"className":"heading--bubble"} --><h2 class="wp-block-heading heading--bubble">吹き出し見出しのサンプル</h2><!-- /wp:heading -->',
    ]);

    // --- ボックスパターン ---

    register_block_pattern('koi-ria/box-pink', [
        'title'       => 'ピンクボックス',
        'description' => 'ピンクボーダーのかわいいボックス',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"box--pink"} --><div class="wp-block-group box--pink"><!-- wp:paragraph --><p>ピンクボックスの中にテキストを入力してください。</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/box-point', [
        'title'       => 'ポイントボックス',
        'description' => '「POINT」ラベル付きの強調ボックス',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"box--point"} --><div class="wp-block-group box--point"><!-- wp:paragraph --><p>ここに重要なポイントを記載します。読者に伝えたい大事な情報を入れましょう！</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/box-caution', [
        'title'       => '注意ボックス',
        'description' => '注意喚起用のオレンジ系ボックス',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"box--caution"} --><div class="wp-block-group box--caution"><!-- wp:paragraph --><p>注意事項やネタバレ警告などに使ってください。</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/box-check', [
        'title'       => 'チェックリストボックス',
        'description' => 'チェックマーク付きリストボックス',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"box--check"} --><div class="wp-block-group box--check"><!-- wp:list --><ul class="wp-block-list"><li>チェック項目1</li><li>チェック項目2</li><li>チェック項目3</li></ul><!-- /wp:list --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/box-quote', [
        'title'       => 'おしゃれ引用ボックス',
        'description' => '大きなクォート記号付きの引用ボックス',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"box--quote"} --><div class="wp-block-group box--quote"><!-- wp:paragraph --><p>引用テキストをここに入力します。印象的な言葉や名言などに。</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/box-ranking', [
        'title'       => 'ランキングボックス',
        'description' => '番号付きランキング風ボックス',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"box--ranking"} --><div class="wp-block-group box--ranking"><!-- wp:list {"ordered":true} --><ol class="wp-block-list"><li>ランキング1位の項目</li><li>ランキング2位の項目</li><li>ランキング3位の項目</li></ol><!-- /wp:list --></div><!-- /wp:group -->',
    ]);

    // --- フキダシパターン ---

    register_block_pattern('koi-ria/balloon-left', [
        'title'       => '左フキダシ',
        'description' => '左からのピンクのフキダシ',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"balloon--left"} --><div class="wp-block-group balloon--left"><!-- wp:paragraph --><p>左からのフキダシです。会話風の演出に使えます！</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/balloon-right', [
        'title'       => '右フキダシ',
        'description' => '右からのパープルのフキダシ',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"balloon--right"} --><div class="wp-block-group balloon--right"><!-- wp:paragraph --><p>右からのフキダシです。相手のセリフに使えます！</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    register_block_pattern('koi-ria/balloon-think', [
        'title'       => '考え中フキダシ',
        'description' => '丸い雲型の考え中フキダシ',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:group {"className":"balloon--think"} --><div class="wp-block-group balloon--think"><!-- wp:paragraph --><p>考え中のフキダシです。心の声の表現に。</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);

    // --- マーカーパターン ---

    register_block_pattern('koi-ria/marker-pink', [
        'title'       => 'ピンクマーカー',
        'description' => 'ピンクの蛍光ペン風マーカー付きテキスト',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:paragraph --><p><span class="marker--pink">ピンクマーカーで強調したいテキスト</span>を含む文章です。</p><!-- /wp:paragraph -->',
    ]);

    register_block_pattern('koi-ria/marker-yellow', [
        'title'       => '黄色マーカー',
        'description' => '黄色の蛍光ペン風マーカー付きテキスト',
        'categories'  => ['koi-ria-portal'],
        'content'     => '<!-- wp:paragraph --><p><span class="marker--yellow">黄色マーカーで強調したいテキスト</span>を含む文章です。</p><!-- /wp:paragraph -->',
    ]);
});
