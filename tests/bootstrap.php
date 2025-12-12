<?php
/**
 * PHPUnit bootstrap file for OneSignal WordPress Plugin tests
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define ABSPATH for WordPress compatibility
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

// Initialize WP_Mock
WP_Mock::bootstrap();

// Load the plugin helper functions
require_once dirname(__DIR__) . '/v3/onesignal-helpers.php';
