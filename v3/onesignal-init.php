<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Add OneSignal initialisation code to head of pages.
add_action('wp_head', 'onesignal_init');

function onesignal_init()
{
  $onesignal_wp_settings = get_option('OneSignalWPSetting');
?>
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
          window.OneSignalDeferred = window.OneSignalDeferred || [];
          OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
              appId: "<?php echo esc_html($onesignal_wp_settings['app_id']); ?>",
              serviceWorkerOverrideForTypical: true,
              path: "<?php echo ONESIGNAL_PLUGIN_URL; ?>sdk_files/",
              serviceWorkerParam: { scope: '/' },
              serviceWorkerPath: 'OneSignalSDKWorker.js.php',
            });
          });
        </script>
<?php
}
