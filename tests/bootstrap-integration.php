<?php
/**
 * PHPUnit bootstrap file for OneSignal WordPress Plugin integration tests
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define ABSPATH for WordPress compatibility
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

// Ensure REST_REQUEST is not set (would prevent notifications from being sent in tests)
if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}

// Load WordPress function stubs for unit tests first
require_once __DIR__ . '/test-helpers/wordpress-stubs.php';

// Load extended WordPress stubs for integration tests
require_once __DIR__ . '/test-helpers/wordpress-integration-stubs.php';

// Load the plugin helper functions
require_once dirname(__DIR__) . '/v3/onesignal-helpers.php';

// Load the notification functions (which registers hooks)
require_once dirname(__DIR__) . '/v3/onesignal-notification.php';
