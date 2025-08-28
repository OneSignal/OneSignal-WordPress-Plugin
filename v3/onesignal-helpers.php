<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

/**
 * Determines the type of OneSignal API Key.
 *
 * @return string "Legacy" for legacy keys, "Rich" for newer keys, or "Unknown" if no key is set.
 */
function onesignal_get_api_key_type()
{
    $apiKey = get_option('OneSignalWPSetting')['app_rest_api_key'] ?? '';

    if (empty($apiKey)) {
        return "Unknown";
    }

    return (strpos($apiKey, 'os_v') === 0) ? "Rich" : "Legacy";
}

/* If >= PHP 5.4, ENT_HTML401 | ENT_QUOTES will correctly decode most entities including both double and single quotes.
   In PHP 5.3, ENT_HTML401 does not exist, so we have to use `str_replace("&apos;","'", $value)` before feeding it to html_entity_decode(). */
function decode_entities($string) {
  $HTML_ENTITY_DECODE_FLAGS = ENT_QUOTES;
  if (defined('ENT_HTML401')) {
    $HTML_ENTITY_DECODE_FLAGS = ENT_HTML401 | $HTML_ENTITY_DECODE_FLAGS;
  }
  return html_entity_decode(str_replace(['&apos;', '&#x27;', '&#39;', '&quot;'], '\'', $string), $HTML_ENTITY_DECODE_FLAGS, 'UTF-8');
}

function sanitize_content_for_excerpt($content) {
  $decoded = wp_specialchars_decode($content);
  $stripped_slashes = stripslashes_deep($decoded);
  $cleaned_content = wp_strip_all_tags(strip_shortcodes($stripped_slashes));
  return $cleaned_content;
}

function onesignal_is_post_type_allowed($post_type) {
    if ($post_type === 'post') return true;

    $settings = get_option("OneSignalWPSetting");

    if($post_type === 'page' && !empty($settings['notification_on_page'])) return true;

    if (empty($settings['allowed_custom_post_types'])) return false;

    $allowed_post_types = array_map('trim', explode(',', $settings['allowed_custom_post_types']));
    return in_array($post_type, $allowed_post_types);
}

function onesignal_save_notification_id($post_id, $notification_id) {
    update_post_meta($post_id, 'os_notification_id', sanitize_text_field($notification_id));
}

function onesignal_get_notification_id($post_id) {
    return get_post_meta($post_id, 'os_notification_id', true);
}

function onesignal_cancel_notification($notification_id) {
    if (empty($notification_id)) {
        return false;
    }

    $apiKeyType = onesignal_get_api_key_type();
    $authorizationHeader = $apiKeyType === "Rich"
        ? 'Key ' . get_option('OneSignalWPSetting')['app_rest_api_key']
        : 'Basic ' . get_option('OneSignalWPSetting')['app_rest_api_key'];

    $args = array(
        'method' => 'DELETE',
        'headers' => array(
            'Authorization' => $authorizationHeader,
            'accept' => 'application/json',
        ),
    );

    $response = wp_remote_request("https://onesignal.com/api/v1/notifications/{$notification_id}?app_id=" . get_option('OneSignalWPSetting')['app_id'], $args);
    
    if (is_wp_error($response)) {
        error_log('Failed to cancel OneSignal notification: ' . $response->get_error_message());
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    return $response_code === 200;
}
