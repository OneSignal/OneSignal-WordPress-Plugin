<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

if (!OneSignalUtils::can_modify_plugin_settings()) {
  // Exit if the current user does not have permission
  die('Insufficient permissions to access config page.');
}

// The user is just viewing the config page; this page cannot be accessed directly
$onesignal_wp_settings = OneSignal::get_onesignal_settings();

?>

<header class="onesignal">
  <a href="https://onesignal.com" target="_blank">
    <div class="onesignal logo" id="logo-onesignal" style="width: 250px; height: 52px; margin: 0 auto;">&nbsp;</div>
  </a>
</header>
<div class="outer site onesignal container">
  <div class="ui site onesignal container" id="content-container">
    <div class="ui menu">
      <span style="padding:0 20px 15px; color:#E54B4D; font-weight:700;">
        ⚠️ OneSignal Push Important Update:<br><br>
        <p style="font-size:1.15rem;">We are soon releasing Version 3 of the OneSignal WordPress Plugin. Before updating, you must migrate your configuration to dashboard.onesignal.com.</p>
        <form method="post" action="">
          <?php wp_nonce_field('onesignal_export_nonce'); ?>
          <input type="hidden" name="plugin_action" value="export_settings">
          <button type="button" class="ui medium teal button" onclick="window.open('https://documentation.onesignal.com/docs/wordpress-plugin-30','_blank');">Learn More</button>
          <button type="submit" class="ui medium button">Export Current Configuration</button>
        </form>
      </span>
    </div>
  </div>
  <div class="ui pointing stackable menu">
    <a class="active item" data-tab="configuration">Configuration</a>
  </div>
  <div class="ui borderless shadowless active tab segment" style="z-index: 1; padding-top: 0; padding-bottom: 0;" data-tab="configuration">
    <div class="ui special padded raised stack segment">
      <form class="ui form" role="configuration" action="#" method="POST">
        <?php
        // Add an nonce field so we can check for it later.
        wp_nonce_field(OneSignal_Admin::$SAVE_CONFIG_NONCE_ACTION, OneSignal_Admin::$SAVE_CONFIG_NONCE_KEY, true);
        ?>
        <div class="ui dividing header">
          <i class="setting icon"></i>
          <div class="content">
            Account Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <div class="field">
            <label>App ID<i class="tiny circular help icon link" role="popup" data-title="App ID" data-content="Your 36 character alphanumeric app ID. You can find this in App Settings > Keys & IDs." data-variation="wide"></i></label>
            <input type="text" name="app_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxx" value="<?php echo esc_attr($onesignal_wp_settings['app_id']); ?>">
          </div>
          <div class="field">
            <label>REST API Key<i class="tiny circular help icon link" role="popup" data-title="Rest API Key" data-content="Your 48 character alphanumeric REST API Key. You can find this in App Settings > Keys & IDs." data-variation="wide"></i></label>
            <input type="text" name="app_rest_api_key" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" value="<?php echo esc_attr(OneSignal::maskedRestApiKey($onesignal_wp_settings['app_rest_api_key'])); ?>">
          </div>
      </form>
    </div>
  </div>
</div>
</div>