# Changelog

All notable changes to Lightsail CDN Control will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0] - 2024-02-11

### Added
- Initial release of Lightsail CDN Control
- Manual CDN cache clearing functionality
- Automatic cache clearing on content updates
- Scheduled cache clearing with daily, weekly, and monthly options
- Live countdown timer showing time until next scheduled clear
- Support for custom post types in auto-clear settings
- Select/deselect all buttons for post type selection
- Local timezone support for scheduled clearing
- Two-column responsive admin layout
- Day of month selector for monthly scheduling (1-31)
- Configuration status indicators
- Comprehensive error handling and user feedback
- AWS Signature Version 4 authentication
- Transient-based admin notices for auto-clear events
- WordPress coding standards compliance
- Full internationalization support (translation-ready)

### Security
- Credentials stored in wp-config.php (not database)
- Nonce verification for all admin actions
- Capability checks (manage_options)
- Secure AWS API communication
- No user data collection or external tracking

### Documentation
- Comprehensive readme.txt for WordPress.org
- Detailed README.md for GitHub
- Inline code documentation
- Setup instructions and examples
- FAQ section
- Troubleshooting guide

## [Unreleased]

### Planned Features
- Selective cache clearing (specific paths/patterns)
- Cache statistics and history
- Multi-distribution support
- Email notifications for scheduled clears
- WP-CLI commands
- Purge cache via REST API
- Import/export settings
- Advanced scheduling options (multiple times per day)

---

## Version History

- **1.0** (2024-02-11) - Initial public release
