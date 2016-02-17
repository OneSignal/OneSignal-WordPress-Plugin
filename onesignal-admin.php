<?php
//include(dirname(__DIR__).'/../../ChromePhp.php');

function change_footer_admin() {
  return '';
}

class OneSignal_Admin {
  private static $RESOURCES_VERSION = '13';

  public function __construct() {
  }

  public static function init() {
    $onesignal = new self();
    if (current_user_can('update_plugins')) {
      add_action( 'admin_menu', array(__CLASS__, 'add_admin_page') );
    }
    if (current_user_can('publish_posts') || current_user_can('edit_published_posts')) {
      add_action('admin_init', array( __CLASS__, 'add_onesignal_post_options' ));
    }
    
    add_action( 'transition_post_status', array( __CLASS__, 'on_transition_post_status' ), 10, 3 );
    add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
    
    return $onesignal;
  }

  public static function admin_styles() {
    wp_enqueue_style( 'onesignal-admin-styles', plugin_dir_url( __FILE__ ) . 'views/css/onesignal-menu-styles.css', false, OneSignal_Admin::$RESOURCES_VERSION);
  }
  
  public static function add_onesignal_post_options() {
      add_meta_box('onesignal_notif_on_post',
                   'OneSignal',
                   array( __CLASS__, 'onesignal_notif_on_post_html_view' ),
                   'post',
                   'side',
                   'high');
    $args = array(
      'public'   => true,
      '_builtin' => false
    );
    $output = 'names';
    $operator = 'and';
    $post_types = get_post_types( $args, $output, $operator );
    foreach ( $post_types  as $post_type ) {
      add_meta_box(
        'onesignal_notif_on_post',
        'OneSignal',
        array( __CLASS__, 'onesignal_notif_on_post_html_view' ),
        $post_type,
        'side',
        'high'
      );
    }
  }
  
  public static function onesignal_notif_on_post_html_view($post) {
    $post_type = $post->post_type;
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    ?>
      <input type="checkbox" name="send_onesignal_notification" value="true" <?php if ($onesignal_wp_settings['notification_on_post'] && $post->post_status != "publish" && $post->post_type == "post") { echo "checked"; } ?>></input>
      <input type="hidden" name="has_onesignal_setting" value="true"></input>
      <label> <?php if ($post->post_status == "publish") { echo "Send notification on " . $post_type . " update"; } else { echo "Send notification on " . $post_type . " publish"; } ?></label>
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

    if (array_key_exists('subdomain', $config)) {
      $onesignal_wp_settings['subdomain'] = $config['subdomain'];
    } else {
      $onesignal_wp_settings['subdomain'] = "";
    }

    $onesignal_wp_settings['is_site_https_firsttime'] = 'set';

    $booleanSettings = array(
      'is_site_https',
      'prompt_auto_register',
      'use_modal_prompt',
      'send_welcome_notification',
      'notification_on_post',
      'notification_on_post_from_plugin',
      'persistNotification',
      'prompt_customize_enable',
      'prompt_showcredit',
      'notifyButton_enable',
      'notifyButton_prenotify',
      'notifyButton_showcredit',
      'notifyButton_customize_enable',
      'notifyButton_customize_colors_enable',
      'notifyButton_customize_offset_enable',
    );
    OneSignal_Admin::saveBooleanSettings($onesignal_wp_settings, $config, $booleanSettings);

    $stringSettings = array(
      'app_rest_api_key',
      'safari_web_id',
      'prompt_action_message',
      'prompt_example_notification_title_desktop',
      'prompt_example_notification_message_desktop',
      'prompt_example_notification_title_mobile',
      'prompt_example_notification_message_mobile',
      'prompt_example_notification_caption',
      'prompt_cancel_button_text',
      'prompt_accept_button_text',
      'welcome_notification_title',
      'welcome_notification_message',
      'welcome_notification_url',
      'notifyButton_size',
      'notifyButton_theme',
      'notifyButton_position',
      'notifyButton_color_background',
      'notifyButton_color_foreground',
      'notifyButton_color_badge_background',
      'notifyButton_color_badge_foreground',
      'notifyButton_color_badge_border',
      'notifyButton_color_pulse',
      'notifyButton_color_popup_button_background',
      'notifyButton_color_popup_button_background_hover',
      'notifyButton_color_popup_button_background_active',
      'notifyButton_color_popup_button_color',
      'notifyButton_offset_bottom',
      'notifyButton_offset_left',
      'notifyButton_offset_right',
      'notifyButton_message_prenotify',
      'notifyButton_tip_state_unsubscribed',
      'notifyButton_tip_state_subscribed',
      'notifyButton_tip_state_blocked',
      'notifyButton_message_action_subscribed',
      'notifyButton_message_action_resubscribed',
      'notifyButton_message_action_unsubscribed',
      'notifyButton_dialog_main_title',
      'notifyButton_dialog_main_button_subscribe',
      'notifyButton_dialog_main_button_unsubscribe',
      'notifyButton_dialog_blocked_title',
      'notifyButton_dialog_blocked_message',
    );
    OneSignal_Admin::saveStringSettings($onesignal_wp_settings, $config, $stringSettings);

    OneSignal::save_onesignal_settings($onesignal_wp_settings);
    
    return $onesignal_wp_settings;
  }

  public static function saveBooleanSettings(&$onesignal_wp_settings, &$config, $settings) {
    foreach ($settings as $setting) {
      if (array_key_exists($setting, $config)) {
        $onesignal_wp_settings[$setting] = true;
      } else {
        $onesignal_wp_settings[$setting] = false;
      }
    }
  }

  public static function saveStringSettings(&$onesignal_wp_settings, &$config, $settings) {
    foreach ($settings as $setting) {
      if (array_key_exists($setting, $config)) {
        $onesignal_wp_settings[$setting] = $config[$setting];
      }
    }
  }

	public static function add_admin_page() {
		$OneSignal_menu = add_menu_page('OneSignal Push',
                                    'OneSignal Push',
                                    'manage_options',
                                    'onesignal-push',
                                    array(__CLASS__, 'admin_menu')
    );
                       
    add_action( 'load-' . $OneSignal_menu, array(__CLASS__, 'admin_custom_load') );
	}

	public static function admin_menu() {
    require_once( plugin_dir_path( __FILE__ ) . '/views/config.php' );
  }

  public static function admin_custom_load() {
    add_action( 'admin_enqueue_scripts', array(__CLASS__, 'admin_custom_scripts') );
  }
  
  public static function admin_custom_scripts() {
    add_filter('admin_footer_text', 'change_footer_admin', 9999); // 9999 means priority, execute after the original fn executes

    wp_enqueue_style( 'icons', plugin_dir_url( __FILE__ ) . 'views/css/icons.css', false,  OneSignal_Admin::$RESOURCES_VERSION);
    wp_enqueue_style( 'semantic-ui', plugin_dir_url( __FILE__ ) . 'views/css/semantic-ui.css', false,  OneSignal_Admin::$RESOURCES_VERSION);
    wp_enqueue_style( 'site', plugin_dir_url( __FILE__ ) . 'views/css/site.css', false,  OneSignal_Admin::$RESOURCES_VERSION);

    wp_enqueue_script( 'jquery.min', plugin_dir_url( __FILE__ ) . 'views/javascript/jquery.min.js', false,  OneSignal_Admin::$RESOURCES_VERSION);
    wp_enqueue_script( 'semantic-ui', plugin_dir_url( __FILE__ ) . 'views/javascript/semantic-ui.js', false,  OneSignal_Admin::$RESOURCES_VERSION);
    wp_enqueue_script( 'jquery.cookie', plugin_dir_url( __FILE__ ) . 'views/javascript/jquery.cookie.js', false,  OneSignal_Admin::$RESOURCES_VERSION);
    wp_enqueue_script( 'intercom', plugin_dir_url( __FILE__ ) . 'views/javascript/intercom.js', false,  OneSignal_Admin::$RESOURCES_VERSION);
    wp_enqueue_script( 'site', plugin_dir_url( __FILE__ ) . 'views/javascript/site-admin.js', false,  OneSignal_Admin::$RESOURCES_VERSION);

  }
  
  public static function send_notification_on_wp_post($new_status, $old_status, $post) {
    if (empty( $post ) || $new_status !== "publish") {
        return;
    }

    if ($post->post_type == 'page') {
      return;
    }
    
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();

    $send_onesignal_notification = false;
    if (isset($_POST['has_onesignal_setting'])) {
      if (array_key_exists('send_onesignal_notification', $_POST)) {
        $send_onesignal_notification = $_POST['send_onesignal_notification'];
      }
    }
    elseif ($old_status !== "publish" && $post->post_type === "post") {
      $send_onesignal_notification = $onesignal_wp_settings['notification_on_post_from_plugin'];
    }
    
    if ($send_onesignal_notification === true || $send_onesignal_notification === "true") {  
      $notif_content = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8');

      $site_title = "";
      if ($onesignal_wp_settings['default_title'] != "") {
        $site_title = html_entity_decode($onesignal_wp_settings['default_title'], ENT_HTML401 | ENT_QUOTES, 'UTF-8');
      }
      else {
        $site_title = html_entity_decode(get_bloginfo( 'name' ), ENT_HTML401 | ENT_QUOTES, 'UTF-8');
      }
      
      $fields = array(
        'app_id' => $onesignal_wp_settings['app_id'],
        'headings' => array("en" => $site_title),
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