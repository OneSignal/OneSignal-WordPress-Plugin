<?php
	header("Service-Worker-Allowed: /");
	header("Content-Type: application/javascript");
	if (defined('ONESIGNAL_DEBUG')) {
		echo "importScripts('https://localhost:3001/dev_sdks/OneSignalSDK.js');";
	} else {
		echo "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDK.js');";
	}
?>
