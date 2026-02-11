=== Lightsail CDN Control ===
Contributors: stephen
Tags: aws, lightsail, cdn, cache, cloudfront, performance, optimization
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Comprehensive AWS Lightsail CDN cache management with manual clearing, automatic invalidation on content updates, and flexible scheduling.

== Description ==

Lightsail CDN Control is a powerful yet user-friendly plugin that gives you complete control over your AWS Lightsail CDN cache directly from your WordPress admin panel. Whether you need to clear your cache immediately, automatically invalidate it when content changes, or schedule regular cache clearing, this plugin has you covered.

= Key Features =

**Manual Cache Clearing**
* One-click cache invalidation from WordPress admin
* Instant feedback on cache clearing status
* Perfect for when you need immediate control

**Automatic Cache Clearing**
* Automatically clear CDN cache when content is published or updated
* Select which post types trigger cache clearing
* Support for custom post types
* Immediate content updates across your CDN

**Scheduled Cache Clearing**
* Set up recurring cache clears on your schedule
* Daily, weekly, or monthly options
* Choose specific times and days
* Live countdown timer showing next scheduled clear
* Uses your WordPress timezone settings

**Easy Configuration**
* Simple wp-config.php based credentials
* Clear status indicators
* Helpful configuration examples
* Secure AWS Signature Version 4 authentication

= Perfect For =

* WordPress sites using AWS Lightsail CDN
* Content publishers who need fresh content delivered fast
* Developers who want automated cache management
* Sites with frequent content updates
* Anyone wanting more control over their CDN

= Requirements =

* AWS Lightsail distribution with CDN enabled
* AWS Access Key ID and Secret Access Key with Lightsail permissions
* WordPress 5.0 or higher
* PHP 7.2 or higher

= Documentation =

Full setup instructions and documentation included. Easy 5-minute setup with wp-config.php constants.

== Installation ==

= From WordPress Admin =

1. Go to Plugins > Add New
2. Search for "Lightsail CDN Control"
3. Click "Install Now" and then "Activate"
4. Configure your AWS credentials in wp-config.php
5. Go to Settings > CDN Control to manage your cache

= Manual Installation =

1. Download the plugin ZIP file
2. Upload to /wp-content/plugins/ directory
3. Extract the ZIP file
4. Activate the plugin through WordPress admin
5. Configure AWS credentials (see Configuration section)

= Configuration =

Add these constants to your wp-config.php file:

`define('AWS_ACCESS_KEY_ID', 'your_access_key_here');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key_here');
define('AWS_DEFAULT_REGION', 'us-east-1');
define('LIGHTSAIL_DISTRIBUTION_NAME', 'your_distribution_name');`

**Finding Your Distribution Name:**
1. Log in to AWS Lightsail console
2. Go to Networking > CDN distributions
3. Find your distribution and copy its name

**Creating AWS Credentials:**
1. Go to AWS IAM console
2. Create a new user or use existing one
3. Attach Lightsail permissions
4. Generate access keys
5. Add keys to wp-config.php

== Frequently Asked Questions ==

= What is AWS Lightsail CDN? =

AWS Lightsail CDN is a content delivery network service from Amazon Web Services that caches your website content at edge locations worldwide, making your site faster for visitors globally.

= Do I need an AWS account? =

Yes, you need an AWS account with an active Lightsail distribution and appropriate permissions.

= How do I get my AWS credentials? =

You can create AWS credentials (Access Key ID and Secret Access Key) from the AWS IAM console. Make sure the credentials have permissions to manage Lightsail distributions.

= Is this plugin secure? =

Yes, the plugin uses AWS Signature Version 4 for secure authentication. Your credentials are stored in wp-config.php (not in the database) and are never exposed in the admin interface.

= Does this work with CloudFront? =

This plugin is specifically designed for AWS Lightsail CDN. For CloudFront, you would need a different plugin.

= Can I schedule cache clears at specific times? =

Yes! The plugin supports daily, weekly, and monthly scheduling with customizable times. You can choose the exact time and day/date for cache clearing.

= What happens if cache clearing fails? =

The plugin will display an error message with details about what went wrong. Common issues include incorrect credentials or distribution name.

= Will this slow down my site? =

No, cache clearing operations happen in the background and don't affect your site's performance. The plugin only communicates with AWS when you manually clear cache, when content is updated (if auto-clear is enabled), or during scheduled clears.

= Can I clear cache for specific pages only? =

Currently, the plugin clears the entire CDN cache. Selective cache clearing may be added in future versions.

= Does this work with multisite? =

The plugin is compatible with WordPress multisite installations. Each site can have its own cache clearing schedule.

== Screenshots ==

1. Main admin interface with manual cache clearing
2. Auto-clear settings for automatic cache invalidation
3. Scheduled cache clearing with live countdown timer
4. Configuration panel showing AWS status
5. Post type selection for auto-clear functionality

== Changelog ==

= 1.0 =
* Initial release
* Manual cache clearing functionality
* Automatic cache clearing on content updates
* Scheduled cache clearing (daily, weekly, monthly)
* Live countdown timer for scheduled clears
* Support for custom post types
* Local timezone support for scheduled clears
* Comprehensive AWS error reporting
* Clean, modern admin interface
* Full WordPress coding standards compliance

== Upgrade Notice ==

= 1.0 =
Initial release of Lightsail CDN Control.

== Support ==

For support, feature requests, or bug reports, please visit:
* Plugin support forum: https://wordpress.org/support/plugin/lightsail-cdn-control/
* GitHub repository: https://github.com/yourusername/lightsail-cdn-control

== Privacy Policy ==

This plugin does not collect, store, or transmit any user data. All AWS credentials are stored locally in your wp-config.php file and are only used to communicate with AWS Lightsail API for cache management operations.

== Credits ==

Developed by Stephen with ❤️ for the WordPress community.

== License ==

This plugin is licensed under the GPLv2 or later.

