=== OneSignal - Web Push Notifications ===
Contributors: OneSignal
Donate link: https://onesignal.com
Tags: chrome, firefox, safari, push, push notifications, push notification, chrome push, safari push, firefox push, notification, notifications, web push, notify, mavericks, android, android push, android notifications, android notification, mobile notification, mobile notifications, mobile, desktop notification, roost, goroost, desktop notifications, gcm, push messages, onesignal
Requires at least: 3.8
Tested up to: 5.3.2
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Increase engagement and drive more repeat traffic to your WordPress site with desktop push notifications. Now supporting Chrome, Firefox, and Safari.

== Description ==

[OneSignal](https://onesignal.com) is an easy way to increase user engagement. Use OneSignal to send visitors targeted push notifications so they keep coming back. It takes just a few minutes to install.

After setup, your visitors opt-in to receive push notifications when you publish a new post. Visitors receive these notifications even after they’ve left your website, thus driving re-engagement. 

You can configure notification delivery at preset intervals, create user segments, and customize the opt-in process for visitors.

OneSignal’s free plan allows targeting up to 30,000 subscribers with push notifications. Contact support@onesignal.com if you have any questions. We’d love to hear from you!

= Company =
OneSignal is trusted by over 860,000 developers and marketing strategists. We power push notifications for everyone from early stage startups to Fortune 500 Companies, sending 4 billion notifications per day. It is the most popular push notification plugin on Wordpress with 90,000+ installations.

= Features =
* **Supports Chrome** (Desktop & Android), **Safari** (Mac OS X), **Microsoft Edge** (Desktop & Android), **Opera** (Desktop & Android) and **Firefox** (Desktop & Android) on both HTTP and HTTPS sites.

* **Automatic Notifications** - Send notifications to followers every time you publish a new post. Or set up a reminder that gets automatically sent to them if they haven’t visited for a few days.

* **Targeting Segments** - Send notifications to specific visitors based on language, number of times they’ve visited your blog, or even set up your own user attributes that you can target.

* **Opt-In Customization** - Choose when and how to ask your visitors to opt-in to browser notifications. Customize the prompt they first see.

* **Real Time Analytics** - See your notifications being delivered in real time, and watch them as they convert into visitors.

* **A/B Testing** - Try out different messages to a smaller set of your visitors to figure out which messages are more effective and then send the more effective message to the rest of your visitors!

* **Scheduled Notifications** - Schedule notifications to be delivered in the future, based on a user’s time zone, or even based on the same time of day they last visited your website.

== Installation ==

1. Install OneSignal from the WordPress.org plugin directory or by uploading the OneSignal plugin folder to your wp-content/plugins directory.
2. Active the OneSignal plugin from your WordPress settings dashboard.
3. Follow the instructions on the Setup page.

== Screenshots ==

1. Notifications on Chrome, Safari, and Firefox.
2. Our detailed setup instructions to get you started.
3. Our configuration main configuration setup page.
4. Our OneSignal dashboard users page, where you can see your subscribed users.
5. Our OneSignal dashboard sent notifications page, where you can see the status of your sent notifications.
6. Our OneSignal dashboard notification creation page, with show some of the browser settings available.
7. Our OneSignal Wordpress welcome notification options.
8. Our OneSignal Wordpress setting for prompting options.

== Frequently Asked Questions ==

= HTTP Site Setup Video =
[youtube https://www.youtube.com/watch?v=j8qO9gDr9Wg]

= HTTPS Site Setup Video =
HTTPS Setup Video: [youtube https://www.youtube.com/watch?v=BeTZ2KgytC0]

== Changelog ==

= 2.1.1 =

- Minor bug fixes: check native_prompt setting key exists, reworked checkbox logic to support custom post statuses

= 2.1.0 = 

- OneSignal config page interface changes to prompting options to discourage native prompt use, bug fixes

= 2.0.1 =

- Bug fix: link expired issue due to failing nonce check when creating posts from other WordPress plugins

= 2.0.0 =

- Wordpress VIP support, general refactoring, bug fixes

= 1.17.9 =

- Rolled back sending notifications on scheduled posts to be when its status changes to published

= 1.17.8 =

- Added escaping to fields in OneSignal config to remove invalid characters, bug fixes

= 1.17.7 =

- Fixed bug from 1.17.6 where updating old posts would result in 400 level errors

= 1.17.6 =

- Fixed bug where scheduled posts would send notifications immediately, added Gutenberg support for scheduled notifications

= 1.17.5 =

- Updated notice message to reflect changes to time limiter, removed extra newline from description

= 1.17.4 = 

- Changed time limiter to 2 minutes from 1 hour to ease restrictions on sending update notifications

= 1.17.3 =

- Added debug to logging to responses with non 200-level status codes
- Made notices unique
- Bug fixes

= 1.17.2 =

- Lengthened timeout, debugging tool, status-code bug fixes

= 1.17.1 =

- Support for more detailed error messages

= 1.17.0 =

- Bug fixes, edge-case handling, refactoring

= 1.16.16 = 

- Code to catch error where core/editor is not defined for old versions of the editor

= 1.16.15 =

- WP5 notice support and error handling for errors arising from v 1.16.14

= 1.16.14 =

- Replaced cURL calls with HTTP API

= 1.16.13 =

- Added timestamp to allow re-pushing notifications upon editing an existing post after 1 hr

= 1.16.12 =

- Reverted unchecking send notifcation on post publish

= 1.16.11 =

- On Wordpress 5.0 "Send notification on post publish" now unchecks after posting.
- Added extra checks to ensure double notifications are not sent for the same post.

= 1.16.10 =

Updated plugin description and added FAQ section.

= 1.16.9 =

Reverting the UI changes for HTTP switch.

= 1.16.8 =

This release makes HTTP switch match the dashboard (renamed to "My site is not fully HTTPS") and removes deprecation warnings for php 7.2.

= 1.16.7 =

This release updates the service worker to use a 50% smaller service worker-only file.

= 1.16.6 =

This release the issue where even after saving the form, the error about not completing the required fields would appear because the settings for the new page view were loaded before the previous page's settings were saved. Also a broken doc link was fixed.

= 1.16.5 =

This release changes the notification send rate limit from at most one every 10 seconds to at most one every 1 second.

= 1.16.4 =

This release removes begining and ending whitespaces from textboxes when saving. This can fix common errors like pasting the App ID, REST API Key, or subdomain (also called label) with an ending whitespace which normally causes errors.

= 1.16.3 =

This release greatly simplifies the setup guide to follow our documentation instead of an inline guide.

- Update AMP helper files to be centralized instead of hardcoded inlined JavaScript

= 1.16.2 =

This release updates the amp-helper-frame.html and amp-permission-dialog.html files used for amp-web-push due to permission changes in Chrome 62.

- Update readme to show tested up to 4.9 Release Candidate 2

= 1.16.1 =

This release adds an option to disable the "Successfully sent a notification to X recipients" message, and also includes files for AMP web push (to be used with another AMP plugin) in case you decide to add AMP web push for your site.

- Add option "Show status message after sending notifications"
- Include amp-helper-frame.html and amp-permission-dialog.html for use with AMP web push

= 1.16.0 =

This release adds a helpful message describing the notification's recipient count after sending a notification. After publishing a post, for example, the info box may say "Successfully sent a notification to 100 recipients.".

- Lowered priority of wp_head hook from 5 to 10.

= 1.15.1 =

This release fixes a bug with the 1.15.0 release that checks the "Dismiss notifications automatically after ~20 seconds" field. The wrong field was being checked and caused a warning to appear.

= 1.15.0 =

This release adds a setting for hiding notifications on Mac OS X platforms.

- "Dismiss notifications automatically after ~20 seconds" has been replaced with "Hide notifications after a few seconds" with a couple of
choices "Yes", "No", "Yes on Mac OS X. No on other platforms". Previously, notifications would be persisted on all platforms except Mac OS X.

= 1.14.4 =

This release restores 4 missing image files in the Setup guide included in our WordPress plugin (Chrome & Firefox Push step 7, OneSignal Keys step 2).

No code changes were made in this patch.

= 1.14.3 =
- Use larger sized icons for the featured image

  Notification small icons and large images previously used the uploaded image closest to size 80x80. For a large uploaded image with no
  resized variants, there would be no issues. But for uploaded images resized to different sizes by WordPress, this caused the smallest image
  size to be selected and look blurry. Notification small icons now use the closest available image to 192x192 for a sharper image, whereas
  large images use the closest available image to 640x480.

= 1.14.2 =
- Update Setup tab's images and text

  Clarify some steps that are typically confusing to new users setting up the plugin, like how to get the value in the Subdomain textbox.

= 1.14.1 =
- Remove .htaccess file

  Apache httpd.conf configurations that don't allow custom .htaccess options will error out with a 500 if we place this file there.

= 1.14.0 =
- Update semantic versioning; update minor version for new backwards-compatible functionality
- Hide Google Project Number from configuration (using one is unnecessary since we provide a default Project Number)
- Add option "Use the post's featured image for Chrome's large notification image" (see: https://goo.gl/uSDr5p)
- Lower notification rate limit from 55 seconds to 10 seconds. Countdown shows time remaining (e.g. "Please try again in 6 seconds")

= 1.13.9 =
- Check in missing image to SVN: admin Configuration page HTTP Permission Request modal
- Remove obsolete admin option "Show the OneSignal logo on the prompt"
- Remove unused Bootstrap CSS/JS assets
- Fix Prompt options custom language text not outputted for HTTPS sites (https://goo.gl/5Hi4HA)

= 1.13.8 =
- Config page changes

= 1.13.7 =
- Add rate limiting to prevent notifications from being sent too quickly; one notification can be sent every 55 seconds
- Remove Preview Popup button; users can still follow the screenshot in the section header to match their customized
  values with the window their users will see
- Add Configuration page UI option to show the slidedown permission message on HTTPS sites before the browser's native
  permission request
- Implement the HTTP permission request as the default for new sites (only for those who turn on Automatic prompting)
- Clarify "Use my own SDK initialization script" --> renamed as "Disable OneSignal initialization"
- Add a hidden page comment if users disable OneSignal initialization for easier debugging
- Our plugin is in https://wordpress.org/plugins-wp but not https://wordpress.org/plugins. Hopefully resubmitting the
  plugin fixes it

= 1.13.6 =
- Update style that was being overridden on some sites

= 1.13.5 =
- Fix undefined index gcm_sender_id error
- Do not resend notifications for posts restored from trash

= 1.13.4 =
- Assign the script initialization variable OneSignal globally so initialization still works if plugins modify our
  inline script to be run from an external script file

= 1.13.3 =
- A user reported the 'prompt_auto_accept_title' variable being undefined and causing issues with her site. This issue
  is now fixed.

= 1.13.2 =
- Add proper WordPress action/filter hook for OneSignal init

= 1.13.1 =
- The web SDK initialization of our plugin can now be fully customized
- Removed the Intercom live chat support plugin from our plugin. Users can still email support+wp@onesignal.com.
- Click Allow, Site Title, and the auto accept HTTP prompt title can now be customized
- Spaces are removed when users save their Subdomain textbox value
- The meta box checkbox "Send post on notification publish" now correctly *does not* send a notification if unchecked.
  Previously, there was a logic bug where users could check the box, initially save the post without publishing, and
  have a notification sent out when later publishing.
- The default plugin tab is now "Setup" if the user is setting up for the first time, and if their App ID or REST API
  Key is blank (both values are required)
- Correctly call has_post_thumbnail for WordPress versions below 4.4
- Check for admin capabilities is done correctly so as to be compatible for users in stateless mode (DISALLOW_FILE_MODS)
- Apostrophes and other HTML encoded entities are correctly decoded when using the HTTP prompt
- Minor: Remove phantom tooltip linking to GCM page
- Minor: site.css now has a source map

= 1.12.5 =
- Fix broken documentation link

= 1.12.4 =
- Add option to show GCM Project Number field

= 1.12.3 =
- Bug fix for manifest.json GCM Sender ID

= 1.12.2 =
- Remove Google project from the setup flow

= 1.12.1 =
- Allow HTTP users to select "Automatically prompt..." to use the HTTP prompt
- Improve setup documentation screens, add extra troubleshooting notices

= 1.12.0 =
- Add admin UI to change notification title
- Add admin UI to send to Android and iOS platforms (if available)

= 1.11.0 =
- Add admin UI and filter for custom post types
- Add filters for overriding post processing behavior
- Add filter for overriding meta box send notification checkbox behavior
- Add admin UI for adding UTM tracking code parameters (notification URL parameters)
- Add admin UI for hiding notify button after subscription
- Fix Preview Popup not displaying correctly if an 'https://subdomain.onesignal.com' Subdomain textbox value is used
- Display visible error message if notification fails to send
- Updated Google Project Setup guide
- Fix poorly named global function that is conflicting with another template's global function

= 1.10.6 =
- Push notifications should now be sent out for posts created in the default WordPress editor if scheduled, being edited, or awaiting publication

= 1.10.5 =
- Change console.developers.google.com setup URL --> console.cloud.google.com
- Modify onesignal_send_notification filter hook to also allow notifications to not be sent

= 1.10.4 =
- Enable PHP error logging by file

= 1.10.3 =
- Fix on_save_post function not being declared statically
- Fix other PHP warning about property not existing

= 1.10.2 =
- Forgot to add onesignal-utils.php

= 1.10.1 =
- Automatic sending functionality has been rewritten

= 1.10.0 =
- Fix scheduled notifications to be more reliable by associating data with the post's metadata and rewriting the send notification logic
- Modified the WDS Log plugin to log OneSignal-related things; WDS Log plugin must be installed to view
- Add a filter hook for to modify the data we post to create notifications API to allow customizing of notifications
- Fixed Configuration page saving so that a user can choose to only use the Safari platform and skip the Chrome subdomain

= 1.9.2 =
- Make WordPress plugin compatible with PHP v5.2.4
    - Using workaround for constant ENT_HTML401 not defined in < PHP 5.4 used in decode_html_entity

= 1.9.1 =
- Relax subdomain validation now that the web SDK auto-corrects almost-valid values

= 1.9.0 =
- Add Henkler's contributions to WordPress plugin:
    - Allow notification dismissal by Chrome's persistNotification flag
    - Allow featured image to be used as notification icon

= 1.8.2 =
- Restore 'Automatically send notifications using 3rd party post editors'

= 1.8.1 =
- Clarified subdomain instructions to not include ".onesignal.com"
- Improved support for HTML encoded entities

= 1.8.0 =
- Add bell color customization
- Add bell offset position customization
- Add initial support for custom post types

= 1.7.3 =
- Including missing CSS file

= 1.7.2 =
- Fix settings for initial user showing an error for WordPress function get_option()

= 1.7.1 =
- Organized and clarified plugin settings
- Add screenshots to plugin description

= 1.7.0 =
- Fixed error reporting being enabled in version 1.6.0
- Rebranded bell widget to notify button
- Minor fixes to functions that would error but are silent because error reporting is usually disabled
- Update default settings

= 1.6.0 =
- Added interactive bell widget for site visitors to manage push notification subscription
- Improved toggle button text readability

= 1.5.0 =
- Added option to send a welcome notification to new site visitors
- Removed {modalPrompt: true} as the default prompt method for HTTPS sites; the native browser prompt is once again the default
- Added option to use the modal prompt instead of the native prompt method
- Popup settings now display for both HTTPS modal users and HTTP prompt users

= 1.4.0 =
- Added option to disable automatically prompting new visitors to register for push notifications

= 1.3.2 =
- Fixed settings save when subdomain goes from set to empty. Admin JS now uses jQuery instead of $.

= 1.3.1 =
- Fixed HTTP popup prompt dialog to not display empty values if configuration options are unset

= 1.3.0 =
- Added popup settings to localize prompt text. Updated fonts to render better on Firefox and Safari.

= 1.2.0 =
- Graphical redesign of the plugin. Much better instructions.

= 1.1.1 =
- OneSignal library initialization now occurs regardless of whether the window.onload event has yet to be fired or has already fired.

= 1.1.0 =
- Added Safari Mac OSX support.

= 1.0.8 =
- UTF-8 characters in post's titles now display correctly in notifications.
- Fixed bug where manifest.json was not being created for HTTPS sites due to permissions.
- Now adapts to use HTTPS for service worker files if the WordPress settings are not correct.

= 1.0.7 =
- Fixed bug where some plugins that create posts were not sending out a OneSignal notifications automatically when 'All Posts created from other plugins' was enabled.
- Fixed errors that display when 'WP_DEBUG' is set to true

= 1.0.6 =
- Added Automatic Push Notifications option for 'All Posts created from other plugins' on the "Notification Settings" tab.
   - Note, this is on by default for new installs but off of existing ones so behavior does not automatically change when you update.
- Fixed errors with missing images.

= 1.0.5 =
- Send notification on post is now available to any Wordpress user with permissions to create or edit posts.

= 1.0.4 =
- Notifications sent with the Automatic Push Notifications on Post feature directly link to the post instead of the homepage when opening the notification.
- Updated GCM instructions and added HTTP subscribe link/widget instructions on the Getting Started tab.

= 1.0.3 =
- Fixed compatibility issue with PHP versions older than 5.3.0
- For HTTPS sites a modal dialog is shown before the native Chrome Notification permission prompt.

= 1.0.2 =
- Fixed bug with OneSignal not getting initialized in some cases.
- Now omits extra unneeded manifest link from the head tag when using HTTP.
- Clicks handler added to elements with the class OneSignal-prompt are now setup in a more compatible way.

= 1.0.1 =
- Modified description

= 1.0.0 =
- Initial release of the plugin
