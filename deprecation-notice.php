<?php
// Add an admin notice for outdated plugin version
add_action('admin_notices', 'notify_plugin_update');
function notify_plugin_update() {
    $dismissed_timestamp = get_option('onesignal_deprecation_notice_dismissed_time');
    $time_passed = !$dismissed_timestamp || (time() - $dismissed_timestamp) > (7 * DAY_IN_SECONDS);
    $cutoff_date = strtotime('2025-12-31 23:59:59'); // Set cutoff date and time

    // Show the notice only if it's before the cutoff date and the notice has not been recently dismissed
    if (time() <= $cutoff_date && $time_passed) {
        echo '
        <div class="notice notice-warning is-dismissible" id="onesignal-update-notice">
            <p>
              <strong>
                OneSignal Push Important Update:</strong>
                All push notification configuration options are moving to OneSignal.com.
                Please ensure your settings are updated by December 13, 2024 to avoid any service disruptions.
                <a href="https://documentation.onesignal.com/docs/wordpress-plugin-30">Learn More.</a>
            </p>
        </div>';
    }
}

// Handle AJAX dismissal of the notice
add_action('wp_ajax_dismiss_onesignal_deprecation_notice', 'dismiss_onesignal_deprecation_notice');
function dismiss_onesignal_deprecation_notice() {
    // Check if nonce is valid
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dismiss_notice_nonce')) {
        // If nonce is invalid, log the error and return a JSON error response
        error_log('Invalid nonce for AJAX request');
        wp_send_json_error(array('message' => 'Invalid nonce'));
        return;
    }

    // Update the option if nonce validation passes
    update_option('onesignal_deprecation_notice_dismissed_time', time());
    wp_send_json_success(array('message' => 'Dismissal timestamp set successfully'));
}

add_action('admin_enqueue_scripts', 'enqueue_notify_plugin_scripts');
function enqueue_notify_plugin_scripts() {
    wp_enqueue_script('onesignal-admin', plugin_dir_url(__FILE__) . 'deprecation-notice.js', array('jquery'), '1.0', true);
    wp_localize_script('onesignal-admin', 'onesignalData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dismiss_notice_nonce')
    ));
}
