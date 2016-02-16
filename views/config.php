<?php
$onesignal_wp_settings = OneSignal::get_onesignal_settings();

if (array_key_exists('app_id', $_POST)) {
  $onesignal_wp_settings = OneSignal_Admin::save_config_page($_POST);
}
?>

<header class="onesignal">
  <a href="https://onesignal.com" target="_blank">
    <div class="onesignal logo" id="logo-onesignal" style="width: 250px; height: 52px; margin: 0 auto;">&nbsp;</div>
  </a>
</header>
<div class="outer site onesignal container">
  <div class="ui site onesignal container" id="content-container">
    <div class="ui pointing stackable menu">
      <a class="item" data-tab="setup">Setup</a>
      <a class="active item" data-tab="configuration">Configuration</a>
    </div>
    <div class="ui tab borderless shadowless segment" data-tab="setup" style="padding-top: 0; padding-bottom: 0;">
      <div class="ui special padded segment" style="padding-top: 0 !important;">
      <div class="ui top secondary pointing menu">
      <div class="ui grid" style="margin: 0 !important; padding: 0 !important;">
        <a class="item" data-tab="setup/0">Overview</a>
        <a class="item" data-tab="setup/1">Google Keys</a>
        <a class="item" data-tab="setup/2">Chrome & Firefox Push</a>
        <a class="item" data-tab="setup/3">OneSignal Keys</a>
        <a class="item" data-tab="setup/4">Modify Site</a>
        <a class="item" data-tab="setup/5">Safari Push</a>
        <a class="item" data-tab="setup/7">Results</a>
        </div>
      </div>
      <div class="ui tab borderless shadowless segment" data-tab="setup/0">
        <p>We'll guide you through adding web push for Chrome, Safari, and Firefox for your Wordpress blog.</p>
        <p>First you'll get some required keys from Google. Then you'll be on our website creating a new app and setting up web push for each browser. This entire process should take around 15 minutes.</p>
        <p>Please follow each step in order! If you're ever stuck or have questions, click the bright red button to chat with us! We read and respond to every message.</p>
        <p>Click <a href="javascript:void(0);" onclick="activateSetupTab('setup/1');">Google Keys</a> to begin.</p>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/1">
        <p>To begin, we'll create and configure a Google Project. This authorizes us to use Google's web push services for your notifications.</p>
        <dl>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>Create a <a href="https://console.developers.google.com/project" target="_blank">Google Developers</a> account or log in to your existing account.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>Once you're logged in, click <strong>Create project</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-1.jpg" ?>">
              <p>Choose any name for your project. Here we use <code>example-project</code>. Click <strong>Create</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-1-2.jpg" ?>">
            </dd>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>Find your <strong>Project number</strong>.</p>
              <p>Put this number in the <em>Project Number</em> field of the <em>Configuration</em> tab. You'll also need this number again in the next page, so save it!</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-2.jpg" ?>">
            </dd>
          </div>

          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>Project Number</h3>
            <p>Put this number in the <em>Project Number</em> field of the <em>Configuration</em> tab.
              <br> You'll also need this number again in the next page, so save it!</p>
          </div>
          <div class="ui segment">
            <dt>4</dt>
            <dd>
              <p>Click <strong>Enable and manage APIs</strong> underneath your Project number.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-3.jpg" ?>">
              <p>On the right pane, in the search box, type <strong><code>cloud messaging android</code></strong>.</p>
              <p>Select <strong>Google Cloud Messaging for Android</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-3-1.jpg" ?>">
              <p>Click <strong>Enable API</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-4.jpg" ?>">
            </dd>
          </div>
          <div class="ui segment">
            <dt>5</dt>
            <dd>
              <p>Click <strong>Credentials</strong> on the left sidebar.</p>
              <p>On the right pane, click <strong>New credentials > API key</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-5.jpg" ?>">
              <p>Click <strong>Server Key</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-6.jpg" ?>">
              <p><em>Without entering any values</em>, click <strong>Create</strong>.</p>
              <p class="alternate"><em>Make sure to leave the IP address textbox blank. You may name the key if you'd like, but it's not necessary.</em></p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-7.jpg" ?>">
              <p>Find your <strong>API Key</strong>.</p>
              <p>You'll need this number in the next page, so save it!</p>
              </p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-8.jpg" ?>">
            </dd>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>API Key</h3>
            <p>You'll need this number in the next page, so save it!</p>
          </div>
          <div class="ui segment">
            <dt>6</dt>
            <dd>
              <p>You've successfully created and configured your Google project! You should have these two values:</p>
              <ul>
                <li>Your <strong>Project Number</strong>. It looks something like <code>703322744261</code>. You should have used this on the <em>Configuration</em> tab.</li>
                <li>Your <strong>API key</strong>. It looks something like <code>AIzBSyC_N8hcAeDaZEELfPadGnKBWE5zrmAdYfr</code>. You don't need to use this on the <em>Configuration</em> tab, but you do need it on the next page.</li>
              </ul>
              <p>Click <a href="javascript:void(0);" onclick="activateSetupTab('setup/2');">Chrome & Firefox Push</a> to continue.</p>
            </dd>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/2">
        <p>Now that we've set our Google Project Number and API Key, we'll create and configure a OneSignal app.</p>
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
              <p>Click <strong>Add a new app</strong>.</p>
              <p class="alternate"><em>If you're a new user, a welcome popup will appear. You can click <strong>Add a new app</strong> on the last screen.</em></p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-1.jpg" ?>">
              <p>Choose any name for your app. Here we use <code>Wordpress Demo</code>. Click <strong>Create</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-2.jpg" ?>">
            </dd>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>Select the <strong>Website Push</strong> platform and click <strong>Next</strong>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-3.jpg" ?>">
            </dd>
          </div>

          <div class="ui segment">
            <dt>4</dt>
            <dd>
              <p>Select the <strong>Google Chrome & Mozilla Firefox</strong> sub-platform and click <strong>Next</strong>.</p>
              <p>Instructions to set up Safari web push are available at the end of this guide.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-4.jpg" ?>">
            </dd>
          </div>
          <div class="ui segment">
            <dt>5</dt>
            <dd>
              <p>In this step, we focus only on filling out the <em>Site URL</em>.
                <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-5.jpg" ?>">
              <p>Enter the URL to your site's domain. The purpose of this field is to prevent other sites from hijacking your keys to impersonate you and send push notifications on your behalf. Please note:</p>
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
                  <p>If your site uses HTTPS, use <code>https://domain.com</code>. If your site uses a mix of HTTPS/HTTP or only HTTP, use <code>http://domain.com</code>. If you're not sure, <a href="">contact us!</a>.</p>
                  <p></p>
                </li>
              </ul>
              <p>You may use two special properties instead of the URL to your site domain:</p>
              <ul>
                <li>
                  <p><code>localhost</code></p>
                  <p>You can use this to test locally.</p>
                </li>
                <li>
                  <p><code>*</code></p>
                  <p>This allows all sites. <em>Please don't use this on production, otherwise any other site can send push notifications on your behalf.</em></p>
                </li>
              </ul>
            </dd>
          </div>

          <div class="ui segment">
            <dt>6</dt>
            <dd>
              <p>In this step, we focus only on filling out the <em>Google Server API Key</em>.
                <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-6.jpg" ?>">
              <p>Enter the <em>API Key</em> you saved from the previous page.</p>
            </dd>
          </div>

          <div class="ui segment">
            <dt>7</dt>
            <dd>
              <p>In this step, we focus only on filling out the <em>Default Notification Icon URL</em>.
                <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-7.jpg" ?>">
              <p>Enter the complete URL to your notification icon. Please note:</p>
              <ul>
                <li>
                  <p>Your notification icon must be <code>80 pixels &times; 80 pixels</code> large</p>
                  <p>On some platforms, larger icons are forcefully downsized to <code>40 &times; 40</code> and centered with an ugly white 20 pixel margin</p>
                  <p></p>
                </li>
                <li>The <a href="https://onesignal.com/images/notification_logo.png" target="_blank">default OneSignal notification icon</a> will be used as a default if you don't choose one</li>
              </ul>
            </dd>
          </div>
          <div class="ui segment">
            <dt>8</dt>
            <dd>
              <p>In this step, we focus only on the <em>HTTP Fallback Mode</em>.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-8.jpg" ?>">
              <div class="relative ui two column middle aligned very relaxed stackable grid">
                <div class="center aligned column">
                  <code><strong>http</strong>://domain.com</code>
                  <h3>HTTP</h3>
                </div>
                <div class="ui vertical divider">
                  Or
                </div>
                <div class="center aligned column">
                  <code><strong>https</strong>://domain.com</code>
                  <h3>HTTPS</h3>
                </div>
              </div>
              <p>Check the <em>My site is not fully HTTPS</em> box if:</p>
              <ul>
                <li>You already know your site doesn't support HTTPS</li>
                <li>Your site supports HTTPS, but your site can be viewed on <code><strong>http</strong>://domain.com</code>, without being automatically redirected to <code><strong>https</strong>://domain.com</code></li>
              </ul>
              <p><strong>Otherwise, do not check the box and leave it blank.</strong></p>
            </dd>
          </div>
        </dl>
        <div class="ui center aligned piled segment">
          <i class="big grey announcement pinned icon"></i>
          <h3>Next Steps</h3>
          <p><strong>Steps 9 &hyphen; 10 only apply if you've checked <code>My site is not fully HTTPS</code>.</strong>
            <br> If you've left the option blank, you may optionally continue to <a href="javascript:void(0);" onclick="activateSetupTab('setup/5');">Safari Push</a>!</p>
        </div>
        <dl>
          <div class="ui segment">
            <dt>9</dt>
            <dd>
              <p>In this step, we focus only on filling out the <em>Subdomain</em>.
                <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-9.jpg" ?>">
              <p>Chrome web push notifications don't support HTTP sites, but we work around that by subscribing your users to a subdomain of our site, which <em>is</em> fully HTTPS.</p>
              <p>Choose any subdomain you like; your push notifications will come from <code>https://yoursubdomain.onesignal.com</code>.</p>
              <p>Choose your subdomain well the first time! Changing your subdomain in the future has a nasty side effect: all previously subscribed users will see notifications from your old subdomain and new subdomain, unless they clear their browser data.</p>
              <p>When you're done, <strong>enter this value into the <em>Configuration</em> tab under Subdomain</strong>.</p>
            </dd>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>Subdomain</h3>
            <p>Put this value in the <em>Subdomain</em> field of the <em>Configuration</em> tab.</p>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey warning pinned icon"></i>
            <h3>Changing Your Subdomain</h3>
            <p>Changing your subdomain makes all currently subscribed players receive notifications from both
              <br> your old and new subdomain. It's important to choose your subdomain well the first time.</p>
          </div>
          <div class="ui segment">
            <dt>10</dt>
            <dd>
              <p>In this step, we focus only on filling out the <em>Google Project Number</em>.
                <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-10.jpg" ?>">
              <p>Enter the <em>Project Number</em> you saved from the previous page.</p>
              <p>Please note that changing this Project Number makes all subscribed players under the old Project Number unmessageable.</p>
            </dd>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey warning pinned icon"></i>
            <h3>Changing Your Project Number</h3>
            <p>Changing your Project Number makes all subscribed players under the old Project Number unmessageable.
              <br> It's important to set it up correctly the first time.</p>
          </div>
          <div class="ui segment">
            <dt>11</dt>
            <dd>
              <p>Click <strong>Save</strong> to commit your Chrome & Firefox push settings <strong>and then exit the dialog</strong>.</p>
              <p>If you get errors please follow the instructions to fix them. If you're still experiencing problems, <a href="javascript:void(0);" onclick="showSupportMessage('chrome-push-settings');">chat with us and we'll help you out</a>. Let us know what your specific issue is.</p>
              <p>Click <a href="javascript:void(0);" onclick="activateSetupTab('setup/3');">OneSignal Keys</a> to continue. This next section is much easier!</p>
            </dd>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/3">
        <p>Now that we've set our Chrome & Firefox push settings, we'll get our <em>App ID</em> and <em>REST API Key</em> from the OneSignal dashboard.</p>
        <dl>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>If you're continuing from the previous page:</p>
              <ol>
                <li>Close <strong>&times;</strong> the dialog.</li>
                <li>Click <strong>Yes</strong> to the <em>Finish later?</em> prompt.</li>
                <li>Click the app you just configured to access the main app's page.</li>
                <li>Click <strong>App Settings</strong> from the left sidebar.</li>
              </ol>
              <p>If you're resuming this setup from another time:</p>
              <ol>
                <li>Log in to your OneSignal account.</li>
                <li>Click the app you configured in the previous page to access the main app's page.</li>
                <li>Click <strong>App Settings</strong> from the left sidebar.</li>
              </ol>
              <p>You should be on this page:</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/keys-1.jpg" ?>">
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>Click <strong>Keys &amp; IDs</strong> on the right tabbed pane.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/keys-2.jpg" ?>">
              <p>Copy the <strong>REST API Key</strong> and <strong>OneSignal App ID</strong> to the <em>Configuration</em> tab.</p>
            </dd>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>REST API Key &amp; App ID</h3>
            <p>Copy the <strong>REST API Key</strong> and <strong>OneSignal App ID</strong> to the <em>Configuration</em> tab.</p>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>You're done configuring settings! Continue to <a href="javascript:void(0);" onclick="activateSetupTab('setup/4');">Modify Site</a>.</p>
            </dd>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/4">
        <p>By now, push notifications already work on your site. <strong>But your users still need a way to <em>subscribe</em> to your site's notifications</strong>. There are two ways:
          <div class="relative ui two column middle aligned very relaxed stackable grid" style="margin-bottom: 0 !important; padding-bottom: 0 !important;">
            <div class="center aligned column">
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/bell.jpg" ?>" width="60%">
            </div>
            <div class="ui vertical divider">
              Or
            </div>
            <div class="center aligned column">
              <code class="massive">&lt;div <strong>class="OneSignal-prompt"</strong>&gt;</code>
            </div>
          </div>
          <div class="relative ui two column middle aligned very relaxed stackable grid" style="margin-top: 0 !important; padding-top: 0 !important;">
            <div class="center aligned column">
              <h3>OneSignal Notify Button <span class="ui green horizontal label">EASY</span></h3>
            </div>
            <div class="center aligned column">
              <h3>Custom Implementation <span class="ui purple horizontal label">ADVANCED</span></h3>
            </div>
          </div>
          <ol>
            <li>The notify button is an interactive site widget we developed (enabled by default for new users)</li>
              <ol>
                <li>Users see the notify button on the bottom right corner of your site. They can click the notify button to subscribe.</li>
                <li>The notify button is custom developed by us and does all the work for you! It detects when users are unsubscribed, already subscribed, or have blocked your site and show instructions to unblock. It allows users to easily temporarily subscribe from and resubscribe to notifications.</li>
              </ol>
            <li>The custom implementation uses the CSS class <code>OneSignal-prompt</code> on any clickable element</li>
              <ol>
                <li>Clicking any element with this CSS class will allow users to subscribe</li>
                <li>Use the CSS class if your site is highly specialized and needs complete control over styling and positioning. Our SDK detects any elements with the CSS class <code>OneSignal-prompt</code> and prompts the user to subscribe upon clicking those elements.</li>
              </ol>
            <li><strong>For HTTPS sites only</strong>, a third way &mdash; auto-prompting users to subscribe &mdash; can be combined with either method above</li>
            <ol>
              <li>Site visitors will be prompted to subscribe as soon as your site loads</li>
            </ol>
          </ol>
        </p>

        <dl>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>In more detail:</p>

              <p>To add the notify button, go to the Configuration tab and enable it under Prompt Settings.</p>
              <p>To add the CSS class, add <strong><code>OneSignal-prompt</code></strong> to any element you'd like. Our plugin initializes JavaScript code on your page that, on document ready, searches for all instances of the class and attaches a click event handler.</p>

              <p>For HTTPS sites only, to automatically prompt visitors to subscribe, enable the option under Prompt Settings.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>You're done setting up your site for Chrome & Firefox push!</p>
              <p>Your site works completely with Chrome & Firefox push now. You can learn how to add <a href="javascript:void(0);" onclick="activateSetupTab('setup/5')">Safari</a> web push.</p>
            </dd>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/5">
        <dl>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>Log in to your OneSignal account, and navigate to the <em>App Settings</em> page of the app you configured in this guide.</p>
              <p>You should be on this page:</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-1.jpg" ?>">
              <p>Click <strong>Configure</strong> on the platform <em>Apple Safari</em>.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>In this step, we'll focus on filling out the <em>Site Name</em> and <em>Site URL</em> fields.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-2.jpg" ?>">
              <p>For the <strong>Site Name</strong>, enter a name you'd like your users to see.</p>
              <p>In the following sample image, <em>OneSignal</em> is the site name:</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-prompt.jpg" ?>" width="450">
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
                  <p>If your site uses HTTPS, use <code>https://domain.com</code>. If your site uses a mix of HTTPS/HTTP or only HTTP, use <code>http://domain.com</code>. If you're not sure, <a href="javascript:void(0);" onclick="showSupportMessage('not_sure_protocol')">contact us!</a>.</p>
                  <p></p>
                </li>
              </ul>
            </dd>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>In this step, we'll focus on uploading your Safari notification icons.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-3.jpg" ?>">
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
              <p>If you get errors please follow the instructions to fix them. If you're still experiencing problems, <a href="javascript:void(0);" onclick="showSupportMessage('safari-push-settings');">chat with us and we'll help you out</a>. Let us know what your specific issue is.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>5</dt>
            <dd>
              <p><strong>Refresh</strong> the page, and then copy the <strong>Safari Web ID</strong> you see to the <em>Configuration</em> tab.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-4.jpg" ?>">
              <p>That's it for setting up Safari push!</p>
            </dd>
          </div>
          <div class="ui center aligned piled segment">
            <i class="big grey pin pinned icon"></i>
            <h3>Safari Web ID</h3>
            <p>Copy the <strong>Safari Web ID</strong> to the <em>Configuration</em> tab.</p>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/6">
        <dl>
          <div class="ui segment">
            <dt>1</dt>
            <dd>
              <p>Log in to your OneSignal account, and navigate to the <em>App Settings</em> page of the app you configured in this guide.</p>
              <p>You should be on this page:</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-1.jpg" ?>">
              <p>Click <strong>Configure</strong> on the platform <em>Mozilla Firefox</em>.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>2</dt>
            <dd>
              <p>A friendly message will tell you no actions are required to activate Mozilla Firefox.</p>
              <p>Click <strong>Save</strong> to activate Mozilla Firefox push notifications.</p>
              <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/firefox-1.jpg" ?>">
              <p>If you see a gray colored box saying <em>Chrome Website Push needs to be configured first</em>, please <a href="javascript:void(0);" onclick="activateSetupTab('setup/2');">follow this guide to activate Chrome website push first</a>.</p>
              <p>If your Chrome web push works correctly, your Firefox web push will automatically work correctly.</p>
            </dd>
          </div>
          <div class="ui segment">
            <dt>3</dt>
            <dd>
              <p>That's it for setting up Firefox push!</p>
            </dd>
          </div>
        </dl>
      </div>
      <div class="ui tab borderless shadowless segment" style="z-index: 1;" data-tab="setup/7">
        <p>This section shows push notifications working for <em>Chrome</em>, <em>Safari</em>, and <em>Firefox</em> in <em>HTTP</em> and <em>HTTPS</em> mode.</p>
        <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/web-push.jpg" ?>">
        <p></p>
        <dl>
          <div class="ui horizontal divider">Notify Button</div>
          <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/notify-button.jpg" ?>">
          <div class="ui horizontal divider">Chrome (HTTP)</div>
          <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/chrome-http.jpg" ?>">
          <div class="ui horizontal divider">Chrome (HTTPS)</div>
          <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/chrome-https.jpg" ?>">
          <div class="ui horizontal divider">Safari (HTTP & HTTPS)</div>
          <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/safari-https.jpg" ?>">
          <div class="ui horizontal divider">Firefox (HTTP)</div>
          <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/firefox-http.jpg" ?>">
          <div class="ui horizontal divider">Firefox (HTTPS)</div>
          <img class="img-responsive" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/firefox-https.jpg" ?>">
        </dl>
      </div>
    </div>
    </div>
    <div class="ui borderless shadowless active tab segment" style="z-index: 1; padding-top: 0; padding-bottom: 0;" data-tab="configuration">
    <div class="ui special padded raised stack segment">
      <form class="ui form" role="configuration" action="#" method="POST">
        <div class="ui dividing header">
          <i class="setting icon"></i>
          <div class="content">
            Account Settings
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="is_site_https" <?php if (@$onesignal_wp_settings['is_site_https_firsttime'] === 'unset') { echo "data-unset=\"true\""; }  if (@$onesignal_wp_settings['is_site_https']) { echo "checked"; }  ?>>
              <label>My site uses an HTTPS connection (SSL)<i class="tiny circular help icon link" role="popup" data-html="<p>Check this if your site uses HTTPS:</p><img src='<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/https-url.png" ?>' width=619>" data-variation="flowing"></i></label>
            </div>
          </div>
          <div class="ui inline subdomain-http nag">
            <span class="title">
              This option is disabled when your current URL begins with <code>http://</code>. Please access this page using <code>https://</code> to enable this option.
            </span>
            <i class="close icon"></i>
          </div>
          <div class="field">
            <label>Google Project Number<i class="tiny circular help icon link" role="popup" data-title="Google Project Number" data-content="Your project number. You can find this on Setup > Google Keys > Step 3." data-variation="wide"></i></label>
            <input type="text" name="gcm_sender_id" placeholder="#############" value="<?php echo $onesignal_wp_settings['gcm_sender_id'] ?>">
          </div>
          <div class="field">
            <label>App ID<i class="tiny circular help icon link" role="popup" data-title="App ID" data-content="Your 36 character alphanumeric app ID. You can find this on Setup > OneSignal Keys > Step 2." data-variation="wide"></i></label>
            <input type="text" name="app_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxx" value="<?php echo $onesignal_wp_settings['app_id'] ?>">
          </div>
          <div class="field">
            <label>REST API Key<i class="tiny circular help icon link" role="popup" data-title="Rest API Key" data-content="Your 48 character alphanumeric REST API Key. You can find this on Setup > OneSignal Keys > Step 2." data-variation="wide"></i></label>
            <input type="text" name="app_rest_api_key" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" value="<?php echo $onesignal_wp_settings['app_rest_api_key'] ?>">
          </div>
          <div class="field subdomain-feature">
            <label>Subdomain<i class="tiny circular help icon link" role="popup" data-title="Subdomain" data-content="Your chosen subdomain. You can find this on Setup > Chrome & Firefox Push > Step 9." data-variation="wide"></i></label>
            <input type="text" name="subdomain" placeholder="example" value="<?php echo $onesignal_wp_settings['subdomain'] ?>">
          </div>
          <div class="field">
            <label>Safari Web ID<i class="tiny circular help icon link" role="popup" data-title="Safari Web ID" data-content="Your chosen subdomain. You can find this on Setup > Safari Push > Step 5." data-variation="wide"></i></label>
            <input type="text" name="safari_web_id" placeholder="web.com.example" value="<?php echo @$onesignal_wp_settings['safari_web_id']; ?>">
          </div>
        </div>
        <div class="ui dividing header">
          <i class="alarm outline icon"></i>
          <div class="content">
            Prompt Settings & Notify Button
          </div>
        </div>
        <div class="ui borderless shadowless segment">
          <img class="img-responsive no-center" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/nb-unsubscribe.png" ?>" width="234">
          <div class="explanation">
            <p>Control the way visitors are prompted to subscribe. The notify button is an interactive widget your site visitors can use to manage their push notification subscription status. The notify button can be used to initially subscribe to push notifications, and to unsubscribe.</p>
          </div>

          <div class="field modal-prompt-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="use_modal_prompt" value="true" <?php if ($onesignal_wp_settings['use_modal_prompt']) { echo "checked"; } ?>>
              <label>Use an alternate full-screen prompt when requesting subscription permission (incompatible with notify button and auto-prompting)</label>
            </div>
          </div>
          <div class="field auto-register-feature">
            <div class="field">
              <div class="ui toggle checkbox">
                <input type="checkbox" name="prompt_auto_register" value="true" <?php if ($onesignal_wp_settings['prompt_auto_register']) { echo "checked"; } ?>>
                <label>Automatically prompt new site visitors to subscribe to push notifications<i class="tiny circular help icon link" role="popup" data-html="<p>If enabled, your site will automatically present the following without any code required:</p><img src='<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/chrome-https.jpg" ?>' width=450>" data-variation="flowing"></i></label>
              </div>
            </div>
          </div>

          <div class="field">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_enable" value="true" <?php if (array_key_exists('notifyButton_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_enable']) { echo "checked"; } ?>>
              <label>Enable the notify button<i class="tiny circular help icon link" role="popup" data-title="Notify Button" data-content="If checked, the notify button and its resources will be loaded into your website." data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_prenotify" value="true" <?php if (array_key_exists('notifyButton_prenotify', $onesignal_wp_settings) && @$onesignal_wp_settings['notifyButton_prenotify']) { echo "checked"; } ?>>
              <label>Show first-time site visitors an unread message icon<i class="tiny circular help icon link" role="popup" data-html="<p>If checked, a circle indicating 1 unread message will be shown:</p><img src='<?php echo ONESIGNAL_PLUGIN_URL."views/images/bell-prenotify.jpg" ?>' width=56><p>A message will be displayed when they hover over the notify button. This message can be customized below.</p>" data-variation="wide"></i></label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_showcredit" value="true" <?php if (array_key_exists('notifyButton_showcredit', $onesignal_wp_settings) && @$onesignal_wp_settings['notifyButton_showcredit']) { echo "checked"; } ?>>
              <label>Show the OneSignal logo on the notify button dialog</label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_customize_enable" value="true" <?php if (array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings) && @$onesignal_wp_settings['notifyButton_customize_enable']) { echo "checked"; } ?>>
              <label>Customize the notify bell text</label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_customize_offset_enable" value="true" <?php if (array_key_exists('notifyButton_customize_offset_enable', $onesignal_wp_settings) && @$onesignal_wp_settings['notifyButton_customize_offset_enable']) { echo "checked"; } ?>>
              <label>Customize the notify bell offset position</label>
            </div>
          </div>
          <div class="field nb-feature">
            <div class="ui toggle checkbox">
              <input type="checkbox" name="notifyButton_customize_colors_enable" value="true" <?php if (array_key_exists('notifyButton_customize_colors_enable', $onesignal_wp_settings) && @$onesignal_wp_settings['notifyButton_customize_colors_enable']) { echo "checked"; } ?>>
              <label>Customize the notify bell theme colors</label>
            </div>
          </div>
          <div class="inline-setting short field nb-feature">
            <label class="inline-setting">Size:</label>
            <select class="ui dropdown" name="notifyButton_size">
              <option value="small" <?php if (array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] == "small") { echo "selected"; } ?>>Small</option>
              <option value="medium" <?php if ((array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] == "medium") || !array_key_exists('notifyButton_theme', $onesignal_wp_settings)) { echo "selected"; } ?>>Medium</option>
              <option value="large" <?php if (array_key_exists('notifyButton_size', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_size'] == "large") { echo "selected"; } ?>>Large</option>
            </select>
          </div>
          <div class="inline-setting short field nb-feature">
            <label class="inline-setting">Position:</label>
            <select class="ui dropdown" name="notifyButton_position">
              <option value="bottom-left" <?php if (array_key_exists('notifyButton_position', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_position'] == "bottom-left") { echo "selected"; } ?>>Bottom Left</option>
              <option value="bottom-right" <?php if ((array_key_exists('notifyButton_position', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_position'] == "bottom-right") || !array_key_exists('notifyButton_position', $onesignal_wp_settings)) { echo "selected"; } ?>>Bottom Right</option>
            </select>
          </div>
          <div class="inline-setting short field nb-feature">
            <label class="inline-setting">Theme:</label>
            <select class="ui dropdown" name="notifyButton_theme">
              <option value="default" <?php if ((array_key_exists('notifyButton_theme', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_theme'] == "default") || !array_key_exists('notifyButton_theme', $onesignal_wp_settings)) { echo "selected"; } ?>>Red</option>
              <option value="inverse" <?php if (array_key_exists('notifyButton_theme', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_theme'] == "inverse") { echo "selected"; } ?>>White</option>
            </select>
          </div>
          <div class="ui segment nb-feature nb-position-feature">
            <div class="ui dividing header">
              <h4>
                Notify Button Offset Position Customization
              </h4>
            </div>
            <p class="small normal-weight lato">You can override the notify button's offset position in the X and Y direction using CSS-valid position values. For example, <code>20px</code> is the default value.</p>
            <div class="field nb-feature nb-position-feature">
              <label>Bottom offset<i class="tiny circular help icon link" role="popup" data-title="Notify Button Bottom Offset" data-content="The distance to offset the notify button from the bottom of the page. For example, <code>20px</code> is the default value." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_offset_bottom" placeholder="20px" value="<?php echo @$onesignal_wp_settings['notifyButton_offset_bottom']; ?>">
            </div>
            <div class="field nb-feature nb-position-feature nb-position-bottom-left-feature">
              <label>Left offset<i class="tiny circular help icon link" role="popup" data-title="Notify Button Left Offset" data-content="The distance to offset the notify button from the left of the page. For example, <code>20px</code> is the default value." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_offset_left" placeholder="20px" value="<?php echo @$onesignal_wp_settings['notifyButton_offset_left']; ?>">
            </div>
            <div class="field nb-feature nb-position-feature nb-position-bottom-right-feature">
              <label>Right offset<i class="tiny circular help icon link" role="popup" data-title="Notify Button Right Offset" data-content="The distance to offset the notify button from the right of the page. For example, <code>20px</code> is the default value." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_offset_right" placeholder="20px" value="<?php echo @$onesignal_wp_settings['notifyButton_offset_right']; ?>">
            </div>
          </div>

          <div class="ui segment nb-feature nb-color-feature">
            <div class="ui dividing header">
              <h4>
                Notify Button Color Customization
              </h4>
            </div>
            <p class="small normal-weight lato">You can override the theme's colors by entering your own. Use any CSS-valid color. For example, <code>white</code>, <code>#FFFFFF</code>, <code>#FFF</code>, <code>rgb(255, 255, 255)</code>, <code>rgba(255, 255, 255, 1.0)</code>, and <code>transparent</code> are all valid values.</p>
            <div class="field nb-feature nb-color-feature">
              <label>Main button background color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Background Color" data-content="The background color of the main notify button." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_background" placeholder="#e54b4d" value="<?php echo @$onesignal_wp_settings['notifyButton_color_background']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Main button foreground color (main bell icon and inner circle)<i class="tiny circular help icon link" role="popup" data-title="Notify Button Foreground Color" data-content="The color of the bell icon and inner circle on the main notify button." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_foreground" placeholder="white" value="<?php echo @$onesignal_wp_settings['notifyButton_color_foreground']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pre-notify badge background color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Badge Background Color" data-content="The background color of the small secondary circle on the main notify button. This badge is shown to first-time site visitors only." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_badge_background" placeholder="black" value="<?php echo @$onesignal_wp_settings['notifyButton_color_badge_background']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pre-notify badge foreground color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Badge Foreground Color" data-content="The text color on the small secondary circle on the main notify button. This badge is shown to first-time site visitors only." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_badge_foreground" placeholder="white" value="<?php echo @$onesignal_wp_settings['notifyButton_color_badge_foreground']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pre-notify badge border color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Badge Border Color" data-content="The border color of the small secondary circle on the main notify button. This badge is shown to first-time site visitors only." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_badge_border" placeholder="white" value="<?php echo @$onesignal_wp_settings['notifyButton_color_badge_border']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Pulse animation color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Pulse Animation Color" data-content="The color of the quickly expanding circle that's used as an animation when a user clicks on the notify button." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_pulse" placeholder="#e54b4d" value="<?php echo @$onesignal_wp_settings['notifyButton_color_pulse']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button background color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Popup - Action Button Background Color" data-content="The color of the action button (SUBSCRIBE/UNSUBSCRIBE) on the notify button popup." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_background" placeholder="#e54b4d" value="<?php echo @$onesignal_wp_settings['notifyButton_color_popup_button_background']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button background color (on hover)<i class="tiny circular help icon link" role="popup" data-title="Notify Button Popup - Action Button Background Color (on hover)" data-content="The color of the action button (SUBSCRIBE/UNSUBSCRIBE) on the notify button popup when you hover over the button." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_background_hover" placeholder="#CC3234" value="<?php echo @$onesignal_wp_settings['notifyButton_color_popup_button_background_hover']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button background color (on click)<i class="tiny circular help icon link" role="popup" data-title="Notify Button Popup - Action Button Background Color (on click)" data-content="The color of the action button (SUBSCRIBE/UNSUBSCRIBE) on the notify button popup when you hold down the mouse." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_background_active" placeholder="#B2181A" value="<?php echo @$onesignal_wp_settings['notifyButton_color_popup_button_background_active']; ?>">
            </div>
            <div class="field nb-feature nb-color-feature">
              <label>Popup action button text color<i class="tiny circular help icon link" role="popup" data-title="Notify Button Popup - Action Button Text Color" data-content="The color of the quickly expanding circle that's used as an animation when a user clicks on the notify button." data-variation="wide"></i></label>
              <input type="text" name="notifyButton_color_popup_button_color" placeholder="white" value="<?php echo @$onesignal_wp_settings['notifyButton_color_popup_button_color']; ?>">
            </div>
          </div>

          <div class="ui segment nb-feature nb-text-feature">
            <div class="ui dividing header">
              <h4>
                Notify Button Text Customization
              </h4>
            </div>
          <div class="field nb-feature nb-text-feature">
            <label>First-time visitor message (on notify button hover)</label>
            <input type="text" name="notifyButton_message_prenotify" placeholder="Click to subscribe to notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_message_prenotify']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Tip when unsubscribed</label>
            <input type="text" name="notifyButton_tip_state_unsubscribed" placeholder="Subscribe to notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_tip_state_unsubscribed']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Tip when subscribed</label>
            <input type="text" name="notifyButton_tip_state_subscribed" placeholder="You're subscribed to notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_tip_state_subscribed']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Tip when blocked</label>
            <input type="text" name="notifyButton_tip_state_blocked" placeholder="You've blocked notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_tip_state_blocked']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Message on subscribed</label>
            <input type="text" name="notifyButton_message_action_subscribed" placeholder="Thanks for subscribing!" value="<?php echo @$onesignal_wp_settings['notifyButton_message_action_subscribed']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Message on re-subscribed (after first unsubscribing)</label>
            <input type="text" name="notifyButton_message_action_resubscribed" placeholder="You're subscribed to notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_message_action_resubscribed']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Message on unsubscribed</label>
            <input type="text" name="notifyButton_message_action_unsubscribed" placeholder="You won't receive notifications again" value="<?php echo @$onesignal_wp_settings['notifyButton_message_action_unsubscribed']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Main dialog title</label>
            <input type="text" name="notifyButton_dialog_main_title" placeholder="Manage Site Notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_dialog_main_title']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Main dialog subscribe button</label>
            <input type="text" name="notifyButton_dialog_main_button_subscribe" placeholder="SUBSCRIBE" value="<?php echo @$onesignal_wp_settings['notifyButton_dialog_main_button_subscribe']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Main dialog unsubscribe button</label>
            <input type="text" name="notifyButton_dialog_main_button_unsubscribe" placeholder="UNSUBSCRIBE" value="<?php echo @$onesignal_wp_settings['notifyButton_dialog_main_button_unsubscribe']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Blocked dialog title</label>
            <input type="text" name="notifyButton_dialog_blocked_title" placeholder="Unblock Notifications" value="<?php echo @$onesignal_wp_settings['notifyButton_dialog_blocked_title']; ?>">
          </div>
          <div class="field nb-feature nb-text-feature">
            <label>Blocked dialog message</label>
            <input type="text" name="notifyButton_dialog_blocked_message" placeholder="Follow these instructions to allow notifications:" value="<?php echo @$onesignal_wp_settings['notifyButton_dialog_blocked_message']; ?>">
          </div>
          </div>
        </div>
        <div class="popup-modal-settings">
          <div class="ui dividing header">
            <i class="external icon"></i>
            <div class="content">
              Popup Settings
            </div>
          </div>
          <img class="img-responsive no-center" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/http-prompt.jpg" ?>" width="360">
          <div class="ui borderless shadowless segment" style="position: relative;">
              <p class="lato">These settings modify the popup message and button text for all users. Use this to localize the popup to your language. All fields here are limited in the length of text they can display; use the Preview Popup button to preview your changes.</p>
              <div class="field">
                <div class="ui toggle checkbox">
                  <input type="checkbox" name="prompt_showcredit" value="true" <?php if (@$onesignal_wp_settings['prompt_showcredit']) { echo "checked"; } ?>>
                  <label>Show the OneSignal logo on the prompt</label>
                </div>
              </div>
              <div class="field">
                <div class="ui toggle checkbox">
                  <input type="checkbox" name="prompt_customize_enable" value="true" <?php if (array_key_exists('prompt_customize_enable', $onesignal_wp_settings) && @$onesignal_wp_settings['prompt_customize_enable']) { echo "checked"; } ?>>
                  <label>Customize the popup text</label>
                </div>
              </div>
              <div class="field prompt-customize-feature">
                <button class="ui gray button" type="button" onclick="showHttpPopup()">Preview Popup</button>
              </div>
              <div class="field prompt-customize-feature">
                  <label>Action Message</label>
                  <input type="text" name="prompt_action_message" placeholder="wants to show notifications:" value="<?php echo @$onesignal_wp_settings['prompt_action_message']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Title (Desktop)</label>
                  <input type="text" name="prompt_example_notification_title_desktop" placeholder="This is an example notification" value="<?php echo @$onesignal_wp_settings['prompt_example_notification_title_desktop']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Message (Desktop)</label>
                  <input type="text" name="prompt_example_notification_message_desktop" placeholder="Notifications will appear on your desktop" value="<?php echo @$onesignal_wp_settings['prompt_example_notification_message_desktop']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Title (Mobile)</label>
                  <input type="text" name="prompt_example_notification_title_mobile" placeholder="Example notification" value="<?php echo @$onesignal_wp_settings['prompt_example_notification_title_mobile']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Message (Mobile)</label>
                  <input type="text" name="prompt_example_notification_message_mobile" placeholder="Notifications will appear on your device" value="<?php echo @$onesignal_wp_settings['prompt_example_notification_message_mobile']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Example Notification Caption</label>
                  <input type="text" name="prompt_example_notification_caption" placeholder="(you can unsubscribe anytime)" value="<?php echo @$onesignal_wp_settings['prompt_example_notification_caption']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Accept Button Text</label>
                  <input type="text" name="prompt_accept_button_text" placeholder="CONTINUE" value="<?php echo @$onesignal_wp_settings['prompt_accept_button_text']; ?>">
              </div>
              <div class="field prompt-customize-feature">
                  <label>Cancel Button Text</label>
                  <input type="text" name="prompt_cancel_button_text" placeholder="NO THANKS" value="<?php echo @$onesignal_wp_settings['prompt_cancel_button_text']; ?>">
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
            <img class="img-responsive no-center" src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/welcome-notification.jpg" ?>" width="360">
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
                <input type="text" placeholder="(defaults to your website's title if blank)" name="welcome_notification_title" value="<?php echo @$onesignal_wp_settings['welcome_notification_title']; ?>">
            </div>
            <div class="field welcome-notification-feature">
                <label>Message<i class="tiny circular help icon link" role="popup" data-title="Welcome Notification Message" data-content="The welcome notification's message. You can localize this to your own language. A message is required. If left blank, the default of 'Thanks for subscribing!' will be used." data-variation="wide"></i></label>
                <input type="text" placeholder="Thanks for subscribing!" name="welcome_notification_message" value="<?php echo @$onesignal_wp_settings['welcome_notification_message']; ?>">
            </div>
          <div class="field welcome-notification-feature">
            <label>URL<i class="tiny circular help icon link" role="popup" data-title="Welcome Notification URL" data-content="The webpage to open when clicking the notification. If left blank, your main site URL will be used as a default." data-variation="wide"></i></label>
            <input type="text" placeholder="(defaults to your website's URL if blank)" name="welcome_notification_url" value="<?php echo @$onesignal_wp_settings['welcome_notification_url']; ?>">
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
              <label>Automatically send a push notification when I create a post from the default WordPress editor<i class="tiny circular help icon link" role="popup" data-title="Automatic Push from WordPress Editor" data-content="If checked, when you create a new post, the checkbox 'Send notification on publish' will be automatically checked." data-variation="wide"></i></label>
            </div>
          </div>
        </div>
        <button class="ui large teal button" type="submit">Save</button>
        <div class="ui error message">
        </div>
      </form>
    </div>
    </div>
  </div>
</div>