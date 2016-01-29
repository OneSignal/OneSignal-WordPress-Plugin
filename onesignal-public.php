<?php

function debug($var) {
  print "<div style='position: absolute; top: 30px; left: 160px; font-family: Monaco, monospace; font-size: 13px; background: whitesmoke; border-bottom-right-radius: 8px; color: black;font-weight: 500; z-index: 9999999; padding: 1em; margin: 0em;'><pre>"; print_r($var); echo "</pre></div>";
}

function print_settings() {
  debug(OneSignal::get_onesignal_settings());
}

function wipe_settings() {
  OneSignal::save_onesignal_settings(array());
}

function wipe_setting($setting) {
  $settings = OneSignal::get_onesignal_settings();
  unset($settings[$setting]);
  OneSignal::save_onesignal_settings($settings);
}

function set_setting($setting, $yesOrNo) {
  $settings = OneSignal::get_onesignal_settings();
  $settings[$setting] = $yesOrNo;
  OneSignal::save_onesignal_settings($settings);
}

function wipe_notifyButton_settings() {
  $settings = OneSignal::get_onesignal_settings();
  unset($settings['notifyButton_position']);
  unset($settings['notifyButton_size']);
  unset($settings['notifyButton_theme']);
  unset($settings['notifyButton_enable']);
  unset($settings['notifyButton_prenotify']);
  unset($settings['notifyButton_showcredit']);
  unset($settings['notifyButton_message_prenotify']);
  unset($settings['notifyButton_tip_state_unsubscribed']);
  unset($settings['notifyButton_tip_state_subscribed']);
  unset($settings['notifyButton_tip_state_blocked']);
  unset($settings['notifyButton_message_action_subscribed']);
  unset($settings['notifyButton_message_action_resubscribed']);
  unset($settings['notifyButton_message_action_unsubscribed']);
  unset($settings['notifyButton_dialog_main_title']);
  unset($settings['notifyButton_dialog_main_button_subscribe']);
  unset($settings['notifyButton_dialog_main_button_unsubscribe']);
  unset($settings['notifyButton_dialog_blocked_title']);
  unset($settings['notifyButton_dialog_blocked_message']);
  OneSignal::save_onesignal_settings($settings);
}

function wipe_new_settings() {
  $settings = OneSignal::get_onesignal_settings();
  unset($settings['is_site_https_firsttime']);
  unset($settings['is_site_https']);
  unset($settings['prompt_customize_enable']);
  unset($settings['notifyButton_customize_enable']);
  unset($settings['welcome_notification_url']);
  OneSignal::save_onesignal_settings($settings);
}

function test() {
  //set_setting('no_welcome_notification', true);
  //wipe_setting('send_welcome_notification');
  //wipe_notifyButton_settings();
  //wipe_settings();
  //wipe_new_settings();
  //debug('Wiped new settings.');
  //print_settings();
}

class OneSignal_Public {

  public function __construct() {}

  public static function init() {
    add_action( 'wp_head', array( __CLASS__, 'onesignal_header' ), 1 );
  }

  public static function onesignal_header() {
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();

    //test();

    if ($onesignal_wp_settings["subdomain"] == "") {
      if (strpos(ONESIGNAL_PLUGIN_URL, "http://localhost") === false && strpos(ONESIGNAL_PLUGIN_URL, "http://127.0.0.1") === false) {
        $current_plugin_url = preg_replace("/(http:\/\/)/i", "https://", ONESIGNAL_PLUGIN_URL);
      }
      else {
        $current_plugin_url = ONESIGNAL_PLUGIN_URL;
      }
?>
      <?php
        $settings = get_option("OneSignalWPSetting");
        $key = 'gcm_sender_id';
        if ($settings !== false && array_key_exists($key, $settings)) {
          $gcm_sender_id = $settings[$key];
        } else {
          $gcm_sender_id = 'WORDPRESS_NO_SENDER_ID_ENTERED';
        }
      ?>
    <link rel="manifest" href="<?php echo( $current_plugin_url . 'sdk_files/manifest.json.php?gcm_sender_id=' . $gcm_sender_id ) ?>" />
<?php } ?>
    <?php /* <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script> */ ?>
    <?php /* <script src="https://192.168.1.206:3000/dev_sdks/OneSignalSDK.js" async></script> */ ?>
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>
    <script>
      var OneSignal = OneSignal || [];

      OneSignal.push( function() {
        OneSignal.SERVICE_WORKER_UPDATER_PATH = "OneSignalSDKUpdaterWorker.js.php";
        OneSignal.SERVICE_WORKER_PATH = "OneSignalSDKWorker.js.php";
        OneSignal.SERVICE_WORKER_PARAM = { scope: '/' };

        <?php if ($onesignal_wp_settings['default_title'] != "") {
          echo "OneSignal.setDefaultTitle(\"" . $onesignal_wp_settings['default_title'] . "\");\n";
        }
        else {
          echo "OneSignal.setDefaultTitle(\"" . get_bloginfo( 'name' ) . "\");\n";
        }

        if ($onesignal_wp_settings['default_icon'] != "") {
          echo "OneSignal.setDefaultIcon(\"" . $onesignal_wp_settings['default_icon'] . "\");\n";
        }

        if ($onesignal_wp_settings['default_url'] != "") {
          echo "OneSignal.setDefaultNotificationUrl(\"" . $onesignal_wp_settings['default_url'] . "\");";
        }
        else {
           echo "OneSignal.setDefaultNotificationUrl(\"" . get_site_url() . "\");\n";
        }
        ?>
        var oneSignal_options = {};

        <?php
        echo "oneSignal_options['wordpress'] = true;\n";
        echo "oneSignal_options['appId'] = '" . $onesignal_wp_settings["app_id"] . "';\n";
        ?>


      <?php
          echo "oneSignal_options['wordpress'] = true;\n";
        ?>

        <?php
        if ($onesignal_wp_settings["prompt_auto_register"] == "1") {
          echo "oneSignal_options['autoRegister'] = true;\n";
        }
        else {
          echo "oneSignal_options['autoRegister'] = false;\n";
        }

        if ($onesignal_wp_settings["send_welcome_notification"] == "1") {
          echo "oneSignal_options['welcomeNotification'] = { };\n";
          echo "oneSignal_options['welcomeNotification']['title'] = \"" . $onesignal_wp_settings["welcome_notification_title"] . "\";\n";
          echo "oneSignal_options['welcomeNotification']['message'] = \"" . $onesignal_wp_settings["welcome_notification_message"] . "\";\n";
          if ($onesignal_wp_settings["welcome_notification_url"] != "") {
            echo "oneSignal_options['welcomeNotification']['url'] = \"" . $onesignal_wp_settings["welcome_notification_url"] . "\";\n";
          }
        }
        else {
          echo "oneSignal_options['welcomeNotification'] = { };\n";
          echo "oneSignal_options['welcomeNotification']['disable'] = true;\n";
        }

        if ($onesignal_wp_settings["subdomain"] != "") {
          echo "oneSignal_options['subdomainName'] = \"" . $onesignal_wp_settings["subdomain"] . "\";\n";
        }
        else {
          echo "oneSignal_options['path'] = \"" . $current_plugin_url . "sdk_files/\";\n";
        }

        if (@$onesignal_wp_settings["safari_web_id"]) {
          echo "oneSignal_options['safari_web_id'] = \"" . $onesignal_wp_settings["safari_web_id"] . "\";\n";
        }


        if ($onesignal_wp_settings["subdomain"] != "" || $onesignal_wp_settings["use_modal_prompt"] == "1") {
          echo "oneSignal_options['promptOptions'] = { };\n";
          if (array_key_exists('prompt_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings["prompt_customize_enable"] == "1") {
            if ($onesignal_wp_settings["prompt_action_message"] != "") {
              echo "oneSignal_options['promptOptions']['actionMessage'] = '" . $onesignal_wp_settings["prompt_action_message"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_title_desktop"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationTitleDesktop'] = '" . $onesignal_wp_settings["prompt_example_notification_title_desktop"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_message_desktop"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationMessageDesktop'] = '" . $onesignal_wp_settings["prompt_example_notification_message_desktop"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_title_mobile"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationTitleMobile'] = '" . $onesignal_wp_settings["prompt_example_notification_title_mobile"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_message_mobile"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationMessageMobile'] = '" . $onesignal_wp_settings["prompt_example_notification_message_mobile"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_caption"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationCaption'] = '" . $onesignal_wp_settings["prompt_example_notification_caption"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_accept_button_text"] != "") {
              echo "oneSignal_options['promptOptions']['acceptButtonText'] = '" . $onesignal_wp_settings["prompt_accept_button_text"] . "';\n";
            }
            if ($onesignal_wp_settings["prompt_cancel_button_text"] != "") {
              echo "oneSignal_options['promptOptions']['cancelButtonText'] = '" . $onesignal_wp_settings["prompt_cancel_button_text"] . "';\n";
            }
          }
          if (array_key_exists('prompt_showcredit', $onesignal_wp_settings) && $onesignal_wp_settings["prompt_showcredit"] != "1") {
            echo "oneSignal_options['promptOptions']['showCredit'] = false;\n";
          }
        }

        if (array_key_exists('notifyButton_enable', $onesignal_wp_settings) && $onesignal_wp_settings["notifyButton_enable"] == "1") {
          echo "oneSignal_options['notifyButton'] = { };\n";
          echo "oneSignal_options['notifyButton']['enable'] = true;\n";


          if (array_key_exists('notifyButton_position', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_position'] != "") {
            echo "oneSignal_options['notifyButton']['position'] = '" . $onesignal_wp_settings["notifyButton_position"] . "';\n";
          }
          if (array_key_exists('notifyButton_theme', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_theme'] != "") {
            echo "oneSignal_options['notifyButton']['theme'] = '" . $onesignal_wp_settings["notifyButton_theme"] . "';\n";
          }
          if (array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] != "") {
            echo "oneSignal_options['notifyButton']['size'] = '" . $onesignal_wp_settings["notifyButton_size"] . "';\n";
          }

          if ($onesignal_wp_settings["notifyButton_prenotify"] == "1") {
            echo "oneSignal_options['notifyButton']['prenotify'] = true;\n";
          } else {
            echo "oneSignal_options['notifyButton']['prenotify'] = false;\n";
          }

          if ($onesignal_wp_settings["use_modal_prompt"] == "1") {
            echo "oneSignal_options['notifyButton']['modalPrompt'] = true;\n";
          }

          if ($onesignal_wp_settings["notifyButton_showcredit"] == "1") {
            echo "oneSignal_options['notifyButton']['showCredit'] = true;\n";
          } else {
            echo "oneSignal_options['notifyButton']['showCredit'] = false;\n";
          }
            echo "oneSignal_options['notifyButton']['text'] = {};\n";

          if (array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings["notifyButton_customize_enable"] == "1") {
            if ($onesignal_wp_settings["notifyButton_message_prenotify"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.prenotify'] = '" . $onesignal_wp_settings["notifyButton_message_prenotify"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_tip_state_unsubscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['tip.state.unsubscribed'] = '" . $onesignal_wp_settings["notifyButton_tip_state_unsubscribed"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_tip_state_subscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['tip.state.subscribed'] = '" . $onesignal_wp_settings["notifyButton_tip_state_subscribed"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_tip_state_blocked"] != "") {
              echo "oneSignal_options['notifyButton']['text']['tip.state.blocked'] = '" . $onesignal_wp_settings["notifyButton_tip_state_blocked"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_message_action_subscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.action.subscribed'] = '" . $onesignal_wp_settings["notifyButton_message_action_subscribed"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_message_action_resubscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.action.resubscribed'] = '" . $onesignal_wp_settings["notifyButton_message_action_resubscribed"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_message_action_unsubscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.action.unsubscribed'] = '" . $onesignal_wp_settings["notifyButton_message_action_unsubscribed"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_main_title"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.main.title'] = '" . $onesignal_wp_settings["notifyButton_dialog_main_title"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_main_button_subscribe"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.main.button.subscribe'] = '" . $onesignal_wp_settings["notifyButton_dialog_main_button_subscribe"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_main_button_unsubscribe"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.main.button.unsubscribe'] = '" . $onesignal_wp_settings["notifyButton_dialog_main_button_unsubscribe"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_blocked_title"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.blocked.title'] = '" . $onesignal_wp_settings["notifyButton_dialog_blocked_title"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_blocked_message"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.blocked.message'] = '" . $onesignal_wp_settings["notifyButton_dialog_blocked_message"] . "';\n";
            }
          }
        } else {

        }
        ?>

        OneSignal.init(oneSignal_options);
      });

      function documentInitOneSignal() {
        var oneSignal_elements = document.getElementsByClassName("OneSignal-prompt");

        <?php
        if ($onesignal_wp_settings["use_modal_prompt"] == "1") {
          echo "var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications', {modalPrompt: true}]); event.preventDefault(); };";
        }
        else {
          echo "var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications']); event.preventDefault(); };";
        }
        ?>
        for(var i = 0; i < oneSignal_elements.length; i++)
          oneSignal_elements[i].addEventListener('click', oneSignalLinkClickHandler, false);
      }

      if (document.readyState === 'complete') {
           documentInitOneSignal();
      }
      else {
           window.addEventListener("load", function(event){
               documentInitOneSignal();
          });
      }
    </script>

<?php
  }
}
?>