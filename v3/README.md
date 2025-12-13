OneSignal WordPress Plugin - v3.0.0
====================================
[OneSignal WordPress Plugin 3.0 – Documentation](https://documentation.onesignal.com/docs/wordpress)

## Overview

- 🚀 Initialises the latest OneSignal Web SDK (v16).
- ⏩ Automatically sends Push Notifications when a WordPress post is published.
- 💬 Setup [prompts](https://documentation.onesignal.com/docs/permission-requests) within the OneSignal dashboard. No custom code required.
- 🧑‍🤝‍🧑 Choose which [Segment](https://documentation.onesignal.com/docs/segmentation) should recieve notifications for each post.
- 📑 [Web Topics](https://documentation.onesignal.com/docs/web-push-topic-collapsing) included by default.
- 📲 Send to mobile app subscribers, with an option to direct them to a different URL ([Deep Link](https://documentation.onesignal.com/docs/links#deep-linking)).

## Running Tests

- Install dependencies: `composer install`
- Run all tests: `./vendor/bin/phpunit  --testdox`
    - Unit tests: `./vendor/bin/phpunit --testsuite unit --testdox`
    - Integration tests: `./vendor/bin/phpunit --testsuite integration --testdox`
