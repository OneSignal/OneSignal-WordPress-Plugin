<?php
$onesignal_wp_settings = OneSignal::get_onesignal_settings();

if (array_key_exists('app_id', $_POST)) {
  $onesignal_wp_settings = OneSignal_Admin::save_config_page($_POST);
}
?>


<div class="container" id="content-container">

  <div class="row">
    <h1>OneSignal Settings</h1>
  </div>

  <div class="row">
    <ul class="nav nav-tabs" role="tablist" id="myTab">
      <li role="presentation" class="active"><a href="#getting-started" aria-controls="getting-started" role="tab" data-toggle="tab">Getting Started</a></li>
      <li role="presentation"><a href="#account-settings" aria-controls="account-settings" role="tab" data-toggle="tab">Account Settings</a></li>
      <li role="presentation"><a href="#notification-settings" aria-controls="notification-settings" role="tab" data-toggle="tab">Notification Settings</a></li>
      <li role="presentation"><a href="#create-notification" aria-controls="create-notification" role="tab" data-toggle="tab">Create Notification</a></li>
    </ul>
  </div>

  
  <form action="#" method="POST">
  
  <div class="row">

    <div class="tab-content">

      <!-- Creating / Managing a OneSignal Account -->
      <div role="tabpanel" class="tab-pane active" id="getting-started">

        <div id="getting-started-container">

          <div class="row">
            <div class="col-md-8 col-md-offset-2" id="thanks-message">
            <p> Thanks for choosing OneSignal as your Push Notification service for your WordPress powered app! Getting started is simple and takes 10 minutes. </p>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">

              <ul>
                <li>
                  <h4 class="steps"> Step 1: Register an account and create a OneSignal App</h4>
                  <p> Go to <a href="https://www.onesignal.com"> onesignal.com </a> and create a new account. After verifying your account, sign in and create a new OneSignal app. </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/create-1.jpg" ?>" style="width: 80%">
                </li>

                <li> 
                  <h4 class="steps"> Step 2: Create a Google Project </h4>
                  <p> <strong> 2.1: Create a project at <a href="https://console.developers.google.com/project">https://console.developers.google.com/project</a> for your app.</strong> </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-1.jpg"?>" style="width: 80%">
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-1-2.jpg"?>" style="width: 80%">

                  <p> <strong>2.2: Once your project has finished creating, select your project and click on Overview. Your project number should be located on this page. </strong> </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-2.jpg"?>" style="width: 80%">

                 <div class="alert alert-warning" role="alert">
                    <p> <strong>Save the Project Number: </strong> You will need this number shortly when you configure your OneSignal app. </p>
                  </div>
                </li>

                <li>

                  <br>
                  <p> <strong>STEP 3: Turn on both "Google Cloud Messaging for Chrome" and "Google Cloud Messaging for Android" APIs</strong> </p>
                  <p> 3.1: Under APIs & auth > APIs, search for Google Cloud Messaging for Chrome. Turn it on. You will need this for desktop notifications.</p>

                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-3.jpg"?>" style="width: 80%">
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-4.jpg"?>" style="width: 80%">
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-5.jpg"?>" style="width: 80%">

                  <br>
                  <p> <strong> STEP 4: Create and save Server Key</strong> </p>
                  <p>4.1: Under APIs & auth > Credentials, click the Add credentials button, and click API key. </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-6.jpg"?>" style="width: 80%">

                  <p>4.2: Select "Server key" </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-7.jpg"?>" style="width: 80%">

                  <p>4.3: Without entering any values into the textbox, press the Create button.</p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-8.jpg"?>" style="width: 80%">

                  <p>4.4: Copy the API Key. You will need it to enter it to the Google Server API Key field in your App Settings. </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-9.jpg"?>" style="width: 80%">
                </li>


                <li>
                  <h4 class="steps"> STEP 5: Configure your Chrome Website with OneSignal  </h4>
                  <p> <strong> 5.1: Log into OneSignal. In the dashboard, select Application Settings then press the Configure button to the right of Chrome Website (GCM). </strong> </p>
                  <p> <strong> 5.2: Paste your Google API Key in here.</strong> </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-10.jpg"?>" style="width: 80%">
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-11.jpg"?>" style="width: 80%">
                  
                  <p> <strong> 5.3: Fill in the remaining HTTP fields and press Save.</strong></p>
                  <p>(Skip these fields if your site is HTTPS)</p>
                  
                </li>
                
                <li>
                  <h4 class="steps"> STEP 6: Add Subscribe Link or Widget (HTTP Only)</h4>
                  <p> <strong> 6.1:</strong> On HTTP sites you can not auto prompt for push notifications. You need to supply either a link/button or add our widget to your site. </p>
                  <p> <strong> Option 1 Widget: </strong> This provides a quick and easy way to add an opt-in option for push notifications.<br>Under Appearance > Widgets drag the OneSignal Widget from the list on the left to the Widget Area on the right. You can customize the widget title and body to your liking.</p>
                  <p> <strong> Option 2 CSS Class: </strong>Assign the class <span class="sample-code"> "OneSignal-prompt" </span> to any link or button element. <br/>Example:</p>
                  <div id="custom-link-example"><xmp class="sample-code"><a href="#" class="OneSignal-prompt"> Subscribe to Notifications </a></xmp></div>
                  <br>
                  <p><strong>6.2:</strong> Test your subcribe link by clicking on the link, button, or widget.</p>
                </li>
                
                 <li>
                  <h4 class="steps"> STEP 7: Add Safari Desktop support</h4>
                  <p> <strong> 7.1:</strong> On the OneSignal Dashboard go to "App Settings" > Safari. Fill out "Site Name" and "Site Domain". Highly recommend you upload your own icons otherwise it will default to OneSignal icons. You can skip the .p12 and password if you don't have one. </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/SafariRequiredSettings.png"?>" style="width: 80%">
                  <p> <strong> 7.2:</strong> Press save and copy the generated WebID shown. Enter this in the 'Safari WebID' field under the "Account Settings" tab in Wordpress. </p>
                  <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/SafariWebId.png"?>" style="width: 80%">
                </li>
                
                <br><br>
                <div class="alert alert-info" role="alert">
                  <h4> Subdomain </h4>
                  <p> <strong> Why do I need to choose a Subdomain Name? </strong> Chrome Push Notifications only work on HTTPS websites. Because most WordPress sites are on HTTP, we send notifications through our HTTPS site using your desired subdomain. Users will see notifications sent from: "your-subdomain.onesignal.com"</p>
                  <br/><br/>
                  <p> <strong> What if I'm already on HTTPS? </strong> If your site is already on HTTPS, you don't have to do anything! You can leave the Subdomain field blank. </p>
                  <br/><br/>
                  <p> <strong> Choosing a Subdomain: </strong> Users will see this subdomain on every notification, so keep it recognizable! We recommend using your site's name as the subdomain. For example, if your subdomain were "ilikemuskrats," we would send notifications from https://ilikemuskrats.onesignal.com.</p>
                  <br/>
                  <br/>
                  <h4> Your Site URL </h4>
                  <p> This is your WordPress site's URL.</p>
                  <p> <span style="font-style: italic;"> ex: www.myWordPressSite.com </span> </p>
                  <br/>
                  <br/>
                  <h4> Google Project Number </h4>
                  <p> This can be retrieved in Step 2.2 </p>
                </div>

                <div class="alert alert-info" role="success">
                    <p> Congratulations! The next step is to configure your OneSignal Plugin's Account Settings, which only takes a minute.</p>
                </div>

              </ul>
            </div>
          </div>
        </div>
      </div>










      <!-- Creating / Managing a OneSignal Account -->
      <div role="tabpanel" class="tab-pane" id="account-settings">

        <div class="alert alert-warning" role="alert">
          <p> If you haven't created a OneSignal Account, follow the steps in the "Getting Started" tab. </p>
        </div>

          <!-- Google Project Number -->
          <div class="row topic">
            <div class="col-md-4">
              <label>Google Project Number</label>
              <a onclick="showProjectNumberHelper()" class="help"> What's my Google Project Number? </a>
            </div>
            <div class="col-md-8">
              <input name="gcm_sender_id" type="text" value="<?php echo $onesignal_wp_settings['gcm_sender_id'] ?>"></input>
            </div>
          </div>

          <div class="row" id="project-number-helper" style="display:none;">
            <div class="col-md-12">
              <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-10.jpg"?>">
              <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/gcm-12.jpg"?>">
            </div>
          </div>


          <!-- App ID -->
          <div class="row topic">
            <div class="col-md-4">
              <label>OneSignal App ID</label>
              <a onclick="showAppIDHelper()" class="help"> What's my OneSignal App ID? </a>
            </div>
            <div class="col-md-8">
              <input name="app_id" type="text" value="<?php echo $onesignal_wp_settings['app_id'] ?>"></input>
            </div>
          </div>
          
          <div class="row" id="appID-helper" style="display:none;">
            <div class="col-md-12">
              <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/app_id-1.jpg" ?>" style="width: 80%">
            </div>
          </div>
        


          <!--REST API Key -->
          <div class="row topic">
            <div class="col-md-4">
              <label>REST API Key</label>
              <a onclick="showRESTAPIHelper()" class="help"> What's my OneSignal REST API Key? </a>
            </div>
            <div class="col-md-8">
              <input name="app_rest_api_key" type="text" value="<?php echo $onesignal_wp_settings['app_rest_api_key'] ?>"></input>
            </div>
          </div>

          <div class="row" id="rest-API-helper" style="display:none;">
            <div class="col-md-12">
              <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/settings/app_id-1.jpg"?>" style="width: 80%">
            </div>
          </div>



          <!-- Subdomain Name -->
          <div class="row topic">
            <div class="col-md-4">
              <label>Subdomain Name</label>
              <p> <span style="font-style: italic"> (the same name you chose for your OneSignal app) </span></p>
            </div>
            <div class="col-md-8">
              <input name="subdomain" type="text" value="<?php echo $onesignal_wp_settings['subdomain'] ?>"></input>
            </div>
          </div>
          
          <!-- Safari Web ID -->
          <div class="row topic">
            <div class="col-md-4">
              <label>Safari WebID</label>
              <p> <span style="font-style: italic"> (OneSignal dashboard - "App Settings" > Safari) </span></p>
            </div>
            <div class="col-md-8">
              <input name="safari_web_id" type="text" value="<?php echo @$onesignal_wp_settings['safari_web_id']; ?>"></input>
            </div>
          </div>


          <div class="row">
            <div class="col-md-12">

              <div class="alert alert-info" role="alert">
                <p> <strong> Why do I need to choose a Subdomain Name? </strong> Chrome Push Notifications only work on HTTPS websites. Because most WordPress sites are on HTTP, we send notifications through our HTTPS site using your desired subdomain. Users will see notifications sent from: "your-subdomain.onesignal.com"</p>
                <br/>
                <p> <strong> What if I'm already on HTTPS? </strong> If your site is already on HTTPS, you don't have to do anything! You can leave the Subdomain Name field blank. </p>
                <br/>
                <p> <strong> Choosing a Subdomain: </strong> Users will see this subdomain on every notification, so keep it recognizable! We recommend using your site's name as the subdomain. For example, if your subdomain were "lolchinchillas," we would send notifications from https://lolchinchillas.onesignal.com.</p>
                <br/>
              </div>


              <div class="alert alert-warning" role="alert">
                <p> <strong>Updating Subdomain Names: </strong> If you choose a different subdomain, you will have to update it both here and on the OneSignal website for notifications to work. Visitors who accepted notifications from your old subdomain will need to re-accept them for your new one. </p>
              </div>
            </div>
          </div>

          <input type="submit"></input>
      </div>






      <!-- Setting up auto notifs, prompts, + design of notifs -->
      <div role="tabpanel" class="tab-pane" id="notification-settings">

          <!-- Auto Push -->
          <div class="row topic">
            <div class="col-md-3">
              <label> Automatic Push Notifications </label>
            </div>

            <div class="col-md-9">
              <input type="checkbox" name="notification_on_post" value="true" <?php if ($onesignal_wp_settings['notification_on_post']) { echo "checked"; } ?>></input>
              <p> Post from default Wordpress creator.</p><br/>
              <p style="font-style:italic; font-size:10pt">(You can change this setting per post before you publish.)</p>
            </div>
            
            <div class="col-md-3">
            </div>
            <div class="col-md-9">
              <input type="checkbox" name="notification_on_post_from_plugin" value="true" <?php if (@$onesignal_wp_settings['notification_on_post_from_plugin']) { echo "checked"; } ?>></input>
              <p> All Posts created from other plugins. </p>
            </div>
          </div>


          <!-- Notification Content -->
          <div class="row topic">
            <div class="col-md-3">
              <label> Notification Content </label>
            </div>

            <div class="col-md-9">

              <p> Your notifications will have the following layout: </p>
              <br/>
              <img src="<?php echo ONESIGNAL_PLUGIN_URL."views/images/SampleNotification.png"?>">
              <br/>
              <p> You can send a notification with custom content via <a href="https://www.onesignal.com"> the OneSignal website </a></p>
              
            </div>
          </div>


          <!-- Notification Prompt -->
          <div class="row topic">
            <div class="col-md-3">
              <label> Notification Permission Prompt Options </label>
            </div>

            <div class="col-md-9">

              <!--<input type="checkbox" name="add-widget" value="true" <?php if ($onesignal_wp_settings['auto_register']) { echo "checked"; } ?>></input>-->
              <p> <strong> Option 1: </strong> Search for and install the OneSignal widget, allowing users to receive notifications from your site. </p>

              <br/></br/>
              
              
              <p> <strong> Option 2 (for advanced users): </strong> Embed a custom link or button on your site that, once clicked, prompts users to accept notifications.</p>

              <div id="custom-link-example">
                <p> <strong> Directions: </strong> </p>
                <br/>
                <p> Assign the class <span class="sample-code"> "OneSignal-prompt" </span> to any element. Example:</p>
                <p> <xmp class="sample-code"><a href="#" class="OneSignal-prompt"> Subscribe to Notifications </a></xmp></p>
              </div>
            </div>
          </div>

          
            <input type="submit"></input>

      </div>








      <!-- creating a one-off notification -->
      <div role="tabpanel" class="tab-pane" id="create-notification">
        <p> The ability to create custom notifications from this page is coming soon. </p>
        <p> In the mean time, log in to your <a href="https://www.onesignal.com">OneSignal account</a> and create a custom notification from there. </p>
      </div>


    </div>
  </div>
  
  </form>

</div>