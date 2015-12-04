<?php

class OneSignal_Admin {
  public function __construct() {
  }

  public static function init() {
    $onesignal = new self();
    if (current_user_can('update_plugins')) {
      add_action( 'admin_menu', array(__CLASS__, 'add_admin_page') );
    }
    if (current_user_can('publish_posts') || current_user_can('edit_published_posts')) {
      add_action( 'add_meta_boxes_post', array( __CLASS__, 'add_onesignal_post_options' ) );
    }
    
    add_action( 'transition_post_status', array( __CLASS__, 'on_transition_post_status' ), 10, 3 );
    
    return $onesignal;
  }
  
  public static function add_onesignal_post_options() {
      add_meta_box('onesignal_notif_on_post',
                   'OneSignal',
                   array( __CLASS__, 'onesignal_notif_on_post_html_view' ),
                   'post',
                   'side',
                   'high');
  }
  
  public static function onesignal_notif_on_post_html_view($post) {
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    ?>
      <input type="checkbox" name="send_onesignal_notification" value="true" <?php if ($onesignal_wp_settings['notification_on_post'] && $post->post_status != "publish") { echo "checked"; } ?>></input>
      <input type="hidden" name="has_onesignal_setting" value="true"></input>
      <label> <?php if ($post->post_status == "publish") { echo "Send notification on update"; } else { echo "Send notification on publish"; } ?></label>
    <?php
  }
  
  public static function save_config_page($config) {
    if (!current_user_can('update_plugins'))
      return;
    
    $sdk_dir = plugin_dir_path( __FILE__ ) . 'sdk_files/';
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    $new_app_id = $config['app_id'];
    
    // Validate the UUID
    if( preg_match('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $new_app_id, $m))
      $onesignal_wp_settings['app_id'] = $new_app_id;
    
    if (is_numeric($config['gcm_sender_id'])) {
      $onesignal_wp_settings['gcm_sender_id'] = $config['gcm_sender_id'];
    }
    
    $onesignal_wp_settings['subdomain'] = $config['subdomain'];

    if (@$config['no_auto_register'] == "true") {
      $onesignal_wp_settings['no_auto_register'] = true;
    }
    else {
      $onesignal_wp_settings['no_auto_register'] = false;
    }

    if (@$config['use_modal_prompt'] == "true") {
      $onesignal_wp_settings['use_modal_prompt'] = true;
    }
    else {
      $onesignal_wp_settings['use_modal_prompt'] = false;
    }
    
    if (@$config['notification_on_post'] == "true") {
      $onesignal_wp_settings['notification_on_post'] = true;
    }
    else {
      $onesignal_wp_settings['notification_on_post'] = false;
    }
    
    if (@$config['notification_on_post_from_plugin'] == "true") {
      $onesignal_wp_settings['notification_on_post_from_plugin'] = true;
    }
    else {
      $onesignal_wp_settings['notification_on_post_from_plugin'] = false;
    }
    
    $onesignal_wp_settings['app_rest_api_key'] = $config['app_rest_api_key'];
    
    $onesignal_wp_settings['safari_web_id'] = $config['safari_web_id'];

    if (array_key_exists('prompt_action_message', $config)) {
      $onesignal_wp_settings['prompt_action_message'] = $config['prompt_action_message'];
    }
    if (array_key_exists('prompt_example_notification_title_desktop', $config)) {
      $onesignal_wp_settings['prompt_example_notification_title_desktop'] = $config['prompt_example_notification_title_desktop'];
    }
    if (array_key_exists('prompt_example_notification_message_desktop', $config)) {
      $onesignal_wp_settings['prompt_example_notification_message_desktop'] = $config['prompt_example_notification_message_desktop'];
    }
    if (array_key_exists('prompt_example_notification_title_mobile', $config)) {
      $onesignal_wp_settings['prompt_example_notification_title_mobile'] = $config['prompt_example_notification_title_mobile'];
    }
    if (array_key_exists('prompt_example_notification_message_mobile', $config)) {
      $onesignal_wp_settings['prompt_example_notification_message_mobile'] = $config['prompt_example_notification_message_mobile'];
    }
    if (array_key_exists('prompt_example_notification_caption', $config)) {
      $onesignal_wp_settings['prompt_example_notification_caption'] = $config['prompt_example_notification_caption'];
    }
    if (array_key_exists('prompt_cancel_button_text', $config)) {
      $onesignal_wp_settings['prompt_cancel_button_text'] = $config['prompt_cancel_button_text'];
    }
    if (array_key_exists('prompt_accept_button_text', $config)) {
      $onesignal_wp_settings['prompt_accept_button_text'] = $config['prompt_accept_button_text'];
    }
    
    OneSignal::save_onesignal_settings($onesignal_wp_settings);
    
    return $onesignal_wp_settings;
  }

	public static function add_admin_page() {
		$OneSignal_menu = add_menu_page('OneSignal Push',
                                    'OneSignal Push',
                                    'manage_options',
                                    'onesignal-push',
                                    array(__CLASS__, 'admin_menu'),
                                    plugin_dir_url( __FILE__ ) .'views/images/menu_icon.png');
                       
    add_action( 'load-' . $OneSignal_menu, array(__CLASS__, 'admin_custom_load') );                       
	}

	public static function admin_menu() {
    require_once( plugin_dir_path( __FILE__ ) . '/views/config.php' );
  }

  public static function admin_custom_load() {
    add_action( 'admin_enqueue_scripts', array(__CLASS__, 'admin_custom_scripts') );
  }

function change_footer_admin() {
  echo '';
}

  
  public static function admin_custom_scripts() {
    add_filter('admin_footer_text', 'change_footer_admin ');

    wp_enqueue_style( 'icons', plugin_dir_url( __FILE__ ) . 'views/css/icons.css');
    wp_enqueue_style( 'semantic-ui', plugin_dir_url( __FILE__ ) . 'views/css/semantic-ui.css');
    wp_enqueue_style( 'site', plugin_dir_url( __FILE__ ) . 'views/css/site.css');

    wp_enqueue_script( 'jquery.min', plugin_dir_url( __FILE__ ) . 'views/javascript/jquery.min.js');
    wp_enqueue_script( 'semantic-ui', plugin_dir_url( __FILE__ ) . 'views/javascript/semantic-ui.js');
    wp_enqueue_script( 'intercom', plugin_dir_url( __FILE__ ) . 'views/javascript/intercom.js');
    wp_enqueue_script( 'site', plugin_dir_url( __FILE__ ) . 'views/javascript/site-admin.js');

  }
  
  public static function send_notification_on_wp_post($new_status, $old_status, $post) {
    if (empty( $post ) || get_post_type( $post ) !== 'post' || $new_status !== "publish") {
        return;
    }
    
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    
    if (isset($_POST['has_onesignal_setting'])) {
      $send_onesignal_notification = $_POST['send_onesignal_notification'];
    }
    elseif ($old_status !== "publish") {
      $send_onesignal_notification = $onesignal_wp_settings['notification_on_post_from_plugin'];
    }
    
    if ($send_onesignal_notification === true || $send_onesignal_notification === "true") {  
      $notif_content = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8');
      
      $fields = array(
        'app_id' => $onesignal_wp_settings['app_id'],
        'included_segments' => array('All'),
        'isAnyWeb' => true,
        'url' => get_permalink($post->ID),
        'contents' => array("en" => $notif_content)
      );
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                             'Authorization: Basic ' . $onesignal_wp_settings['app_rest_api_key']));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

      $response = curl_exec($ch);
      curl_close($ch);
      
      return $response;
    }
  }
  
  public static function on_transition_post_status( $new_status, $old_status, $post ) {
    self::send_notification_on_wp_post($new_status, $old_status, $post);
  }
}

?>