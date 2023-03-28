<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

/*
 * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 2.4.0
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

require_once plugin_dir_path(__FILE__).'onesignal-utils.php';
require_once plugin_dir_path(__FILE__).'onesignal-admin.php';
require_once plugin_dir_path(__FILE__).'onesignal-public.php';
require_once plugin_dir_path(__FILE__).'onesignal-settings.php';
require_once plugin_dir_path(__FILE__).'onesignal-widget.php';

if (file_exists(plugin_dir_path(__FILE__).'onesignal-extra.php')) {
    require_once plugin_dir_path(__FILE__).'onesignal-extra.php';
}

add_action('init', ['OneSignal_Admin', 'init']);
add_action('init', ['OneSignal_Public', 'init']);
