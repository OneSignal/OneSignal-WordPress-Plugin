<?php
/**
 * WP-CLI E2E Tests for OneSignal WordPress Plugin
 *
 * Run this file using WP-CLI:
 * wp eval-file tests/e2e/wp-cli-tests.php
 *
 * Or from Docker:
 * docker exec wordpress-container wp eval-file /path/to/wp-cli-tests.php
 */

if (!defined('WP_CLI') || !WP_CLI) {
    die('This script can only be run via WP-CLI');
}

class OneSignal_E2E_Tests {
    private $test_results = array();
    private $test_count = 0;
    private $passed_count = 0;
    private $failed_count = 0;

    public function run_all_tests() {
        WP_CLI::line('');
        WP_CLI::line('========================================');
        WP_CLI::line('OneSignal E2E Tests');
        WP_CLI::line('========================================');
        WP_CLI::line('');

        // Run test suites
        $this->test_plugin_activation();
        $this->test_settings_management();
        $this->test_post_notification_workflow();
        $this->test_post_type_validation();
        $this->test_metadata_storage();

        // Print summary
        $this->print_summary();
    }

    private function assert($condition, $test_name, $message = '') {
        $this->test_count++;

        if ($condition) {
            $this->passed_count++;
            WP_CLI::success($test_name);
            return true;
        } else {
            $this->failed_count++;
            WP_CLI::error($test_name . ($message ? ": $message" : ''), false);
            return false;
        }
    }

    private function test_plugin_activation() {
        WP_CLI::line('');
        WP_CLI::line('Test Suite: Plugin Activation');
        WP_CLI::line('---');

        // Check if plugin is active
        $active = is_plugin_active('OneSignal-WordPress-Plugin/onesignal.php') ||
                  is_plugin_active('onesignal-free-web-push-notifications/onesignal.php');

        $this->assert($active, 'Plugin is activated');

        // Check if OneSignal functions are available
        $this->assert(
            function_exists('onesignal_get_api_key_type'),
            'OneSignal helper functions are loaded'
        );

        $this->assert(
            function_exists('onesignal_create_notification'),
            'OneSignal notification functions are loaded'
        );
    }

    private function test_settings_management() {
        WP_CLI::line('');
        WP_CLI::line('Test Suite: Settings Management');
        WP_CLI::line('---');

        // Test saving settings
        $test_settings = array(
            'app_id' => 'test-e2e-app-id-' . time(),
            'app_rest_api_key' => 'os_v2_test_key_' . time(),
            'notification_on_post' => 1,
            'notification_on_page' => 0,
            'utm_additional_url_params' => 'utm_source=test&utm_medium=e2e',
            'allowed_custom_post_types' => 'product,event'
        );

        update_option('OneSignalWPSetting', $test_settings);

        // Verify settings were saved
        $saved_settings = get_option('OneSignalWPSetting');

        $this->assert(
            $saved_settings['app_id'] === $test_settings['app_id'],
            'Settings saved: App ID'
        );

        $this->assert(
            $saved_settings['app_rest_api_key'] === $test_settings['app_rest_api_key'],
            'Settings saved: API Key'
        );

        $this->assert(
            $saved_settings['notification_on_post'] === 1,
            'Settings saved: Notification on post enabled'
        );

        $this->assert(
            $saved_settings['utm_additional_url_params'] === 'utm_source=test&utm_medium=e2e',
            'Settings saved: UTM parameters'
        );

        // Test API key type detection
        $this->assert(
            onesignal_get_api_key_type() === 'Rich',
            'API key type detected as Rich'
        );

        // Test with Legacy key
        update_option('OneSignalWPSetting', array_merge($test_settings, array(
            'app_rest_api_key' => 'legacy_key_test'
        )));

        $this->assert(
            onesignal_get_api_key_type() === 'Legacy',
            'API key type detected as Legacy'
        );

        // Restore Rich key for other tests
        update_option('OneSignalWPSetting', $test_settings);
    }

    private function test_post_notification_workflow() {
        WP_CLI::line('');
        WP_CLI::line('Test Suite: Post Notification Workflow');
        WP_CLI::line('---');

        // Create a test post
        $post_data = array(
            'post_title' => 'E2E Test Post - ' . time(),
            'post_content' => 'This is a test post for E2E testing',
            'post_status' => 'draft',
            'post_type' => 'post'
        );

        $post_id = wp_insert_post($post_data);

        $this->assert(
            $post_id > 0,
            'Test post created',
            $post_id ? '' : 'Post creation failed'
        );

        if ($post_id <= 0) {
            return;
        }

        // Add notification metadata
        update_post_meta($post_id, 'os_meta', array(
            'os_update' => '1',
            'os_title' => 'E2E Test Notification',
            'os_content' => 'Test content',
            'os_segment' => 'All'
        ));

        // Verify metadata was saved
        $os_meta = get_post_meta($post_id, 'os_meta', true);

        $this->assert(
            !empty($os_meta) && $os_meta['os_update'] === '1',
            'Post metadata saved correctly'
        );

        // Simulate publishing (Note: We don't actually send notifications in tests)
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ));

        $published_post = get_post($post_id);

        $this->assert(
            $published_post->post_status === 'publish',
            'Post published successfully'
        );

        // Clean up
        wp_delete_post($post_id, true);
    }

    private function test_post_type_validation() {
        WP_CLI::line('');
        WP_CLI::line('Test Suite: Post Type Validation');
        WP_CLI::line('---');

        // Set up custom post types in settings
        update_option('OneSignalWPSetting', array(
            'app_id' => 'test-app-id',
            'app_rest_api_key' => 'test-key',
            'notification_on_page' => 1,
            'allowed_custom_post_types' => 'product,event'
        ));

        // Test post type 'post' (always allowed)
        $this->assert(
            onesignal_is_post_type_allowed('post'),
            'Post type "post" is allowed'
        );

        // Test post type 'page' (enabled in settings)
        $this->assert(
            onesignal_is_post_type_allowed('page'),
            'Post type "page" is allowed when enabled'
        );

        // Test custom post type 'product' (in allowed list)
        $this->assert(
            onesignal_is_post_type_allowed('product'),
            'Custom post type "product" is allowed'
        );

        // Test custom post type 'event' (in allowed list)
        $this->assert(
            onesignal_is_post_type_allowed('event'),
            'Custom post type "event" is allowed'
        );

        // Test custom post type 'portfolio' (NOT in allowed list)
        $this->assert(
            !onesignal_is_post_type_allowed('portfolio'),
            'Custom post type "portfolio" is not allowed'
        );

        // Test with page disabled
        update_option('OneSignalWPSetting', array(
            'app_id' => 'test-app-id',
            'app_rest_api_key' => 'test-key',
            'notification_on_page' => 0
        ));

        $this->assert(
            !onesignal_is_post_type_allowed('page'),
            'Post type "page" is not allowed when disabled'
        );
    }

    private function test_metadata_storage() {
        WP_CLI::line('');
        WP_CLI::line('Test Suite: Metadata Storage');
        WP_CLI::line('---');

        // Create a test post
        $post_id = wp_insert_post(array(
            'post_title' => 'Metadata Test Post',
            'post_content' => 'Test content',
            'post_status' => 'draft',
            'post_type' => 'post'
        ));

        if ($post_id <= 0) {
            $this->assert(false, 'Metadata test post creation', 'Failed to create test post');
            return;
        }

        // Test notification ID storage
        $notification_id = 'test-notification-' . time();
        onesignal_save_notification_id($post_id, $notification_id);

        $retrieved_id = onesignal_get_notification_id($post_id);

        $this->assert(
            $retrieved_id === $notification_id,
            'Notification ID stored and retrieved correctly'
        );

        // Test previous publish date storage
        $test_date = '2024-01-15 10:30:00';
        update_post_meta($post_id, 'os_previous_publish_date', $test_date);

        $retrieved_date = get_post_meta($post_id, 'os_previous_publish_date', true);

        $this->assert(
            $retrieved_date === $test_date,
            'Previous publish date stored correctly'
        );

        // Test os_meta storage
        $os_meta = array(
            'os_update' => '1',
            'os_title' => 'Custom Title',
            'os_content' => 'Custom Content',
            'os_segment' => 'Premium Users',
            'os_mobile_url' => 'myapp://post/123'
        );

        update_post_meta($post_id, 'os_meta', $os_meta);

        $retrieved_meta = get_post_meta($post_id, 'os_meta', true);

        $this->assert(
            is_array($retrieved_meta) && $retrieved_meta['os_title'] === 'Custom Title',
            'OneSignal metadata stored correctly'
        );

        // Clean up
        wp_delete_post($post_id, true);
    }

    private function print_summary() {
        WP_CLI::line('');
        WP_CLI::line('========================================');
        WP_CLI::line('Test Summary');
        WP_CLI::line('========================================');
        WP_CLI::line('');
        WP_CLI::line("Total Tests:  {$this->test_count}");
        WP_CLI::success("Passed:       {$this->passed_count}");

        if ($this->failed_count > 0) {
            WP_CLI::error("Failed:       {$this->failed_count}", false);
        } else {
            WP_CLI::line("Failed:       0");
        }

        WP_CLI::line('');

        if ($this->failed_count === 0) {
            WP_CLI::success('All E2E tests passed!');
        } else {
            WP_CLI::error("{$this->failed_count} test(s) failed.", true);
        }
    }
}

// Run the tests
$e2e_tests = new OneSignal_E2E_Tests();
$e2e_tests->run_all_tests();
