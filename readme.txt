=== WP Activity ===
Contributors: prolificdigital
Tags: activity log, audit, tracking, users
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tracks activity in WordPress and displays a log with user-centric details.

== Description ==

WP Activity records site activity as entries in an **Activity** admin menu, each showing what happened, who did it and when.

Logged events:

* Posts, pages and custom post types: published, updated, status changes, trashed, restored and deleted.
* Media: added, modified and deleted.
* Users: registered, updated and deleted.
* Plugins: activated and deactivated.

Entries are stored as posts of the private `activity` post type and cannot be created or edited by hand. The log is kept if the plugin is deleted.

== Installation ==

1. Upload the `wp-activity` folder to `/wp-content/plugins/`, or install the release zip from **Plugins > Add New > Upload Plugin**.
2. Activate the plugin and open **Activity** in the admin menu.
3. To receive automatic updates, add your license key to `wp-config.php`:
   `define( 'PROLIFIC_LICENSE_KEY_WP_ACTIVITY', 'your-key' );`

== Changelog ==

= 1.1.0 =
* Security: escape activity titles, user names, dates and IDs in the activity log (stored XSS).
* Updates now come from the Prolific Digital license server. Define `PROLIFIC_LICENSE_KEY_WP_ACTIVITY` to receive them.
* Main file renamed to `wp-activity.php`; reactivate the plugin once after updating.
* License changed from GPL-3.0 to GPL-2.0-or-later.
* Requires WordPress 6.5+ and PHP 8.4+.

= 1.0.0 =
* Initial release.
