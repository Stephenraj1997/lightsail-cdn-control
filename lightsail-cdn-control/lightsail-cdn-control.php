<?php
/**
 * Plugin Name: Lightsail CDN Control
 * Plugin URI: https://github.com/yourusername/lightsail-cdn-control
 * Description: Comprehensive AWS Lightsail CDN cache management plugin with manual clearing, automatic cache invalidation on content updates, and scheduled clearing capabilities. Supports multiple post types and flexible scheduling options.
 * Version: 1.0
 * Author: Stephen
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lightsail-cdn-control
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LIGHTSAIL_CDN_VERSION', '1.0');
define('LIGHTSAIL_CDN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LIGHTSAIL_CDN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LIGHTSAIL_CDN_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load required files
require_once LIGHTSAIL_CDN_PLUGIN_DIR . 'includes/class-lightsail-cdn-core.php';
require_once LIGHTSAIL_CDN_PLUGIN_DIR . 'includes/class-lightsail-cdn-aws.php';
require_once LIGHTSAIL_CDN_PLUGIN_DIR . 'includes/class-lightsail-cdn-scheduler.php';
require_once LIGHTSAIL_CDN_PLUGIN_DIR . 'admin/class-lightsail-cdn-admin.php';

/**
 * Initialize the plugin
 *
 * @since 1.0
 */
function lightsail_cdn_control_init() {
    // Initialize the core plugin class
    $plugin = new Lightsail_CDN_Core();
    $plugin->run();
}

// Hook into WordPress init
add_action('plugins_loaded', 'lightsail_cdn_control_init');

/**
 * Activation hook
 *
 * @since 1.0
 */
function lightsail_cdn_control_activate() {
    // Set default options on activation
    add_option('lightsail_auto_clear_enabled', false);
    add_option('lightsail_auto_clear_post_types', array('post', 'page'));
    add_option('lightsail_scheduled_clear_enabled', false);
    add_option('lightsail_scheduled_clear_frequency', 'daily');
    add_option('lightsail_scheduled_clear_time', '00:00');
    add_option('lightsail_scheduled_clear_day', '1');
    add_option('lightsail_scheduled_clear_date', '1');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'lightsail_cdn_control_activate');

/**
 * Deactivation hook
 *
 * @since 1.0
 */
function lightsail_cdn_control_deactivate() {
    // Clear scheduled events
    $timestamp = wp_next_scheduled('lightsail_scheduled_cache_clear');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'lightsail_scheduled_cache_clear');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'lightsail_cdn_control_deactivate');
