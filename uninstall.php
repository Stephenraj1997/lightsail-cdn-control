<?php
/**
 * Uninstall script
 * 
 * Fired when the plugin is uninstalled.
 *
 * @package Lightsail_CDN_Control
 * @since 1.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove all plugin options from database
 */
delete_option('lightsail_auto_clear_enabled');
delete_option('lightsail_auto_clear_post_types');
delete_option('lightsail_scheduled_clear_enabled');
delete_option('lightsail_scheduled_clear_frequency');
delete_option('lightsail_scheduled_clear_time');
delete_option('lightsail_scheduled_clear_day');
delete_option('lightsail_scheduled_clear_date');

/**
 * Clear all plugin transients
 */
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_lightsail_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_lightsail_%'");

/**
 * Clear scheduled cron events
 */
wp_clear_scheduled_hook('lightsail_scheduled_cache_clear');
