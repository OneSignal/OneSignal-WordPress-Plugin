<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

/*
 * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 2.4.4
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 */

define('ONESIGNAL_PLUGIN_URL', plugin_dir_url(__FILE__));
/*
 * The number of seconds required to wait between requests.
 */
define('ONESIGNAL_API_RATE_LIMIT_SECONDS', 1);
define('ONESIGNAL_URI_REVEAL_PROJECT_NUMBER', 'reveal_project_number=true');

// Check migration status
$is_migrated = get_option('onesignal_plugin_migrated', false);

if (!$is_migrated) {
  require_once plugin_dir_path(__FILE__).'v2/onesignal-utils.php';
  require_once plugin_dir_path(__FILE__).'v2/onesignal-admin.php';
  require_once plugin_dir_path(__FILE__).'v2/onesignal-public.php';
  require_once plugin_dir_path(__FILE__).'v2/onesignal-settings.php';
  require_once plugin_dir_path(__FILE__).'v2/onesignal-widget.php';
  include_once plugin_dir_path(__FILE__).'v2/configuration-export.php';
  include_once plugin_dir_path(__FILE__).'v2/complete-migration.php';

  add_action('init', ['OneSignal_Admin', 'init']);
  add_action('init', ['OneSignal_Public', 'init']);
} else {
  require_once plugin_dir_path(__FILE__) . 'v3/onesignal-admin/onesignal-admin.php';
  require_once plugin_dir_path(__FILE__) . 'v3/onesignal-init.php';
  require_once plugin_dir_path(__FILE__) . 'v3/onesignal-metabox/onesignal-metabox.php';
  require_once plugin_dir_path(__FILE__) . 'v3/onesignal-notification.php';
}

if (file_exists(plugin_dir_path(__FILE__).'onesignal-extra.php')) {
    require_once plugin_dir_path(__FILE__).'onesignal-extra.php';
}
