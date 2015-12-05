<?php
class OneSignal {
  public static function get_onesignal_settings() {
    if (!isset($onesignal_wp_settings)) {
      $onesignal_wp_settings = get_option("OneSignalWPSetting");
      if (empty( $onesignal_wp_settings )) {
         $onesignal_wp_settings = array(
                'app_id' => '',
                'gcm_sender_id' => '',
                'no_auto_register' => false,
                'no_welcome_notification' => false,
                'welcome_notification_title' => '',
                'welcome_notification_message' => '',
                'notification_on_post' => true,
                'notification_on_post_from_plugin' => true,
                'use_http' => false,
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
                'prompt_cancel_button_text' => ""
                );
      }
    }

    if (!array_key_exists('no_welcome_notification', $onesignal_wp_settings)) {
       $onesignal_wp_settings['no_welcome_notification'] = false;
    }

    if (!array_key_exists('welcome_notification_title', $onesignal_wp_settings)) {
       $onesignal_wp_settings['welcome_notification_title'] = '';
    }

    if (!array_key_exists('welcome_notification_message', $onesignal_wp_settings)) {
       $onesignal_wp_settings['welcome_notification_message'] = '';
    }

    if (!array_key_exists('use_modal_prompt', $onesignal_wp_settings)) {
       $onesignal_wp_settings['use_modal_prompt'] = false;
    }

    if (!array_key_exists('no_auto_register', $onesignal_wp_settings)) {
       $onesignal_wp_settings['no_auto_register'] = false;
    }
    
    return $onesignal_wp_settings;
  }
  
  public static function save_onesignal_settings($settings) {
    $onesignal_wp_settings = $settings;
    update_option("OneSignalWPSetting", $onesignal_wp_settings);
  }
}
?>