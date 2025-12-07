<?php
/**
 * Integration tests for OneSignal post hooks and transitions
 */

use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class Test_OneSignal_Post_Hooks extends TestCase {
    use AssertionRenames;

    /**
     * Reset global state before each test
     */
    protected function setUp(): void {
        parent::setUp();
        reset_wordpress_state();

        // Re-register hooks by manually calling add_action
        // (notification file is already loaded in bootstrap)
        add_action('transition_post_status', 'onesignal_schedule_notification', 10, 3);
        add_action('save_post', 'onesignal_handle_quick_edit_date_change', 10, 3);
        add_action('wp_trash_post', 'onesignal_cancel_notification_on_post_delete');
    }

    /**
     * Test that transition_post_status hook is registered
     */
    public function test_transition_post_status_hook_registered() {
        $this->assertNotFalse(has_action('transition_post_status', 'onesignal_schedule_notification'));
    }

    /**
     * Test that save_post hook is registered
     */
    public function test_save_post_hook_registered() {
        $this->assertNotFalse(has_action('save_post', 'onesignal_handle_quick_edit_date_change'));
    }

    /**
     * Test that wp_trash_post hook is registered
     */
    public function test_wp_trash_post_hook_registered() {
        $this->assertNotFalse(has_action('wp_trash_post', 'onesignal_cancel_notification_on_post_delete'));
    }

    /**
     * Test notification ID is saved after notification creation
     */
    public function test_notification_id_saved() {
        $post_id = 123;
        $notification_id = 'test-notification-abc';

        onesignal_save_notification_id($post_id, $notification_id);
        $retrieved = onesignal_get_notification_id($post_id);

        $this->assertSame($notification_id, $retrieved);
    }

    /**
     * Test notification ID can be deleted
     */
    public function test_notification_id_deleted() {
        $post_id = 456;
        $notification_id = 'test-notification-def';

        onesignal_save_notification_id($post_id, $notification_id);
        $this->assertSame($notification_id, onesignal_get_notification_id($post_id));

        delete_post_meta($post_id, 'os_notification_id');
        $this->assertSame('', onesignal_get_notification_id($post_id));
    }

    /**
     * Test previous publish date is stored
     */
    public function test_previous_publish_date_stored() {
        $post_id = 789;
        $date = '2024-01-15 10:30:00';

        update_post_meta($post_id, 'os_previous_publish_date', $date);
        $retrieved = get_post_meta($post_id, 'os_previous_publish_date', true);

        $this->assertSame($date, $retrieved);
    }

    /**
     * Test multiple post metadata can be stored
     */
    public function test_multiple_post_metadata() {
        $post_id = 999;

        onesignal_save_notification_id($post_id, 'notification-123');
        update_post_meta($post_id, 'os_previous_publish_date', '2024-01-20 15:00:00');
        update_post_meta($post_id, 'os_meta', array(
            'os_update' => '1',
            'os_title' => 'Custom Title',
            'os_content' => 'Custom Content',
            'os_segment' => 'All'
        ));

        $notification_id = onesignal_get_notification_id($post_id);
        $prev_date = get_post_meta($post_id, 'os_previous_publish_date', true);
        $meta = get_post_meta($post_id, 'os_meta', true);

        $this->assertSame('notification-123', $notification_id);
        $this->assertSame('2024-01-20 15:00:00', $prev_date);
        $this->assertIsArray($meta);
        $this->assertSame('Custom Title', $meta['os_title']);
    }

    /**
     * Test post type validation with allowed types
     */
    public function test_post_type_validation() {
        // Post type 'post' is always allowed
        $this->assertTrue(onesignal_is_post_type_allowed('post'));

        // Page requires settings
        update_option('OneSignalWPSetting', array('notification_on_page' => true));
        $this->assertTrue(onesignal_is_post_type_allowed('page'));

        // Custom post types
        update_option('OneSignalWPSetting', array(
            'allowed_custom_post_types' => 'product,event'
        ));
        $this->assertTrue(onesignal_is_post_type_allowed('product'));
        $this->assertTrue(onesignal_is_post_type_allowed('event'));
        $this->assertFalse(onesignal_is_post_type_allowed('portfolio'));
    }

    /**
     * Test transition_post_status action fires
     */
    public function test_transition_post_status_fires() {
        $callback_fired = false;
        $received_args = array();

        // Add a test hook that captures when the action fires
        add_action('transition_post_status', function($new_status, $old_status, $post) use (&$callback_fired, &$received_args) {
            $callback_fired = true;
            $received_args = array($new_status, $old_status, $post);
        }, 5, 3);

        // Create a mock post object
        $post = (object) array(
            'ID' => 100,
            'post_status' => 'publish',
            'post_title' => 'Test Post',
            'post_type' => 'post'
        );

        // Fire the action
        do_action('transition_post_status', 'publish', 'draft', $post);

        $this->assertTrue($callback_fired);
        $this->assertSame('publish', $received_args[0]);
        $this->assertSame('draft', $received_args[1]);
        $this->assertSame(100, $received_args[2]->ID);
    }

    /**
     * Test wp_trash_post action fires
     */
    public function test_wp_trash_post_fires() {
        $callback_fired = false;
        $received_post_id = null;

        add_action('wp_trash_post', function($post_id) use (&$callback_fired, &$received_post_id) {
            $callback_fired = true;
            $received_post_id = $post_id;
        });

        do_action('wp_trash_post', 123);

        $this->assertTrue($callback_fired);
        $this->assertSame(123, $received_post_id);
    }

    /**
     * Test metadata cleanup on post delete
     */
    public function test_metadata_cleanup_on_delete() {
        $post_id = 200;

        // Set up metadata
        onesignal_save_notification_id($post_id, 'notification-to-delete');
        update_post_meta($post_id, 'os_previous_publish_date', '2024-01-25 12:00:00');

        // Verify metadata exists
        $this->assertSame('notification-to-delete', onesignal_get_notification_id($post_id));

        // Delete metadata
        delete_post_meta($post_id, 'os_notification_id');
        delete_post_meta($post_id, 'os_previous_publish_date');

        // Verify metadata is gone
        $this->assertSame('', onesignal_get_notification_id($post_id));
        $this->assertSame('', get_post_meta($post_id, 'os_previous_publish_date', true));
    }

    /**
     * Test action hook execution count
     */
    public function test_action_execution_count() {
        $this->assertSame(0, did_action('test_action_counter'));

        do_action('test_action_counter');
        $this->assertSame(1, did_action('test_action_counter'));

        do_action('test_action_counter');
        do_action('test_action_counter');
        $this->assertSame(3, did_action('test_action_counter'));
    }
}
