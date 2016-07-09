<?php
	header("Service-Worker-Allowed: /");
	header("Content-Type: application/javascript");
	header("X-Robots-Tag: none");
	if (defined('ONESIGNAL_DEBUG') && defined('ONESIGNAL_LOCAL')) {
		echo "importScripts('https://localhost:3001/dev_sdks/OneSignalSDK.js');";
	} else {
		echo "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDK.js');";
	}
?>
