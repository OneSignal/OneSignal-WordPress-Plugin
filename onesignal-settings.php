<?php
class OneSignal {
  public static function get_onesignal_settings() {

    $defaults = array(
                  'app_id' => '',
                  'gcm_sender_id' => '',
                  'prompt_auto_register' => 'CALCULATE_LEGACY_VALUE',
                  'send_welcome_notification' => 'CALCULATE_LEGACY_VALUE',
                  'welcome_notification_title' => '',
                  'welcome_notification_message' => '',
                  'notification_on_post' => true,
                  'notification_on_post_from_plugin' => true,
                  'use_http' => false,
                  'use_modal_prompt' => false,
                  'subdomain' => "",
                  'origin' => "",
                  'default_title' => "",
                  'default_icon' => "",
                  'default_url' => "",
                  'app_rest_api_key' => "",
                  'safari_web_id' => "",
                  'prompt_action_message' => "",
                  'prompt_example_notification_title_desktop' => "",
                  'prompt_example_notification_message_desktop' => "",
                  'prompt_example_notification_title_mobile' => "",
                  'prompt_example_notification_message_mobile' => "",
                  'prompt_example_notification_caption' => "",
                  'prompt_accept_button_text' => "",
                  'prompt_cancel_button_text' => "",
                  'prompt_showcredit' => true,
                  'bell_position' => 'bottom-right',
                  'bell_size' => 'medium',
                  'bell_theme' => 'default',
                  'bell_enable' => false,
                  'bell_prenotify' => true,
                  'bell_showcredit' => true,
                  'bell_message_prenotify' => '',
                  'bell_tip_state_unsubscribed' => '',
                  'bell_tip_state_subscribed' => '',
                  'bell_tip_state_blocked' => '',
                  'bell_message_action_subscribed' => '',
                  'bell_message_action_resubscribed' => '',
                  'bell_message_action_unsubscribed' => '',
                  'bell_dialog_main_title' => '',
                  'bell_dialog_main_button_subscribe' => '',
                  'bell_dialog_main_button_unsubscribe' => '',
                  'bell_dialog_blocked_title' => '',
                  'bell_dialog_blocked_message' => ''
                  );

    $legacies = array(
        'send_welcome_notification.legacyKey' => 'no_welcome_notification',
        'send_welcome_notification.invertLegacyValue' => true,
        'send_welcome_notification.default' => true,
        'prompt_auto_register.legacyKey' => 'no_auto_register',
        'prompt_auto_register.invertLegacyValue' => true,
        'prompt_auto_register.default' => true
    );

    // If not set or empty, load a fresh empty array
    if (!isset($onesignal_wp_settings)) {
      $onesignal_wp_settings = get_option("OneSignalWPSetting");
      if (empty( $onesignal_wp_settings )) {
         $onesignal_wp_settings = array();
      }
    }

    // Assign defaults if the key doesn't exist in $onesignal_wp_settings
    // Except for those with value CALCULATE_LEGACY_VALUE -- we need special logic for legacy values that used to exist in previous plugin versions
    reset($defaults);
    while (list($key, $value) = each($defaults)) {
        if ($value === "CALCULATE_LEGACY_VALUE") {
            if (!array_key_exists($key, $onesignal_wp_settings)) {
               $legacyKey = $legacies[$key . '.legacyKey'];
               $inverted = (array_key_exists($key . '.invertLegacyValue', $legacies) && $legacies[$key . '.invertLegacyValue']);
               $default = $legacies[$key . '.default'];
               if (array_key_exists($legacyKey, $onesignal_wp_settings)) {
                 if ($inverted) {
                    $onesignal_wp_settings[$key] = !$onesignal_wp_settings[$legacyKey];
                 } else {
                    $onesignal_wp_settings[$key] = $onesignal_wp_settings[$legacyKey];
                 }
               } else {
                 $onesignal_wp_settings[$key] = $default;
               }
            }
        }
        else {
            if (!array_key_exists($key, $onesignal_wp_settings)) {
               $onesignal_wp_settings[$key] = $value;
            }
        }
    }
    
    return $onesignal_wp_settings;
  }
  
  public static function save_onesignal_settings($settings) {
    $onesignal_wp_settings = $settings;
    update_option("OneSignalWPSetting", $onesignal_wp_settings);
  }
}
?>