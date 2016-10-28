<?php
/**
  * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 1.13.4
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 */

define( 'ONESIGNAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once( plugin_dir_path( __FILE__ ) . 'onesignal-utils.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-admin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-public.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-widget.php' );

if (file_exists(plugin_dir_path( __FILE__ ) . 'onesignal-extra.php')) {
    require_once( plugin_dir_path( __FILE__ ) . 'onesignal-extra.php' );
}

add_action( 'init', array( 'OneSignal_Admin', 'init' ) );
add_action( 'init', array( 'OneSignal_Public', 'init' ) );

?>