<?php

function change_footer_admin() {
  return '';
}

class OneSignal_Admin {
  private static $RESOURCES_VERSION = '17';
  private static $NONCE_KEY = 'onesignal_meta_box_nonce';
  private static $NONCE_FIELD = 'onesignal_meta_box';

  public function __construct() {
  }

  public static function init() {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
      try {
        switch ($errno) {
          case E_USER_ERROR:
            onesignal_debug('FATAL ERROR: ' . $errstr . ' @ ' . $errfile . ':' . $errline);
            exit(1);
            break;

          case E_USER_WARNING:
            onesignal_debug('WARNING: ' . $errstr . ' @ ' . $errfile . ':' . $errline);
            break;

          case E_USER_NOTICE || E_NOTICE:
            //onesignal_debug('NOTICE: ' . $errstr . ' @ ' . $errfile . ':' . $errline);
            break;

          case E_STRICT:
            //onesignal_debug('DEPRECATED: ' . $errstr . ' @ ' . $errfile . ':' . $errline);
            break;

          default:
            onesignal_debug('UNKNOWN EXCEPTION (' . $errno . '): ' . $errstr . ' @ ' . $errfile . ':' . $errline);
            break;
        }
        return true;
      } catch (Exception $ex) {
        return true;
      }
    }
    set_error_handler("exception_error_handler");

    $onesignal = new self();
    if (current_user_can('update_plugins')) {
      add_action( 'admin_menu', array(__CLASS__, 'add_admin_page') );
    }
    if (current_user_can('publish_posts') || current_user_can('edit_published_posts')) {
      add_action('admin_init', array( __CLASS__, 'add_onesignal_post_options' ));
    }
    
    add_action( 'transition_post_status', array( __CLASS__, 'on_transition_post_status' ), 10, 3 );
    add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
    add_action( 'save_post',      array( __CLASS__, 'on_save_post') );
    return $onesignal;
  }

  public static function admin_styles() {
    wp_enqueue_style( 'onesignal-admin-styles', plugin_dir_url( __FILE__ ) . 'views/css/onesignal-menu-styles.css', false, OneSignal_Admin::$RESOURCES_VERSION);
  }

  /**
   * Save the meta when the post is saved.
   * @param int $post_id The ID of the post being saved.
   */
  public function on_save_post($post_id) {
    /*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
    // Check if our nonce is set.
    if (!isset( $_POST[OneSignal_Admin::$NONCE_KEY] ) ) {
      return $post_id;
    }

    $nonce = $_POST[OneSignal_Admin::$NONCE_KEY];

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($nonce, OneSignal_Admin::$NONCE_FIELD)) {
      return $post_id;
    }

    /*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $post_id;
    }

    // Check the user's permissions.
    if ( 'page' == $_POST['post_type'] ) {
      if (!current_user_can( 'edit_page', $post_id)) {
        return $post_id;
      }
    } else {
      if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
      }
    }

    /*
     * Never add onesignal_debug() statements above this line or they will recursively output.
     */

    /* OK, it's safe for us to save the data now. */

    // Sanitize the user input.
    $checkbox_send_notification = sanitize_text_field($_POST['send_onesignal_notification']);
    onesignal_debug('In on_save_post:', '$checkbox_send_notification', $checkbox_send_notification);

    // Update the meta field.
    if ($checkbox_send_notification) {
      update_post_meta( $post_id, 'onesignal_send_notification', true );
    } else {
      update_post_meta( $post_id, 'onesignal_send_notification', false);
    }
  }
  
  public static function add_onesignal_post_options() {
    // Add our meta box for the "post" post type (default)
    add_meta_box('onesignal_notif_on_post',
                 'OneSignal',
                 array( __CLASS__, 'onesignal_notif_on_post_html_view' ),
                 'post',
                 'side',
                 'high');

    // Then add our meta box for all other post types that are public but not built in to WordPress
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


  /**
   * Render Meta Box content.
   * @param WP_Post $post The post object.
   */
  public static function onesignal_notif_on_post_html_view($post) {
    $post_type = $post->post_type;
    $onesignal_wp_settings = OneSignal::get_onesignal_settings();

    // Add an nonce field so we can check for it later.
    wp_nonce_field(OneSignal_Admin::$NONCE_FIELD, OneSignal_Admin::$NONCE_KEY );

    // Use get_post_meta to retrieve an existing value from the database.
    // Since $single is set to true, if a meta field with the given key isn't found for the post, an empty string is returned
    $meta_send_notification = get_post_meta($post->ID, 'onesignal_send_notification', true);
    onesignal_debug('[onesignal_notif_on_post_html_view]', '$meta_send_notification:', $meta_send_notification);

    // In our WP plugin config, have we checked "Automatically send a push notification when I create a post from the default WordPress editor"
    // This condition is truy only when: setting is enabled on Config page, post type is ONLY "post", and the post has not been published (new posts are status "auto-draft")
    $settings_send_notification_on_standard_post_create = $onesignal_wp_settings['notification_on_post'] && $post->post_type == "post" && $post->post_status !== "publish";
    onesignal_debug('[onesignal_notif_on_post_html_view]', '$settings_send_notification_on_standard_post_create:', $settings_send_notification_on_standard_post_create);
    onesignal_debug('[onesignal_notif_on_post_html_view]', '[settings_send_notification_on_standard_post_create]', '$onesignal_wp_settings[\'notification_on_post\']:', $onesignal_wp_settings['notification_on_post']);
    onesignal_debug('[onesignal_notif_on_post_html_view]', '[settings_send_notification_on_standard_post_create]', '$post->post_type == "post":', $post->post_type == "post");
    onesignal_debug('[onesignal_notif_on_post_html_view]', '[settings_send_notification_on_standard_post_create]', '$post->post_status !== "publish:', $post->post_status !== "publish");

    $checkbox_send_notification = $settings_send_notification_on_standard_post_create || $meta_send_notification;
    onesignal_debug('[onesignal_notif_on_post_html_view]', '$checkbox_send_notification:', $checkbox_send_notification);

    ?>
      <input type="checkbox" name="send_onesignal_notification" value="true" <?php if ($checkbox_send_notification) { echo "checked"; } ?>></input>
      <label>
        <?php if ($post->post_status == "publish") {
          echo "Send notification on " . $post_type . " update";
        } else {
          echo "Send notification on " . $post_type . " publish";
        } ?>
      </label>
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
      'showNotificationIconFromPostThumbnail',
      'chrome_auto_dismiss_notifications',
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
        $value = $config[$setting];
        $value = OneSignalUtils::normalize($value);
        $onesignal_wp_settings[$setting] = $value;
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
    try {
      $onesignal_wp_settings = OneSignal::get_onesignal_settings();
      $meta_send_notification = get_post_meta($post->ID, 'onesignal_send_notification', true);
      $settings_send_notification_on_third_party_post_create = !$meta_send_notification &&
                                                               $onesignal_wp_settings['notification_on_post_from_plugin'] &&
                                                               $post->post_type == "post" &&
                                                               $post->post_status !== "publish";
      onesignal_debug('[send_notification_on_wp_post] $meta_send_notification: ' . $meta_send_notification);
      onesignal_debug('[send_notification_on_wp_post] $settings_send_notification_on_third_party_post_create: ' . $settings_send_notification_on_third_party_post_create);
      onesignal_debug('[send_notification_on_wp_post] [settings_send_notification_on_third_party_post_create] !$meta_send_notification: ' . !$meta_send_notification);
      onesignal_debug('[send_notification_on_wp_post] [settings_send_notification_on_third_party_post_create] notification_on_post_from_plugin: ' . $onesignal_wp_settings['notification_on_post_from_plugin']);
      onesignal_debug('[send_notification_on_wp_post] [settings_send_notification_on_third_party_post_create] post_type: ' . ($post->post_type === "post") . '  (' . $post->post_type . ')');
      onesignal_debug('[send_notification_on_wp_post] [settings_send_notification_on_third_party_post_create] post_status: ' . ($post->post_status !== "publish") . '  (' . $post->post_status . ')');
      $do_send_notification = $meta_send_notification || $settings_send_notification_on_third_party_post_create;
      if ($do_send_notification) {
        $notif_content = OneSignalUtils::decode_entities(get_the_title($post->ID));

        $site_title = "";
        if ($onesignal_wp_settings['default_title'] != "") {
          $site_title = OneSignalUtils::decode_entities($onesignal_wp_settings['default_title']);
        } else {
          $site_title = OneSignalUtils::decode_entities(get_bloginfo('name'));
        }

        if (function_exists('qtrans_getLanguage')) {
          try {
            $qtransLang    = qtrans_getLanguage();
            $site_title    = qtrans_use($qtransLang, $site_title, false);
            $notif_content = qtrans_use($qtransLang, $notif_content, false);
          } catch (Exception $e) {
            onesignal_debug('Caught qTrans exception:', $e->getMessage());
          }
        }

        $fields = array(
          'app_id'            => $onesignal_wp_settings['app_id'],
          'headings'          => array("en" => $site_title),
          'included_segments' => array('All'),
          'isAnyWeb'          => true,
          'url'               => get_permalink($post->ID),
          'contents'          => array("en" => $notif_content)
        );

        $post_has_featured_image           = has_post_thumbnail($post);
        $config_use_featured_image_as_icon = $onesignal_wp_settings['showNotificationIconFromPostThumbnail'] == "1";
        if ($post_has_featured_image == true && $config_use_featured_image_as_icon) {
          // get the icon image from wordpress if it exists
          $post_thumbnail_id = get_post_thumbnail_id($post->ID);
          $thumbnail_array   = wp_get_attachment_image_src($post_thumbnail_id, array(80, 80), true);
          if (!empty($thumbnail_array)) {
            $thumbnail = $thumbnail_array[0];
            // set the icon image for both chrome and firefox-1
            $fields['chrome_web_icon'] = $thumbnail;
            $fields['firefox_icon']    = $thumbnail;
          }
        }

        if (defined('ONESIGNAL_DEBUG')) {
          // http://blog.kettle.io/debugging-curl-requests-in-php/
          ob_start();
          $out = fopen('php://output', 'w');
        }

        $ch = curl_init();

        $onesignal_post_url = "https://onesignal.com/api/v1/notifications";

        if (defined('ONESIGNAL_DEBUG')) {
          $onesignal_post_url = "https://localhost:3001/api/v1/notifications";
        }

        $onesignal_auth_key = $onesignal_wp_settings['app_rest_api_key'];

        if (defined('ONESIGNAL_DEBUG')) {
          $onesignal_auth_key = "NDQyMjM3OTYtNjBkOC00YjI0LWI2NzMtZDZmODQ3ODU4ZmM2";
        }
        curl_setopt($ch, CURLOPT_URL, $onesignal_post_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Authorization: Basic ' . $onesignal_auth_key
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (defined('ONESIGNAL_DEBUG')) {
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($ch, CURLOPT_FAILONERROR, false);
          curl_setopt($ch, CURLOPT_HTTP200ALIASES, array(400));
          curl_setopt($ch, CURLOPT_VERBOSE, true);
          curl_setopt($ch, CURLOPT_STDERR, $out);
        }

        $response = curl_exec($ch);

        if (defined('ONESIGNAL_DEBUG')) {
          fclose($out);
          $debug_output = ob_get_clean();

          $curl_effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
          $curl_http_code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $curl_total_time    = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

          onesignal_debug('[send_notification_on_wp_post] cURL POST Fields:', json_encode($fields));
          onesignal_debug('[send_notification_on_wp_post] cURL URL:', $curl_effective_url);
          onesignal_debug('[send_notification_on_wp_post] cURL Status Code:', $curl_http_code);
          //onesignal_debug('cURL Request Time:', $curl_total_time, 'seconds');
          //onesignal_debug('cURL Error Number:', curl_errno($ch));
          //onesignal_debug('cURL Error Description:', curl_error($ch));
          //onesignal_debug('cURL Response:', print_r($response, true));
          //onesignal_debug('cURL Log:', $debug_output);  Too much verbose output
          curl_close($ch);
        } else {
          curl_close($ch);
        }

        return $response;
      }
    }
    catch (Exception $e) {
      onesignal_debug('EXCEPTION: ' . $e->getMessage());
    }
  }
  
  public static function on_transition_post_status( $new_status, $old_status, $post ) {
    if (empty($post) || $new_status !== "publish" || $post->post_type == 'page' || $post->post_type == 'wdslp-wds-log') {
      // It's important not to call onesignal_debug() on posts of type wdslp-wds-log, otherwise each post will recursively generate 4 more posts
      return;
    }
    self::send_notification_on_wp_post($new_status, $old_status, $post);
  }
}

?>