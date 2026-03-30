<?php
/**
 * YouTubeヒーローカルーセル（チャンネル別最新動画スライダー）
 *
 * 各チャンネルの最新動画を黒背景スライダーで表示。
 * 自動再生・スワイプ・矢印ナビ対応。
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

// チャンネル別に最新動画を取得
$all_videos = get_posts([
    'post_type'      => 'youtube_video',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

if (empty($all_videos)) {
    return;
}

// チャンネルごとに最新1件を取得（ピン留め動画は常に含める）
$channel_latest = [];
$pinned_videos  = [];
$seen_channels  = [];

foreach ($all_videos as $video) {
    $video_id = get_field('video_id', $video->ID) ?: '';
    if (!$video_id) continue;

    $is_pinned    = get_field('is_pinned', $video->ID);
    $channel_name = get_field('channel_name', $video->ID) ?: '';

    if ($is_pinned) {
        $pinned_videos[] = $video;
        continue;
    }

    // チャンネル名が空 or 既に取得済みならスキップ
    $channel_key = $channel_name ?: ('_no_channel_' . $video->ID);
    if (isset($seen_channels[$channel_key])) continue;

    $seen_channels[$channel_key] = true;
    $channel_latest[] = $video;
}

// ピン留め動画を先頭に、その後チャンネル別最新動画
$slides = array_merge($pinned_videos, $channel_latest);

// 最大10件に制限
$slides = array_slice($slides, 0, 10);

if (empty($slides)) {
    return;
}
?>

<section class="hero-carousel" id="heroCarousel">
    <div class="hero-carousel__track" id="heroTrack">
        <?php foreach ($slides as $i => $video) :
            $video_id      = get_field('video_id', $video->ID) ?: '';
            $thumbnail_url = get_field('thumbnail_url', $video->ID) ?: '';
            $channel_name  = get_field('channel_name', $video->ID) ?: '';
            $platform      = get_field('platform', $video->ID) ?: '';
            $is_pinned     = get_field('is_pinned', $video->ID);

            // サムネイルが無い場合はYouTubeのデフォルトを使用
            if (!$thumbnail_url && $video_id) {
                $thumbnail_url = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
            }
        ?>
        <div class="hero-carousel__slide" data-video-id="<?php echo esc_attr($video_id); ?>" data-index="<?php echo $i; ?>">
            <?php if ($thumbnail_url) : ?>
                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($video->post_title); ?>" class="hero-carousel__thumb" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
            <?php else : ?>
                <div class="hero-carousel__thumb hero-carousel__thumb--placeholder"><?php echo koi_ria_icon('play', 48); ?></div>
            <?php endif; ?>

            <!-- 再生ボタンオーバーレイ -->
            <div class="hero-carousel__play-btn">
                <?php echo koi_ria_icon('play', 28); ?>
            </div>

            <div class="hero-carousel__overlay">
                <div class="hero-carousel__badges">
                    <?php if ($is_pinned) : ?>
                        <span class="badge badge--pinned"><?php echo koi_ria_icon('star', 12); ?> PICK UP</span>
                    <?php endif; ?>
                    <?php if ($platform) : ?>
                        <span class="badge badge--<?php echo esc_attr(sanitize_title($platform)); ?>"><?php echo esc_html($platform); ?></span>
                    <?php endif; ?>
                </div>
                <p class="hero-carousel__title"><?php echo esc_html($video->post_title); ?></p>
                <?php if ($channel_name) : ?>
                    <p class="hero-carousel__channel"><?php echo koi_ria_icon('youtube', 14); ?> <?php echo esc_html($channel_name); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 左右矢印ナビ -->
    <?php if (count($slides) > 1) : ?>
    <button class="hero-carousel__arrow hero-carousel__arrow--prev" id="heroPrev" aria-label="前の動画">
        <?php echo koi_ria_icon('arrow-right', 20); ?>
    </button>
    <button class="hero-carousel__arrow hero-carousel__arrow--next" id="heroNext" aria-label="次の動画">
        <?php echo koi_ria_icon('arrow-right', 20); ?>
    </button>
    <?php endif; ?>

    <!-- ドットインジケーター -->
    <div class="hero-carousel__dots" id="heroDots">
        <?php foreach ($slides as $i => $video) : ?>
            <button class="hero-carousel__dot <?php echo $i === 0 ? 'is-active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="スライド<?php echo $i + 1; ?>"></button>
        <?php endforeach; ?>
    </div>

    <!-- スライドカウンター -->
    <div class="hero-carousel__counter" id="heroCounter">
        <span id="heroCounterCurrent">1</span> / <?php echo count($slides); ?>
    </div>
</section>
