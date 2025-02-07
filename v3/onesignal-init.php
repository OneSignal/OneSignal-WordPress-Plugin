<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Add OneSignal initialisation code to head of pages.
add_action('wp_head', 'onesignal_init');

// Add any development-only features as long as user is an admin
add_action('init', function() {
    if (is_admin() && test_plugin_is_development()) {
        test_plugin_register_post_types();
    }
});

function onesignal_init()
{
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
        </script>
<?php
}

/**
 * Register custom post types for testing
 * Only loads in development/testing environments
 */

 function test_plugin_register_post_types() {
  register_post_type('test_product',
    array(
      'labels'            => array(
          'name'          => __('Test Products', 'test-plugin'),
          'singular_name' => __('Test Product', 'test-plugin'),
      ),
      'public'       => true,
      'has_archive'  => true,
      'rewrite'      => array('slug' => 'test-products'),
      'supports'     => array('title', 'editor', 'thumbnail', 'excerpt'),
      'show_in_rest' => true,
    )
  );
}

/**
 * Check if we're in a development environment
 *
 * @return boolean
 */
function test_plugin_is_development() {
  return (
      // Check for common development environment constants
      defined('WP_DEBUG') && WP_DEBUG
      // ||
      // defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ||
      // // Check if we're in the test environment
      // defined('WP_TESTS_DOMAIN') ||
      // // Check if we're in a local environment
      // strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
      // strpos($_SERVER['HTTP_HOST'] ?? '', '.test') !== false ||
      // strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false
  );
}