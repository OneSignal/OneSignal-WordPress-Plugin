<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Add OneSignal initialisation code to head of pages.
add_action('wp_head', 'onesignal_init');

function onesignal_init()
{
  $onesignal_wp_settings = get_option('OneSignalWPSetting');
  $use_root_scope = array_key_exists('onesignal_sw_js', $onesignal_wp_settings) ? false : true;
  $path = rtrim(parse_url(ONESIGNAL_PLUGIN_URL)['path'], '/');
  $scope = $path . '/sdk_files/push/onesignal/';
  $filename = 'OneSignalSDKWorker.js' . ($use_root_scope ? '.php' : '');
?>
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
          window.OneSignalDeferred = window.OneSignalDeferred || [];
          OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
              appId: "<?php echo esc_html($onesignal_wp_settings['app_id']); ?>",
              serviceWorkerOverrideForTypical: true,
              path: "<?php echo ONESIGNAL_PLUGIN_URL; ?>sdk_files/",
              serviceWorkerParam: { scope: "<?php echo $use_root_scope ? '/' : $scope ?>" },
              serviceWorkerPath: "<?php echo $filename; ?>",
            });
          });
          // TO DO: move this to a separate file
          navigator.serviceWorker.getRegistrations().then((registrations) => {
            // Iterate through all registered service workers
            registrations.forEach((registration) => {
              // Check the script URL to identify the specific service worker
              if (registration.active && registration.active.scriptURL.includes('OneSignalSDKWorker.js.php')) {
                // Unregister the service worker
                registration.unregister().then((success) => {
                  if (success) {
                    console.log('Successfully unregistered:', registration.active.scriptURL);
                  } else {
                    console.log('Failed to unregister:', registration.active.scriptURL);
                  }
                });
              }
            });
          }).catch((error) => {
            console.error('Error fetching service worker registrations:', error);
          });
        </script>
<?php
}
