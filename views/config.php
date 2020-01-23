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
      <span style="padding-bottom:15px; padding-left:20px; color:#E54B4D; font-weight:700;">
	⭐ Appreciate OneSignal?
	<a style="margin-left:15px;" href="https://wordpress.org/support/plugin/onesignal-free-web-push-notifications/reviews/#new-post" target="_blank">Leave us a review →	</a>
      </span>
    </div>    
    <div class="ui pointing stackable menu">
      <a class="item" data-tab="setup">Setup</a>
      <a class="active item" data-tab="configuration">Configuration</a>
    </div>
    <div class="ui tab borderless shadowless segment" data-tab="setup" style="padding-top: 0; padding-bottom: 0;">
      <div class="ui special padded segment" style="padding-top: 0 !important;">
      <div class="ui top secondary pointing menu">
      <div class="ui grid" style="margin: 0 !important; padding: 0 !important;">
        <a class="item" data-tab="setup/0">Overview</a>
        <a class="item" data-tab="setup/1">Prompts</a>
        <a class="item" data-tab="setup/2">Safari Push</a>
        <a class="item" data-tab="setup/3">Results</a>
        </div>
      </div>
      <div class="ui tab borderless shadowless segment" data-tab="setup/0">
        <p>Follow these steps to add Web Push to your Wordpress blog:</p>
        <dl>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>Create a <a href="https://onesignal.com" target="_blank">OneSignal</a> account or log in to your existing account.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>
                Create a Web Push app in OneSignal, following the instructions in our
                <a href="https://documentation.onesignal.com/docs/web-push-quickstart" target="_new">Web Push Quickstart guide</a>.
              </p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>
                Set up your Web Push app following the instructions in OneSignal's <strong>Web Push Editor</strong>.
              </p>
            </dd>
          </div>
        </dl>

        <div class="ui center aligned piled segment">
          <i class="big grey pin pinned icon"></i>
          <h3>Troubleshooting</h3>
          <p>
            If you run into issues or need extra guidance, you can follow along each step of our
            <a href="https://documentation.onesignal.com/docs/wordpress" target="_new">Wordpress Setup Guide</a>.
          </p>
          <p>
            If you're ever stuck or have questions, <a href="mailto:support+wp@onesignal.com">email us</a>!
          </p>
          <p>
            If you run into issues getting your plugin to work, you can also browse our
            <a href="https://documentation.onesignal.com/docs/troubleshooting-web-push" target="_blank">Troubleshooting Website Push</a> documentation.
          </p>
        </div>
      </div>

      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/1">
        <p>If you've finished the guide up to here, push notifications already work on your site. <strong>But your users still need a way to <em>subscribe</em> to your site's notifications</strong>. There are a couple ways:
          <h4>HTTP Sites:</h4>
          <div class="relative ui two column middle aligned very relaxed stackable grid" style="margin-bottom: 0 !important; padding-bottom: 0 !important;">
            <div class="center aligned column">
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/http-prompt.png") ?>" width="100%">
            </div>
            <div class="center aligned column">
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/bell.jpg") ?>" width="60%">
            </div>
          </div>
          <div class="relative ui two column middle aligned very relaxed stackable grid" style="margin-top: 0 !important; padding-top: 0 !important;">
            <div class="center aligned column">
              <h3>Slide Prompt</h3>
            </div>
            <div class="center aligned column">
              <h3>Subscription Bell</h3>
            </div>
          </div>
          <h4>HTTPS Sites:</h4>
          <div class="relative ui two column middle aligned very relaxed stackable grid" style="margin-bottom: 0 !important; padding-bottom: 0 !important;">
            <div class="center aligned column">
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/https-prompt.png") ?>" width="100%">
            </div>
            <div class="center aligned column">
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/bell.jpg") ?>" width="60%">
            </div>
          </div>
          <div class="relative ui two column middle aligned very relaxed stackable grid" style="margin-top: 0 !important; padding-top: 0 !important;">
            <div class="center aligned column">
              <h3>Browser Permission Request <span class="ui green horizontal label">HTTPS Only</span></h3>
            </div>
            <div class="center aligned column">
              <h3>Subscription Bell</h3>
            </div>
          </div>
          <ol>
            <li><strong>Subscription Bell:</strong> Enable it in <em>Configuration</em> -> <em>Prompt Settings & Subscription Bell</em> -> <em>Enable the Subscription Bell</em></li>
            <ol>
              <li>The Subscription Bell is an interactive site widget.</li>
              <li>Users see the Subscription Bell on the bottom right corner of your site. They can click the Subscription Bell to subscribe.</li>
              <li>The Subscription Bell is custom developed by us and does all the work for you! It detects when users are unsubscribed, already subscribed, or have blocked your site and show instructions to unblock. It allows users to easily temporarily subscribe from and resubscribe to notifications.</li>
            </ol>
            <li><strong>HTTP/HTTPS Prompt:</strong> Enable it in <em>Configuration</em> -> <em>Prompt Settings & Subscription Bell</em> -> <em>Automatically prompt new site visitors to subscribe to push notifications</em></li>
            <ol>
              <li><a href="https://documentation.onesignal.com/docs/permission-requests" target="_blank">Read more about it at our documentation.</a></li>
            </ol>
          </ol>
          <p>If you're a technical user and would like to implement your own subscription process, this is entirely possible. Please see this guide on <a href="https://documentation.onesignal.com/docs/customize-permission-messages#section-custom-link-permission-message" target="_blank">how to subscribe user with a link</a> using HTML and JavaScript. Our <a href="https://documentation.onesignal.com/docs/web-push-sdk" target="_blank">web SDK JavaScript API</a> is also available and can be called anywhere on the page.</p>
        </p>

        <dl>
          <div class="ui segment">
              <p>You're done setting up your site for Chrome & Firefox push!</p>
              <p>Your site works completely with Chrome & Firefox push now. You can learn how to add <a href="javascript:void(0);" onclick="activateSetupTab('setup/4')">Safari</a> web push.</p>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/2">
        <dl>
          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>Safari on Windows Not Supported</h3>
            <p>Safari on Windows does not support web push notifications. Please use Safari on Mac OS X. <a href="https://onesignal.com/blog/when-will-web-push-be-supported-in-ios/" target="_blank">Apple also does not support web push notifications on iOS yet.</a></p>
          </div>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>Log in to your OneSignal account, and navigate to the <em>App Settings</em> page of the app you configured in this guide.</p>
              <p>You should be on this page:</p>
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/safari-1.jpg") ?>">
              <p>Click <strong>Configure</strong> on the platform <em>Apple Safari</em>.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>In this step, we'll focus on filling out the <em>Site Name</em> and <em>Site URL</em> fields.</p>
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/safari-2.jpg") ?>">
              <p>For the <strong>Site Name</strong>, enter a name you'd like your users to see.</p>
              <p>In the following sample image, <em>OneSignal</em> is the site name:</p>
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/safari-prompt.jpg") ?>" width="450">
              <p>For the <strong>Site URL</strong>, enter the URL to your site's domain. The purpose of this field is to prevent other sites from hijacking your keys to impersonate you and send push notifications on your behalf. Please note:</p>
              <ul>
                <li>
                  <p>Don't include trailing slashes</p>
                  <p>Instead of using <code>http://domain.com/</code>, use <code>http://domain.com</code> instead.</p>
                  <p></p>
                </li>
                <li>
                  <p>Don't include subfolders</p>
                  <p>Even if your WordPress blog is hosted on <code>http://domain.com/resource/blog</code>, use <code>http://domain.com</code></p>
                  <p></p>
                </li>
                <li>
                  <p>Include the correct protocol</p>
                  <p>If your site uses HTTPS, use <code>https://domain.com</code>. If your site uses a mix of HTTPS/HTTP or only HTTP, use <code>http://domain.com</code>.</a>.</p>
                  <p></p>
                </li>
              </ul>
            </dd>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>In this step, we'll focus on uploading your Safari notification icons.</p>
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/safari-3.jpg") ?>">
              <p>Please have your icon in the following sizes:</p>
              <ul>
                <li>16 &times; 16</li>
                <li>32 &times; 32</li>
                <li>64 &times; 64</li>
                <li>128 &times; 128</li>
                <li>256 &times; 256</li>
              </ul>
              <p>The different sizes are used in different places (e.g. the <code>64 &times; 64</code> icon is used in the allow notification prompt). If you don't have these different sizes, you may simply upload one <code>256 &times; 256</code> icon into each entry, and we will resize them for you to the appropriate size.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>4</dt>
            <dd>
              <p>Click <strong>Save</strong> to commit your Safari push settings <strong>and then exit the dialog</strong>.</p>
              <p>If you get errors please follow the instructions to fix them. If you're still experiencing problems, email us for support.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>5</dt>
            <dd>
              <p><strong>Refresh</strong> the page, and then copy the <strong>Safari Web ID</strong> you see to the <em>Configuration</em> tab.</p>
              <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/safari-4.jpg") ?>">
              <p>That's it for setting up Safari push!</p>
            </dd>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>Safari Web ID (optional)</h3>
            <p>Copy the <strong>Safari Web ID</strong> to the <em>Configuration</em> tab.</p>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/3">
        <p>This section shows push notifications working for <em>Chrome</em>, <em>Safari</em>, and <em>Firefox</em> in <em>HTTP</em> and <em>HTTPS</em> mode.</p>
        <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/web-push.jpg") ?>">
        <p></p>
        <dl>
          <div class="ui horizontal divider">Subscription Bell</div>
          <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/notify-button.jpg") ?>">
          <div class="ui horizontal divider">Chrome (HTTP)</div>
          <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/chrome-http.jpg") ?>">
          <div class="ui horizontal divider">Chrome (HTTPS)</div>
          <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/chrome-https.jpg") ?>">
          <div class="ui horizontal divider">Safari (HTTP & HTTPS)</div>
          <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/safari-https.jpg") ?>">
          <div class="ui horizontal divider">Firefox (HTTP)</div>
          <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/firefox-http.jpg") ?>">
          <div class="ui horizontal divider">Firefox (HTTPS)</div>
          <img class="img-responsive" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/firefox-https.jpg") ?>">
        </dl>
      </div>
    </div>
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
            <div class="ui toggle checkbox">
              <input type="checkbox" name="is_site_https" <?php if ($onesignal_wp_settings['is_site_https_firsttime'] === 'unset') { echo "data-unset=\"true\""; }  if ($onesignal_wp_settings['is_site_https']) { echo "checked"; }  ?>>
              <label>My site uses an HTTPS connection (SSL)<i class="tiny circular help icon link" role="popup" data-html="<p>Check this if your site uses HTTPS:</p><img src='<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/https-url.png") ?>' width=619>" data-variation="flowing"></i></label>
            </div>
          </div>
          <div class="ui inline subdomain-http nag">
            <span class="title">
              This option is disabled when your current URL begins with <code>http://</code>. Please access this page using <code>https://</code> to enable this option.
            </span>
            <i class="close icon"></i>
          </div>
          <?php if (
                     (
                       $onesignal_wp_settings['gcm_sender_id'] !== '' ||
                       $onesignal_wp_settings['show_gcm_sender_id']
                     ) &&
                     (OneSignalUtils::url_contains_parameter(ONESIGNAL_URI_REVEAL_PROJECT_NUMBER))
                   ): ?>
          <div class="field">
            <label>Google Project Number<i class="tiny circular help icon link" role="popup" data-title="Google Project Number" data-content="Your Google Project Number. Do NOT change this as it can cause all existing subscribers to become unreachable." data-variation="wide"></i></label>
            <p class="hidden danger-label" data-target="[name=gcm_sender_id]">WARNING: Changing this causes all existing subscribers to become unreachable. Please do not change unless instructed to do so!</p>
            <input type="text" name="gcm_sender_id" placeholder="#############" value="<?php echo esc_attr($onesignal_wp_settings['gcm_sender_id']); ?>">
          </div>
          <?php endif; ?>
          <div class="field">
            <label>App ID<i class="tiny circular help icon link" role="popup" data-title="App ID" data-content="Your 36 character alphanumeric app ID. You can find this in App Settings > Keys & IDs." data-variation="wide"></i></label>
            <input type="text" name="app_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxx" value="<?php echo esc_attr($onesignal_wp_settings['app_id']); ?>">
          </div>
          <div class="field">
            <label>REST API Key<i class="tiny circular help icon link" role="popup" data-title="Rest API Key" data-content="Your 48 character alphanumeric REST API Key. You can find this in App Settings > Keys & IDs." data-variation="wide"></i></label>
            <input type="text" name="app_rest_api_key" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" value="<?php echo esc_attr($onesignal_wp_settings['app_rest_api_key']); ?>">
          </div>
          <div class="field subdomain-feature">
            <label>OneSignal Label<i class="tiny circular help icon link" role="popup" data-title="Subdomain" data-content="The label you chose for your site. You can find this in Step 2. Wordpress Site Setup" data-variation="wide"></i></label>
            <input type="text" name="subdomain" placeholder="example" value="<?php echo esc_attr($onesignal_wp_settings['subdomain']); ?>">
            <div class="callout info">Once your site is public, <strong>do not change your label</strong>. If you do, users will receive duplicate notifications.</div>
          </div>
          <div class="field">
            <label>Safari Web ID<i class="tiny circular help icon link" role="popup" data-title="Safari Web ID" data-content="Your Safari Web ID. You can find this on Setup > Safari Push > Step 5." data-variation="wide"></i></label>
            <input type="text" name="safari_web_id" placeholder="web.com.example" value="<?php echo esc_attr($onesignal_wp_settings['safari_web_id']); ?>">
          </div>
        </div>
        <div class="ui dividing header">
          <i class="desktop icon"></i>
          <div class="content">
            Sent Notification Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="showNotificationIconFromPostThumbnail" value="true" <?php if ($onesignal_wp_settings['showNotificationIconFromPostThumbnail']) { echo "checked"; } ?>>
              <label>Use the post's featured image for the notification icon<i class="tiny circular help icon link" role="popup" data-title="Use post featured image for notification icon" data-content="If checked, use the post's featured image in the notification icon (small icon).  Chrome and Firefox Desktop supported." data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="showNotificationImageFromPostThumbnail" value="true" <?php if ($onesignal_wp_settings['showNotificationImageFromPostThumbnail']) { echo "checked"; } ?>>
              <label>Use the post's featured image for Chrome's large notification image<i class="tiny circular help icon link" role="popup" data-title="Use post featured image for notification image (Chrome only)" data-html="<p>If checked, use the post's featured image in the notification large image (Chrome only). See <a target='docs' href='https://documentation.onesignal.com/docs/web-push-notification-icons#section-image'>our documentation on web push images</a>.</p>" data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field">
            <label>
              Hide notifications after a few seconds
              <i class="tiny circular help icon link"
                  role="popup"
                  data-html="
                    <p><strong>Yes</strong></p>
                    <p>The notification hides itself after some time, depending on the platform.</p>
                      <ul style='font-size: 95%'>
                        <li>Windows: Up to 20 seconds</li>
                        <li>Mac OS X: 3 - 5 seconds</li>
                        <li>Android: Notifications persist in the notification tray</li>
                      </ul>
                    <p><strong>Yes on Mac OS X. No on other platforms.</strong></p>
                    <p><em>Recommended.</em> Mac OS X notifiations will disappear after a few seconds, but they can still be seen in the Notification Center.</p>
                    <p><strong>No</strong></p>
                    <p>This option will lead to a browser settings button being shown on Mac OS X notifications, which reduces the available length for the notification text.</p>
                    "
                  width=650
                  data-variation="wide">
              </i>
            </label>
            <select class="ui dropdown" name="persist_notifications">
              <option
                value="platform-default"
                  <?php
                    if ((array_key_exists('persist_notifications', $onesignal_wp_settings) &&
                        $onesignal_wp_settings['persist_notifications'] === "platform-default")) {
                          echo "selected";
                    }
                  ?>>Yes
              </option>
              <option
                value="yes-except-notification-manager-platforms"
                  <?php
                    if ((array_key_exists('persist_notifications', $onesignal_wp_settings) &&
                        $onesignal_wp_settings['persist_notifications'] === "yes-except-notification-manager-platforms")) {
                          echo "selected";
                    }
                  ?>>Yes on Mac OS X. No on other platforms.
              </option>
              <option
                value="yes-all"
                  <?php
                    if ((array_key_exists('persist_notifications', $onesignal_wp_settings) &&
                        $onesignal_wp_settings['persist_notifications'] === "yes-all")) {
                          echo "selected";
                    }
                  ?>>No
              </option>
            </select>
          </div>
          <div class="field">
              <label>Notification Title<i class="tiny circular help icon link" role="popup" data-html="The notification title to use for all outgoing notifications. Defaults to your site's title." data-variation="wide"></i></label>
              <input type="text" name="notification_title" placeholder="<?php echo esc_attr(OneSignalUtils::decode_entities(get_bloginfo('name'))); ?>" value="<?php echo esc_attr($onesignal_wp_settings['notification_title']); ?>">
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="send_to_mobile_platforms" value="true" <?php if ($onesignal_wp_settings['send_to_mobile_platforms']) { echo "checked"; } ?>>
              <label>Send notifications additionally to iOS & Android platforms<i class="tiny circular help icon link" role="popup" data-title="Deliver to iOS & Android" data-html="<p>If checked, the notification will also be sent to Android and iOS <em>if you have those platforms enabled</em> in addition to your web push users. <strong class='least-strong'>Your OneSignal app must have either an active iOS or an Android platform and you must have either iOS or Android users for this to work</strong>.</p>" data-variation="wide"></i></label>
            </div>
          </div>
        </div>
        <div class="ui dividing header">
          <i class="alarm outline icon"></i>
          <div class="content">
            Prompt Settings & Subscription Bell
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <img class="img-responsive no-center" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/nb-unsubscribe.png") ?>" width="234">
          <div class="explanation">
            <p>Control the way visitors are prompted to subscribe. The Subscription Bell is an interactive widget your site visitors can use to manage their push notification subscription status. The Subscription Bell can be used to initially subscribe to push notifications, and to unsubscribe.</p>
          </div>
          <div class="field auto-register-feature">
            <div class="field">
              <div class="ui toggle checkbox">
                <input type="checkbox" name="prompt_auto_register" value="true" <?php if ($onesignal_wp_settings['prompt_auto_register']) { echo "checked"; } ?>>
                <label>
                  Automatically prompt new site visitors with OneSignal Slide Prompt before Native Browser Prompt (recommended)
                  <i class="tiny circular help icon link"
		             role="popup"
                     data-html="
                       <h4>Slide Prompt</h4><p>If enabled, the Slide Prompt will be shown before the browser's permission request.</p><p>Please note that this Slide Prompt cannot replace the browser's native permission request.</p><p>The browser's native permission request must always be finally shown before the user can be subscribed.</p>
                       <p>
                         <img
                            src='<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/http-prompt.png") ?>'
                            width=400>
                       </p>"
                     width=450
                     data-variation="flowing">
                  </i>
                </label>
              </div>
            </div>
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_enable" value="true" <?php if (array_key_exists('notifyButton_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_enable']) { echo "checked"; } ?>>
              <label>Enable the Subscription Bell<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell" data-content="If checked, the Subscription Bell and its resources will be loaded into your website." data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_showAfterSubscribed" value="true" <?php if (array_key_exists('notifyButton_showAfterSubscribed', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_showAfterSubscribed']) { echo "checked"; } ?>>
              <label>Show the Subscription Bell after users have subscribed<i class="tiny circular help icon link" role="popup" data-html="<p>If checked, the Subscription Bell will continue to be shown on all pages after the user subscribes.</p><p>If unchecked, the Subscription Bell will be hidden not be shown after the user subscribes and refreshes the page.</p>" data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_showcredit" value="true" <?php if (array_key_exists('notifyButton_showcredit', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_showcredit']) { echo "checked"; } ?>>
              <label>Show the OneSignal logo on the Subscription Bell dialog</label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_customize_enable" value="true" <?php if (array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_enable']) { echo "checked"; } ?>>
              <label>Customize the Subscription Bell text</label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_customize_offset_enable" value="true" <?php if (array_key_exists('notifyButton_customize_offset_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_offset_enable']) { echo "checked"; } ?>>
              <label>Customize the Subscription Bell offset position</label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_customize_colors_enable" value="true" <?php if (array_key_exists('notifyButton_customize_colors_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_colors_enable']) { echo "checked"; } ?>>
              <label>Customize the Subscription Bell theme colors</label>
            </div>
          </div>
          <div class="inline-setting short field nb-feature">
            <label class="inline-setting">Size:</label>
            <select class="ui dropdown" name="notifyButton_size">
              <option value="small" <?php if (array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] === "small") { echo "selected"; } ?>>Small</option>
              <option value="medium" <?php if ((array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] === "medium") || !array_key_exists('notifyButton_theme', $onesignal_wp_settings)) { echo "selected"; } ?>>Medium</option>
              <option value="large" <?php if (array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] === "large") { echo "selected"; } ?>>Large</option>
            </select>
          </div>
          <div class="inline-setting short field nb-feature">
            <label class="inline-setting">Position:</label>
            <select class="ui dropdown" name="notifyButton_position">
              <option value="bottom-left" <?php if (array_key_exists('notifyButton_position', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_position'] === "bottom-left") { echo "selected"; } ?>>Bottom Left</option>
              <option value="bottom-right" <?php if ((array_key_exists('notifyButton_position', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_position'] === "bottom-right") || !array_key_exists('notifyButton_position', $onesignal_wp_settings)) { echo "selected"; } ?>>Bottom Right</option>
            </select>
          </div>
          <div class="inline-setting short field nb-feature">
            <label class="inline-setting">Theme:</label>
            <select class="ui dropdown" name="notifyButton_theme">
              <option value="default" <?php if ((array_key_exists('notifyButton_theme', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_theme'] === "default") || !array_key_exists('notifyButton_theme', $onesignal_wp_settings)) { echo "selected"; } ?>>Red</option>
              <option value="inverse" <?php if (array_key_exists('notifyButton_theme', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_theme'] === "inverse") { echo "selected"; } ?>>White</option>
            </select>
          </div>
          <div class="ui segment nb-feature nb-position-feature">
            <div class="ui dividing header">
              <h4>
                Subscription Bell Offset Position Customization
              </h4>
            </div>
            <p class="small normal-weight lato">You can override the Subscription Bell's offset position in the X and Y direction using CSS-valid position values. For example, <code>20px</code> is the default value.</p>
            <div class="field nb-feature nb-position-feature">
              <label>Bottom offset<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Bottom Offset" data-content="The distance to offset the Subscription Bell from the bottom of the page. For example, <code>20px</code> is the default value." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_offset_bottom" placeholder="20px" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_offset_bottom']); ?>">
            </div>
            <div class="field nb-feature nb-position-feature nb-position-bottom-left-feature">
              <label>Left offset<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Left Offset" data-content="The distance to offset the Subscription Bell from the left of the page. For example, <code>20px</code> is the default value." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_offset_left" placeholder="20px" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_offset_left']); ?>">
            </div>
            <div class="field nb-feature nb-position-feature nb-position-bottom-right-feature">
              <label>Right offset<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Right Offset" data-content="The distance to offset the Subscription Bell from the right of the page. For example, <code>20px</code> is the default value." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_offset_right" placeholder="20px" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_offset_right']); ?>">
            </div>
          </div>

          <div class="ui segment nb-feature nb-color-feature">
            <div class="ui dividing header">
              <h4>
                Subscription Bell Color Customization
              </h4>
            </div>
            <p class="small normal-weight lato">You can override the theme's colors by entering your own. Use any CSS-valid color. For example, <code>white</code>, <code>#FFFFFF</code>, <code>#FFF</code>, <code>rgb(255, 255, 255)</code>, <code>rgba(255, 255, 255, 1.0)</code>, and <code>transparent</code> are all valid values.</p>
            <div class="field nb-feature nb-color-feature">
              <label>Main button background color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Background Color" data-content="The background color of the main Subscription Bell." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_background" placeholder="#e54b4d" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_background']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Main button foreground color (main bell icon and inner circle)<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Foreground Color" data-content="The color of the bell icon and inner circle on the main Subscription Bell." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_foreground" placeholder="white" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_foreground']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pre-notify badge background color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Badge Background Color" data-content="The background color of the small secondary circle on the main Subscription Bell. This badge is shown to first-time site visitors only." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_badge_background" placeholder="black" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_badge_background']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pre-notify badge foreground color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Badge Foreground Color" data-content="The text color on the small secondary circle on the main Subscription Bell. This badge is shown to first-time site visitors only." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_badge_foreground" placeholder="white" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_badge_foreground']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pre-notify badge border color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Badge Border Color" data-content="The border color of the small secondary circle on the main Subscription Bell. This badge is shown to first-time site visitors only." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_badge_border" placeholder="white" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_badge_border']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pulse animation color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Pulse Animation Color" data-content="The color of the quickly expanding circle that's used as an animation when a user clicks on the Subscription Bell." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_pulse" placeholder="#e54b4d" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_pulse']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button background color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Popup - Action Button Background Color" data-content="The color of the action button (SUBSCRIBE/UNSUBSCRIBE) on the Subscription Bell popup." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_background" placeholder="#e54b4d" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_popup_button_background']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button background color (on hover)<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Popup - Action Button Background Color (on hover)" data-content="The color of the action button (SUBSCRIBE/UNSUBSCRIBE) on the Subscription Bell popup when you hover over the button." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_background_hover" placeholder="#CC3234" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_popup_button_background_hover']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button background color (on click)<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Popup - Action Button Background Color (on click)" data-content="The color of the action button (SUBSCRIBE/UNSUBSCRIBE) on the Subscription Bell popup when you hold down the mouse." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_background_active" placeholder="#B2181A" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_popup_button_background_active']); ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button text color<i class="tiny circular help icon link" role="popup" data-title="Subscription Bell Popup - Action Button Text Color" data-content="The color of the quickly expanding circle that's used as an animation when a user clicks on the Subscription Bell." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_color" placeholder="white" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_color_popup_button_color']); ?>">
            </div>
          </div>

          <div class="ui segment nb-feature nb-text-feature">
            <div class="ui dividing header">
              <h4>
                Subscription Bell Text Customization
              </h4>
            </div>
          <div class="field nb-feature nb-text-feature">
            <label>Tip when unsubscribed</label>
            <input type="text" name="notifyButton_tip_state_unsubscribed" placeholder="Subscribe to notifications" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_tip_state_unsubscribed']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Tip when subscribed</label>
            <input type="text" name="notifyButton_tip_state_subscribed" placeholder="You're subscribed to notifications" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_tip_state_subscribed']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Tip when blocked</label>
            <input type="text" name="notifyButton_tip_state_blocked" placeholder="You've blocked notifications" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_tip_state_blocked']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Message on subscribed</label>
            <input type="text" name="notifyButton_message_action_subscribed" placeholder="Thanks for subscribing!" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_message_action_subscribed']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Message on re-subscribed (after first unsubscribing)</label>
            <input type="text" name="notifyButton_message_action_resubscribed" placeholder="You're subscribed to notifications" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_message_action_resubscribed']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Message on unsubscribed</label>
            <input type="text" name="notifyButton_message_action_unsubscribed" placeholder="You won't receive notifications again" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_message_action_unsubscribed']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Main dialog title</label>
            <input type="text" name="notifyButton_dialog_main_title" placeholder="Manage Site Notifications" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_dialog_main_title']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Main dialog subscribe button</label>
            <input type="text" name="notifyButton_dialog_main_button_subscribe" placeholder="SUBSCRIBE" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_dialog_main_button_subscribe']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Main dialog unsubscribe button</label>
            <input type="text" name="notifyButton_dialog_main_button_unsubscribe" placeholder="UNSUBSCRIBE" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_dialog_main_button_unsubscribe']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Blocked dialog title</label>
            <input type="text" name="notifyButton_dialog_blocked_title" placeholder="Unblock Notifications" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_dialog_blocked_title']); ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Blocked dialog message</label>
            <input type="text" name="notifyButton_dialog_blocked_message" placeholder="Follow these instructions to allow notifications:" value="<?php echo esc_attr($onesignal_wp_settings['notifyButton_dialog_blocked_message']); ?>">
          </div>
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="use_native_prompt" value="true" <?php if (array_key_exists('use_native_prompt', $onesignal_wp_settings) && $onesignal_wp_settings['use_native_prompt']) { echo "checked"; } ?>>
              <label>Attempt to automatically prompt new site visitors with Native Browser Prompt (not recommended)<i class="tiny circular help icon link" role="popup" data-title="Native Prompt" data-content="If checked, we will attempt to automatically present the browser's native prompt. We don't recommend this as browsers may penalize you for immediately displaying this prompt. Instead we recommend using one of our two-step prompting options: Slide Prompt or Subscription Bell." data-variation="wide"></i></label>
            </div>
	  </div>
          <div class="callout danger native-prompt-warning" style="display: none;">
	    <p>We strongly recommend not immediately prompting users with the native prompt as most browsers will hide this prompt if users frequently click deny. <a href="https://documentation.onesignal.com/docs/native-browser-prompt" target="_blank"> More information</a></p>
          </div>
        </div>
        <div class="popup-modal-settings">
          <div class="ui dividing header">
            <i class="external icon"></i>
            <div class="content">
              Prompt Customization
            </div>
          </div>
          <div style="display:flex; flex-wrap: wrap; align-items: center;">
            <img src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/http-prompt.jpg") ?>" width="500">
            <img src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/http-prompt.png") ?>" width="500">
          </div>
          <div class="ui borderless shadowless segment" style="position: relative;">
              <p class="lato">These settings modify the HTTP Pop-up Prompt and HTTPS Slide Prompt for all users. Use this to localize the Prompt to your language. All fields here are limited in the length of text they can display.</p>
              <div class="field">
                <div class="ui toggle checkbox">
                  <input type="checkbox" name="prompt_customize_enable" value="true" <?php if (array_key_exists('prompt_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings['prompt_customize_enable']) { echo "checked"; } ?>>
                  <label>Customize the Prompt text</label>
                </div>
              </div>
              <div class="field prompt-customize-feature">
                  <label>Action Message</label>
                  <input type="text" name="prompt_action_message" placeholder="We'd like to send you push notifications. You can unsubscribe at any time." value="<?php echo esc_attr($onesignal_wp_settings['prompt_action_message']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Accept Button Text</label>
                  <input type="text" name="prompt_accept_button_text" placeholder="ALLOW" value="<?php echo esc_attr($onesignal_wp_settings['prompt_accept_button_text']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Cancel Button Text</label>
                  <input type="text" name="prompt_cancel_button_text" placeholder="NO THANKS" value="<?php echo esc_attr($onesignal_wp_settings['prompt_cancel_button_text']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                <label>Auto Accept Title (Click Allow) - HTTP Only</label>
                <input type="text" name="prompt_auto_accept_title" placeholder="Click Allow" value="<?php echo esc_attr($onesignal_wp_settings['prompt_auto_accept_title']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                <label>Site Name - HTTP Only</label>
                <input type="text" name="prompt_site_name" placeholder="http://yoursite.com" value="<?php echo esc_attr($onesignal_wp_settings['prompt_site_name']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Title (Desktop) - HTTP Only</label>
                  <input type="text" name="prompt_example_notification_title_desktop" placeholder="This is an example notification" value="<?php echo esc_attr($onesignal_wp_settings['prompt_example_notification_title_desktop']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Message (Desktop) - HTTP Only</label>
                  <input type="text" name="prompt_example_notification_message_desktop" placeholder="Notifications will appear on your desktop" value="<?php echo esc_attr($onesignal_wp_settings['prompt_example_notification_message_desktop']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Title (Mobile) - HTTP Only</label>
                  <input type="text" name="prompt_example_notification_title_mobile" placeholder="Example notification" value="<?php echo esc_attr($onesignal_wp_settings['prompt_example_notification_title_mobile']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Message (Mobile) - HTTP Only</label>
                  <input type="text" name="prompt_example_notification_message_mobile" placeholder="Notifications will appear on your device" value="<?php echo esc_attr($onesignal_wp_settings['prompt_example_notification_message_mobile']); ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Caption - HTTP Only</label>
                  <input type="text" name="prompt_example_notification_caption" placeholder="(you can unsubscribe anytime)" value="<?php echo esc_attr($onesignal_wp_settings['prompt_example_notification_caption']); ?>">
              </div>
          </div>
        </div>
        <div class="ui dividing header">
          <i class="birthday outline icon"></i>
          <div class="content">
            Welcome Notification Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment" style="position: relative;">
            <img class="img-responsive no-center" src="<?php echo esc_url(ONESIGNAL_PLUGIN_URL."views/images/settings/welcome-notification.jpg") ?>" width="360">
            <div class="explanation">
              <p>A welcome notification is sent to new visitors after subscribing. A new visitor is someone who hasn't previously registered. If a user's browser cache is cleared, the user is considered new again.</p>
            </div>
            <div class="field">
              <div class="ui toggle checkbox">
                <input type="checkbox" name="send_welcome_notification" value="true" <?php if ($onesignal_wp_settings['send_welcome_notification']) { echo "checked"; } ?>>
                <label>Send new users a welcome push notification after subscribing<i class="tiny circular help icon link" role="popup" data-title="Welcome Notification" data-content="If enabled, your site will send a welcome push notification to new site visitors who have just subscribed. The message is customizable below."></i></label>
              </div>
            </div>
            <div class="field welcome-notification-feature">
                <label>Title<i class="tiny circular help icon link" role="popup" data-title="Welcome Notification Title" data-content="The welcome notification's title. You can localize this to your own language. If not set, the site's title will be used. Set to one space ' ' to clear the title, although this is not recommended." data-variation="wide"></i></label>
                <input type="text" placeholder="(defaults to your website's title if blank)" name="welcome_notification_title" value="<?php echo esc_attr($onesignal_wp_settings['welcome_notification_title']); ?>">
            </div>
            <div class="field welcome-notification-feature">
                <label>Message<i class="tiny circular help icon link" role="popup" data-title="Welcome Notification Message" data-content="The welcome notification's message. You can localize this to your own language. A message is required. If left blank, the default of 'Thanks for subscribing!' will be used." data-variation="wide"></i></label>
                <input type="text" placeholder="Thanks for subscribing!" name="welcome_notification_message" value="<?php echo esc_attr($onesignal_wp_settings['welcome_notification_message']); ?>">
            </div>
          <div class="field welcome-notification-feature">
            <label>URL<i class="tiny circular help icon link" role="popup" data-title="Welcome Notification URL" data-content="The webpage to open when clicking the notification. If left blank, your main site URL will be used as a default." data-variation="wide"></i></label>
            <input type="text" placeholder="(defaults to your website's URL if blank)" name="welcome_notification_url" value="<?php echo esc_attr($onesignal_wp_settings['welcome_notification_url']); ?>">
          </div>
        </div>
        <div class="ui dividing header">
          <i class="wizard icon"></i>
          <div class="content">
            Automatic Notification Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notification_on_post" value="true" <?php if ($onesignal_wp_settings['notification_on_post']) { echo "checked"; } ?>>
              <label>Automatically send a push notification when I create a post from the WordPress editor<i class="tiny circular help icon link" role="popup" data-title="Automatic Push from WordPress Editor" data-content="If checked, when you create a new post from WordPress's editor, the checkbox 'Send notification on post publish/update' will be automatically checked. The checkbox can be unchecked to prevent sending a notification." data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notification_on_post_from_plugin" value="true" <?php if ($onesignal_wp_settings['notification_on_post_from_plugin']) { echo "checked"; } ?>>
              <label>Automatically send a push notification when I publish a post from 3<sup>rd</sup> party plugins<i class="tiny circular help icon link" role="popup" data-title="Automatic Push outside WordPress Editor" data-content="If checked, when a post is created outside of WordPress's editor, a push notification will automatically be sent. Must be the built-in WordPress post type 'post' and the post must be published." data-variation="wide"></i></label>
            </div>
          </div>
        </div>
        <div class="ui dividing header">
          <i class="area chart icon"></i>
          <div class="content">
            UTM Tracking Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <div class="field">
            <label>Additional Notification URL Parameters<i class="tiny circular help icon link" role="popup" data-html="Adds the specified string as extra URL parameters to your notification URL so that they can be tracked as an event by your analytics system. <em>Please escape your parameter values</em>; your input will be added as-is to the end of your notification URL. Example:</p>If you want:<em><li><code>utm_medium</code> to be <code>ppc</code></li><li><code>utm_source</code> to be <code>adwords</code></li><li><code>utm_campaign</code> to be <code>snow boots</code></li><li><code>utm_content</code> to be <code>durable snow boots</code></li></em><p><p>Then use the following string:</p><p><code style='word-break: break-all;'>utm_medium=ppc&utm_source=adwords&utm_campaign=snow%20boots&utm_content=durable%20%snow%boots</code></p>" data-variation="wide"></i></label>
            <input type="text" placeholder="utm_medium=ppc&utm_source=adwords&utm_campaign=snow%20boots&utm_content=durable%20%snow%boots" name="utm_additional_url_params" value="<?php echo esc_attr($onesignal_wp_settings['utm_additional_url_params']); ?>">
          </div>
        </div>
        <div class="ui dividing header">
          <i class="lab icon"></i>
          <div class="content">
            Advanced Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <div class="field">
            <label>Additional Custom Post Types for Automatic Notifications Created From Plugins<i class="tiny circular help icon link" role="popup" data-html="Enter a comma-separated list of custom post type names. Anytime a post is published with one of the listed post types, a notification will be sent to all your users. <strong class='least-strong'>The setting</strong> <em>Automatically send a push notification when I publish a post from 3rd party plugins</em> <strong class='least-strong'>must be enabled for this feature to work</strong>." data-variation="wide"></i></label>
            <input type="text" placeholder="forum,reply,topic  (comma separated, no spaces between commas)" name="allowed_custom_post_types" value="<?php echo esc_attr($onesignal_wp_settings['allowed_custom_post_types']); ?>">
          </div>
          <?php if (OneSignalUtils::url_contains_parameter(ONESIGNAL_URI_REVEAL_PROJECT_NUMBER)): ?>
            <div class="field">
              <div class="ui toggle checkbox">
                <input type="checkbox" name="show_gcm_sender_id" value="true" <?php if ($onesignal_wp_settings['show_gcm_sender_id']) { echo "checked"; } ?>>
                <label>Use my own Google Project Number<i class="tiny circular help icon link" role="popup" data-title="Providing Your Own Web Push Keys" data-content="Check this if you'd like to provide your own Google Project Number."></i></label>
              </div>
            </div>
          <?php endif; ?>
          <div class="field custom-manifest-feature">
            <label>Custom manifest.json URL<i class="tiny circular help icon link" role="popup" data-html="<p>Enter the complete URL to your existing manifest.json file to be used in place of our own. Your URL's domain should match that of your main site that users are visiting.</p><p>e.g. <code>https://yoursite.com/manifest.json</code></p>" data-variation="wide"></i></label>
            <input type="text" placeholder="https://yoursite.com/manifest.json" name="custom_manifest_url" value="<?php echo esc_attr($onesignal_wp_settings['custom_manifest_url']); ?>">
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="use_custom_sdk_init" value="true" <?php if ($onesignal_wp_settings['use_custom_sdk_init']) {
                      echo 'checked';
                  } ?>>
              <label>Disable OneSignal initialization<i class="tiny circular help icon link" role="popup" data-title="Disable OneSignal Initialization" data-content="Check this if you'd like to disable OneSignal's normal initialization. Useful if you are adding a custom initialization script. All the options you've set here in the WordPress plugin will be accessible in a global variable window._oneSignalInitOptions."></i></label>
            </div>
          </div>
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="show_notification_send_status_message" value="true" <?php if ($onesignal_wp_settings['show_notification_send_status_message']) { echo "checked"; } ?>>
              <label>Show status message after sending notifications<i class="tiny circular help icon link" role="popup" data-title="Show Notification Send Status Message" data-content="If enabled, a notice at the top of your admin interface will show 'Successfully sent a notification to # recipients.' after our plugin sends a notification."></i></label>
            </div>
          </div>
        </div>
        </div>
        <button class="ui large teal button" type="submit">Save</button>
        <div class="ui inline validation nag">
            <span class="title">
              Your OneSignal subdomain cannot be empty or less than 4 characters. Use the same one you entered on the platform settings at onesignal.com.
            </span>
          <i class="close icon"></i>
        </div>
      </form>
    </div>
    </div>
  </div>
</div>
