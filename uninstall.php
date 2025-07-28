<?php

/**
 * Page View Counter Plugin Uninstall
 * 
 * This file is executed when the plugin is deleted via the WordPress admin.
 *
 * @package PageViewCounter
 * @author  SamratEmily
 * @since   1.1.0
 */

// If uninstall not called from WordPress, exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all view count meta data.
global $wpdb;

// Delete all page view counter view count meta.
$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_pvc_view_count'
    )
);

// Delete all page view counter last viewed meta.
$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_pvc_last_viewed'
    )
);

// Clear any cached data.
wp_cache_flush();
