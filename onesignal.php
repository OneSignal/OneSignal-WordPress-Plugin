<?php
/**
  * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: 
 * Version: 1.1.2
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 */

define( 'ONESIGNAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once( plugin_dir_path( __FILE__ ) . 'onesignal-admin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-public.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'onesignal-widget.php' );

add_action( 'init', array( 'OneSignal_Admin', 'init' ) );
add_action( 'init', array( 'OneSignal_Public', 'init' ) );

?>