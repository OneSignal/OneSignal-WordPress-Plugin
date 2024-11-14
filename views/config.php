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
        ⚠️ OneSignal Push Important Update:<br><br>
        Before updating to Version 3 of the OneSignal WordPress plugin you must migrate your configuration to dashboard.onesignal.com.
        <a href="https://documentation.onesignal.com/docs/wordpress-plugin-30" target="_blank">Learn more.</a>
      </span>
    </div>
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
