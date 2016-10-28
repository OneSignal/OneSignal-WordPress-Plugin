<?php

function onesignal_debug() {
  if (!defined('ONESIGNAL_DEBUG') && !class_exists('WDS_Log_Post')) {
    return;
  }
  $numargs  = func_num_args();
  $arg_list = func_get_args();
  $bt       = debug_backtrace();
  $output   = '[' . $bt[1]['function'] . '] ';
  for ($i = 0; $i < $numargs; $i ++) {
    $arg = $arg_list[ $i ];

    if (is_string($arg)) {
      $arg_output = $arg;
    } else {
      $arg_output = var_export($arg, true);
    }

    if ($arg === "") {
      $arg_output = "\"\"";
    } else if ($arg === null) {
      $arg_output = "null";
    }

    $output = $output . $arg_output . ' ';
  }
  $output = substr($output, 0, - 1);
  $output = substr($output, 0, 1024); // Restrict messages to 1024 characters in length
  if (defined('ONESIGNAL_DEBUG')) {
    error_log('OneSignal: ' . $output);
  }
  if (class_exists('WDS_Log_Post')) {
    $num_log_posts = wp_count_posts('wdslp-wds-log', 'readable');
    // Limit the total number of log entries to 500
    if ($num_log_posts && property_exists($num_log_posts, 'publish') && $num_log_posts->publish < 500) {
      WDS_Log_Post::log_message($output, '', 'general');
    }
  }
}

function onesignal_debug_post($post) {
  if (!$post) {
    return;
  }
  return onesignal_debug('Post:', array('ID' => $post->ID,
                        'Post Date' => $post->post_date,
                        'Modified Date' => $post->post_modified,
                        'Title' => $post->post_title,
                        'Status:' => $post->post_status,
                        'Type:' => $post->post_type));
}

class OneSignal_Public {

  public function __construct() {}

  public static function init() {
    add_action('wp_head', array(__CLASS__, 'onesignal_header'), 5);
  }

  public static function insert_onesignal_header_manifest($onesignal_wp_settings, $current_plugin_url) {
    $use_custom_manifest = $onesignal_wp_settings["use_custom_manifest"];
    $custom_manifest_url = $onesignal_wp_settings["custom_manifest_url"];
    if ($onesignal_wp_settings !== false && array_key_exists('gcm_sender_id', $onesignal_wp_settings)) {
      $gcm_sender_id = $onesignal_wp_settings['gcm_sender_id'];
    } else {
      $gcm_sender_id = 'WORDPRESS_NO_SENDER_ID_ENTERED';
    }
    if ($use_custom_manifest) {
      ?>
      <link rel="manifest"
            href="<?php echo($custom_manifest_url) ?>"/>
      <?php
    } else {
      ?>
      <link rel="manifest"
            href="<?php echo($current_plugin_url . 'sdk_files/manifest.json.php?gcm_sender_id=' . $gcm_sender_id) ?>"/>
      <?php
    }
  }

  // For easier debugging of sites by identifying them as WordPress
  public static function insert_onesignal_stamp() {
    ?>
      <meta name="onesignal" content="wordpress-plugin">
    <?php
  }

  public static function onesignal_header() {
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();

    if ($onesignal_wp_settings["subdomain"] == "") {
      if (strpos(ONESIGNAL_PLUGIN_URL, "http://localhost") === false && strpos(ONESIGNAL_PLUGIN_URL, "http://127.0.0.1") === false) {
        $current_plugin_url = preg_replace("/(http:\/\/)/i", "https://", ONESIGNAL_PLUGIN_URL);
      } else {
        $current_plugin_url = ONESIGNAL_PLUGIN_URL;
      }
      OneSignal_Public::insert_onesignal_stamp();
      OneSignal_Public::insert_onesignal_header_manifest($onesignal_wp_settings, $current_plugin_url);
    }
    ?>
    <?php
    if (defined('ONESIGNAL_DEBUG') && defined('ONESIGNAL_LOCAL')) {
        echo '<script src="https://localhost:3001/sdks/OneSignalSDK.js" async></script>';
      } else {
        echo '<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>';
      }
    ?>
    <script>

      window.OneSignal = window.OneSignal || [];

      OneSignal.push( function() {
        OneSignal.SERVICE_WORKER_UPDATER_PATH = "OneSignalSDKUpdaterWorker.js.php";
        OneSignal.SERVICE_WORKER_PATH = "OneSignalSDKWorker.js.php";
        OneSignal.SERVICE_WORKER_PARAM = { scope: '/' };

        <?php

        if ($onesignal_wp_settings['default_icon'] != "") {
          echo "OneSignal.setDefaultIcon(\"" . OneSignalUtils::decode_entities($onesignal_wp_settings['default_icon']) . "\");\n";
        }

        if ($onesignal_wp_settings['default_url'] != "") {
          echo "OneSignal.setDefaultNotificationUrl(\"" . OneSignalUtils::decode_entities($onesignal_wp_settings['default_url']) . "\");";
        }
        else {
           echo "OneSignal.setDefaultNotificationUrl(\"" . OneSignalUtils::decode_entities(get_site_url()) . "\");\n";
        }
        ?>
        var oneSignal_options = {};
        window._oneSignalInitOptions = oneSignal_options;

        <?php
        echo "oneSignal_options['wordpress'] = true;\n";
        echo "oneSignal_options['appId'] = '" . $onesignal_wp_settings["app_id"] . "';\n";

        if ($onesignal_wp_settings["prompt_auto_register"] == "1") {
          echo "oneSignal_options['autoRegister'] = true;\n";
        }
        else {
          echo "oneSignal_options['autoRegister'] = false;\n";
        }

        if ($onesignal_wp_settings["send_welcome_notification"] == "1") {
          echo "oneSignal_options['welcomeNotification'] = { };\n";
          echo "oneSignal_options['welcomeNotification']['title'] = \"" . OneSignalUtils::html_safe($onesignal_wp_settings["welcome_notification_title"]) . "\";\n";
          echo "oneSignal_options['welcomeNotification']['message'] = \"" . OneSignalUtils::html_safe($onesignal_wp_settings["welcome_notification_message"]) . "\";\n";
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

        if ($onesignal_wp_settings["chrome_auto_dismiss_notifications"] == "1") {
          echo "oneSignal_options['persistNotification'] = false;\n";
        }


        if ($onesignal_wp_settings["subdomain"] != "" || $onesignal_wp_settings["use_modal_prompt"] == "1") {
          echo "oneSignal_options['promptOptions'] = { };\n";
          if (array_key_exists('prompt_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings["prompt_customize_enable"] == "1") {
            if ($onesignal_wp_settings["prompt_action_message"] != "") {
              echo "oneSignal_options['promptOptions']['actionMessage'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_action_message"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_title_desktop"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationTitleDesktop'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_example_notification_title_desktop"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_message_desktop"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationMessageDesktop'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_example_notification_message_desktop"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_title_mobile"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationTitleMobile'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_example_notification_title_mobile"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_message_mobile"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationMessageMobile'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_example_notification_message_mobile"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_example_notification_caption"] != "") {
              echo "oneSignal_options['promptOptions']['exampleNotificationCaption'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_example_notification_caption"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_accept_button_text"] != "") {
              echo "oneSignal_options['promptOptions']['acceptButtonText'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_accept_button_text"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_cancel_button_text"] != "") {
              echo "oneSignal_options['promptOptions']['cancelButtonText'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_cancel_button_text"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_site_name"] != "") {
              echo "oneSignal_options['promptOptions']['siteName'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_site_name"]) . "';\n";
            }
            if ($onesignal_wp_settings["prompt_auto_accept_title"] != "") {
              echo "oneSignal_options['promptOptions']['autoAcceptTitle'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["prompt_auto_accept_title"]) . "';\n";
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

          if ($onesignal_wp_settings["notifyButton_showAfterSubscribed"] !== true) {
            echo "oneSignal_options['notifyButton']['displayPredicate'] = function() {
              return OneSignal.isPushNotificationsEnabled()
                      .then(function(isPushEnabled) {
                          return !isPushEnabled;
                      });
            };\n";
          }

          if ($onesignal_wp_settings["use_modal_prompt"] == "1") {
            echo "oneSignal_options['notifyButton']['modalPrompt'] = true;\n";
          }

          if ($onesignal_wp_settings["notifyButton_showcredit"] == "1") {
            echo "oneSignal_options['notifyButton']['showCredit'] = true;\n";
          } else {
            echo "oneSignal_options['notifyButton']['showCredit'] = false;\n";
          }

          if (array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings["notifyButton_customize_enable"] == "1") {
            echo "oneSignal_options['notifyButton']['text'] = {};\n";
            if ($onesignal_wp_settings["notifyButton_message_prenotify"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.prenotify'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_message_prenotify"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_tip_state_unsubscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['tip.state.unsubscribed'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_tip_state_unsubscribed"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_tip_state_subscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['tip.state.subscribed'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_tip_state_subscribed"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_tip_state_blocked"] != "") {
              echo "oneSignal_options['notifyButton']['text']['tip.state.blocked'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_tip_state_blocked"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_message_action_subscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.action.subscribed'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_message_action_subscribed"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_message_action_resubscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.action.resubscribed'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_message_action_resubscribed"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_message_action_unsubscribed"] != "") {
              echo "oneSignal_options['notifyButton']['text']['message.action.unsubscribed'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_message_action_unsubscribed"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_main_title"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.main.title'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_dialog_main_title"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_main_button_subscribe"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.main.button.subscribe'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_dialog_main_button_subscribe"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_main_button_unsubscribe"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.main.button.unsubscribe'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_dialog_main_button_unsubscribe"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_blocked_title"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.blocked.title'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_dialog_blocked_title"]) . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_dialog_blocked_message"] != "") {
              echo "oneSignal_options['notifyButton']['text']['dialog.blocked.message'] = '" . OneSignalUtils::html_safe($onesignal_wp_settings["notifyButton_dialog_blocked_message"]) . "';\n";
            }
          }

          if (array_key_exists('notifyButton_customize_colors_enable', $onesignal_wp_settings) && $onesignal_wp_settings["notifyButton_customize_colors_enable"] == "1") {
            echo "oneSignal_options['notifyButton']['colors'] = {};\n";
            if ($onesignal_wp_settings["notifyButton_color_background"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['circle.background'] = '" . $onesignal_wp_settings["notifyButton_color_background"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_foreground"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['circle.foreground'] = '" . $onesignal_wp_settings["notifyButton_color_foreground"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_badge_background"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['badge.background'] = '" . $onesignal_wp_settings["notifyButton_color_badge_background"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_badge_foreground"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['badge.foreground'] = '" . $onesignal_wp_settings["notifyButton_color_badge_foreground"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_badge_border"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['badge.bordercolor'] = '" . $onesignal_wp_settings["notifyButton_color_badge_border"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_pulse"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['pulse.color'] = '" . $onesignal_wp_settings["notifyButton_color_pulse"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_popup_button_background"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['dialog.button.background'] = '" . $onesignal_wp_settings["notifyButton_color_popup_button_background"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_popup_button_background_hover"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['dialog.button.background.hovering'] = '" . $onesignal_wp_settings["notifyButton_color_popup_button_background_hover"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_popup_button_background_active"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['dialog.button.background.active'] = '" . $onesignal_wp_settings["notifyButton_color_popup_button_background_active"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_color_popup_button_color"] != "") {
              echo "oneSignal_options['notifyButton']['colors']['dialog.button.foreground'] = '" . $onesignal_wp_settings["notifyButton_color_popup_button_color"] . "';\n";
            }
          }

          if (array_key_exists('notifyButton_customize_offset_enable', $onesignal_wp_settings) && $onesignal_wp_settings["notifyButton_customize_offset_enable"] == "1") {
            echo "oneSignal_options['notifyButton']['offset'] = {};\n";
            if ($onesignal_wp_settings["notifyButton_offset_bottom"] != "") {
              echo "oneSignal_options['notifyButton']['offset']['bottom'] = '" . $onesignal_wp_settings["notifyButton_offset_bottom"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_offset_left"] != "") {
              echo "oneSignal_options['notifyButton']['offset']['left'] = '" . $onesignal_wp_settings["notifyButton_offset_left"] . "';\n";
            }
            if ($onesignal_wp_settings["notifyButton_offset_right"] != "") {
              echo "oneSignal_options['notifyButton']['offset']['right'] = '" . $onesignal_wp_settings["notifyButton_offset_right"] . "';\n";
            }
          }

        }

        $use_custom_sdk_init = $onesignal_wp_settings['use_custom_sdk_init'];
        if (!$use_custom_sdk_init) {
          if (has_filter('onesignal_initialize_sdk')) {
            onesignal_debug('Applying onesignal_initialize_sdk filter.');
            if (apply_filters('onesignal_initialize_sdk', $onesignal_wp_settings)) {
              // If the filter returns "$do_initialize_sdk: true", initialize the web SDK
              ?>
              OneSignal.init(window._oneSignalInitOptions);
              <?php
            }
          } else {
          ?>
            OneSignal.init(window._oneSignalInitOptions);
          <?php
          }
        }
        ?>
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
