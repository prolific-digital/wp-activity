# WP Activity Tracker

The WP Activity Tracker is a powerful plugin developed by [Prolific Digital](http://prolificdigital.com) that tracks all activity that happens within your WordPress site and displays a comprehensive log with user-centric details. This handy tool helps you keep track of important changes, updates, and user actions on your site.

## Features

- Tracks various activities in WordPress, including post changes, media additions/deletions, user registrations, updates, and deletions.
- Provides a log with essential details, such as the activity description, user involved, and the date it occurred.
- Integrates seamlessly with your WordPress admin interface for easy management.

## Installation

To install the WP Activity Tracker plugin, follow these steps:

1. Download `wp-activity.zip` from the latest [GitHub release](https://github.com/prolific-digital/wp-activity/releases).
2. Upload the plugin folder to your WordPress plugin directory (`wp-content/plugins/`).
3. Activate the plugin through the 'Plugins' menu in your WordPress admin.

> This plugin requires WordPress 6.5+ and PHP 8.4+.

## How to Use

After installing and activating the plugin, the WP Activity Tracker will automatically start recording various activities on your WordPress site. You can view the activity log from the 'Activity' menu in your WordPress admin panel.

### Supported Activities

- **Post Changes**: Any updates, publishing, or trashing of posts will be logged.
- **Page Changes**: Any updates, publishing, or trashing of posts will be logged.
- **Custom Post Type Changes**: Any updates, publishing, or trashing of posts will be logged.
- **Media Additions/Deletions**: When media files are added or deleted, the plugin will record these actions.
- **User Registrations**: Whenever a new user registers on your site, it will be logged.
- **User Updates/Deletions**: Changes to user profiles or deletions of user accounts will be tracked.
- **Plugin Activation/Deactivation**: Whenever a plugin is activated or deactivated, the WP Activity Tracker will keep you informed.

## Updates

Updates are served by the Prolific Digital license server. Add the site's license key to `wp-config.php`:

```php
define( 'PROLIFIC_LICENSE_KEY_WP_ACTIVITY', 'your-key' );
```

You can also supply the key with the `prolific_license_key` filter, which receives `( $key, 'wp-activity' )`.

## Development

The dev site is the DDEV sandbox at `https://wp-sandbox.ddev.site`. Run commands in the container:

```sh
ddev exec -d /var/www/html/wp-content/plugins/wp-activity composer install
ddev exec -d /var/www/html/wp-content/plugins/wp-activity composer lint    # php -l on every file
ddev exec -d /var/www/html/wp-content/plugins/wp-activity composer phpcs   # WordPress-Extra + PHPCompatibilityWP
```

`vendor/` is not committed. A git checkout needs `composer install` before the updater loads. The plugin itself still runs without it.

## Releasing

1. Bump the version in the plugin header, `WP_ACTIVITY_VERSION` and `readme.txt` `Stable tag`.
2. Add a `## [x.y.z] - YYYY-MM-DD` section to `CHANGELOG.md` and a `= x.y.z =` entry to `readme.txt`.
3. Merge to `main`, then push a `vx.y.z` tag. The release workflow builds `wp-activity.zip` (with `composer install --no-dev`, honoring `.distignore`) and attaches it to a GitHub Release.

CI and release logic live in the shared workflows in [`prolific-digital/.github`](https://github.com/prolific-digital/.github).
## Stored data

| Key | Type | Purpose |
|---|---|---|
| `activity` | custom post type | One post per logged event |
| `_activity_user_id` | post meta (on `activity` posts) | User who performed the action |
| `_modified_post_id` | post meta (on `activity` posts) | ID of the affected post, attachment or user |
| `_trashed_logged` | post meta (on tracked posts) | Prevents logging the same trash twice |

There is no `uninstall.php`: the activity log is kept when the plugin is deleted.

## Support and Contributing

For support or to report any issues, please [submit an issue](https://github.com/prolific-digital/wp-activity/issues) on our GitHub repository. We welcome contributions from the community to further improve this plugin. If you have any feature additions or bug fixes, feel free to send us a pull request with a brief description of your changes.

> Before submitting a pull request for a new feature, we encourage you to discuss it with us first to ensure it aligns with our project's direction and goals.

## License

This project is licensed under GPL-2.0-or-later. For more information, please see the [LICENSE file](LICENSE).

## Versioning

We follow Semantic Versioning for versioning this plugin. You can find the available versions and releases on the [GitHub repository](https://github.com/prolific-digital/wp-activity/releases).

Start tracking your WordPress activities with the WP Activity Tracker plugin and stay informed about your site's changes!

---

_Proudly crafted by Prolific Digital_
