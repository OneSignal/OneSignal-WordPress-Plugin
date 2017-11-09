<?php

defined( 'ABSPATH' ) or die('This page may not be accessed directly.');

/**
 * Plugin Name: OneSignal Push Notifications
 * Plugin URI: https://onesignal.com/
 * Description: Free web push notifications.
 * Version: 1.16.2
 * Author: OneSignal
 * Author URI: https://onesignal.com
 * License: MIT
 * Text Domain: onesignal-free-web-push-notifications
 * Domain Path: /languages
 */

define( 'ONESIGNAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin absolute path
 */
define( 'ONESIGNAL_PLUGIN_ABSPATH', plugin_dir_path( __FILE__ ) );

/**
 * The number of seconds required to wait between requests.
 */
define( 'ONESIGNAL_API_RATE_LIMIT_SECONDS', 10 );
define( 'ONESIGNAL_URI_REVEAL_PROJECT_NUMBER', 'reveal_project_number=true' );

/**
 * init plugin translation
 */
add_action('plugins_loaded', function(){
    load_plugin_textdomain( 'onesignal-free-web-push-notifications', false, basename( ONESIGNAL_PLUGIN_ABSPATH ) . '/languages/' );
});

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
