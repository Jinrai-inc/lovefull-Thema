<?php
/**
 * SVG Icon Helper
 *
 * Inline SVG icons for the theme.
 * Style: thin stroke, rounded caps, cute/feminine aesthetic.
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

/**
 * Return inline SVG markup for a named icon.
 *
 * @param string $name Icon name (e.g. 'heart', 'tv').
 * @param int    $size Width and height in px (default 20).
 * @return string SVG markup or empty string if icon not found.
 */
function koi_ria_icon(string $name, int $size = 20): string {
    $icons = [
        // Heart outline
        'heart' => '<path d="M12 21s-7-4.35-9-8C1 9 3.5 5 7 5c2 0 3.5 1.5 5 3.5C13.5 6.5 15 5 17 5c3.5 0 6 4 4 8-2 3.65-9 8-9 8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Heart filled
        'heart-filled' => '<path d="M12 21s-7-4.35-9-8C1 9 3.5 5 7 5c2 0 3.5 1.5 5 3.5C13.5 6.5 15 5 17 5c3.5 0 6 4 4 8-2 3.65-9 8-9 8z" fill="currentColor"/>',

        // TV / monitor
        'tv' => '<rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Users (two people)
        'users' => '<circle cx="9" cy="7" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M3 20c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="17" cy="8" r="2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M21 20c0-2.76-2.24-5-5-5-.7 0-1.37.14-1.98.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Calendar
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Search (magnifying glass)
        'search' => '<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Home
        'home' => '<path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 21v-7h6v7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Bar chart
        'chart' => '<path d="M4 20h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><rect x="5" y="10" width="3" height="10" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="10.5" y="4" width="3" height="16" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="16" y="8" width="3" height="12" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Star
        'star' => '<path d="M12 2l2.94 5.96L21 8.87l-4.5 4.38L17.58 20 12 17.27 6.42 20l1.08-6.75L3 8.87l6.06-.91L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Clock
        'clock' => '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Play button
        'play' => '<path d="M6 4l14 8-14 8V4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Arrow right / chevron
        'arrow-right' => '<path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Hamburger menu
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Close (X)
        'close' => '<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Instagram (camera)
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>',

        // TikTok (music note style)
        'tiktok' => '<path d="M9 12V4a8 8 0 0 0 8 4V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="16" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M17 4v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // YouTube (play in rounded rect)
        'youtube' => '<rect x="2" y="4" width="20" height="16" rx="4" stroke="currentColor" stroke-width="1.5"/><path d="M10 9l5 3-5 3V9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Twitter / X
        'twitter' => '<path d="M4 4l7.2 8.4M20 4l-7.2 8.4m0 0L20 20M11.2 12.4L4 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Sparkle / glitter
        'sparkle' => '<path d="M12 2l1.5 5.5L19 9l-5.5 1.5L12 16l-1.5-5.5L5 9l5.5-1.5L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 14l.75 2.25L21 17l-2.25.75L18 20l-.75-2.25L15 17l2.25-.75L18 14z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Fire / flame
        'fire' => '<path d="M12 2c0 4-4 6-4 10a6 6 0 0 0 6 6 6 6 0 0 0 6-6c0-4-4-6-4-10-1 2-3 3-4 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22a3 3 0 0 1-3-3c0-2 3-4 3-4s3 2 3 4a3 3 0 0 1-3 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Crown
        'crown' => '<path d="M2 17l3-8 4 4 3-8 3 8 4-4 3 8H2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 17h20v3H2v-3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Couple (two hearts)
        'couple' => '<path d="M8.5 15s-4.5-2.8-5.8-5.2C1.5 7.5 3.2 5 5.5 5c1.3 0 2.2 1 3 2.2.8-1.2 1.7-2.2 3-2.2 2.3 0 4 2.5 2.7 4.8C13 12.2 8.5 15 8.5 15z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.5 20s-4.5-2.8-5.8-5.2C8.5 12.5 10.2 10 12.5 10c1.3 0 2.2 1 3 2.2.8-1.2 1.7-2.2 3-2.2 2.3 0 4 2.5 2.7 4.8-1.3 2.4-5.7 5.2-5.7 5.2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // News / newspaper
        'news' => '<rect x="2" y="3" width="16" height="18" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 8h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 7h8M6 11h8M6 15h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Column / pen (writing)
        'column' => '<path d="M17 3l4 4L8.5 19.5 3 21l1.5-5.5L17 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.5 5.5l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Vote / ballot check
        'vote' => '<rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 5l6-3 6 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 13l2.5 2.5L16 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',

        // External link
        'link' => '<path d="M10 4H4v16h16v-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2h8v8M22 2L11 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Share
        'share' => '<circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/><circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/><circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M8.7 13.5l6.6 4M15.3 6.5l-6.6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',

        // Bookmark ribbon
        'bookmark' => '<path d="M5 3h14a1 1 0 0 1 1 1v18l-8-4-8 4V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Compass (explore)
        'compass' => '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><polygon points="16.24,7.76 14.12,14.12 7.76,16.24 9.88,9.88" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',

        // Grid squares
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
    ];

    $svg = $icons[$name] ?? '';
    if (!$svg) {
        return '';
    }

    return '<svg class="icon icon--' . esc_attr($name) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $svg . '</svg>';
}
