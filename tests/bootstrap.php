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

// Ensure REST_REQUEST is not set (would prevent notifications from being sent in tests)
if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}

// Initialize WP_Mock
WP_Mock::bootstrap();

// Define WP_Error class if not already defined
if (!class_exists('WP_Error')) {
    class WP_Error {
        public $errors = array();
        public $error_data = array();

        public function __construct($code = '', $message = '', $data = '') {
            if (empty($code)) {
                return;
            }
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }

        public function get_error_message($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return $this->errors[$code][0] ?? '';
        }

        public function get_error_code() {
            $codes = array_keys($this->errors);
            return $codes[0] ?? '';
        }
    }
}

// Define essential WordPress functions that are needed at bootstrap time
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

// Extract ONESIGNAL_PLUGIN_VERSION from onesignal.php
if (!defined('ONESIGNAL_PLUGIN_VERSION')) {
    $plugin_file = dirname(__DIR__) . '/onesignal.php';
    $plugin_content = file_get_contents($plugin_file);

    // Extract the constant value: define('ONESIGNAL_PLUGIN_VERSION', 'XXYYZZ');
    if (preg_match("/define\s*\(\s*['\"]ONESIGNAL_PLUGIN_VERSION['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $plugin_content, $matches)) {
        define('ONESIGNAL_PLUGIN_VERSION', $matches[1]);
    }
}

// Define the SDK wrapper header function - this is to avoid loading the plugin file in the tests
if (!function_exists('onesignal_get_sdk_wrapper_header')) {
    function onesignal_get_sdk_wrapper_header() {
        return 'onesignal/wordpress/' . ONESIGNAL_PLUGIN_VERSION;
    }
}

// Load the plugin helper functions
require_once dirname(__DIR__) . '/v3/onesignal-helpers.php';

// Load the notification functions (for integration tests)
// Use require_once to prevent redeclaration errors
require_once dirname(__DIR__) . '/v3/onesignal-notification.php';
