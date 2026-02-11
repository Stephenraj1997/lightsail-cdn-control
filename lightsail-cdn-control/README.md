# Lightsail CDN Control

A comprehensive WordPress plugin for managing AWS Lightsail CDN cache with manual clearing, automatic invalidation, and flexible scheduling.

![WordPress Plugin Version](https://img.shields.io/badge/version-1.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.2%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPLv2%2B-green.svg)

## Features

### 🚀 Manual Cache Clearing
- One-click cache invalidation from WordPress admin
- Instant feedback on cache clearing status
- Perfect for immediate control

### ⚡ Automatic Cache Clearing
- Auto-clear CDN cache when content is published or updated
- Select which post types trigger cache clearing
- Support for all custom post types
- Immediate content updates across your CDN

### 🕒 Scheduled Cache Clearing
- Daily, weekly, or monthly clearing options
- Choose specific times and days/dates
- Live countdown timer showing next scheduled clear
- Uses your WordPress timezone settings

### ⚙️ Easy Configuration
- Simple wp-config.php based credentials
- Clear status indicators
- Secure AWS Signature Version 4 authentication

## Installation

### Via WordPress Admin

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin
5. Configure AWS credentials (see Configuration below)

### Manual Installation

1. Download and extract the plugin
2. Upload the `lightsail-cdn-control` folder to `/wp-content/plugins/`
3. Activate through the WordPress admin
4. Configure AWS credentials

## Configuration

Add these constants to your `wp-config.php` file:

```php
define('AWS_ACCESS_KEY_ID', 'your_access_key_here');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key_here');
define('AWS_DEFAULT_REGION', 'us-east-1');
define('LIGHTSAIL_DISTRIBUTION_NAME', 'your_distribution_name');
```

### Finding Your Distribution Name

1. Log in to [AWS Lightsail console](https://lightsail.aws.amazon.com/)
2. Navigate to **Networking > CDN distributions**
3. Find your distribution and copy its name

### Creating AWS Credentials

1. Go to [AWS IAM console](https://console.aws.amazon.com/iam/)
2. Create a new user or use an existing one
3. Attach Lightsail permissions policy
4. Generate access keys
5. Add keys to wp-config.php

## Usage

### Manual Cache Clearing

1. Go to **Settings > CDN Control** in WordPress admin
2. Click **Clear CDN Cache Now** button
3. Confirm the action
4. Wait for success notification

### Auto-Clear Setup

1. Go to **Settings > CDN Control**
2. Enable **Auto-Clear** toggle
3. Select which post types should trigger cache clearing
4. Save settings
5. Cache will now clear automatically when selected content is updated

### Scheduled Clearing

1. Go to **Settings > CDN Control**
2. Enable **Scheduled Auto-Clear** toggle
3. Choose frequency (Daily, Weekly, or Monthly)
4. Set time of day
5. For weekly: choose day of week
6. For monthly: choose day of month (1-31)
7. Save settings
8. View live countdown to next clear

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- AWS Lightsail distribution with CDN enabled
- AWS Access Key with Lightsail permissions

## File Structure

```
lightsail-cdn-control/
├── admin/
│   └── class-lightsail-cdn-admin.php       # Admin interface
├── assets/
│   ├── css/
│   │   └── admin.css                        # Admin styles
│   └── js/
│       └── admin.js                         # Admin JavaScript
├── includes/
│   ├── class-lightsail-cdn-core.php        # Core plugin class
│   ├── class-lightsail-cdn-aws.php         # AWS API handler
│   └── class-lightsail-cdn-scheduler.php   # Scheduling logic
├── languages/                               # Translation files (future)
├── lightsail-cdn-control.php               # Main plugin file
├── uninstall.php                            # Uninstall cleanup
├── readme.txt                               # WordPress.org readme
└── README.md                                # This file
```

## Security

- Credentials stored securely in wp-config.php (not in database)
- Uses AWS Signature Version 4 for authentication
- Nonce verification for all admin actions
- Capability checks for all operations
- No user data collection or transmission

## Troubleshooting

### Cache Not Clearing

1. Verify AWS credentials are correct in wp-config.php
2. Check distribution name matches exactly
3. Ensure IAM user has Lightsail permissions
4. Check error messages in WordPress admin

### Scheduled Clear Not Running

1. Verify WordPress cron is working
2. Check timezone settings in WordPress
3. Confirm scheduled clear is enabled
4. Look for errors in debug.log

### Auto-Clear Not Working

1. Ensure auto-clear is enabled
2. Verify post types are selected
3. Check that post status is "published"
4. Confirm AWS credentials are valid

## Changelog

### Version 1.0 (Initial Release)
- Manual cache clearing functionality
- Automatic cache clearing on content updates
- Scheduled cache clearing (daily, weekly, monthly)
- Live countdown timer for scheduled clears
- Support for custom post types
- Local timezone support
- Comprehensive error reporting
- Modern, responsive admin interface

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

- WordPress Support Forum: [Plugin Support](https://wordpress.org/support/plugin/lightsail-cdn-control/)
- GitHub Issues: [Report Issues](https://github.com/yourusername/lightsail-cdn-control/issues)

## License

This plugin is licensed under the GPLv2 or later.

```
Copyright (C) 2024 Stephen

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Author

**Stephen**

---

Made with ❤️ for the WordPress community
