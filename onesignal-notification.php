<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Register the notification function, called when a post is published
add_action('publish_post', 'onesignal_send_notification', 30);

// Function to send a notification
function onesignal_send_notification($post_id)
{

    // Get the post
    $post = get_post($post_id);

    // check if update is on.
    $update = $_POST['os_update'] ?? '';

    // do not send notification if not enabled
    if (empty($update)) {
        return;
    }

    // set api params
    $title = !empty($_POST['os_title']) ? $_POST['os_title'] : $post->post_title;
    $content = !empty($_POST['os_content']) ? $_POST['os_content'] : $post->post_content;
    $segment = $_POST['os_segment'] ?? 'All';

    $args = array(
        'headers' => array(
            'Authorization' => 'Basic ' . get_option('OneSignalWPSetting')['app_rest_api_key'],
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ),
        'body' => json_encode(array(
            'app_id' => get_option('OneSignalWPSetting')['app_id'],
            'headings' => array('en' => $title),
            'contents' => array('en' => wp_strip_all_tags($content)),
            'included_segments' => array($segment),
            'web_push_topic' => str_replace(' ', '-', strtolower($segment)),
            'isAnyWeb' => true,
        )),
    );


    $fields = json_decode($args['body'], true);

    // Conditionally include mobile parameters
    if (get_option('OneSignalWPSetting')['send_to_mobile_platforms'] && get_option('OneSignalWPSetting')['send_to_mobile_platforms'] == 1) {
        $fields['isIos'] = true;
        $fields['isAndroid'] = true;
        $fields['isHuawei'] = true;
        $fields['isWP_WNS'] = true;
        if (!empty($_POST['os_mobile_url'])) {
            $fields['app_url'] = $_POST['os_mobile_url'];
            $fields['web_url'] = get_permalink($post_id);
        } else {
            $fields['url'] = get_permalink($post_id);
        }
    } else {
        $fields['url'] = get_permalink($post_id);
    }
    // Set notification images based on the post's featured image
    if (has_post_thumbnail($post_id)) {
        // Get the post thumbnail ID
        $post_thumbnail_id = get_post_thumbnail_id($post_id);

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
        $fields = apply_filters('onesignal_send_notification', $fields, $post_id);
    }

    $args['body'] = json_encode($fields);

    // Make the API request and log errors
    if (defined('REST_REQUEST') && REST_REQUEST) return;
    $response = wp_remote_post('https://onesignal.com/api/v1/notifications', $args);
    if (is_wp_error($response)) {
        error_log('API request failed: ' . $response->get_error_message());
    }
}
