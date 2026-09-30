<?php
/**
 * Security regression check: activity log output must be escaped.
 *
 * Run with the plugin active:
 *   ddev wp eval-file wp-content/plugins/wp-activity/tests/security/activity-xss.php
 *
 * As the `e2e` user, publishes a post whose title is an XSS payload, which
 * the plugin logs as an activity entry. Then renders the log's "Activity
 * Details" meta box and its ID/User list columns and fails if the payload
 * appears unescaped. The test post and its activity entries are removed.
 *
 * @package WPActivity
 */

defined( 'ABSPATH' ) || exit;

$wp_activity_payload = '"><script>alert(1)</script><img src=x onerror=alert(2)>';
$wp_activity_user    = get_user_by( 'login', 'e2e' );
if ( ! $wp_activity_user ) {
	WP_CLI::error( 'The e2e user does not exist.' );
}
wp_set_current_user( $wp_activity_user->ID );

// Find the plugin's tracker instance through its hooks.
$wp_activity_tracker = null;
foreach ( $GLOBALS['wp_filter']['add_meta_boxes']->callbacks as $wp_activity_callbacks ) {
	foreach ( $wp_activity_callbacks as $wp_activity_callback ) {
		if ( is_array( $wp_activity_callback['function'] ) && $wp_activity_callback['function'][0] instanceof WP_Activity_Tracker ) {
			$wp_activity_tracker = $wp_activity_callback['function'][0];
		}
	}
}
if ( ! $wp_activity_tracker ) {
	WP_CLI::error( 'WP Activity is not active.' );
}

$wp_activity_post_id = wp_insert_post(
	array(
		'post_title'  => $wp_activity_payload,
		'post_status' => 'publish',
		'post_type'   => 'post',
	)
);

$wp_activity_entries = get_posts(
	array(
		'post_type'      => 'activity',
		'posts_per_page' => -1,
		'meta_key'       => '_modified_post_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => $wp_activity_post_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	)
);

$wp_activity_html = '';
foreach ( $wp_activity_entries as $wp_activity_entry ) {
	ob_start();
	$wp_activity_tracker->display_activity_details( $wp_activity_entry );
	$wp_activity_tracker->display_activity_user( $wp_activity_entry );
	$wp_activity_tracker->manage_activity_columns( 'id', $wp_activity_entry->ID );
	$wp_activity_tracker->manage_activity_columns( 'user', $wp_activity_entry->ID );
	$wp_activity_html .= ob_get_clean();
}

// Clean up.
foreach ( $wp_activity_entries as $wp_activity_entry ) {
	wp_delete_post( $wp_activity_entry->ID, true );
}
wp_delete_post( $wp_activity_post_id, true );
foreach ( get_posts( array( 'post_type' => 'activity', 'posts_per_page' => -1, 's' => 'alert(1)' ) ) as $wp_activity_entry ) { // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
	wp_delete_post( $wp_activity_entry->ID, true );
}

WP_CLI::log( 'Rendered log output:' );
WP_CLI::log( $wp_activity_html );

if ( ! $wp_activity_entries ) {
	WP_CLI::error( 'No activity entry was logged for the test post.' );
}
if ( str_contains( $wp_activity_html, '<script>' ) || str_contains( $wp_activity_html, '<img' ) ) {
	WP_CLI::error( 'FAIL: the payload is rendered unescaped.' );
}
if ( ! str_contains( $wp_activity_html, '&lt;script&gt;' ) ) {
	WP_CLI::error( 'FAIL: the escaped title is missing from the output.' );
}
WP_CLI::success( 'Activity log output is escaped.' );
