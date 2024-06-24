<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Add OneSignal initialisation code to head of pages.
add_action('wp_head', 'onesignal_init');

function onesignal_init()
{
?>
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function(OneSignal) {
      await OneSignal.init({
        appId: "<?php echo get_option('OneSignalWPSetting')['app_id']; ?>",
      });
    });
  </script>
<?php
}
