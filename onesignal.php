<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

/*
 * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 3.0.5
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 */

// Define plugin constants
define('ONESIGNAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ONESIGNAL_API_RATE_LIMIT_SECONDS', 1);
define('ONESIGNAL_URI_REVEAL_PROJECT_NUMBER', 'reveal_project_number=true');

// Constants for plugin versions
define('ONESIGNAL_VERSION_V2', 'v2');
define('ONESIGNAL_VERSION_V3', 'v3');

// Check migration status and existing settings
$is_migrated = get_option('onesignal_plugin_migrated', false); // Tracks whether the plugin has been migrated to V3
$settings = get_option('OneSignalWPSetting'); // Fetch existing plugin settings (if any)
$is_new_install = !$settings || !isset($settings['app_id']); // Determine if this is a fresh install (no settings yet)

// Determine which plugin version to load
$plugin_version = $is_new_install || $is_migrated ? ONESIGNAL_VERSION_V3 : ONESIGNAL_VERSION_V2;

// Load the appropriate plugin version based on the state
if ($plugin_version === ONESIGNAL_VERSION_V3) {
    // Load V3 plugin files
    require_once plugin_dir_path(__FILE__) . 'v3/onesignal-admin/onesignal-admin.php';
    require_once plugin_dir_path(__FILE__) . 'v3/onesignal-init.php';
    require_once plugin_dir_path(__FILE__) . 'v3/onesignal-metabox/onesignal-metabox.php';
    require_once plugin_dir_path(__FILE__) . 'v3/onesignal-notification.php';

    // Ensure migration is marked as complete after loading V3
    if (!$is_migrated) {
        update_option('onesignal_plugin_migrated', true);
    }
} else {
    // Load V2 plugin files
    require_once plugin_dir_path(__FILE__) . 'v2/onesignal-utils.php';
    require_once plugin_dir_path(__FILE__) . 'v2/onesignal-admin.php';
    require_once plugin_dir_path(__FILE__) . 'v2/onesignal-public.php';
    require_once plugin_dir_path(__FILE__) . 'v2/onesignal-settings.php';
    require_once plugin_dir_path(__FILE__) . 'v2/onesignal-widget.php';
    include_once plugin_dir_path(__FILE__) . 'v2/configuration-export.php';
    include_once plugin_dir_path(__FILE__) . 'v2/complete-migration.php';

    // Initialize V2 admin and public components
    add_action('init', ['OneSignal_Admin', 'init']);
    add_action('init', ['OneSignal_Public', 'init']);
    add_action('admin_notices', 'migration_notice');
}

function migration_notice() {
    // Only show the notice on the Plugins page
    $screen = get_current_screen();
    if ($screen && $screen->id === 'plugins') {
        echo '<div class="notice notice-warning is-dismissible">
                <p><strong>OneSignal Migration Needed:</strong> All OneSignal prompt configurations are moving to OneSignal.com. See the plugin page for more info.</p>
              </div>';
    }
}
