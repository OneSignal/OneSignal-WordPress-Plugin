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
                  'welcome_notification_url' => '',
                  'notification_on_post' => true,
                  'notification_on_post_from_plugin' => false,
                  'is_site_https_firsttime' => 'unset',
                  'is_site_https' => false,
                  'use_modal_prompt' => false,
                  'subdomain' => "",
                  'origin' => "",
                  'default_title' => "",
                  'default_icon' => "",
                  'default_url' => "",
                  'app_rest_api_key' => "",
                  'safari_web_id' => "",
                  'showNotificationIconFromPostThumbnail' => false,
                  'chrome_auto_dismiss_notifications' => false,
                  'prompt_customize_enable' => 'CALCULATE_SPECIAL_VALUE',
                  'prompt_action_message' => "",
                  'prompt_example_notification_title_desktop' => "",
                  'prompt_example_notification_message_desktop' => "",
                  'prompt_example_notification_title_mobile' => "",
                  'prompt_example_notification_message_mobile' => "",
                  'prompt_example_notification_caption' => "",
                  'prompt_accept_button_text' => "",
                  'prompt_cancel_button_text' => "",
                  'prompt_showcredit' => true,
                  'notifyButton_position' => 'bottom-right',
                  'notifyButton_size' => 'medium',
                  'notifyButton_theme' => 'default',
                  'notifyButton_enable' => 'CALCULATE_SPECIAL_VALUE',
                  'notifyButton_prenotify' => true,
                  'notifyButton_customize_enable' => 'CALCULATE_SPECIAL_VALUE',
                  'notifyButton_customize_colors_enable' => false,
                  'notifyButton_customize_offset_enable' => false,
                  'notifyButton_color_background' => '',
                  'notifyButton_color_foreground' => '',
                  'notifyButton_color_badge_background' => '',
                  'notifyButton_color_badge_foreground' => '',
                  'notifyButton_color_badge_border' => '',
                  'notifyButton_color_pulse' => '',
                  'notifyButton_color_popup_button_background' => '',
                  'notifyButton_color_popup_button_background_hover' => '',
                  'notifyButton_color_popup_button_background_active' => '',
                  'notifyButton_color_popup_button_color' => '',
                  'notifyButton_offset_bottom' => '',
                  'notifyButton_offset_left' => '',
                  'notifyButton_offset_right' => '',
                  'notifyButton_showcredit' => true,
                  'notifyButton_message_prenotify' => '',
                  'notifyButton_tip_state_unsubscribed' => '',
                  'notifyButton_tip_state_subscribed' => '',
                  'notifyButton_tip_state_blocked' => '',
                  'notifyButton_message_action_subscribed' => '',
                  'notifyButton_message_action_resubscribed' => '',
                  'notifyButton_message_action_unsubscribed' => '',
                  'notifyButton_dialog_main_title' => '',
                  'notifyButton_dialog_main_button_subscribe' => '',
                  'notifyButton_dialog_main_button_unsubscribe' => '',
                  'notifyButton_dialog_blocked_title' => '',
                  'notifyButton_dialog_blocked_message' => '',
                  'prompt_only_on_posts' => false
                  );

    $legacies = array(
        'send_welcome_notification.legacyKey' => 'no_welcome_notification',
        'send_welcome_notification.invertLegacyValue' => true,
        'send_welcome_notification.default' => true,
        'prompt_auto_register.legacyKey' => 'no_auto_register',
        'prompt_auto_register.invertLegacyValue' => true,
        'prompt_auto_register.default' => false
    );

    $is_new_user = false;

    // If not set or empty, load a fresh empty array
    if (!isset($onesignal_wp_settings)) {
      $onesignal_wp_settings = get_option("OneSignalWPSetting");
      if (empty( $onesignal_wp_settings )) {
         $is_new_user = true;
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
        else if ($value === "CALCULATE_SPECIAL_VALUE") {
	        // Do nothing, handle below
        }
        else {
            if (!array_key_exists($key, $onesignal_wp_settings)) {
               $onesignal_wp_settings[$key] = $value;
            }
        }
    }

    // Special case for notify button
    if (!array_key_exists('notifyButton_enable', $onesignal_wp_settings)) {
        if ( $is_new_user ) {
            // Enable the notify button by default for new sites
            $onesignal_wp_settings['notifyButton_enable'] = true;
        } else {
            // Do NOT enable the notify button for existing WordPress sites, since they might have a lot of users
            $onesignal_wp_settings['notifyButton_enable'] = false;
        }
    }

    // Special case for notify button customization
    if (!array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings)) {
      if ( $is_new_user ) {
        // Initially turn off notifyButton_customize_enable by default for new sites
        $onesignal_wp_settings['notifyButton_customize_enable'] = true;
      } else {
        $text_customize_settings = array(
          'notifyButton_message_prenotify',
          'notifyButton_tip_state_unsubscribed',
          'notifyButton_tip_state_subscribed',
          'notifyButton_tip_state_blocked',
          'notifyButton_message_action_subscribed',
          'notifyButton_message_action_resubscribed',
          'notifyButton_message_action_unsubscribed',
          'notifyButton_dialog_main_title',
          'notifyButton_dialog_main_button_subscribe',
          'notifyButton_dialog_main_button_unsubscribe',
          'notifyButton_dialog_blocked_title',
          'notifyButton_dialog_blocked_message'
        );
        $was_customized = false;
        foreach ($text_customize_settings as $text_customize_setting) {
          if ($onesignal_wp_settings[$text_customize_setting] !== "") {
            $was_customized = true;
          }
        }
        $onesignal_wp_settings['notifyButton_customize_enable'] = $was_customized;
      }
    }

    // Special case for prompt customization
    if (!array_key_exists('prompt_customize_enable', $onesignal_wp_settings)) {
      if ( $is_new_user ) {
        // Initially turn off prompt_customize_enable by default for new sites
        $onesignal_wp_settings['prompt_customize_enable'] = true;
      } else {
        $text_customize_settings = array(
          'prompt_action_message',
          'prompt_example_notification_title_desktop',
          'prompt_example_notification_message_desktop',
          'prompt_example_notification_title_mobile',
          'prompt_example_notification_message_mobile',
          'prompt_example_notification_caption',
          'prompt_accept_button_text',
          'prompt_cancel_button_text'
        );
        $was_customized = false;
        foreach ($text_customize_settings as $text_customize_setting) {
          if ($onesignal_wp_settings[$text_customize_setting] !== "") {
            $was_customized = true;
          }
        }
        $onesignal_wp_settings['prompt_customize_enable'] = $was_customized;
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