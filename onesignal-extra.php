<?php
add_filter('onesignal_initialize_sdk', 'onesignal_initialize_sdk_filter', 10, 4);
function onesignal_initialize_sdk_filter($onesignal_settings) {
    return true;
}