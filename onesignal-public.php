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
      
      function initOneSignal() {
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
           echo "OneSignal.setDefaultNotificationUrl(\"" . get_site_url() . "\");";
        } 
        ?>
        
        OneSignal.init({appId: "<?php echo $onesignal_wp_settings["app_id"] ?>",
                        <?php
                        if ($onesignal_wp_settings["subdomain"] != "") {
                          echo "subdomainName: \"" . $onesignal_wp_settings["subdomain"] . "\"";
                        }
                        else {
                          echo 'path: "' . $current_plugin_url . 'sdk_files/"';
                        } ?>});
      }
      
      window.addEventListener("load", function(event){
        OneSignal.push(initOneSignal);
        
        var oneSignal_elements = document.getElementsByClassName("OneSignal-prompt");
        var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications', {modalPrompt: true}]); event.preventDefault(); };
        for(var i = 0; i < oneSignal_elements.length; i++)
          oneSignal_elements[i].addEventListener('click', oneSignalLinkClickHandler, false);
      });
    </script>

<?php
  }
}
?>