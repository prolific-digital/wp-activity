<?php

/**
 * Plugin Name: WP Activity
 * Description: Tracks all activity that happens in WordPress and displays a log with user-centric details.
 * Version: 1.0.0
 * Author: Prolific Digital
 * Author URI: http://prolificdigital.com
 * Plugin URI: http://prolificdigital.com
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-activity
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

class WP_Activity_Tracker {
  public function __construct() {
    add_action('init', array($this, 'register_activity_post_type'));
    add_action('transition_post_status', array($this, 'track_post_changes'), 10, 3);
    add_action('admin_menu', array($this, 'remove_publish_box'));
    add_action('before_delete_post', array($this, 'log_delete'));
    add_action('untrash_post', array($this, 'track_post_restored'));
    add_action('load-edit.php', array($this, 'track_bulk_actions'));
    add_filter('bulk_actions-edit-activity', array($this, 'remove_bulk_actions'));
    add_filter('post_row_actions', array($this, 'remove_quick_edit'), 10, 2);
    add_action('admin_head', array($this, 'remove_publish_filter'));
    add_filter('post_row_actions', array($this, 'update_row_actions'), 10, 2);
    add_action('add_meta_boxes', array($this, 'add_activity_detail_box'));
    add_action('user_register', array($this, 'track_user_registered'));
    add_action('delete_user', array($this, 'track_user_deleted'));
    add_action('profile_update', array($this, 'track_user_updated'), 10, 2);
    add_action('edit_attachment', array($this, 'track_media_changes')); // Add this line
    add_action('add_attachment', array($this, 'track_media_added')); // Add this line
    add_action('delete_attachment', array($this, 'track_media_deleted')); // Add this line
    add_filter('manage_activity_posts_columns', array($this, 'add_activity_columns'));
    add_action('manage_activity_posts_custom_column', array($this, 'manage_activity_columns'), 10, 2);
    add_action('activated_plugin', array($this, 'track_plugin_activation'));
    add_action('deactivated_plugin', array($this, 'track_plugin_deactivation'));
  }

  // Track plugin activation
  public function track_plugin_activation($plugin) {
    $current_user = wp_get_current_user();
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
    $plugin_name = $plugin_data['Name'];
    $this->log_activity("Plugin '{$plugin_name}' was activated", $current_user->ID);
  }

  // Track plugin deactivation
  public function track_plugin_deactivation($plugin) {
    $current_user = wp_get_current_user();
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
    $plugin_name = $plugin_data['Name'];
    $this->log_activity("Plugin '{$plugin_name}' was deactivated", $current_user->ID);
  }

  // Add new columns to the activity post type
  public function add_activity_columns($columns) {
    // Rearrange the order of the columns
    $new_columns = array(
      'cb' => $columns['cb'],
      'title' => $columns['title'],
      'id' => __('ID', 'wp-activity'),
      'user' => __('User', 'wp-activity'),
      'date' => $columns['date'],
    );

    return $new_columns;
  }

  // Manage the content of custom columns
  public function manage_activity_columns($column, $post_id) {
    switch ($column) {
      case 'id':
        $tracked_item_id = get_post_meta($post_id, '_modified_post_id', true);
        if ($tracked_item_id) {
          echo '<a href="' . get_edit_post_link($tracked_item_id) . '">' . $tracked_item_id . '</a>';
        } else {
          echo 'N/A';
        }
        break;
      case 'user':
        $user_id = get_post_meta($post_id, '_activity_user_id', true);
        $user_info = get_userdata($user_id);
        echo $user_info ? $user_info->user_login : 'N/A';
        break;
        // Handle other custom columns if needed
    }
  }

  public function track_media_added($post_id) {
    $post = get_post($post_id);
    $current_user = wp_get_current_user();
    $activity_args = array(
      'post_type' => 'activity',
      'post_title' => $post->post_title . ' was added',
      'post_status' => 'publish',
    );
    $this->log_activity($activity_args['post_title'], $current_user->ID, $post->ID);
  }

  public function track_media_deleted($post_id) {
    $post = get_post($post_id);
    $current_user = wp_get_current_user();
    $activity_args = array(
      'post_type' => 'activity',
      'post_title' => $post->post_title . ' was deleted',
      'post_status' => 'publish',
    );
    $this->log_activity($activity_args['post_title'], $current_user->ID, $post->ID);
  }

  public function track_media_changes($post_id) {
    $post = get_post($post_id);

    if ($post->post_type == 'attachment') {
      $current_user = wp_get_current_user();
      $activity_args = array(
        'post_type' => 'activity',
        'post_title' => $post->post_title . ' was modified',
        'post_status' => 'publish',
      );
      $this->log_activity($activity_args['post_title'], $current_user->ID, $post->ID);
    }
  }

  public function track_user_registered($user_id) {
    $user_info = get_userdata($user_id);
    $this->log_activity("User {$user_info->user_login} was registered", $user_id);
  }

  public function track_user_deleted($user_id) {
    $this->log_activity("User with ID {$user_id} was deleted", $user_id);
  }

  public function track_user_updated($user_id, $old_user_data) {
    $user_info = get_userdata($user_id);
    $this->log_activity("User {$user_info->user_login} was updated", $user_id, $user_id);  // Pass $user_id as the third argument.
  }

  private function log_activity($message, $user_id, $modified_post_id = 0) {
    $activity_args = array(
      'post_type' => 'activity',
      'post_title' => $message,
      'post_status' => 'publish',
    );
    $activity_id = wp_insert_post($activity_args);
    add_post_meta($activity_id, '_activity_user_id', $user_id, true);
    if ($modified_post_id > 0) {
      add_post_meta($activity_id, '_modified_post_id', $modified_post_id, true);
    }
  }

  public function display_activity_details($post) {
    $user_id = get_post_meta($post->ID, '_activity_user_id', true);
    $user_info = get_userdata($user_id);
    $modified_post_id = get_post_meta($post->ID, '_modified_post_id', true);
    echo "<div><strong>Activity:</strong> {$post->post_title}</div>";
    echo "<div><strong>User:</strong> {$user_info->user_login}</div>";
    echo "<div><strong>Date:</strong> {$post->post_date}</div>";
    echo "<div><strong>ID:</strong> {$modified_post_id}</div>";
  }

  public function add_activity_detail_box() {
    add_meta_box(
      'activity_details',
      'Activity Details',
      array($this, 'display_activity_details'),
      'activity',
      'normal',
      'high'
    );
  }

  public function update_row_actions($actions, $post) {
    if ($post->post_type == 'activity') {
      unset($actions['inline hide-if-no-js']); // Remove Quick Edit
      if (isset($actions['edit'])) {
        // Update 'Edit' to 'View' but keep the same link
        $actions['edit'] = str_replace('Edit', 'View', $actions['edit']);
      }
    }
    return $actions;
  }

  public function remove_quick_edit($actions, $post) {
    if ($post->post_type == 'activity') {
      unset($actions['inline hide-if-no-js']);
    }
    return $actions;
  }

  public function remove_publish_filter() {
    global $typenow;
    if ('activity' === $typenow) {
      add_filter('views_edit-activity', function ($views) {
        unset($views['publish']);
        return $views;
      });
    }
  }

  public function track_bulk_actions() {
    if (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'trash' || $_REQUEST['post_type'] == 'activity') {
      return;
    }

    $post_ids = array_map('intval', (array) $_REQUEST['post']);
    foreach ($post_ids as $post_id) {
      $this->log_delete($post_id);
    }
  }

  public function log_delete($post_id) {
    $post = get_post($post_id);
    if ($post->post_type == 'activity' || $post->post_status == 'trash') {
      return;
    }

    $current_user = wp_get_current_user();
    $activity_args = array(
      'post_type' => 'activity',
      'post_title' => $post->post_title . ' was deleted',
      'post_status' => 'publish',
    );
    $activity_id = wp_insert_post($activity_args);
    add_post_meta($activity_id, '_activity_user_id', $current_user->ID, true);
    add_post_meta($activity_id, '_modified_post_id', $post_id, true);
  }

  public function display_activity_user($post) {
    $user_id = get_post_meta($post->ID, '_activity_user_id', true);
    $user_info = get_userdata($user_id);
    echo "<div>User: {$user_info->user_login}</div>";
  }

  public function register_activity_post_type() {
    $args = array(
      'public' => false,
      'label'  => 'Activity',
      'labels' => array(
        'edit_item' => 'Activity Details',
      ),
      'show_ui' => true,
      'capability_type' => 'post',
      'hierarchical' => false,
      'rewrite' => array('slug' => 'activity'),
      'query_var' => true,
      'supports' => false,
      'capabilities' => array(
        'create_posts' => 'do_not_allow', // Removes support for the "Add New" function (Note: 'do_not_allow' is a special flag)
      ),
      'map_meta_cap' => true, // Set to `true` to map custom "capabilities" to built-in WordPress capabilities
      'menu_icon' => 'dashicons-clock', // Use the clock dashicon
    );
    register_post_type('activity', $args);
  }

  public function remove_bulk_actions($actions) {
    unset($actions['edit']);
    return $actions;
  }

  public function track_post_restored($post_id) {
    $post = get_post($post_id);
    if ($post->post_type == 'activity') {
      return;
    }

    $current_user = wp_get_current_user();
    $activity_args = array(
      'post_type' => 'activity',
      'post_title' => $post->post_title . ' was restored',
      'post_status' => 'publish',
    );

    $activity_id = wp_insert_post($activity_args);
    add_post_meta($activity_id, '_activity_user_id', $current_user->ID, true);
    add_post_meta($activity_id, '_modified_post_id', $post_id, true);
  }

  public function track_post_changes($new_status, $old_status, $post) {
    if ($post->post_type == 'activity' || $new_status == 'auto-draft' || $new_status == 'inherit') {
      return;
    }

    $current_user = wp_get_current_user();
    $activity_args = array(
      'post_type' => 'activity',
      'post_status' => 'publish',
    );

    if ($new_status == 'trash') {
      if (get_post_meta($post->ID, '_trashed_logged', true)) {
        return;
      }

      $activity_args['post_title'] = $post->post_title . ' was trashed';
      add_post_meta($post->ID, '_trashed_logged', '1', true);
    } else if ($new_status == 'publish' && $old_status == 'trash') {
      $activity_args['post_title'] = $post->post_title . ' was restored';
    } else if ($new_status == 'publish' && $old_status != 'publish') {
      $activity_args['post_title'] = $post->post_title . ' was published';
    } else if ($new_status == 'publish' && $old_status == 'publish') {
      // Log when a post is updated.
      $activity_args['post_title'] = $post->post_title . ' was updated';
    } else {
      // For all other status changes, log the status transition.
      $activity_args['post_title'] = "{$post->post_title} status changed from {$old_status} to {$new_status}";
    }

    $this->log_activity($activity_args['post_title'], $current_user->ID, $post->ID);
  }

  public function remove_publish_box() {
    remove_meta_box('submitdiv', 'activity', 'side');
  }
}

new WP_Activity_Tracker();
