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

// Load WordPress function stubs for unit tests
// These are minimal stubs to allow the helper functions to be tested in isolation
require_once __DIR__ . '/test-helpers/wordpress-stubs.php';

// Load the plugin helper functions
require_once dirname(__DIR__) . '/v3/onesignal-helpers.php';
