<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Add OneSignal initialisation code to head of pages.
add_action('wp_head', 'onesignal_init');

/**
 * Get the plugin version from the plugin header
 *
 * @return string The plugin version (e.g., "3.7.1")
 */
function onesignal_get_plugin_version() {
    static $version = null;

    if ($version === null) {
        $plugin_file = dirname(dirname(__FILE__)) . '/onesignal.php';
        $plugin_data = get_file_data($plugin_file, array('Version' => 'Version'), 'plugin');
        $version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '';
    }

    return $version;
}

function onesignal_init()
{
  // Add plugin version meta tag
  $plugin_version = onesignal_get_plugin_version();
  if (!empty($plugin_version)) {
    echo '<meta name="onesignal-plugin" content="wordpress-' . esc_attr($plugin_version) . '">' . "\n";
  }

  $onesignal_wp_settings = get_option('OneSignalWPSetting');
  $path = rtrim(parse_url(ONESIGNAL_PLUGIN_URL)['path'], '/');
  $scope = $path . '/sdk_files/push/onesignal/';
  $filename = 'OneSignalSDKWorker.js';
?>
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
          window.OneSignalDeferred = window.OneSignalDeferred || [];
          OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
              appId: "<?php echo esc_html($onesignal_wp_settings['app_id']); ?>",
              serviceWorkerOverrideForTypical: true,
              path: "<?php echo ONESIGNAL_PLUGIN_URL; ?>sdk_files/",
              serviceWorkerParam: { scope: "<?php echo $scope ?>" },
              serviceWorkerPath: "<?php echo $filename; ?>",
            });
          });

          // Unregister the legacy OneSignal service worker to prevent scope conflicts
          if (navigator.serviceWorker) {
            navigator.serviceWorker.getRegistrations().then((registrations) => {
              // Iterate through all registered service workers
              registrations.forEach((registration) => {
                // Check the script URL to identify the specific service worker
                if (registration.active && registration.active.scriptURL.includes('OneSignalSDKWorker.js.php')) {
                  // Unregister the service worker
                  registration.unregister().then((success) => {
                    if (success) {
                      console.log('OneSignalSW: Successfully unregistered:', registration.active.scriptURL);
                    } else {
                      console.log('OneSignalSW: Failed to unregister:', registration.active.scriptURL);
                    }
                  });
                }
              });
            }).catch((error) => {
              console.error('Error fetching service worker registrations:', error);
            });
        }
        </script>
<?php
}
