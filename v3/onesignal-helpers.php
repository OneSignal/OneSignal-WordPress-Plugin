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
