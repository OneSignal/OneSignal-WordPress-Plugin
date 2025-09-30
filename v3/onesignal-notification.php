<?php

require_once plugin_dir_path(__FILE__) . 'onesignal-helpers.php';
defined('ABSPATH') or die('This page may not be accessed directly.');

// Register the notification function, called when a post status changes
add_action('transition_post_status', 'onesignal_schedule_notification', 10, 3);

// Register the quick-edit handler to handle publish date changes
add_action('save_post', 'onesignal_handle_quick_edit_date_change', 10, 3);

// Register handler to cancel scheduled notifications when posts are deleted
add_action('wp_trash_post', 'onesignal_cancel_notification_on_post_delete');

// Core function to create and send/schedule a notification
function onesignal_create_notification($post, $notification_options = array())
{
    $onesignal_wp_settings = get_option("OneSignalWPSetting");

    // Cancel any existing scheduled notification for this post
    $existing_notification_id = onesignal_get_notification_id($post->ID);
    if (!empty($existing_notification_id)) {
        onesignal_cancel_notification($existing_notification_id);
    }

    // Store the current publish date for future quick-edit comparisons
    update_post_meta($post->ID, 'os_previous_publish_date', $post->post_date);

    // set api params - use provided options or defaults
    $title = $notification_options['title'] ?? decode_entities(get_bloginfo('name'));
    $content = $notification_options['content'] ?? $post->post_title;
    $excerpt = sanitize_content_for_excerpt($content);
    $segment = $notification_options['segment'] ?? 'All';
    $mobile_url = $notification_options['mobile_url'] ?? '';
    $config_utm_additional_url_params = $onesignal_wp_settings['utm_additional_url_params'] ?? '';

    $url = get_permalink($post->ID);
    if (!empty($config_utm_additional_url_params)) {
      // validate and encode the URL parameters
      $params = urlencode($config_utm_additional_url_params);
      $url = $url . (strpos($url, '?') === false ? '?' : '&') . $params;
    }

    $apiKeyType = onesignal_get_api_key_type();
    $authorizationHeader = $apiKeyType === "Rich"
        ? 'Key ' . get_option('OneSignalWPSetting')['app_rest_api_key']
        : 'Basic ' . get_option('OneSignalWPSetting')['app_rest_api_key'];

    $args = array(
        'headers' => array(
            'Authorization' => $authorizationHeader,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ),
        'body' => json_encode(array(
            'app_id' => get_option('OneSignalWPSetting')['app_id'],
            'headings' => array('en' => $title),
            'contents' => array('en' => $excerpt),
            'included_segments' => array($segment),
            'web_push_topic' => str_replace(' ', '-', strtolower($segment)),
            'isAnyWeb' => true,
        )),
    );

    $fields = json_decode($args['body'], true);

    // Conditionally send or schedule
    $postDate = new DateTime('now', new DateTimeZone('UTC'));
    $sendDate = new DateTime($post->post_date_gmt, new DateTimeZone('UTC'));

    if ($sendDate > $postDate) {
        // Schedule the notification to be sent in the future
        $fields['send_after'] = $sendDate->format('Y-m-d H:i:s e');
    }

    // Conditionally include mobile parameters
    if (get_option('OneSignalWPSetting')['send_to_mobile_platforms'] && get_option('OneSignalWPSetting')['send_to_mobile_platforms'] == 1) {
        $fields['isIos'] = true;
        $fields['isAndroid'] = true;
        $fields['isHuawei'] = true;
        $fields['isWP_WNS'] = true;
        if (!empty($mobile_url)) {
            $fields['app_url'] = $mobile_url;
            $fields['web_url'] = get_permalink($post->ID);
        } else {
            $fields['url'] = $url;
        }
    } else {
        $fields['url'] = $url;
    }
    // Set notification images based on the post's featured image
    if (has_post_thumbnail($post->ID)) {
        // Get the post thumbnail ID
        $post_thumbnail_id = get_post_thumbnail_id($post->ID);

        // Retrieve image URLs for different sizes
        $thumbnail_size_url = wp_get_attachment_image_src($post_thumbnail_id, array(192, 192), true)[0];
        $large_size_url = wp_get_attachment_image_src($post_thumbnail_id, 'large', true)[0];

        // Assign image URLs to notification fields
        $fields['firefox_icon'] =  $thumbnail_size_url;
        $fields['chrome_web_icon'] =  $thumbnail_size_url;
        $fields['chrome_web_image'] = $large_size_url;
    }

    // Include any fields from onesignal_send_notification filter
    if (has_filter('onesignal_send_notification')) {
        $fields = apply_filters('onesignal_send_notification', $fields, $post->ID);
    }

    $args['body'] = json_encode($fields);

    if (defined('REST_REQUEST') && REST_REQUEST) return;
    $response = wp_remote_post('https://onesignal.com/api/v1/notifications', $args);
    if (is_wp_error($response)) {
        error_log('API request failed: ' . $response->get_error_message());
    } else {
        // Save the notification ID for potential future cancellation
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            if (!empty($response_data['id'])) {
                onesignal_save_notification_id($post->ID, $response_data['id']);
            }
        }
    }
}

// Function to schedule notification (called on post status transitions)
function onesignal_schedule_notification($new_status, $old_status, $post)
{
    if (($new_status === 'publish') || ($new_status === 'future')) {
        // check if update is on.
        $update = !empty($_POST['os_update']) ? $_POST['os_update'] : $post->os_update;
        
        // do not send notification if not enabled
        if (empty($update)) {
            return;
        }

        // Prepare notification options from POST data
        $notification_options = array(
            'title' => !empty($_POST['os_title']) ? sanitize_text_field($_POST['os_title']) : null,
            'content' => !empty($_POST['os_content']) ? sanitize_text_field($_POST['os_content']) : null,
            'segment' => $_POST['os_segment'] ?? 'All',
            'mobile_url' => $_POST['os_mobile_url'] ?? ''
        );

        // Call the core notification function
        onesignal_create_notification($post, $notification_options);
    }
}

// Function to handle quick-edit publish date changes
function onesignal_handle_quick_edit_date_change($post_id, $post, $update)
{
    // Check if this is an autosave, revision, or not an update
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !$update) {
        return;
    }

    // Check if the post type is allowed for notifications
    if (!onesignal_is_post_type_allowed($post->post_type)) {
        return;
    }

    // Only handle posts with 'future' status (scheduled posts)
    if ($post->post_status !== 'future') {
        return;
    }

    // Get the previous publish date stored in post meta
    $previous_publish_date = get_post_meta($post_id, 'os_previous_publish_date', true);
    $current_publish_date = $post->post_date;

    // If this is the first time we're tracking the publish date, store it and return
    if (empty($previous_publish_date)) {
        update_post_meta($post_id, 'os_previous_publish_date', $current_publish_date);
        return;
    }

    // Check if the publish date has actually changed
    if ($previous_publish_date !== $current_publish_date) {
        // Cancel any existing scheduled notification for this post
        $existing_notification_id = onesignal_get_notification_id($post_id);
        if (!empty($existing_notification_id)) {
            $cancelled = onesignal_cancel_notification($existing_notification_id);
            if ($cancelled) {
                // Clear the stored notification ID since we cancelled it
                delete_post_meta($post_id, 'os_notification_id');
            }
        }

        // Update the stored publish date
        update_post_meta($post_id, 'os_previous_publish_date', $current_publish_date);

        // Create a new notification with default options (no custom title/content from metabox)
        // This will use the post title and default settings
        onesignal_create_notification($post);
    }
}

// Function to cancel scheduled notifications when a post is deleted
function onesignal_cancel_notification_on_post_delete($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return;
    }

    $existing_notification_id = onesignal_get_notification_id($post_id);
    if (!empty($existing_notification_id)) {
        $cancelled = onesignal_cancel_notification($existing_notification_id);
        if ($cancelled) {
            delete_post_meta($post_id, 'os_notification_id');
            delete_post_meta($post_id, 'os_previous_publish_date');
        }
    }
}
