<?php
/**
 * Integration tests for OneSignal settings management
 */

use WP_Mock\Tools\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class Test_OneSignal_Settings extends TestCase {
    use AssertionRenames;

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
        
        global $wp_options;
        $wp_options = array();

        // Mock get_option and update_option to use global storage
        WP_Mock::userFunction('get_option')
            ->andReturnUsing(function($option, $default = false) {
                global $wp_options;
                return $wp_options[$option] ?? $default;
            });

        WP_Mock::userFunction('update_option')
            ->andReturnUsing(function($option, $value) {
                global $wp_options;
                $wp_options[$option] = $value;
                return true;
            });
    }

    /**
     * Test saving and loading basic settings
     */
    public function test_settings_save_and_load() {
        $settings = array(
            'app_id' => 'test-app-id-123',
            'app_rest_api_key' => 'test-api-key-456',
            'notification_on_post' => 1,
            'notification_on_page' => 0
        );

        update_option('OneSignalWPSetting', $settings);
        $loaded = get_option('OneSignalWPSetting');

        $this->assertSame($settings, $loaded);
    }

    /**
     * Test settings persist across multiple reads
     */
    public function test_settings_persist() {
        $settings = array(
            'app_id' => 'persistent-app-id',
            'app_rest_api_key' => 'persistent-key'
        );

        update_option('OneSignalWPSetting', $settings);

        // Read multiple times
        $first_read = get_option('OneSignalWPSetting');
        $second_read = get_option('OneSignalWPSetting');

        $this->assertSame($first_read, $second_read);
        $this->assertSame('persistent-app-id', $second_read['app_id']);
    }

    /**
     * Test updating individual settings
     */
    public function test_settings_partial_update() {
        // Set initial settings
        $initial = array(
            'app_id' => 'initial-id',
            'app_rest_api_key' => 'initial-key',
            'notification_on_post' => 0
        );
        update_option('OneSignalWPSetting', $initial);

        // Update one field
        $settings = get_option('OneSignalWPSetting');
        $settings['notification_on_post'] = 1;
        update_option('OneSignalWPSetting', $settings);

        // Verify
        $updated = get_option('OneSignalWPSetting');
        $this->assertSame('initial-id', $updated['app_id']);
        $this->assertSame('initial-key', $updated['app_rest_api_key']);
        $this->assertSame(1, $updated['notification_on_post']);
    }

    /**
     * Test getting non-existent settings returns default
     */
    public function test_settings_default_value() {
        $result = get_option('NonExistentOption', 'default-value');
        $this->assertSame('default-value', $result);
    }

    /**
     * Test settings with all configuration options
     */
    public function test_settings_all_options() {
        $settings = array(
            'app_id' => 'full-app-id',
            'app_rest_api_key' => 'os_v2_full-key',
            'notification_on_post' => 1,
            'notification_on_page' => 1,
            'notification_on_post_from_plugin' => 1,
            'send_to_mobile_platforms' => 1,
            'notification_on_post_update' => 0,
            'notification_on_page_update' => 0,
            'utm_additional_url_params' => 'utm_source=wordpress&utm_medium=push',
            'allowed_custom_post_types' => 'product,event,portfolio'
        );

        update_option('OneSignalWPSetting', $settings);
        $loaded = get_option('OneSignalWPSetting');

        $this->assertSame($settings, $loaded);
        $this->assertSame('full-app-id', $loaded['app_id']);
        $this->assertSame('os_v2_full-key', $loaded['app_rest_api_key']);
        $this->assertSame('utm_source=wordpress&utm_medium=push', $loaded['utm_additional_url_params']);
    }

    /**
     * Test settings work with API key type detection
     */
    public function test_settings_with_api_key_type() {
        // Test with Rich API key
        update_option('OneSignalWPSetting', array(
            'app_rest_api_key' => 'os_v2_rich_key_123'
        ));

        $this->assertSame('Rich', onesignal_get_api_key_type());

        // Test with Legacy API key
        update_option('OneSignalWPSetting', array(
            'app_rest_api_key' => 'legacy_key_456'
        ));

        $this->assertSame('Legacy', onesignal_get_api_key_type());
    }
}
