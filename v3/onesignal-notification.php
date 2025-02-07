<?php

require_once plugin_dir_path(__FILE__) . 'onesignal-helpers.php';
defined('ABSPATH') or die('This page may not be accessed directly.');

// Store the status in post meta using a custom meta key '_onesignal_notification_sent'
// This can then be used to check if the notification was sent
function store_notification_status($post_id, $success) {
    return update_post_meta($post_id, '_onesignal_notification_sent', $success);
}

// Get the notification status from the post meta
function check_notification_status($request) {
    $post_id = $request['post_id'];
    $status = get_post_meta($post_id, '_onesignal_notification_sent', true);
    return rest_ensure_response(['sent' => (bool)$status]);
}

// Add the admin-ajax handler to check the notification status
add_action('wp_ajax_check_onesignal_notification', function() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized');
    }

    $post_id = intval($_POST['post_id']);
    $status = get_post_meta($post_id, '_onesignal_notification_sent', true);
    wp_send_json(['success' => (bool)$status]);
});

// Register the notification function, called when a post status changes
add_action('transition_post_status', 'onesignal_schedule_notification', 10, 3);

// Add a handler for resetting notification status
add_action('wp_ajax_reset_onesignal_status', 'reset_onesignal_notification_status');
function reset_onesignal_notification_status() {
    check_ajax_referer('onesignal_notification_nonce', '_ajax_nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
    }

    store_notification_status($post_id, false);
    wp_send_json_success(array('message' => 'Notification status reset'));
}

// Function to schedule notification
function onesignal_schedule_notification($new_status, $old_status, $post) {
    if (($new_status === 'publish') || ($new_status === 'future')) {
        $onesignal_wp_settings = get_option("OneSignalWPSetting");

        // check if update is on.
        $update = !empty($_POST['os_update']) ? $_POST['os_update'] : $post->os_update;

        store_notification_status($post->ID, false);

        // Only reset status if we're going to send a notification
        if (empty($update)) {
            return;
        }


        // Reset status to false before sending
        store_notification_status($post->ID, false);

        // set api params
        $title = !empty($_POST['os_title']) ? sanitize_text_field($_POST['os_title']) : decode_entities(get_bloginfo('name'));
        $content = !empty($_POST['os_content']) ? sanitize_text_field($_POST['os_content']) : $post->post_title;
        $excerpt = sanitize_content_for_excerpt($content);
        $segment = $_POST['os_segment'] ?? 'All';
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
            if (!empty($_POST['os_mobile_url'])) {
                $fields['app_url'] = $_POST['os_mobile_url'];
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

        // Make the API request and log errors
        if (defined('REST_REQUEST') && REST_REQUEST) return;
        $response = wp_remote_post('https://onesignal.com/api/v1/notifications', $args);

        // Store the final status based on the response
        $success = false;

        // Check if the notification was sent successfully
        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $success = $response_code === 200 && !empty($body['id']);
        } else {
            error_log('Notification failed: ' . $response->get_error_message());
        }

        // Store the final status
        store_notification_status($post->ID, $success);
    }
}
