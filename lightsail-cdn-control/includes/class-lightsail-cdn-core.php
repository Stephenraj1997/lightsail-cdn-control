<?php
/**
 * Core plugin class
 *
 * This is the main class that coordinates all plugin functionality.
 * It initializes the admin interface, AWS communication, and scheduler.
 *
 * @package Lightsail_CDN_Control
 * @since 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 *
 * @since 1.0
 */
class Lightsail_CDN_Core {
    
    /**
     * Plugin admin instance
     *
     * @var Lightsail_CDN_Admin
     */
    protected $admin;
    
    /**
     * AWS handler instance
     *
     * @var Lightsail_CDN_AWS
     */
    protected $aws;
    
    /**
     * Scheduler instance
     *
     * @var Lightsail_CDN_Scheduler
     */
    protected $scheduler;
    
    /**
     * Initialize the plugin
     *
     * @since 1.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_scheduler_hooks();
    }
    
    /**
     * Load plugin dependencies
     *
     * @since 1.0
     */
    private function load_dependencies() {
        // Initialize AWS handler
        $this->aws = new Lightsail_CDN_AWS();
        
        // Initialize admin interface
        $this->admin = new Lightsail_CDN_Admin($this->aws);
        
        // Initialize scheduler
        $this->scheduler = new Lightsail_CDN_Scheduler($this->aws);
    }
    
    /**
     * Register admin hooks
     *
     * @since 1.0
     */
    private function define_admin_hooks() {
        // Register admin menu
        add_action('admin_menu', array($this->admin, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this->admin, 'register_settings'));
        
        // Handle cache clear action
        add_action('admin_post_clear_lightsail_cache', array($this->admin, 'handle_cache_clear'));
        
        // Enqueue admin styles and scripts
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
        
        // Auto-clear functionality
        if (get_option('lightsail_auto_clear_enabled', false)) {
            add_action('save_post', array($this->admin, 'auto_clear_on_update'), 10, 3);
            add_action('admin_notices', array($this->admin, 'show_auto_clear_notice'));
        }
    }
    
    /**
     * Register scheduler hooks
     *
     * @since 1.0
     */
    private function define_scheduler_hooks() {
        // Watch for settings changes that affect scheduling
        add_action('update_option_lightsail_scheduled_clear_enabled', array($this->scheduler, 'reschedule_event'), 10, 2);
        add_action('update_option_lightsail_scheduled_clear_frequency', array($this->scheduler, 'reschedule_event'), 10, 2);
        add_action('update_option_lightsail_scheduled_clear_time', array($this->scheduler, 'reschedule_event'), 10, 2);
        add_action('update_option_lightsail_scheduled_clear_day', array($this->scheduler, 'reschedule_event'), 10, 2);
        add_action('update_option_lightsail_scheduled_clear_date', array($this->scheduler, 'reschedule_event'), 10, 2);
        
        // Register the scheduled event handler
        add_action('lightsail_scheduled_cache_clear', array($this->scheduler, 'run_scheduled_clear'));
    }
    
    /**
     * Run the plugin
     *
     * @since 1.0
     */
    public function run() {
        // Initialize scheduler if needed
        $this->scheduler->maybe_schedule_event();
    }
}
