<?php
/**
 * Admin interface class
 *
 * Handles the WordPress admin interface for the plugin,
 * including settings pages, forms, and cache clearing actions.
 *
 * @package Lightsail_CDN_Control
 * @since 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class
 *
 * @since 1.0
 */
class Lightsail_CDN_Admin {
    
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
     * Add admin menu page
     *
     * @since 1.0
     */
    public function add_admin_menu() {
        add_options_page(
            __('Lightsail CDN Control', 'lightsail-cdn-control'),
            __('CDN Control', 'lightsail-cdn-control'),
            'manage_options',
            'lightsail-cdn-control',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     *
     * @since 1.0
     */
    public function register_settings() {
        // Auto-clear settings
        register_setting('lightsail_cdn_settings', 'lightsail_auto_clear_enabled');
        register_setting('lightsail_cdn_settings', 'lightsail_auto_clear_post_types', array(
            'type' => 'array',
            'default' => array('post', 'page')
        ));
        
        // Scheduled clear settings
        register_setting('lightsail_cdn_settings', 'lightsail_scheduled_clear_enabled');
        register_setting('lightsail_cdn_settings', 'lightsail_scheduled_clear_frequency');
        register_setting('lightsail_cdn_settings', 'lightsail_scheduled_clear_time');
        register_setting('lightsail_cdn_settings', 'lightsail_scheduled_clear_day');
        register_setting('lightsail_cdn_settings', 'lightsail_scheduled_clear_date');
    }
    
    /**
     * Enqueue admin assets
     *
     * @since 1.0
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if ($hook !== 'settings_page_lightsail-cdn-control') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'lightsail-cdn-admin',
            LIGHTSAIL_CDN_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LIGHTSAIL_CDN_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'lightsail-cdn-admin',
            LIGHTSAIL_CDN_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            LIGHTSAIL_CDN_VERSION,
            true
        );
        
        // Pass data to JavaScript
        $next_run = wp_next_scheduled('lightsail_scheduled_cache_clear');
        wp_localize_script('lightsail-cdn-admin', 'lightsailCDN', array(
            'nextRun' => $next_run ? $next_run : 0,
            'timezone' => wp_timezone_string()
        ));
    }
    
    /**
     * Render the settings page
     *
     * @since 1.0
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current settings
        $auto_clear_enabled = get_option('lightsail_auto_clear_enabled', false);
        $selected_post_types = get_option('lightsail_auto_clear_post_types', array('post', 'page'));
        $scheduled_enabled = get_option('lightsail_scheduled_clear_enabled', false);
        $scheduled_frequency = get_option('lightsail_scheduled_clear_frequency', 'daily');
        $scheduled_time = get_option('lightsail_scheduled_clear_time', '00:00');
        $scheduled_day = get_option('lightsail_scheduled_clear_day', '1');
        $scheduled_date = get_option('lightsail_scheduled_clear_date', '1');
        $next_run = wp_next_scheduled('lightsail_scheduled_cache_clear');
        
        // Ensure post types is an array
        if (!is_array($selected_post_types)) {
            $selected_post_types = array();
        }
        
        // Get all public post types
        $post_types = get_post_types(array('public' => true), 'objects');
        
        // Get AWS credentials status
        $creds = $this->aws->get_credentials_status();
        
        ?>
        <div class="wrap lightsail-cdn-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php $this->display_notices(); ?>
            
            <div class="lightsail-cdn-grid">
                
                <!-- Left Column -->
                <div class="lightsail-cdn-column lightsail-cdn-column-left">
                    
                    <!-- Manual Clear Card -->
                    <div class="lightsail-cdn-card">
                        <h2>💨 <?php _e('Manual Cache Clear', 'lightsail-cdn-control'); ?></h2>
                        <p><?php _e('Clear all cached content on your Lightsail CDN distribution immediately.', 'lightsail-cdn-control'); ?></p>
                        
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('<?php esc_attr_e('Clear the entire CDN cache?', 'lightsail-cdn-control'); ?>');">
                            <?php wp_nonce_field('clear_lightsail_cache_action', 'clear_lightsail_cache_nonce'); ?>
                            <input type="hidden" name="action" value="clear_lightsail_cache">
                            <?php submit_button(__('Clear CDN Cache Now', 'lightsail-cdn-control'), 'primary', 'submit', false); ?>
                        </form>
                    </div>
                    
                    <!-- Auto-Clear Settings Card -->
                    <div class="lightsail-cdn-card">
                        <h2>⚡ <?php _e('Auto-Clear Settings', 'lightsail-cdn-control'); ?></h2>
                        <p><?php _e('Automatically clear CDN cache when content is updated.', 'lightsail-cdn-control'); ?></p>
                        
                        <form method="post" action="options.php">
                            <?php settings_fields('lightsail_cdn_settings'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="lightsail_auto_clear_enabled"><?php _e('Enable Auto-Clear', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <label class="lightsail-toggle">
                                            <input type="checkbox" 
                                                   id="lightsail_auto_clear_enabled" 
                                                   name="lightsail_auto_clear_enabled" 
                                                   value="1" 
                                                   <?php checked($auto_clear_enabled, true); ?>>
                                            <span class="lightsail-toggle-slider"></span>
                                        </label>
                                        <p class="description">
                                            <?php _e('When enabled, CDN cache will automatically clear when you publish or update selected content types.', 'lightsail-cdn-control'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr id="post-types-row" style="<?php echo $auto_clear_enabled ? '' : 'display: none;'; ?>">
                                    <th scope="row">
                                        <label><?php _e('Post Types', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text"><span><?php _e('Post Types', 'lightsail-cdn-control'); ?></span></legend>
                                            
                                            <div style="margin-bottom: 12px;">
                                                <button type="button" id="select-all-post-types" class="button button-small"><?php _e('Select All', 'lightsail-cdn-control'); ?></button>
                                                <button type="button" id="deselect-all-post-types" class="button button-small"><?php _e('Deselect All', 'lightsail-cdn-control'); ?></button>
                                            </div>
                                            
                                            <div id="post-types-list">
                                                <?php foreach ($post_types as $post_type): ?>
                                                    <label style="display: block; margin-bottom: 8px;">
                                                        <input type="checkbox" 
                                                               class="post-type-checkbox"
                                                               name="lightsail_auto_clear_post_types[]" 
                                                               value="<?php echo esc_attr($post_type->name); ?>"
                                                               <?php checked(in_array($post_type->name, $selected_post_types)); ?>>
                                                        <strong><?php echo esc_html($post_type->label); ?></strong>
                                                        <span style="color: #666; font-size: 12px;">(<?php echo esc_html($post_type->name); ?>)</span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <p class="description">
                                                <?php _e('Select which content types should trigger automatic cache clearing.', 'lightsail-cdn-control'); ?>
                                            </p>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(__('Save Settings', 'lightsail-cdn-control'), 'secondary'); ?>
                        </form>
                    </div>
                    
                </div>
                
                <!-- Right Column -->
                <div class="lightsail-cdn-column lightsail-cdn-column-right">
                    
                    <!-- Scheduled Clear Card -->
                    <div class="lightsail-cdn-card">
                        <h2>🕒 <?php _e('Scheduled Auto-Clear', 'lightsail-cdn-control'); ?></h2>
                        <p><?php _e('Set up automatic cache clearing on a recurring schedule.', 'lightsail-cdn-control'); ?></p>
                        
                        <?php 
                        $last_cleared = get_option('lightsail_last_cache_clear');
                        if ($last_cleared): 
                            $timezone = wp_timezone();
                            $dt = new DateTime('@' . $last_cleared);
                            $dt->setTimezone($timezone);
                        ?>
                            <div class="lightsail-last-cleared-info" style="margin-bottom: 15px; padding: 10px; background: #f0f0f1; border-left: 3px solid #2271b1; border-radius: 3px;">
                                <strong>📅 <?php _e('Last cache cleared:', 'lightsail-cdn-control'); ?></strong>
                                <div style="margin-top: 5px; font-size: 14px;">
                                    <?php echo esc_html($dt->format('F j, Y @ g:i A')); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($scheduled_enabled && $next_run): ?>
                            <div class="lightsail-next-run-info">
                                <strong>⏰ <?php _e('Next scheduled clear:', 'lightsail-cdn-control'); ?></strong>
                                <div id="countdown-timer" class="lightsail-countdown">
                                    <?php _e('Calculating...', 'lightsail-cdn-control'); ?>
                                </div>
                                <div class="lightsail-next-run-date">
                                    <?php 
                                    $timezone = wp_timezone();
                                    $dt = new DateTime('@' . $next_run);
                                    $dt->setTimezone($timezone);
                                    echo esc_html($dt->format('F j, Y @ g:i A'));
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="options.php">
                            <?php settings_fields('lightsail_cdn_settings'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="lightsail_scheduled_clear_enabled"><?php _e('Enable Scheduled Clear', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <label class="lightsail-toggle">
                                            <input type="checkbox" 
                                                   id="lightsail_scheduled_clear_enabled" 
                                                   name="lightsail_scheduled_clear_enabled" 
                                                   value="1" 
                                                   <?php checked($scheduled_enabled, true); ?>>
                                            <span class="lightsail-toggle-slider"></span>
                                        </label>
                                        <p class="description">
                                            <?php _e('When enabled, CDN cache will be cleared automatically on a schedule.', 'lightsail-cdn-control'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr id="schedule-settings-row" style="<?php echo $scheduled_enabled ? '' : 'display: none;'; ?>">
                                    <th scope="row">
                                        <label for="lightsail_scheduled_clear_frequency"><?php _e('Frequency', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <select name="lightsail_scheduled_clear_frequency" id="lightsail_scheduled_clear_frequency">
                                            <option value="daily" <?php selected($scheduled_frequency, 'daily'); ?>><?php _e('Daily', 'lightsail-cdn-control'); ?></option>
                                            <option value="weekly" <?php selected($scheduled_frequency, 'weekly'); ?>><?php _e('Weekly', 'lightsail-cdn-control'); ?></option>
                                            <option value="monthly" <?php selected($scheduled_frequency, 'monthly'); ?>><?php _e('Monthly', 'lightsail-cdn-control'); ?></option>
                                        </select>
                                        <p class="description"><?php _e('How often to clear the cache.', 'lightsail-cdn-control'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr id="schedule-day-row" style="<?php echo ($scheduled_enabled && $scheduled_frequency === 'weekly') ? '' : 'display: none;'; ?>">
                                    <th scope="row">
                                        <label for="lightsail_scheduled_clear_day"><?php _e('Day of Week', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <select name="lightsail_scheduled_clear_day" id="lightsail_scheduled_clear_day">
                                            <option value="0" <?php selected($scheduled_day, '0'); ?>><?php _e('Sunday', 'lightsail-cdn-control'); ?></option>
                                            <option value="1" <?php selected($scheduled_day, '1'); ?>><?php _e('Monday', 'lightsail-cdn-control'); ?></option>
                                            <option value="2" <?php selected($scheduled_day, '2'); ?>><?php _e('Tuesday', 'lightsail-cdn-control'); ?></option>
                                            <option value="3" <?php selected($scheduled_day, '3'); ?>><?php _e('Wednesday', 'lightsail-cdn-control'); ?></option>
                                            <option value="4" <?php selected($scheduled_day, '4'); ?>><?php _e('Thursday', 'lightsail-cdn-control'); ?></option>
                                            <option value="5" <?php selected($scheduled_day, '5'); ?>><?php _e('Friday', 'lightsail-cdn-control'); ?></option>
                                            <option value="6" <?php selected($scheduled_day, '6'); ?>><?php _e('Saturday', 'lightsail-cdn-control'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr id="schedule-date-row" style="<?php echo ($scheduled_enabled && $scheduled_frequency === 'monthly') ? '' : 'display: none;'; ?>">
                                    <th scope="row">
                                        <label for="lightsail_scheduled_clear_date"><?php _e('Day of Month', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <select name="lightsail_scheduled_clear_date" id="lightsail_scheduled_clear_date">
                                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php selected($scheduled_date, (string)$i); ?>>
                                                    <?php echo $i; ?><?php echo $this->get_ordinal_suffix($i); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <p class="description"><?php _e('Select the day of the month (1-31). If a month has fewer days, the last day will be used.', 'lightsail-cdn-control'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr id="schedule-time-row" style="<?php echo $scheduled_enabled ? '' : 'display: none;'; ?>">
                                    <th scope="row">
                                        <label for="lightsail_scheduled_clear_time"><?php _e('Time', 'lightsail-cdn-control'); ?></label>
                                    </th>
                                    <td>
                                        <input type="time" 
                                               name="lightsail_scheduled_clear_time" 
                                               id="lightsail_scheduled_clear_time" 
                                               value="<?php echo esc_attr($scheduled_time); ?>">
                                        <p class="description">
                                            <?php 
                                            printf(
                                                __('Time of day to run the cache clear (Timezone: %s)', 'lightsail-cdn-control'),
                                                '<strong>' . esc_html(wp_timezone_string()) . '</strong>'
                                            ); 
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(__('Save Schedule', 'lightsail-cdn-control'), 'secondary'); ?>
                        </form>
                    </div>
                    
                    <!-- Configuration Card -->
                    <div class="lightsail-cdn-card">
                        <h3>⚙️ <?php _e('Configuration', 'lightsail-cdn-control'); ?></h3>
                        
                        <div class="lightsail-config-status">
                            <p>
                                <strong><?php _e('Status:', 'lightsail-cdn-control'); ?></strong><br>
                                <?php if ($creds['configured']): ?>
                                    <span class="lightsail-status-badge lightsail-status-success">✓ <?php _e('Configured', 'lightsail-cdn-control'); ?></span>
                                <?php else: ?>
                                    <span class="lightsail-status-badge lightsail-status-error">✗ <?php _e('Not Configured', 'lightsail-cdn-control'); ?></span>
                                <?php endif; ?>
                            </p>
                            
                            <p><strong><?php _e('Distribution:', 'lightsail-cdn-control'); ?></strong><br>
                            <?php echo esc_html($creds['distribution_name'] ?: __('Not configured', 'lightsail-cdn-control')); ?></p>
                            
                            <p><strong><?php _e('Region:', 'lightsail-cdn-control'); ?></strong><br>
                            <?php echo esc_html($creds['region']); ?></p>
                        </div>
                        
                        <hr style="margin: 15px 0;">
                        
                        <p style="font-size: 13px; margin-bottom: 10px;">
                            <strong><?php _e('Add to wp-config.php:', 'lightsail-cdn-control'); ?></strong>
                        </p>
                        <pre class="lightsail-code-block">
define('AWS_ACCESS_KEY_ID', 'your_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key');
define('AWS_DEFAULT_REGION', 'us-east-1');
define('LIGHTSAIL_DISTRIBUTION_NAME', 'your_distribution_name');</pre>
                    </div>
                    
                </div>
                
            </div>
        </div>
        <?php
    }
    
    /**
     * Display admin notices
     *
     * @since 1.0
     */
    private function display_notices() {
        $user_id = get_current_user_id();
        
        // Check for cache cleared transient
        if (get_transient('lightsail_cache_cleared_' . $user_id)) {
            $last_cleared = get_option('lightsail_last_cache_clear');
            $time_display = $last_cleared ? wp_date('F j, Y g:i A', $last_cleared) : '';
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong><?php _e('Success!', 'lightsail-cdn-control'); ?></strong> 
                    <?php _e('CDN cache cleared successfully.', 'lightsail-cdn-control'); ?>
                    <?php if ($time_display): ?>
                        <span style="margin-left: 10px; color: #666;">
                            <?php printf(__('Last cleared: %s', 'lightsail-cdn-control'), $time_display); ?>
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <?php
            delete_transient('lightsail_cache_cleared_' . $user_id);
        }
        
        // Check for error transient
        if ($error = get_transient('lightsail_cache_error_' . $user_id)) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php _e('Error:', 'lightsail-cdn-control'); ?></strong> <?php echo esc_html($error); ?></p>
            </div>
            <?php
            delete_transient('lightsail_cache_error_' . $user_id);
        }
        
        // Settings saved notice
        if (isset($_GET['settings_saved'])) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php _e('Settings saved!', 'lightsail-cdn-control'); ?></strong></p>
            </div>
            <?php
        }
    }
    
    /**
     * Handle manual cache clear request
     *
     * @since 1.0
     */
    public function handle_cache_clear() {
        // Verify nonce
        if (!isset($_POST['clear_lightsail_cache_nonce']) || 
            !wp_verify_nonce($_POST['clear_lightsail_cache_nonce'], 'clear_lightsail_cache_action')) {
            wp_die(__('Security check failed', 'lightsail-cdn-control'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'lightsail-cdn-control'));
        }
        
        // Clear cache
        $result = $this->aws->create_cache_invalidation();
        
        $user_id = get_current_user_id();
        
        // Store result in transient (expires in 30 seconds)
        if ($result['success']) {
            // Store timestamp of last successful clear
            update_option('lightsail_last_cache_clear', time());
            set_transient('lightsail_cache_cleared_' . $user_id, true, 30);
        } else {
            set_transient('lightsail_cache_error_' . $user_id, $result['message'], 30);
        }
        
        // Redirect without URL parameters
        wp_redirect(admin_url('options-general.php?page=lightsail-cdn-control'));
        exit;
    }
    
    /**
     * Auto-clear cache on post update
     *
     * @since 1.0
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     * @param bool    $update  Whether this is an update
     */
    public function auto_clear_on_update($post_id, $post, $update) {
        // Don't run on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Only for published posts
        if ($post->post_status !== 'publish') {
            return;
        }
        
        // Get selected post types
        $selected_post_types = get_option('lightsail_auto_clear_post_types', array('post', 'page'));
        
        // Ensure it's an array
        if (!is_array($selected_post_types) || empty($selected_post_types)) {
            return;
        }
        
        // Check if this post type is selected
        if (!in_array($post->post_type, $selected_post_types)) {
            return;
        }
        
        // Clear cache
        $result = $this->aws->create_cache_invalidation();
        
        // Store result for notice
        $user_id = get_current_user_id();
        if ($result['success']) {
            // Store timestamp of last successful clear
            update_option('lightsail_last_cache_clear', time());
            set_transient('lightsail_auto_clear_success_' . $user_id, true, 30);
        } else {
            set_transient('lightsail_auto_clear_error_' . $user_id, $result['message'], 30);
        }
    }
    
    /**
     * Show auto-clear admin notice
     *
     * @since 1.0
     */
    public function show_auto_clear_notice() {
        $user_id = get_current_user_id();
        
        if (get_transient('lightsail_auto_clear_success_' . $user_id)) {
            echo '<div class="notice notice-success is-dismissible"><p>🚀 <strong>' . 
                 __('CDN cache cleared automatically', 'lightsail-cdn-control') . 
                 '</strong></p></div>';
            delete_transient('lightsail_auto_clear_success_' . $user_id);
        }
        
        if ($error = get_transient('lightsail_auto_clear_error_' . $user_id)) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . 
                 __('CDN auto-clear failed:', 'lightsail-cdn-control') . 
                 '</strong> ' . esc_html($error) . '</p></div>';
            delete_transient('lightsail_auto_clear_error_' . $user_id);
        }
    }
    
    /**
     * Get ordinal suffix for numbers
     *
     * @since 1.0
     * @param int $number Number
     * @return string Ordinal suffix
     */
    private function get_ordinal_suffix($number) {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return 'th';
        } else {
            return $ends[$number % 10];
        }
    }
}
