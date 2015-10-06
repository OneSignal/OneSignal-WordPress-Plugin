<?php
	header("Content-Type: application/json");
  require '../../../../wp-load.php';
  
  $onesignal_wp_options = get_option("OneSignalWPSetting");
?>
{
  "start_url": "/",
  "gcm_sender_id": "<?php echo $onesignal_wp_options['gcm_sender_id']; ?>",
  "gcm_user_visible_only": true
}