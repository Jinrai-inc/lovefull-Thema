<?php
/**
 * 法的ページ自動生成
 *
 * テーマ有効化時に以下の固定ページを自動作成:
 * - 運営会社（company）
 * - 利用規約（terms）
 * - プライバシーポリシー（privacy-policy）
 * - 特定商取引法に基づく表記（tokushoho）
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * テーマ有効化時に法的ページを生成
 */
add_action('after_switch_theme', 'koi_ria_create_legal_pages');

/**
 * 管理画面から手動生成可能
 */
add_action('admin_post_koi_ria_create_legal_pages', function () {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    check_admin_referer('koi_ria_create_legal_pages');

    $result = koi_ria_create_legal_pages();

    set_transient('koi_ria_admin_notice', [
        'type'    => 'success',
        'message' => "法的ページ生成完了: {$result}ページ作成",
    ], 30);

    wp_redirect(admin_url('edit.php?post_type=page'));
    exit;
});

/**
 * 法的ページ一括生成
 */
function koi_ria_create_legal_pages(): int {
    $pages = koi_ria_get_legal_page_data();
    $created = 0;

    foreach ($pages as $slug => $data) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            continue;
        }

        $post_id = wp_insert_post([
            'post_type'    => 'page',
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_content' => $data['content'],
            'post_status'  => 'publish',
            'post_author'  => 1,
        ]);

        if (!is_wp_error($post_id)) {
            // プライバシーポリシーページをWordPress設定に登録
            if ($slug === 'privacy-policy') {
                update_option('wp_page_for_privacy_policy', $post_id);
            }
            $created++;
        }
    }

    return $created;
}

/**
 * 法的ページデータ定義
 */
function koi_ria_get_legal_page_data(): array {
    $site_name = '恋リアポータル';
    $site_url  = home_url('/');

    return [
        // ===== 運営会社 =====
        'company' => [
            'title'   => '運営会社',
            'content' => <<<HTML
<!-- wp:heading -->
<h2>運営会社情報</h2>
<!-- /wp:heading -->

<!-- wp:table {"className":"is-style-stripes"} -->
<figure class="wp-block-table is-style-stripes"><table><tbody>
<tr><th>事業者名</th><td>株式会社仁頼（じんらい） / Jinrai Co., Ltd.</td></tr>
<tr><th>代表者</th><td>齊藤 一樹（さいとう かずき）</td></tr>
<tr><th>所在地</th><td>〒221-0001 神奈川県横浜市神奈川区西寺尾4丁目6番6-3号</td></tr>
<tr><th>設立</th><td>2022年9月</td></tr>
<tr><th>法人番号</th><td>4020001148080</td></tr>
<tr><th>URL</th><td><a href="https://jinrai.co.jp" target="_blank" rel="noopener">https://jinrai.co.jp</a></td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2>事業内容</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>Webメディアの企画・開発・運営</li>
<li>インターネット広告事業</li>
<li>デジタルコンテンツの制作・配信</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>お問い合わせ</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトに関するお問い合わせは、<a href="{$site_url}contact">お問い合わせフォーム</a>よりご連絡ください。</p>
<!-- /wp:paragraph -->
HTML,
        ],

        // ===== 利用規約 =====
        'terms' => [
            'title'   => '利用規約',
            'content' => <<<HTML
<!-- wp:paragraph -->
<p>この利用規約（以下「本規約」）は、株式会社仁頼（以下「当社」）が運営するウェブサイト「{$site_name}」（以下「本サイト」）の利用に関する条件を定めるものです。本サイトをご利用いただく場合は、本規約に同意いただいたものとみなします。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>第1条（適用範囲）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本規約は、本サイトの利用に関する当社とユーザー間のすべての関係に適用されます。当社が本サイト上で別途定める個別規約は、本規約の一部を構成するものとします。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>第2条（定義）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本規約において「ユーザー」とは、本サイトを閲覧・利用するすべての方を指します。「コンテンツ」とは、本サイト上に掲載されるテキスト、画像、動画、データ、プログラム等の情報を指します。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>第3条（禁止事項）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>ユーザーは、本サイトの利用にあたり、以下の行為を行ってはなりません。</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>法令または公序良俗に違反する行為</li>
<li>犯罪行為に関連する行為</li>
<li>当社または第三者の知的財産権、肖像権、プライバシー権等を侵害する行為</li>
<li>当社または第三者の名誉もしくは信用を毀損する行為</li>
<li>本サイトのサーバーまたはネットワークに過度な負荷をかける行為</li>
<li>本サイトの運営を妨害する行為</li>
<li>不正アクセスまたはこれを試みる行為</li>
<li>本サイトの情報を無断で収集・蓄積する行為（スクレイピング等）</li>
<li>他のユーザーに成りすます行為</li>
<li>当社のサービスに関連して反社会的勢力に利益を供与する行為</li>
<li>その他、当社が不適切と判断する行為</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>第4条（本サイトの提供の停止等）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当社は、以下のいずれかの事由がある場合、ユーザーに事前に通知することなく本サイトの全部または一部の提供を停止または中断することができるものとします。</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>本サイトにかかるコンピュータシステムの保守点検または更新を行う場合</li>
<li>地震、落雷、火災、停電または天災等の不可抗力により本サイトの提供が困難となった場合</li>
<li>コンピュータまたは通信回線等が事故により停止した場合</li>
<li>その他、当社が本サイトの提供が困難と判断した場合</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>第5条（著作権・知的財産権）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトに掲載されるコンテンツの著作権その他の知的財産権は、当社または正当な権利を有する第三者に帰属します。ユーザーは、当社の書面による事前の承諾なく、コンテンツの複製、転載、改変、販売、出版等を行うことはできません。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>本サイトに掲載される番組情報、出演者情報等は、各放送局・制作会社・出演者本人が権利を有する場合があり、本サイトでは報道・批評・紹介の範囲内で引用・掲載しています。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>第6条（免責事項）</h2>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>当社は、本サイトに掲載される情報の正確性、完全性、有用性等について保証するものではありません。</li>
<li>当社は、ユーザーが本サイトを利用したことにより生じたいかなる損害についても、一切の責任を負いません。</li>
<li>当社は、本サイトに掲載されるリンク先のウェブサイトの内容について責任を負いません。</li>
<li>本サイトに掲載される番組の放送日時、出演者情報等は、各放送局の公式発表に基づいていますが、変更される場合があります。最新情報は各公式サイトでご確認ください。</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>第7条（広告・アフィリエイト）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトには広告およびアフィリエイトリンクが含まれています。ユーザーがアフィリエイトリンクを通じて商品・サービスを購入された場合、当社は提携先から報酬を受け取ることがあります。これによりユーザーが追加の費用を負担することはありません。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>第8条（本規約の変更）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当社は、必要と判断した場合、ユーザーに個別に通知することなくいつでも本規約を変更することができるものとします。変更後の規約は、本サイトに掲載した時点より効力を生じるものとします。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>第9条（準拠法・裁判管轄）</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本規約の解釈にあたっては、日本法を準拠法とします。本サイトに関して紛争が生じた場合には、横浜地方裁判所を第一審の専属的合意管轄とします。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>制定日:</strong> 2025年1月1日<br><strong>最終更新日:</strong> 2025年1月1日</p>
<!-- /wp:paragraph -->
HTML,
        ],

        // ===== プライバシーポリシー =====
        'privacy-policy' => [
            'title'   => 'プライバシーポリシー',
            'content' => <<<HTML
<!-- wp:paragraph -->
<p>株式会社仁頼（以下「当社」）は、ウェブサイト「{$site_name}」（以下「本サイト」）における個人情報の取り扱いについて、以下のとおりプライバシーポリシー（以下「本ポリシー」）を定めます。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. 個人情報の定義</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本ポリシーにおける「個人情報」とは、個人情報保護法に定義される個人情報を指し、氏名、メールアドレス、IPアドレス、Cookie情報等、特定の個人を識別できる情報を含みます。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. 個人情報の収集方法</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当社は、以下の方法でユーザーの情報を収集する場合があります。</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>お問い合わせフォームからの送信時</li>
<li>アンケート・投票機能の利用時</li>
<li>Cookie およびアクセスログの自動取得</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>3. 個人情報の利用目的</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>収集した個人情報は、以下の目的で利用します。</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>お問い合わせへの対応</li>
<li>本サイトのサービス改善・新機能開発</li>
<li>アクセス分析によるコンテンツ改善</li>
<li>広告配信の最適化</li>
<li>不正利用の防止</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>4. 個人情報の第三者提供</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当社は、以下の場合を除き、ユーザーの個人情報を第三者に提供しません。</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>ユーザーの同意がある場合</li>
<li>法令に基づく場合</li>
<li>人の生命、身体または財産の保護のために必要がある場合</li>
<li>国の機関もしくは地方公共団体またはその委託を受けた者が法令の定める事務を遂行することに対して協力する場合</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>5. Cookie（クッキー）の使用について</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトでは、ユーザー体験の向上および広告配信のためにCookieを使用しています。Cookieとは、ウェブサイトがユーザーのブラウザに送信する小さなテキストファイルです。</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>5-1. Google Analytics</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトでは、Google LLC が提供する「Google Analytics」を使用してアクセス情報を収集しています。Google Analytics はCookieを使用しますが、個人を特定する情報は含みません。収集されたデータはGoogleのプライバシーポリシーに基づいて管理されます。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Google Analytics のプライバシーポリシー: <a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow">https://policies.google.com/privacy</a></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>5-2. Google AdSense</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトでは、Google LLC が提供する広告配信サービス「Google AdSense」を利用しています。Google AdSense はユーザーの興味に基づいた広告を配信するためにCookieを使用することがあります。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Google の広告に関するポリシー: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener nofollow">https://policies.google.com/technologies/ads</a></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>5-3. 投票機能</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトの投票機能では、重複投票を防止するためにCookieを使用しています。投票情報は匿名で集計され、個人を特定する目的では使用しません。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>6. アフィリエイトプログラム</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>本サイトは、以下のアフィリエイトプログラムに参加しています。アフィリエイトリンクを通じて商品・サービスを購入された場合、当社は紹介料を受け取ることがあります。</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Amazonアソシエイト・プログラム</li>
<li>その他各種ASP</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>7. 個人情報の管理</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当社は、個人情報の正確性および安全性を確保するために、セキュリティに十分な対策を講じ、個人情報の漏えい、滅失またはき損の防止に努めます。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>8. 個人情報の開示・訂正・削除</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>ユーザーから個人情報の開示・訂正・削除等の請求があった場合は、ご本人確認のうえ速やかに対応いたします。<a href="{$site_url}contact">お問い合わせフォーム</a>よりご連絡ください。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>9. 本ポリシーの変更</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当社は、必要に応じて本ポリシーを変更することがあります。変更後のポリシーは、本サイトに掲載した時点より効力を生じるものとします。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>10. お問い合わせ窓口</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>個人情報の取り扱いに関するお問い合わせは、以下までご連絡ください。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>株式会社仁頼<br><a href="{$site_url}contact">お問い合わせフォーム</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>制定日:</strong> 2025年1月1日<br><strong>最終更新日:</strong> 2025年1月1日</p>
<!-- /wp:paragraph -->
HTML,
        ],

        // ===== 特定商取引法に基づく表記 =====
        'tokushoho' => [
            'title'   => '特定商取引法に基づく表記',
            'content' => <<<HTML
<!-- wp:heading -->
<h2>特定商取引法に基づく表記</h2>
<!-- /wp:heading -->

<!-- wp:table {"className":"is-style-stripes"} -->
<figure class="wp-block-table is-style-stripes"><table><tbody>
<tr><th>事業者名</th><td>株式会社仁頼（じんらい） / Jinrai Co., Ltd.</td></tr>
<tr><th>代表者</th><td>齊藤 一樹（さいとう かずき）</td></tr>
<tr><th>所在地</th><td>〒221-0001 神奈川県横浜市神奈川区西寺尾4丁目6番6-3号</td></tr>
<tr><th>設立</th><td>2022年9月</td></tr>
<tr><th>法人番号</th><td>4020001148080</td></tr>
<tr><th>電話番号</th><td>お問い合わせフォームよりご連絡ください</td></tr>
<tr><th>メールアドレス</th><td>お問い合わせフォームよりご連絡ください</td></tr>
<tr><th>URL</th><td><a href="https://jinrai.co.jp" target="_blank" rel="noopener">https://jinrai.co.jp</a></td></tr>
<tr><th>商品の販売価格</th><td>各商品・サービスのページに記載</td></tr>
<tr><th>商品代金以外の必要料金</th><td>なし</td></tr>
<tr><th>支払方法</th><td>各サービスページに記載</td></tr>
<tr><th>商品の引渡時期</th><td>各サービスページに記載</td></tr>
<tr><th>返品・キャンセル</th><td>サービスの性質上、提供後の返品・キャンセルはお受けできません</td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p>※ 当サイトは恋愛リアリティ番組の情報メディアであり、動画配信サービス等の紹介はアフィリエイトプログラムを通じて行われます。各動画配信サービスのご利用に関するお問い合わせは、各サービス提供元にお問い合わせください。</p>
<!-- /wp:paragraph -->
HTML,
        ],

        // ===== お問い合わせ =====
        'contact' => [
            'title'   => 'お問い合わせ',
            'content' => <<<HTML
<!-- wp:paragraph -->
<p>{$site_name}に関するお問い合わせは、以下のフォームよりお送りください。内容を確認の上、担当者より折り返しご連絡いたします。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>回答までの目安:</strong> 通常3〜5営業日以内にご返信いたします。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>※ お問い合わせフォームには Contact Form 7 等のプラグインをご利用ください。プラグイン設定後、以下のショートコードを貼り付けてください。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[contact-form-7 id="" title="お問い合わせ"]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>お問い合わせの前に</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>番組の放送日時・内容に関するお問い合わせは、各放送局・配信サービスへ直接お問い合わせください。</li>
<li>出演者個人に関するお問い合わせには対応いたしかねます。</li>
<li>広告掲載・メディア提携に関するお問い合わせも本フォームよりお送りください。</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p><strong>運営会社:</strong> 株式会社仁頼<br><strong>所在地:</strong> 〒221-0001 神奈川県横浜市神奈川区西寺尾4丁目6番6-3号</p>
<!-- /wp:paragraph -->
HTML,
        ],
    ];
}
