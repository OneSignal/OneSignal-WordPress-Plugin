<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

/*
 * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 3.0.0
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 */

require_once plugin_dir_path(__FILE__) . '/onesignal-admin/onesignal-admin.php';
require_once plugin_dir_path(__FILE__) . '/onesignal-init.php';
require_once plugin_dir_path(__FILE__) . '/onesignal-metabox/onesignal-metabox.php';
require_once plugin_dir_path(__FILE__) . '/onesignal-notification.php';
