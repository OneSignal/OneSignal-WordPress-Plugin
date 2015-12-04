<?php

class OneSignal_Public {

  public function __construct() {}

  public static function init() {
    add_action( 'wp_head', array( __CLASS__, 'onesignal_header' ), 1 );
  }

  public static function onesignal_header() {
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    
    if ($onesignal_wp_settings["subdomain"] == "") {
      if (strpos(ONESIGNAL_PLUGIN_URL, "http://localhost") === false && strpos(ONESIGNAL_PLUGIN_URL, "http://127.0.0.1") === false) {
        $current_plugin_url = preg_replace("/(http:\/\/)/i", "https://", ONESIGNAL_PLUGIN_URL);
      }
      else {
        $current_plugin_url = ONESIGNAL_PLUGIN_URL;
      }
?>
    <link rel="manifest" href="<?php echo( $current_plugin_url . 'sdk_files/manifest.json.php' ) ?>" />
<?php } ?>
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
        var oneSignal_options = {appId: "<?php echo $onesignal_wp_settings["app_id"] ?>"};

        <?php
        if ($onesignal_wp_settings["no_auto_register"] == "1") {
          echo "oneSignal_options['autoRegister'] = false;\n";
        }
        else {
          echo "oneSignal_options['autoRegister'] = true;\n";
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


        if ($onesignal_wp_settings["subdomain"] != "") {
          echo "oneSignal_options['promptOptions'] = { };\n";
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