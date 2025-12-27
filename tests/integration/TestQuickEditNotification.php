<?php
/**
 * Integration tests for OneSignal quick edit notification bug fix
 * Tests that quick edit respects global notification settings
 */

use WP_Mock\Tools\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class Test_OneSignal_QuickEdit_Notification extends TestCase {
    use AssertionRenames;

    /**
     * Track if onesignal_create_notification was called
     */
    private static $notification_called = false;
    private static $notification_args = null;

    /**
     * Override setUpContentFiltering to fix PHPUnit 9.6 compatibility issue
     */
    protected function setUpContentFiltering() {
        return;
    }

    /**
     * Set up WP_Mock and WordPress function mocks before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Reset notification tracking
        self::$notification_called = false;
        self::$notification_args = null;
        
        global $wp_post_meta, $wp_options, $_POST;
        $wp_post_meta = array();
        $wp_options = array();
        $_POST = array();

        // Mock get_option
        WP_Mock::userFunction('get_option')
            ->andReturnUsing(function($option, $default = false) {
                global $wp_options;
                if ($option === 'OneSignalWPSetting') {
                    return $wp_options[$option] ?? $default;
                }
                return $wp_options[$option] ?? $default;
            });

        // Mock update_option
        WP_Mock::userFunction('update_option')
            ->andReturnUsing(function($option, $value) {
                global $wp_options;
                $wp_options[$option] = $value;
                return true;
            });

        // Mock get_post_meta
        WP_Mock::userFunction('get_post_meta')
            ->andReturnUsing(function($post_id, $key, $single) {
                global $wp_post_meta;
                if (!isset($wp_post_meta[$post_id][$key])) {
                    return $single ? '' : array();
                }
                return $single ? $wp_post_meta[$post_id][$key] : array($wp_post_meta[$post_id][$key]);
            });

        // Mock update_post_meta
        WP_Mock::userFunction('update_post_meta')
            ->andReturnUsing(function($post_id, $meta_key, $meta_value) {
                global $wp_post_meta;
                $wp_post_meta[$post_id][$meta_key] = $meta_value;
                return true;
            });

        // Mock wp_verify_nonce
        WP_Mock::userFunction('wp_verify_nonce')
            ->andReturnUsing(function($nonce, $action) {
                global $_POST;
                // Return true if nonce exists in POST and matches expected action
                if (isset($_POST['onesignal_v3_metabox_nonce']) && 
                    $_POST['onesignal_v3_metabox_nonce'] === $nonce &&
                    $action === 'onesignal_v3_metabox_save') {
                    return true;
                }
                return false;
            });

        // Mock current_user_can
        WP_Mock::userFunction('current_user_can')
            ->andReturn(true);

        // Mock wp_is_post_autosave
        WP_Mock::userFunction('wp_is_post_autosave')
            ->andReturn(false);

        // Mock wp_is_post_revision
        WP_Mock::userFunction('wp_is_post_revision')
            ->andReturn(false);

        // Mock onesignal_create_notification to track calls
        // We'll use a global function mock since it's defined in the notification file
        if (!function_exists('onesignal_create_notification')) {
            // This will be overridden by the actual function, but we can track calls
        }

        // Mock other required functions
        WP_Mock::userFunction('get_permalink')
            ->andReturnUsing(function($post_id) {
                return 'https://example.com/post/' . $post_id;
            });

        WP_Mock::userFunction('get_bloginfo')
            ->with('name')
            ->andReturn('Test Blog');

        WP_Mock::userFunction('has_post_thumbnail')
            ->andReturn(false);

        WP_Mock::userFunction('sanitize_text_field')
            ->andReturnUsing(function($str) {
                return trim(strip_tags($str));
            });

        WP_Mock::userFunction('sanitize_url')
            ->andReturnUsing(function($url) {
                return filter_var($url, FILTER_SANITIZE_URL);
            });

        WP_Mock::userFunction('wp_specialchars_decode')
            ->andReturnUsing(function($string) {
                return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
            });

        WP_Mock::userFunction('stripslashes_deep')
            ->andReturnUsing(function($value) {
                return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
            });

        WP_Mock::userFunction('wp_strip_all_tags')
            ->andReturnUsing(function($string) {
                return strip_tags($string);
            });

        WP_Mock::userFunction('strip_shortcodes')
            ->andReturnUsing(function($content) {
                return preg_replace('/\[.*?\]/', '', $content);
            });

        // Mock HTTP functions to prevent actual API calls
        WP_Mock::userFunction('wp_remote_post')
            ->andReturn(array(
                'response' => array('code' => 200),
                'body' => json_encode(array('id' => 'test-notification-id'))
            ));

        WP_Mock::userFunction('is_wp_error')
            ->andReturn(false);

        WP_Mock::userFunction('wp_remote_retrieve_response_code')
            ->andReturn(200);

        WP_Mock::userFunction('wp_remote_retrieve_body')
            ->andReturn(json_encode(array('id' => 'test-notification-id')));

        // Mock onesignal_get_default_settings
        WP_Mock::userFunction('onesignal_get_default_settings')
            ->andReturn(array(
                'notification_on_post' => 0,
                'notification_on_page' => 0,
                'notification_on_post_update' => 0,
                'notification_on_page_update' => 0,
                'notification_on_post_from_plugin' => 0
            ));

        // Set default settings
        $this->set_default_settings();
    }

    /**
     * Set default OneSignal settings
     */
    private function set_default_settings($overrides = array()) {
        global $wp_options;
        $defaults = array(
            'app_id' => 'test-app-id',
            'app_rest_api_key' => 'test-api-key',
            'notification_on_post' => 0,
            'notification_on_page' => 0,
            'notification_on_post_update' => 0,
            'notification_on_page_update' => 0,
            'notification_on_post_from_plugin' => 0,
            'allowed_custom_post_types' => '',
            'send_to_mobile_platforms' => 0
        );
        $wp_options['OneSignalWPSetting'] = array_merge($defaults, $overrides);
    }

    /**
     * Create a mock post object
     */
    private function create_mock_post($post_id, $post_type = 'post', $post_status = 'publish', $post_date = '2024-01-01 12:00:00') {
        $post = new stdClass();
        $post->ID = $post_id;
        $post->post_type = $post_type;
        $post->post_status = $post_status;
        $post->post_date = $post_date;
        $post->post_date_gmt = $post_date;
        $post->post_title = 'Test Post ' . $post_id;
        return $post;
    }

    /**
     * Call onesignal_schedule_notification
     */
    private function call_schedule_notification($new_status, $old_status, $post) {
        // Call the function
        onesignal_schedule_notification($new_status, $old_status, $post);
    }
    
    /**
     * Check if notification was created by checking if notification ID was saved
     */
    private function was_notification_created($post_id) {
        $notification_id = get_post_meta($post_id, 'os_notification_id', true);
        return !empty($notification_id);
    }

    /**
     * Test that quick edit respects notification_on_post setting when disabled
     */
    public function test_quick_edit_post_respects_notification_on_post_disabled() {
        global $_POST;
        
        // Set global setting to disabled
        $this->set_default_settings(array('notification_on_post' => 0));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(1, 'post', 'publish');
        
        // Transition from draft to publish (new publish via quick edit)
        $this->call_schedule_notification('publish', 'draft', $post);
        
        // Since notification_on_post is disabled, notification should NOT be created
        // We verify this by checking that no notification ID was saved
        $notification_id = get_post_meta(1, 'os_notification_id', true);
        $this->assertEmpty($notification_id, 'Notification should not be created when notification_on_post is disabled');
    }

    /**
     * Test that quick edit respects notification_on_post setting when enabled
     */
    public function test_quick_edit_post_respects_notification_on_post_enabled() {
        global $_POST;
        
        // Set global setting to enabled
        $this->set_default_settings(array('notification_on_post' => 1));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(2, 'post', 'publish');
        
        // Transition from draft to publish (new publish via quick edit)
        $this->call_schedule_notification('publish', 'draft', $post);
        
        // Since notification_on_post is enabled, notification should be created
        $this->assertTrue($this->was_notification_created(2), 'Notification should be created when notification_on_post is enabled');
    }

    /**
     * Test that quick edit respects notification_on_post_update setting when disabled
     */
    public function test_quick_edit_post_respects_notification_on_post_update_disabled() {
        global $_POST;
        
        // Set global setting to disabled
        $this->set_default_settings(array('notification_on_post_update' => 0));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(3, 'post', 'publish');
        
        // Transition from publish to publish (update via quick edit)
        $this->call_schedule_notification('publish', 'publish', $post);
        
        // Since notification_on_post_update is disabled, notification should NOT be created
        $this->assertFalse($this->was_notification_created(3), 'Notification should not be created when notification_on_post_update is disabled');
    }

    /**
     * Test that quick edit respects notification_on_post_update setting when enabled
     */
    public function test_quick_edit_post_respects_notification_on_post_update_enabled() {
        global $_POST;
        
        // Set global setting to enabled
        $this->set_default_settings(array('notification_on_post_update' => 1));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(4, 'post', 'publish');
        
        // Transition from publish to publish (update via quick edit)
        $this->call_schedule_notification('publish', 'publish', $post);
        
        // Since notification_on_post_update is enabled, notification should be created
        $this->assertTrue($this->was_notification_created(4), 'Notification should be created when notification_on_post_update is enabled');
    }

    /**
     * Test that quick edit respects notification_on_page setting
     */
    public function test_quick_edit_page_respects_notification_on_page() {
        global $_POST;
        
        // Set global setting to enabled
        $this->set_default_settings(array('notification_on_page' => 1));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(5, 'page', 'publish');
        
        // Transition from draft to publish (new publish via quick edit)
        $this->call_schedule_notification('publish', 'draft', $post);
        
        // Since notification_on_page is enabled, notification should be created
        $this->assertTrue($this->was_notification_created(5), 'Notification should be created when notification_on_page is enabled');
    }

    /**
     * Test that quick edit respects notification_on_page_update setting
     */
    public function test_quick_edit_page_respects_notification_on_page_update() {
        global $_POST;
        
        // Set global setting to enabled
        $this->set_default_settings(array('notification_on_page_update' => 1));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(6, 'page', 'publish');
        
        // Transition from publish to publish (update via quick edit)
        $this->call_schedule_notification('publish', 'publish', $post);
        
        // Since notification_on_page_update is enabled, notification should be created
        $this->assertTrue($this->was_notification_created(6), 'Notification should be created when notification_on_page_update is enabled');
    }

    /**
     * Test that quick edit respects notification_on_post for custom post types
     */
    public function test_quick_edit_custom_post_type_respects_notification_on_post() {
        global $_POST;
        
        // Set global setting to enabled and allow custom post type
        $this->set_default_settings(array(
            'notification_on_post' => 1,
            'allowed_custom_post_types' => 'product'
        ));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(7, 'product', 'publish');
        
        // Transition from draft to publish (new publish via quick edit)
        $this->call_schedule_notification('publish', 'draft', $post);
        
        // Since notification_on_post is enabled and post type is allowed, notification should be created
        $this->assertTrue($this->was_notification_created(7), 'Notification should be created for custom post type when notification_on_post is enabled');
    }

    /**
     * Test that quick edit does not send notification for custom post type when setting is disabled
     */
    public function test_quick_edit_custom_post_type_respects_notification_on_post_disabled() {
        global $_POST;
        
        // Set global setting to disabled but allow custom post type
        $this->set_default_settings(array(
            'notification_on_post' => 0,
            'allowed_custom_post_types' => 'product'
        ));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(8, 'product', 'publish');
        
        // Transition from draft to publish (new publish via quick edit)
        $this->call_schedule_notification('publish', 'draft', $post);
        
        // Since notification_on_post is disabled, notification should NOT be created
        $this->assertFalse($this->was_notification_created(8), 'Notification should not be created for custom post type when notification_on_post is disabled');
    }

    /**
     * Test that normal editor with metabox uses POST data, not global settings
     */
    public function test_normal_editor_uses_post_data_not_global_settings() {
        global $_POST;
        
        // Set global setting to disabled
        $this->set_default_settings(array('notification_on_post' => 0));
        
        // Simulate normal editor (with metabox nonce and os_update checked)
        $_POST = array(
            'onesignal_v3_metabox_nonce' => 'valid-nonce',
            'os_update' => 'on'
        );
        
        $post = $this->create_mock_post(9, 'post', 'publish');
        
        // Transition from draft to publish (new publish via normal editor)
        $this->call_schedule_notification('publish', 'draft', $post);
        
        // Even though global setting is disabled, notification should be created because POST data says so
        $this->assertTrue($this->was_notification_created(9), 'Notification should be created when POST data has os_update checked, even if global setting is disabled');
    }

    /**
     * Test that scheduled posts check saved metadata first
     */
    public function test_scheduled_post_checks_saved_metadata_first() {
        global $_POST, $wp_post_meta;
        
        // Set global setting to disabled
        $this->set_default_settings(array('notification_on_post' => 0));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        // Set saved metadata to enabled
        $wp_post_meta[10]['os_meta'] = array('os_update' => 'on');
        
        $post = $this->create_mock_post(10, 'post', 'future');
        
        // Transition to future (scheduled post)
        $this->call_schedule_notification('future', 'draft', $post);
        
        // Even though global setting is disabled, notification should be created because saved metadata says so
        $this->assertTrue($this->was_notification_created(10), 'Scheduled post should use saved metadata even if global setting is disabled');
    }

    /**
     * Test that scheduled posts fall back to global settings when no saved metadata
     */
    public function test_scheduled_post_falls_back_to_global_settings() {
        global $_POST;
        
        // Set global setting to enabled
        $this->set_default_settings(array('notification_on_post' => 1));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        // No saved metadata
        
        $post = $this->create_mock_post(11, 'post', 'future');
        
        // Transition to future (scheduled post)
        $this->call_schedule_notification('future', 'draft', $post);
        
        // Since global setting is enabled and no saved metadata, notification should be created
        $this->assertTrue($this->was_notification_created(11), 'Scheduled post should fall back to global settings when no saved metadata');
    }

    /**
     * Test that custom post type update uses notification_on_post_update
     */
    public function test_custom_post_type_update_uses_notification_on_post_update() {
        global $_POST;
        
        // Set global setting to enabled and allow custom post type
        $this->set_default_settings(array(
            'notification_on_post_update' => 1,
            'allowed_custom_post_types' => 'event'
        ));
        
        // Simulate quick edit (no metabox nonce)
        $_POST = array();
        
        $post = $this->create_mock_post(12, 'event', 'publish');
        
        // Transition from publish to publish (update via quick edit)
        $this->call_schedule_notification('publish', 'publish', $post);
        
        // Since notification_on_post_update is enabled, notification should be created
        $this->assertTrue($this->was_notification_created(12), 'Custom post type update should use notification_on_post_update setting');
    }

    /**
     * Mock onesignal_get_default_settings function
     */
    private function setup_default_settings_mock() {
        WP_Mock::userFunction('onesignal_get_default_settings')
            ->andReturn(array(
                'notification_on_post' => 0,
                'notification_on_page' => 0,
                'notification_on_post_update' => 0,
                'notification_on_page_update' => 0,
                'notification_on_post_from_plugin' => 0
            ));
    }
}

