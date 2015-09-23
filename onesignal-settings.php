<?php
class OneSignal {
  public static function get_onesignal_settings() {
    if (!defined($onesignal_wp_settings)) {
      $onesignal_wp_settings = get_option("OneSignalWPSetting");
      if (empty( $onesignal_wp_settings )) {
         $onesignal_wp_settings = array(
                'app_id' => '',
                'gcm_sender_id' => '',
                'auto_register' => true,
                'notification_on_post' => true,
                'notification_on_post_from_plugin' => true,
                'use_http' => false,
                'subdomain' => "",
                'origin' => "",
                'default_title' => "",
                'default_icon' => "",
                'default_url' => "",
                'app_rest_api_key' => ""
                );
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