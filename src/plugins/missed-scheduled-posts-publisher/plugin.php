<?php

/**
 * Plugin Name:       Missed Scheduled Posts Publisher by WPBeginner
 * Description:       Catches scheduled posts that have been missed and publishes them.
 * Version:           2.0.0
 * Requires at least: 5.0
 * Tested up to:      6.3.1
 * Requires PHP:      5.6
 * Author:            WPBeginner
 * Author URI:        https://www.wpbeginner.com/
 * License:           GPLv2
 * Text Domain:       missed-scheduled-posts-publisher
 */

namespace WPB\MissedScheduledPostsPublisher;

require_once __DIR__ . '/inc/Review.php';
add_action('plugins_loaded', function () {
    (new Review())->load_hooks();
});

require_once __DIR__ . '/inc/namespace.php';
bootstrap();
