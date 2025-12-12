<?php
/**
 * Integration tests for OneSignal API integration with mocked HTTP responses
 */

use WP_Mock\Tools\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class Test_OneSignal_API_Integration extends TestCase {
    use AssertionRenames;

    /**
     * Override setUpContentFiltering to fix PHPUnit 9.6 compatibility issue
     */
    protected function setUpContentFiltering() {
        return;
    }

    /**
     * Set up WP_Mock and common WordPress function mocks before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        global $wp_http_requests_mock, $wp_post_meta, $test_get_option_overrides;
        $wp_http_requests_mock = array();
        $wp_post_meta = array();
        $test_get_option_overrides = array();

        // Set up get_option with support for test-specific overrides
        WP_Mock::userFunction('get_option')
            ->andReturnUsing(function($option, $default = false) use (&$test_get_option_overrides) {
                // Check for test-specific override first
                if (isset($test_get_option_overrides[$option])) {
                    return $test_get_option_overrides[$option];
                }
                // Default settings for OneSignalWPSetting
                if ($option === 'OneSignalWPSetting') {
                    return array(
                        'app_id' => 'test-app-id',
                        'app_rest_api_key' => 'test-api-key',
                        'send_to_mobile_platforms' => 0,
                        'notification_on_post' => 1
                    );
                }
                return $default;
            });

        // Mock common WordPress functions
        WP_Mock::userFunction('get_permalink')
            ->andReturnUsing(function($post_id) {
                return 'https://example.com/post/' . $post_id;
            });

        WP_Mock::userFunction('get_bloginfo')
            ->with('name')
            ->andReturn('Test Blog');

        WP_Mock::userFunction('has_post_thumbnail')
            ->andReturn(false);

        WP_Mock::userFunction('get_post_meta')
            ->andReturnUsing(function($post_id, $key, $single) {
                global $wp_post_meta;
                if (!isset($wp_post_meta[$post_id][$key])) {
                    return $single ? '' : array();
                }
                return $single ? $wp_post_meta[$post_id][$key] : array($wp_post_meta[$post_id][$key]);
            });

        WP_Mock::userFunction('update_post_meta')
            ->andReturnUsing(function($post_id, $meta_key, $meta_value) {
                global $wp_post_meta;
                $wp_post_meta[$post_id][$meta_key] = $meta_value;
                return true;
            });

        WP_Mock::userFunction('sanitize_text_field')
            ->andReturnUsing(function($str) {
                return trim(strip_tags($str));
            });

        // Mock HTTP functions using the global mock array
        WP_Mock::userFunction('wp_remote_post')
            ->andReturnUsing(function($url, $args) {
                global $wp_http_requests_mock;
                if (isset($wp_http_requests_mock[$url])) {
                    return $wp_http_requests_mock[$url];
                }
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array('id' => 'test-notification-id'))
                );
            });

        WP_Mock::userFunction('wp_remote_request')
            ->andReturnUsing(function($url, $args) {
                global $wp_http_requests_mock;
                if (isset($wp_http_requests_mock[$url])) {
                    return $wp_http_requests_mock[$url];
                }
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array('success' => true))
                );
            });

        WP_Mock::userFunction('wp_remote_retrieve_response_code')
            ->andReturnUsing(function($response) {
                return $response['response']['code'] ?? 0;
            });

        WP_Mock::userFunction('wp_remote_retrieve_body')
            ->andReturnUsing(function($response) {
                return $response['body'] ?? '';
            });

        WP_Mock::userFunction('is_wp_error')
            ->andReturnUsing(function($thing) {
                return ($thing instanceof WP_Error);
            });

        // Mock WordPress hook functions
        WP_Mock::userFunction('has_filter')
            ->andReturnUsing(function($tag, $function_to_check = false) {
                global $wp_filters;
                if (!isset($wp_filters[$tag])) {
                    return false;
                }
                if ($function_to_check === false) {
                    return true;
                }
                foreach ($wp_filters[$tag] as $priority => $functions) {
                    foreach ($functions as $function_data) {
                        if ($function_data['function'] === $function_to_check) {
                            return $priority;
                        }
                    }
                }
                return false;
            });
    }

    /**
     * Test successful API notification creation
     */
    public function test_successful_notification_creation() {
        // Ensure get_option returns the default settings
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_id' => 'test-app-id',
                'app_rest_api_key' => 'test-api-key',
                'send_to_mobile_platforms' => 0,
                'notification_on_post' => 1
            ));

        // Mock successful API response
        mock_http_request('https://onesignal.com/api/v1/notifications', array(
            'response' => array('code' => 200),
            'body' => json_encode(array(
                'id' => 'notification-abc-123',
                'recipients' => 150
            ))
        ));

        // Create a mock post
        $post = (object) array(
            'ID' => 100,
            'post_title' => 'Test Post Title',
            'post_date' => '2024-01-15 10:00:00',
            'post_date_gmt' => '2024-01-15 10:00:00',
            'post_type' => 'post'
        );

        // Create notification
        onesignal_create_notification($post);

        // Verify notification ID was saved
        $saved_id = onesignal_get_notification_id($post->ID);
        $this->assertSame('notification-abc-123', $saved_id);
    }

    /**
     * Test API notification with custom options
     */
    public function test_notification_with_custom_options() {
        mock_http_request('https://onesignal.com/api/v1/notifications', array(
            'response' => array('code' => 200),
            'body' => json_encode(array('id' => 'custom-notification-456'))
        ));

        $post = (object) array(
            'ID' => 200,
            'post_title' => 'Post Title',
            'post_date' => '2024-01-15 12:00:00',
            'post_date_gmt' => '2024-01-15 12:00:00',
            'post_type' => 'post'
        );

        $custom_options = array(
            'title' => 'Custom Title',
            'content' => 'Custom Content',
            'segment' => 'Premium Users',
            'mobile_url' => 'myapp://post/200'
        );

        onesignal_create_notification($post, $custom_options);

        $saved_id = onesignal_get_notification_id($post->ID);
        $this->assertSame('custom-notification-456', $saved_id);
    }

    /**
     * Test API error handling with WP_Error
     */
    public function test_api_error_handling() {
        // Mock API error response
        $error = new WP_Error('http_request_failed', 'Connection timeout');
        mock_http_request('https://onesignal.com/api/v1/notifications', $error);

        $post = (object) array(
            'ID' => 300,
            'post_title' => 'Error Test Post',
            'post_date' => '2024-01-15 14:00:00',
            'post_date_gmt' => '2024-01-15 14:00:00',
            'post_type' => 'post'
        );

        // Create notification (should handle error gracefully)
        onesignal_create_notification($post);

        // No notification ID should be saved on error
        $saved_id = onesignal_get_notification_id($post->ID);
        $this->assertSame('', $saved_id);
    }

    /**
     * Test API with non-200 response code
     */
    public function test_api_non_200_response() {
        // Mock 400 Bad Request
        mock_http_request('https://onesignal.com/api/v1/notifications', array(
            'response' => array('code' => 400),
            'body' => json_encode(array('errors' => array('Invalid app_id')))
        ));

        $post = (object) array(
            'ID' => 400,
            'post_title' => 'Bad Request Test',
            'post_date' => '2024-01-15 16:00:00',
            'post_date_gmt' => '2024-01-15 16:00:00',
            'post_type' => 'post'
        );

        onesignal_create_notification($post);

        // No notification ID should be saved on non-200
        $saved_id = onesignal_get_notification_id($post->ID);
        $this->assertSame('', $saved_id);
    }

    /**
     * Test notification cancellation with successful API response
     */
    public function test_notification_cancellation_success() {
        $notification_id = 'cancel-test-123';

        // Mock successful cancellation
        mock_http_request(
            'https://onesignal.com/api/v1/notifications/' . $notification_id . '?app_id=test-app-id',
            array(
                'response' => array('code' => 200),
                'body' => json_encode(array('success' => true))
            )
        );

        $result = onesignal_cancel_notification($notification_id);
        $this->assertTrue($result);
    }

    /**
     * Test notification cancellation with error
     */
    public function test_notification_cancellation_error() {
        $notification_id = 'cancel-error-456';

        // Mock failed cancellation
        mock_http_request(
            'https://onesignal.com/api/v1/notifications/' . $notification_id . '?app_id=test-app-id',
            array(
                'response' => array('code' => 404),
                'body' => json_encode(array('errors' => array('Notification not found')))
            )
        );

        $result = onesignal_cancel_notification($notification_id);
        $this->assertFalse($result);
    }

    /**
     * Test notification cancellation with empty ID
     */
    public function test_notification_cancellation_empty_id() {
        $result = onesignal_cancel_notification('');
        $this->assertFalse($result);

        $result = onesignal_cancel_notification(null);
        $this->assertFalse($result);
    }

    /**
     * Test Rich API key authorization header
     */
    public function test_rich_api_key_authorization() {
        // Set test-specific override for get_option
        global $test_get_option_overrides;
        $test_get_option_overrides['OneSignalWPSetting'] = array(
            'app_id' => 'test-app-id',
            'app_rest_api_key' => 'os_v2_rich_key_123'
        );

        $api_key_type = onesignal_get_api_key_type();
        $this->assertSame('Rich', $api_key_type);
        
        // Clean up
        unset($test_get_option_overrides['OneSignalWPSetting']);
    }

    /**
     * Test Legacy API key authorization header
     */
    public function test_legacy_api_key_authorization() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_id' => 'test-app-id',
                'app_rest_api_key' => 'legacy_key_456'
            ));

        $api_key_type = onesignal_get_api_key_type();
        $this->assertSame('Legacy', $api_key_type);
    }

    /**
     * Test existing notification is cancelled before creating new one
     */
    public function test_cancel_existing_before_new_notification() {
        $post_id = 500;
        $old_notification_id = 'old-notification-789';
        $new_notification_id = 'new-notification-abc';

        // Mock get_option for this test
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_id' => 'test-app-id',
                'app_rest_api_key' => 'test-api-key',
                'send_to_mobile_platforms' => 0,
                'notification_on_post' => 1
            ));

        // Save an existing notification ID
        onesignal_save_notification_id($post_id, $old_notification_id);
        $this->assertSame($old_notification_id, onesignal_get_notification_id($post_id));

        // Mock cancellation of old notification
        mock_http_request(
            'https://onesignal.com/api/v1/notifications/' . $old_notification_id . '?app_id=test-app-id',
            array(
                'response' => array('code' => 200),
                'body' => json_encode(array('success' => true))
            )
        );

        // Mock creation of new notification
        mock_http_request('https://onesignal.com/api/v1/notifications', array(
            'response' => array('code' => 200),
            'body' => json_encode(array('id' => $new_notification_id))
        ));

        $post = (object) array(
            'ID' => $post_id,
            'post_title' => 'Updated Post',
            'post_date' => '2024-01-15 18:00:00',
            'post_date_gmt' => '2024-01-15 18:00:00',
            'post_type' => 'post'
        );

        // Create new notification
        onesignal_create_notification($post);

        // Verify new notification ID replaces old one
        $saved_id = onesignal_get_notification_id($post_id);
        $this->assertSame($new_notification_id, $saved_id);
    }

    /**
     * Test notification with UTM parameters
     */
    public function test_notification_with_utm_parameters() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_id' => 'test-app-id',
                'app_rest_api_key' => 'test-key',
                'utm_additional_url_params' => 'utm_source=wordpress&utm_medium=push&utm_campaign=test',
                'send_to_mobile_platforms' => 0
            ));

        mock_http_request('https://onesignal.com/api/v1/notifications', array(
            'response' => array('code' => 200),
            'body' => json_encode(array('id' => 'utm-notification-123'))
        ));

        $post = (object) array(
            'ID' => 600,
            'post_title' => 'UTM Test Post',
            'post_date' => '2024-01-15 20:00:00',
            'post_date_gmt' => '2024-01-15 20:00:00',
            'post_type' => 'post'
        );

        onesignal_create_notification($post);

        $saved_id = onesignal_get_notification_id($post->ID);
        $this->assertSame('utm-notification-123', $saved_id);
    }

    /**
     * Test notification response body parsing
     */
    public function test_notification_response_parsing() {
        $notification_id = 'parsed-notification-999';

        mock_http_request('https://onesignal.com/api/v1/notifications', array(
            'response' => array('code' => 200),
            'body' => json_encode(array(
                'id' => $notification_id,
                'recipients' => 500,
                'external_id' => 'ext-123'
            ))
        ));

        $post = (object) array(
            'ID' => 700,
            'post_title' => 'Parse Test',
            'post_date' => '2024-01-15 22:00:00',
            'post_date_gmt' => '2024-01-15 22:00:00',
            'post_type' => 'post'
        );

        onesignal_create_notification($post);

        // Only the ID should be saved
        $saved_id = onesignal_get_notification_id($post->ID);
        $this->assertSame($notification_id, $saved_id);
    }
}
