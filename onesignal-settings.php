<?php

defined( 'ABSPATH' ) or die('This page may not be accessed directly.');

class OneSignal {
  public static function get_onesignal_settings() {
    /*
      During first-time setup, all the keys here will be created with their
      default values, except for keys with value 'CALCULATE_LEGACY_VALUE' or
      'CALCULATE_SPECIAL_VALUE'. These special keys aren't created until further
      below.
     */
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
                  'showNotificationIconFromPostThumbnail' => true,
                  'showNotificationImageFromPostThumbnail' => 'CALCULATE_SPECIAL_VALUE',
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
                  'prompt_auto_accept_title' => "",
                  'prompt_site_name' => "",
                  'notifyButton_position' => 'bottom-right',
                  'notifyButton_size' => 'medium',
                  'notifyButton_theme' => 'default',
                  'notifyButton_enable' => 'CALCULATE_SPECIAL_VALUE',
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
                  'notifyButton_showAfterSubscribed' => true,
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
                  'utm_additional_url_params' => '',
                  'allowed_custom_post_types' => '',
                  'notification_title' => OneSignalUtils::decode_entities(get_bloginfo('name')),
                  'send_to_mobile_platforms' => false,
                  'show_gcm_sender_id' => false,
                  'use_custom_manifest' => false,
                  'custom_manifest_url' => '',
                  'use_custom_sdk_init' => false,
                  'show_notification_send_status_message' => true,
                  'use_http_permission_request' => 'CALCULATE_SPECIAL_VALUE',
                  'persist_notifications' => 'CALCULATE_SPECIAL_VALUE'
                  );

    $legacies = array(
        'send_welcome_notification.legacyKey' => 'no_welcome_notification',
        'send_welcome_notification.invertLegacyValue' => true,
        'send_welcome_notification.default' => true,
        'prompt_auto_register.legacyKey' => 'no_auto_register',
        'prompt_auto_register.invertLegacyValue' => true,
        'prompt_auto_register.default' => false,
    );

    $is_new_user = false;

    // If not set or empty, load a fresh empty array
    $onesignal_wp_settings = get_option("OneSignalWPSetting");
    if (empty( $onesignal_wp_settings )) {
        $is_new_user = true;
        $onesignal_wp_settings = array();
    }

    // Assign defaults if the key doesn't exist in $onesignal_wp_settings
    // Except for those with value CALCULATE_LEGACY_VALUE -- we need special logic for legacy values that used to exist in previous plugin versions
    reset($defaults);
    foreach ($defaults as $key => $value) {
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

    // These boolean settings are on by default for new installs only, and off by default on plugin upgrades,
    // so as to minimize impact of change to existing behavior during automated upgrades.
    $onByDefaultForNewInstalls = array(
        // Do NOT enable the notify button for existing WordPress sites,
        // since they may not like the way their notification changes
        'showNotificationImageFromPostThumbnail',
        // Do NOT enable the notify button for existing WordPress sites,
        // since they might have a lot of users
        'prompt_enable',
        // Do NOT enable for existing WordPress sites,
        // since it breaks existing prompt behavior
        'use_http_permission_request'
    );
    foreach ($onByDefaultForNewInstalls as $key) {
        if (!array_key_exists($key, $onesignal_wp_settings)) {
          if ( $is_new_user ) {
            $onesignal_wp_settings[$key] = true;
          } else {
            $onesignal_wp_settings[$key] = false;
          }
    }

    // These settings are groups of customized values
    // and a boolean flag to indicate customization
    $uncustomizedByDefault = array(
        'notifyButton_customize_enable' => array(
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
        ),
        'prompt_customize_enable' => array(
          'prompt_action_message',
          'prompt_example_notification_title_desktop',
          'prompt_example_notification_message_desktop',
          'prompt_example_notification_title_mobile',
          'prompt_example_notification_message_mobile',
          'prompt_example_notification_caption',
          'prompt_accept_button_text',
          'prompt_cancel_button_text'
        )
    );
    foreach ($uncustomizedByDefault as $key => $value) {
        if (!array_key_exists($key, $onesignal_wp_settings)) {
          if ( $is_new_user ) {
            $onesignal_wp_settings[$key] = true;
          } else {
            $was_customized = false;
            foreach ($value as $text_customize_setting) {
              if ($onesignal_wp_settings[$text_customize_setting] !== "") {
                $was_customized = true;
              }
            }
            $onesignal_wp_settings[$key] = $was_customized;
          }
        }
    }

    // Special case for persistent notifications
    if (!array_key_exists('persist_notifications', $onesignal_wp_settings)) {
      if ( $is_new_user ) {
        // Initially set persist_notifications to yes by default for new sites,
        // except on platforms like Mac where a notification manager is used
        $onesignal_wp_settings['persist_notifications'] = 'yes-except-notification-manager-platforms';
      } else {
        // This was the old key name for persist_notifications
        if (array_key_exists('chrome_auto_dismiss_notifications', $onesignal_wp_settings)) {
          if ($onesignal_wp_settings['chrome_auto_dismiss_notifications'] === "1") {
            // The user wants notifications to be dismissed
            $onesignal_wp_settings['persist_notifications'] = 'platform-default';
          } else {
            // The user did not enable this option, and wanted notifications to be persisted (default at that time)
            $onesignal_wp_settings['persist_notifications'] = 'yes-except-notification-manager-platforms';
          }
        } else {
          $onesignal_wp_settings['persist_notifications'] = 'yes-except-notification-manager-platforms';
        }
      }
    }

    return apply_filters( 'onesignal_get_settings', $onesignal_wp_settings );
  }

  public static function save_onesignal_settings($settings) {
    $onesignal_wp_settings = $settings;
    update_option("OneSignalWPSetting", $onesignal_wp_settings);
  }

  public static function maskedRestApiKey($rest_api_key) {
    return str_repeat('*', 44) . substr($rest_api_key, -4);
  }
}
