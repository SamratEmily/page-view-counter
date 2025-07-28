<?php
/*
Plugin Name: Page View Counter - Analytics Free
Description: A lightweight WordPress plugin that tracks page and post views without using external analytics or cookies. Privacy-friendly view tracking with admin dashboard.
Version: 1.1.0
Author: SamratEmily
Author URI: https://github.com/samratemily
Text Domain: page-view-counter
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'PageViewCounter.php';

// Initialize the plugin
new \PageViewCounter();

// Activation hook
register_activation_hook(__FILE__, 'page_view_counter_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'page_view_counter_deactivate');

/**
 * Plugin activation callback.
 */
function page_view_counter_activate()
{
    // Flush rewrite rules to ensure custom post type URLs work
    flush_rewrite_rules();
}

/**
 * Plugin deactivation callback.
 */
function page_view_counter_deactivate()
{
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
