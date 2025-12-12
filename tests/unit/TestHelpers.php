<?php
/**
 * Unit tests for OneSignal helper functions
 */

use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class Test_OneSignal_Helpers extends TestCase {
    use AssertionRenames;

    /**
     * Set up WP_Mock before each test
     */
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    /**
     * Tear down WP_Mock after each test
     */
    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * Test onesignal_get_api_key_type() with Rich API key
     */
    public function test_get_api_key_type_rich() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_rest_api_key' => 'os_v2_abc123def456'
            ));

        $this->assertSame('Rich', onesignal_get_api_key_type());
    }

    /**
     * Test onesignal_get_api_key_type() with Legacy API key
     */
    public function test_get_api_key_type_legacy() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_rest_api_key' => 'NGY2NmE0ZDgtZjdkMi00YjI4LTk3YmMtM2JmNmQ2NjY4Yjdh'
            ));

        $this->assertSame('Legacy', onesignal_get_api_key_type());
    }

    /**
     * Test onesignal_get_api_key_type() with no API key set
     */
    public function test_get_api_key_type_unknown() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array());

        $this->assertSame('Unknown', onesignal_get_api_key_type());
    }

    /**
     * Test onesignal_get_api_key_type() with empty API key
     */
    public function test_get_api_key_type_empty() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'app_rest_api_key' => ''
            ));

        $this->assertSame('Unknown', onesignal_get_api_key_type());
    }

    /**
     * Test decode_entities() with various HTML entities
     */
    public function test_decode_entities_standard() {
        $this->assertSame("Hello & World", decode_entities("Hello &amp; World"));
        $this->assertSame("Hello < World", decode_entities("Hello &lt; World"));
        $this->assertSame("Hello > World", decode_entities("Hello &gt; World"));
    }

    /**
     * Test decode_entities() with quotes
     */
    public function test_decode_entities_quotes() {
        $this->assertSame("It's working", decode_entities("It&apos;s working"));
        $this->assertSame("It's working", decode_entities("It&#x27;s working"));
        $this->assertSame("It's working", decode_entities("It&#39;s working"));
        $this->assertSame("Say 'Hello'", decode_entities("Say &quot;Hello&quot;"));
    }

    /**
     * Test decode_entities() with mixed entities
     */
    public function test_decode_entities_mixed() {
        $input = "Tom&apos;s &amp; Jerry&#39;s &lt;Adventures&gt;";
        $expected = "Tom's & Jerry's <Adventures>";
        $this->assertSame($expected, decode_entities($input));
    }

    /**
     * Test sanitize_content_for_excerpt() with HTML tags
     */
    public function test_sanitize_content_for_excerpt_strips_tags() {
        $input = "<p>Hello <strong>World</strong></p>";
        $expected = "Hello World";

        WP_Mock::userFunction('wp_specialchars_decode')
            ->with($input)
            ->andReturn($input);

        WP_Mock::userFunction('stripslashes_deep')
            ->with($input)
            ->andReturn($input);

        WP_Mock::userFunction('strip_shortcodes')
            ->with($input)
            ->andReturn($input);

        WP_Mock::userFunction('wp_strip_all_tags')
            ->with($input)
            ->andReturn($expected);

        $this->assertSame($expected, sanitize_content_for_excerpt($input));
    }

    /**
     * Test sanitize_content_for_excerpt() with shortcodes
     */
    public function test_sanitize_content_for_excerpt_strips_shortcodes() {
        $input = "Hello [gallery] World [contact-form]";
        $expected = "Hello  World";
        $decoded = "Hello [gallery] World [contact-form]";
        $no_slashes = "Hello [gallery] World [contact-form]";
        $no_shortcodes = "Hello  World";

        WP_Mock::userFunction('wp_specialchars_decode')
            ->with($input)
            ->andReturn($decoded);

        WP_Mock::userFunction('stripslashes_deep')
            ->with($decoded)
            ->andReturn($no_slashes);

        WP_Mock::userFunction('strip_shortcodes')
            ->with($no_slashes)
            ->andReturn($no_shortcodes);

        WP_Mock::userFunction('wp_strip_all_tags')
            ->with($no_shortcodes)
            ->andReturn($expected);

        $this->assertSame($expected, sanitize_content_for_excerpt($input));
    }

    /**
     * Test sanitize_content_for_excerpt() with slashes
     */
    public function test_sanitize_content_for_excerpt_strips_slashes() {
        $input = "Hello \\'World\\'";
        $expected = "Hello 'World'";
        $decoded = "Hello \\'World\\'";
        $no_slashes = "Hello 'World'";
        $no_shortcodes = "Hello 'World'";

        WP_Mock::userFunction('wp_specialchars_decode')
            ->with($input)
            ->andReturn($decoded);

        WP_Mock::userFunction('stripslashes_deep')
            ->with($decoded)
            ->andReturn($no_slashes);

        WP_Mock::userFunction('strip_shortcodes')
            ->with($no_slashes)
            ->andReturn($no_shortcodes);

        WP_Mock::userFunction('wp_strip_all_tags')
            ->with($no_shortcodes)
            ->andReturn($expected);

        $this->assertSame($expected, sanitize_content_for_excerpt($input));
    }

    /**
     * Test sanitize_content_for_excerpt() with complex HTML
     */
    public function test_sanitize_content_for_excerpt_complex() {
        $input = "<div><p>Hello &amp; <strong>World</strong></p>[shortcode]Text</div>";
        $expected = "Hello & WorldText";
        $decoded = "<div><p>Hello & <strong>World</strong></p>[shortcode]Text</div>";
        $no_slashes = "<div><p>Hello & <strong>World</strong></p>[shortcode]Text</div>";
        $no_shortcodes = "<div><p>Hello & <strong>World</strong></p>Text</div>";

        WP_Mock::userFunction('wp_specialchars_decode')
            ->with($input)
            ->andReturn($decoded);

        WP_Mock::userFunction('stripslashes_deep')
            ->with($decoded)
            ->andReturn($no_slashes);

        WP_Mock::userFunction('strip_shortcodes')
            ->with($no_slashes)
            ->andReturn($no_shortcodes);

        WP_Mock::userFunction('wp_strip_all_tags')
            ->with($no_shortcodes)
            ->andReturn($expected);

        $this->assertSame($expected, sanitize_content_for_excerpt($input));
    }

    /**
     * Test onesignal_is_post_type_allowed() for 'post' type
     */
    public function test_is_post_type_allowed_post() {
        $this->assertTrue(onesignal_is_post_type_allowed('post'));
    }

    /**
     * Test onesignal_is_post_type_allowed() for 'page' type when enabled
     */
    public function test_is_post_type_allowed_page_enabled() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'notification_on_page' => true
            ));

        $this->assertTrue(onesignal_is_post_type_allowed('page'));
    }

    /**
     * Test onesignal_is_post_type_allowed() for 'page' type when disabled
     */
    public function test_is_post_type_allowed_page_disabled() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'notification_on_page' => false
            ));

        $this->assertFalse(onesignal_is_post_type_allowed('page'));
    }

    /**
     * Test onesignal_is_post_type_allowed() for custom post type that is allowed
     */
    public function test_is_post_type_allowed_custom_allowed() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'allowed_custom_post_types' => 'product, event, portfolio'
            ));

        $this->assertTrue(onesignal_is_post_type_allowed('product'));
        $this->assertTrue(onesignal_is_post_type_allowed('event'));
        $this->assertTrue(onesignal_is_post_type_allowed('portfolio'));
        $this->assertFalse(onesignal_is_post_type_allowed('about_me'));
    }

    /**
     * Test onesignal_is_post_type_allowed() for custom post type that is not allowed
     */
    public function test_is_post_type_allowed_custom_not_allowed() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array(
                'allowed_custom_post_types' => 'product, event'
            ));

        $this->assertFalse(onesignal_is_post_type_allowed('portfolio'));
    }

    /**
     * Test onesignal_is_post_type_allowed() with no custom post types configured
     */
    public function test_is_post_type_allowed_no_custom_types() {
        WP_Mock::userFunction('get_option')
            ->with('OneSignalWPSetting')
            ->andReturn(array());

        $this->assertFalse(onesignal_is_post_type_allowed('product'));
    }

    /**
     * Test onesignal_parse_utm_parameters() with valid parameters
     */
    public function test_parse_utm_parameters_valid() {
        $input = "utm_source=twitter&utm_medium=social&utm_campaign=launch";
        $expected = "utm_source=twitter&utm_medium=social&utm_campaign=launch";
        $this->assertSame($expected, onesignal_parse_utm_parameters($input));
    }

    /**
     * Test onesignal_parse_utm_parameters() with leading question mark
     */
    public function test_parse_utm_parameters_leading_question_mark() {
        $input = "?utm_source=twitter&utm_medium=social";
        $expected = "utm_source=twitter&utm_medium=social";
        $this->assertSame($expected, onesignal_parse_utm_parameters($input));
    }

    /**
     * Test onesignal_parse_utm_parameters() with leading ampersand
     */
    public function test_parse_utm_parameters_leading_ampersand() {
        $input = "&utm_source=twitter&utm_medium=social";
        $expected = "utm_source=twitter&utm_medium=social";
        $this->assertSame($expected, onesignal_parse_utm_parameters($input));
    }

    /**
     * Test onesignal_parse_utm_parameters() with empty string
     */
    public function test_parse_utm_parameters_empty() {
        $this->assertSame('', onesignal_parse_utm_parameters(''));
        $this->assertSame('', onesignal_parse_utm_parameters('   '));
        $this->assertSame('', onesignal_parse_utm_parameters('?'));
        $this->assertSame('', onesignal_parse_utm_parameters('&'));
    }

    /**
     * Test onesignal_parse_utm_parameters() filters invalid parameters
     */
    public function test_parse_utm_parameters_filters_invalid() {
        // Parameter without value should be filtered out
        $input = "utm_source=twitter&invalid&utm_medium=social";
        $expected = "utm_source=twitter&utm_medium=social";
        $this->assertSame($expected, onesignal_parse_utm_parameters($input));
    }

    /**
     * Test onesignal_parse_utm_parameters() with invalid key characters
     */
    public function test_parse_utm_parameters_invalid_key_chars() {
        // Keys with invalid characters should be filtered out
        $input = "utm_source=twitter&utm@medium=social&utm_campaign=launch";
        $expected = "utm_source=twitter&utm_campaign=launch";
        $this->assertSame($expected, onesignal_parse_utm_parameters($input));
    }

    /**
     * Test onesignal_parse_utm_parameters() URL encodes values
     */
    public function test_parse_utm_parameters_encodes_values() {
        $input = "utm_source=twitter&utm_campaign=hello world";
        $result = onesignal_parse_utm_parameters($input);

        // Should contain encoded space
        $this->assertStringContainsString('hello%20world', $result);
    }

    /**
     * Test onesignal_parse_utm_parameters() with extra whitespace
     */
    public function test_parse_utm_parameters_whitespace() {
        $input = "  utm_source=twitter  &  utm_medium=social  ";
        $expected = "utm_source=twitter&utm_medium=social";
        $this->assertSame($expected, onesignal_parse_utm_parameters($input));
    }

    /**
     * Test onesignal_save_notification_id() and onesignal_get_notification_id()
     */
    public function test_save_and_get_notification_id() {
        $post_id = 123;
        $notification_id = 'abc-123-def-456';
        $sanitized_id = 'abc-123-def-456';

        WP_Mock::userFunction('sanitize_text_field')
            ->with($notification_id)
            ->andReturn($sanitized_id);

        WP_Mock::userFunction('update_post_meta')
            ->with($post_id, 'os_notification_id', $sanitized_id)
            ->andReturn(true);

        WP_Mock::userFunction('get_post_meta')
            ->with($post_id, 'os_notification_id', true)
            ->andReturn($sanitized_id);

        onesignal_save_notification_id($post_id, $notification_id);
        $retrieved = onesignal_get_notification_id($post_id);

        $this->assertSame($sanitized_id, $retrieved);
    }

    /**
     * Test onesignal_get_notification_id() with no saved ID
     */
    public function test_get_notification_id_none_saved() {
        $post_id = 999;

        WP_Mock::userFunction('get_post_meta')
            ->with($post_id, 'os_notification_id', true)
            ->andReturn('');

        $retrieved = onesignal_get_notification_id($post_id);

        $this->assertSame('', $retrieved);
    }

    /**
     * Test onesignal_save_notification_id() sanitizes input
     */
    public function test_save_notification_id_sanitizes() {
        $post_id = 456;
        $notification_id = '<script>alert("xss")</script>abc-123';
        $sanitized_id = 'alert("xss")abc-123';

        WP_Mock::userFunction('sanitize_text_field')
            ->with($notification_id)
            ->andReturn($sanitized_id);

        WP_Mock::userFunction('update_post_meta')
            ->with($post_id, 'os_notification_id', $sanitized_id)
            ->andReturn(true);

        WP_Mock::userFunction('get_post_meta')
            ->with($post_id, 'os_notification_id', true)
            ->andReturn($sanitized_id);

        onesignal_save_notification_id($post_id, $notification_id);
        $retrieved = onesignal_get_notification_id($post_id);

        // Should be sanitized (tags stripped)
        $this->assertStringNotContainsString('<script>', $retrieved);
        $this->assertStringContainsString('abc-123', $retrieved);
    }
}
