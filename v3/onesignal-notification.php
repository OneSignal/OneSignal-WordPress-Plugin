<?php

require_once plugin_dir_path(__FILE__) . 'onesignal-helpers.php';
defined('ABSPATH') or die('This page may not be accessed directly.');

// Register the notification function, called when a post status changes
add_action('transition_post_status', 'onesignal_schedule_notification', 10, 3);

// Function to schedule notification
function onesignal_schedule_notification($new_status, $old_status, $post)
{
  if (($new_status === 'publish') || ($new_status === 'future')) {
        $onesignal_wp_settings = get_option("OneSignalWPSetting");

        // check if update is on.
        $update = !empty($_POST['os_update']) ? $_POST['os_update'] : $post->os_update;

        // do not send notification if not enabled
        if (empty($update)) {
            return;
        }

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
        if (is_wp_error($response)) {
            error_log('API request failed: ' . $response->get_error_message());
        }
    }
}
