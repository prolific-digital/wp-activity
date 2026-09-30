# Changelog

All notable changes to the "WP Activity Tracker" plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-09-29

### Changed
- Adopted the Prolific plugin standard. Updates now come from the Prolific Digital license server (`api.prolificdigital.io`). Set `PROLIFIC_LICENSE_KEY_WP_ACTIVITY` in `wp-config.php` (or use the `prolific_license_key` filter) to receive updates.
- The main file is renamed from `index.php` to `wp-activity.php`. Reactivate the plugin once after updating.
- License changed from GPL-3.0 to GPL-2.0-or-later.
- Requires WordPress 6.5+ and PHP 8.4+.

### Added
- `WP_ACTIVITY_VERSION` constant, ABSPATH guard and translation loading from `languages/`.
- `readme.txt`, coding-standards config and CI/release workflows.

## [1.0.0] - 2023-07-30

### Added

- Initial release of the WP Activity Tracker plugin.
- Tracks various activities in WordPress, including post/page/custom post type changes, media additions/deletions, user registrations, updates, and deletions.
- Provides a log with essential details, such as the activity description, user involved, and the date it occurred.
- Integrates seamlessly with the WordPress admin interface for easy management.

[Unreleased]: https://github.com/prolific-digital/wp-activity/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/prolific-digital/wp-activity/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/prolific-digital/wp-activity/releases/tag/v1.0.0
