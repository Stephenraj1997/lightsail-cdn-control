<?php
/**
 * Scheduler class
 *
 * Handles scheduling of automatic cache clears based on user-defined intervals.
 * Supports daily, weekly, and monthly schedules using WordPress cron.
 *
 * @package Lightsail_CDN_Control
 * @since 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scheduler class
 *
 * @since 1.0
 */
class Lightsail_CDN_Scheduler {
    
    /**
     * AWS handler instance
     *
     * @var Lightsail_CDN_AWS
     */
    private $aws;
    
    /**
     * Constructor
     *
     * @since 1.0
     * @param Lightsail_CDN_AWS $aws AWS handler instance
     */
    public function __construct($aws) {
        $this->aws = $aws;
    }
    
    /**
     * Schedule cron event if needed
     *
     * Checks if scheduling is enabled and creates the cron event if it doesn't exist.
     *
     * @since 1.0
     */
    public function maybe_schedule_event() {
        if (get_option('lightsail_scheduled_clear_enabled', false)) {
            if (!wp_next_scheduled('lightsail_scheduled_cache_clear')) {
                $this->schedule_event();
            }
        }
    }
    
    /**
     * Reschedule event when settings change
     *
     * @since 1.0
     * @param mixed $old_value Previous option value
     * @param mixed $new_value New option value
     */
    public function reschedule_event($old_value = null, $new_value = null) {
        // Clear any existing schedule
        $this->clear_scheduled_event();
        
        // Create new schedule if enabled
        if (get_option('lightsail_scheduled_clear_enabled', false)) {
            $this->schedule_event();
        }
    }
    
    /**
     * Clear scheduled event
     *
     * @since 1.0
     */
    private function clear_scheduled_event() {
        $timestamp = wp_next_scheduled('lightsail_scheduled_cache_clear');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'lightsail_scheduled_cache_clear');
        }
    }
    
    /**
     * Schedule the cache clear event
     *
     * Calculates the next run time based on frequency and creates the WordPress cron event.
     * Uses local timezone from WordPress settings.
     *
     * @since 1.0
     */
    private function schedule_event() {
        $frequency = get_option('lightsail_scheduled_clear_frequency', 'daily');
        $time = get_option('lightsail_scheduled_clear_time', '00:00');
        $day = get_option('lightsail_scheduled_clear_day', '1');
        $date = get_option('lightsail_scheduled_clear_date', '1');
        
        // Parse time (HH:MM format)
        list($hour, $minute) = explode(':', $time);
        $hour = intval($hour);
        $minute = intval($minute);
        
        // Get current time in site's timezone
        $timezone = wp_timezone();
        $now = new DateTime('now', $timezone);
        $scheduled_time = null;
        
        // Calculate next run based on frequency
        switch ($frequency) {
            case 'daily':
                // Create datetime for today at specified time
                $scheduled_time = new DateTime('now', $timezone);
                $scheduled_time->setTime($hour, $minute, 0);
                
                // If time has passed today, schedule for tomorrow
                if ($scheduled_time <= $now) {
                    $scheduled_time->modify('+1 day');
                }
                break;
                
            case 'weekly':
                // Find next occurrence of the specified day
                $target_day = intval($day);
                $current_day = intval($now->format('w'));
                
                // Calculate days until target day
                $days_until = ($target_day - $current_day + 7) % 7;
                
                // Create scheduled time
                $scheduled_time = new DateTime('now', $timezone);
                $scheduled_time->setTime($hour, $minute, 0);
                
                if ($days_until === 0) {
                    // It's the target day - check if time has passed
                    if ($scheduled_time <= $now) {
                        $days_until = 7; // Schedule for next week
                    }
                }
                
                if ($days_until > 0) {
                    $scheduled_time->modify("+{$days_until} days");
                }
                break;
                
            case 'monthly':
                // Schedule for specified date of the month
                $target_date = intval($date);
                
                // Get current month and year
                $scheduled_time = new DateTime('now', $timezone);
                $scheduled_time->setDate(
                    intval($scheduled_time->format('Y')),
                    intval($scheduled_time->format('m')),
                    min($target_date, intval($scheduled_time->format('t'))) // Handle months with fewer days
                );
                $scheduled_time->setTime($hour, $minute, 0);
                
                // If date has passed this month or is today but time passed, go to next month
                if ($scheduled_time <= $now) {
                    $scheduled_time->modify('first day of next month');
                    $scheduled_time->setDate(
                        intval($scheduled_time->format('Y')),
                        intval($scheduled_time->format('m')),
                        min($target_date, intval($scheduled_time->format('t')))
                    );
                    $scheduled_time->setTime($hour, $minute, 0);
                }
                break;
        }
        
        // Convert to UTC timestamp for WordPress cron
        if ($scheduled_time) {
            $timestamp = $scheduled_time->getTimestamp();
            
            // Schedule the event
            if ($frequency === 'monthly') {
                // Monthly uses single event that reschedules itself
                wp_schedule_single_event($timestamp, 'lightsail_scheduled_cache_clear');
            } else {
                // Daily and weekly use recurring events
                wp_schedule_event($timestamp, $frequency, 'lightsail_scheduled_cache_clear');
            }
        }
    }
    
    /**
     * Run the scheduled cache clear
     *
     * This is called by WordPress cron at the scheduled time.
     *
     * @since 1.0
     */
    public function run_scheduled_clear() {
        // Validate credentials
        if (!$this->aws->validate_credentials()) {
            error_log('Lightsail CDN: Scheduled clear failed - Missing credentials');
            return;
        }
        
        // Clear cache
        $result = $this->aws->create_cache_invalidation();
        
        // Log result and store timestamp if successful
        if ($result['success']) {
            update_option('lightsail_last_cache_clear', time());
            error_log('Lightsail CDN: Scheduled clear completed successfully');
        } else {
            error_log('Lightsail CDN: Scheduled clear failed - ' . $result['message']);
        }
        
        // Reschedule for monthly (since WP doesn't have built-in monthly interval)
        $frequency = get_option('lightsail_scheduled_clear_frequency', 'daily');
        if ($frequency === 'monthly') {
            $this->schedule_event();
        }
    }
    
    /**
     * Get next scheduled run time
     *
     * @since 1.0
     * @return int|false Timestamp of next run, or false if not scheduled
     */
    public function get_next_run() {
        return wp_next_scheduled('lightsail_scheduled_cache_clear');
    }
}
