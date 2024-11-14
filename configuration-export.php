<?php

function onesignal_handle_export()
{
    if (isset($_POST['plugin_action']) && $_POST['plugin_action'] === 'export_settings') {

        if (!check_admin_referer('onesignal_export_nonce')) {
            wp_die(__('Security check failed', 'onesignal-push'));
        }

        $settings = get_option('OneSignalWPSetting');

        // name and define settings groups
        $groups = [
            'OneSignal App ID' => '/^app_id/',
            'General Options' => '/^(chrome_auto_|persist_|default_|utm_additional_)/',
            'Slide Prompt Customizations' => '/^(prompt_customize_|prompt_action_|prompt_accept_|prompt_cancel_|prompt_auto_register)/',
            'Subscription Bell Customizations' => '/^notifyButton_/',
            'Welcome Notification Customizations'  => '/^(send_welcome_|welcome_)/',
            'Plugin Settings & HTTP Setup - NO LONGER REQUIRED / DEPRECATED' => '/^(allowed_custom_|customize_http_|custom_manifest_|gcm_|is_site_|notification_on_|notification_title|onesignal_sw_|origin|prompt_auto_accept_title|prompt_example_|prompt_site_name|send_to_mobile_|showNotification|show_gcm_|how_notification_send_|subdomain|use_)/'
        ];

        // sort settings into the defined groups
        foreach ($settings as $key => $value) {
            foreach ($groups as $group_name => $pattern) {
                if (preg_match($pattern, $key)) {
                    $grouped_settings[$group_name][$key] = is_array($value) ? json_encode($value) : $value;
                    break;
                }
            }
        }

        // create txt file with main title, group names, and settings.
        $txt_data = "OneSignal Push Configuration Export\n\n\n";

        foreach ($groups as $group_name => $pattern) {
            if (isset($grouped_settings[$group_name])) {
                $txt_data .= "=== $group_name ===\n";

                foreach ($grouped_settings[$group_name] as $key => $value) {
                    $txt_data .= "$key: $value\n";
                }

                $txt_data .= "\n\n";
            }
        }

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="onesignal-configuration-export.txt"');
        header('Content-Length: ' . strlen($txt_data));
        header('Pragma: public');

        echo $txt_data;
        exit;
    }
}

add_action('admin_init', 'onesignal_handle_export');
