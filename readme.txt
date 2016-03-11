=== OneSignal - Free Web Push Notifications ===
Contributors: OneSignal
Donate link: https://onesignal.com
Tags: chrome, firefox, safari, push, push notifications, push notification, chrome push, safari push, firefox push, notification, notifications, web push, notify, mavericks, android, android push, android notifications, android notification, mobile notification, mobile notifications, mobile, desktop notification, roost, goroost, desktop notifications, gcm, push messages, onesignal
Requires at least: 3.8
Tested up to: 4.4.2
Stable tag: 1.10.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Increase engagement and drive more repeat traffic to your WordPress site with desktop push notifications. Now supporting Chrome, Firefox, and Safari.

== Description ==

[OneSignal](https://onesignal.com) is a complete push notification solution for WordPress blogs and websites, trusted by over 20,500 developers and marketers including some of the largest brands and websites in the world.

After setup, your visitors can opt-in to receive desktop push notifications when you publish a new post, and visitors receive these notifications even after they’ve left your website.

We make it easy to configure delivering notifications at preset intervals, targeting notifications to specific users, and customizing the opt-in process for your visitors.

Features:

* **Supports Chrome** (Desktop & Android), **Safari** (Mac OS X), and **Firefox** (Desktop) on both HTTP and HTTPS sites.

* **Automatic Notifications** - Send notifications to followers every time you publish a new post. Or set up a reminder that gets automatically sent to them if they haven’t visited for a few days.

* **Targeting Segments** - Send notifications to specific visitors based on language, number of times they’ve visited your blog, or even set up your own user attributes that you can target.

* **Opt-In Customization** - Choose when and how to ask your visitors to opt-in to browser notifications. Customize the prompt they first see.

* **Real Time Analytics** - See your notifications being delivered in real time, and watch them as they convert into visitors.

* **A/B Testing** - Try out different messages to a smaller set of your visitors to figure out which messages are more effective and then send the more effective message to the rest of your visitors!

* **Scheduled Notifications** - Schedule notifications to be delivered in the future, based on a user’s time zone, or even based on the same time of day they last visited your website.

**All completely free. No fees or limitations.**

== Installation ==

1. Install OneSignal from the WordPress.org plugin directory or by uploading the OneSignal plugin folder to your wp-content/plugins directory.
2. Active the OneSignal plugin from your WordPress settings dashboard.
3. Follow the instructions on the Setup page.

== Screenshots ==

1. Notifications on Chrome, Safari, and Firefox.
2. Our detailed setup instructions to get you started.
3. Another shot of our detailed setup instructions with images.
4. Our configuration settings allowing you to customize the way users are prompted to subscribe and the notifications they receive.

== Changelog ==
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