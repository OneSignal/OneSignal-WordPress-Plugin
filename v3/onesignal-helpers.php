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
