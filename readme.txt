=== OneSignal - Free Web Push Notifications ===
Contributors: OneSignal
Donate link: https://onesignal.com
Tags: chrome, safari, push, push notifications, safari, chrome push, safari push, notifications, web push, notification, notify, mavericks, firefox push, android, android push, android notifications, mobile notifications, mobile, desktop notifications, gcm, push messages, onesignal
Requires at least: 3.8
Tested up to: 4.3.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Increase engagement and drive more repeat traffic to your WordPress site with desktop push notifications. Now supporting the Google Chrome Safari browsers.

== Description ==

[OneSignal](https://onesignal.com) is a complete push notification solution for WordPress blogs and sites, trusted by over 6000 developers and marketers including some of the largest brands and websites in the world.

After just a few seconds of set-up, your visitors will be able to opt-in to receive desktop push notifications when you publish a new post. OneSignal makes use of a brand new Google Chrome feature to send desktop notifications to your visitors even after they’ve left your website.

OneSignal makes it easy to configure when to send notifications, target notifications to specific users, and to customize the Opt-In process for your visitors.

Best of all, WordPress users that use OneSignal will get a FREE lifetime account.

Features:

* Supports Chrome(Desktop & Android) and Safari(Mac OSX) Push on both HTTP and HTTPS sites.

* **Automatic Notifications** - Send notifications to followers every time you publish a new post. Or set up a reminder that gets automatically sent to them if they haven’t visited for a few days.

* **Target Segments** - Send notifications to specific visitors based on language, number of times they’ve visited your blog, or even set up your own user attributes that you can target.

* **Easy Opt-In Configuration** - Choose when and how to ask your visitors to opt-in to browser notifications.

* **Real Time Analytics** - See your notifications being delivered in real time, and watch them as they convert into visitors.

* **A/B Testing** - Try out different messages to a smaller set of your visitors to figure out which messages are more effective and then send the more effective message to the rest of your visitors!

* **Scheduled Notifications** - Schedule notifications to be delivered in the future, based on a user’s time zone, or even based on the same time of day they last visited your website.

**All completely free. No fees or limitations.**

== Installation ==

1. Install OneSignal from the WordPress.org plugin directory or by uploading the OneSignal plugin folder to your wp-content/plugins directory.
2. Active the OneSignal plugin from your WordPress settings dashboard.
3. Follow the instructions on the new OneSignal Wordpress menu option to get started.

== Changelog ==
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