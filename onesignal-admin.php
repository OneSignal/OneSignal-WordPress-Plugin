<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

function onesignal_change_footer_admin()
{
    return '';
}
/*
 * Loads js script that includes ajax call with post id
 */

add_action('admin_enqueue_scripts', 'load_javascript');
function load_javascript()
{
    global $post;
    if ($post) {
        wp_register_script('notice_script', plugins_url('notice.js', __FILE__), array('jquery'), '1.1', true);
        wp_enqueue_script('notice_script');
        wp_localize_script('notice_script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'post_id' => $post->ID));
    }
}

add_action('wp_ajax_has_metadata', 'has_metadata');
function has_metadata()
{
    $post_id = $_GET['post_id'];

    if (is_null($post_id)) {
        error_log('OneSignal: could not get post_id');
        $data = array('error' => 'could not get post id');
    } else {
        $recipients = get_post_meta($post_id, 'recipients');
        if ($recipients && is_array($recipients)) {
            $recipients = $recipients[0];
        }

        $status = get_post_meta($post_id, 'status');
        if ($status && is_array($status)) {
            $status = $status[0];
        }

        $response_body = get_post_meta($post_id, 'response_body');
        if ($response_body && is_array($response_body)) {
            $response_body = $response_body[0];
        }

        // reset meta
        delete_post_meta($post_id, 'status');
        delete_post_meta($post_id, 'recipients');
        delete_post_meta($post_id, 'response_body');

        $data = array('recipients' => $recipients, 'status_code' => $status, 'response_body' => $response_body);
    }

    echo json_encode($data);

    exit;
}

class OneSignal_Admin
{
    /**
     * Increment $RESOURCES_VERSION any time the CSS or JavaScript changes to view the latest changes.
     */
    private static $RESOURCES_VERSION = '42';
    private static $SAVE_POST_NONCE_KEY = 'onesignal_meta_box_nonce';
    private static $SAVE_POST_NONCE_ACTION = 'onesignal_meta_box';
    public static $SAVE_CONFIG_NONCE_KEY = 'onesignal_config_page_nonce';
    public static $SAVE_CONFIG_NONCE_ACTION = 'onesignal_config_page';

    public function __construct()
    {
    }

    public static function init()
    {
        $onesignal = new self();

        if (class_exists('WDS_Log_Post')) {
            function exception_error_handler($errno, $errstr, $errfile, $errline)
            {
                try {
                    switch ($errno) {
                      case E_USER_ERROR:
                          onesignal_debug('[ERROR]', $errstr.' @ '.$errfile.':'.$errline);
                          exit(1);
                          break;

                      case E_USER_WARNING:
                          onesignal_debug('[WARNING]', $errstr.' @ '.$errfile.':'.$errline);
                          break;

                      case E_USER_NOTICE || E_NOTICE:
                          //onesignal_debug('NOTICE: ' . $errstr . ' @ ' . $errfile . ':' . $errline);
                          break;

                      case E_STRICT:
                          //onesignal_debug('DEPRECATED: ' . $errstr . ' @ ' . $errfile . ':' . $errline);
                          break;

                      default:
                          onesignal_debug('[UNKNOWN ERROR]', '('.$errno.'): '.$errstr.' @ '.$errfile.':'.$errline);
                          break;
                  }

                    return true;
                } catch (Exception $ex) {
                    return true;
                }
            }

            set_error_handler('exception_error_handler');

            function fatal_exception_error_handler()
            {
                $error = error_get_last();
                try {
                    switch ($error['type']) {
                      case E_ERROR:
                      case E_CORE_ERROR:
                      case E_COMPILE_ERROR:
                      case E_USER_ERROR:
                      case E_RECOVERABLE_ERROR:
                      case E_CORE_WARNING:
                      case E_COMPILE_WARNING:
                      case E_PARSE:
                          onesignal_debug('[CRITICAL ERROR]', '('.$error['type'].') '.$error['message'].' @ '.$error['file'].':'.$error['line']);
                  }
                } catch (Exception $ex) {
                    return true;
                }
            }

            register_shutdown_function('fatal_exception_error_handler');
        }

        if (OneSignalUtils::can_modify_plugin_settings()) {
            add_action('admin_menu', array(__CLASS__, 'add_admin_page'));
        }
        if (OneSignalUtils::can_send_notifications()) {
            add_action('admin_init', array(__CLASS__, 'add_onesignal_post_options'));
        }

        add_action('save_post', array(__CLASS__, 'on_save_post'), 1, 3);
        add_action('transition_post_status', array(__CLASS__, 'on_transition_post_status'), 10, 3);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_styles'));

        return $onesignal;
    }

    public static function admin_styles()
    {
        wp_enqueue_style('onesignal-admin-styles', plugin_dir_url(__FILE__).'views/css/onesignal-menu-styles.css', false, OneSignal_Admin::$RESOURCES_VERSION);
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id the ID of the post being saved
     */
    public static function on_save_post($post_id, $post, $updated)
    {
        if ($post->post_type == 'wdslp-wds-log') {
            // Prevent recursive post logging
            return;
        }
        /*
             * We need to verify this came from the our screen and with proper authorization,
             * because save_post can be triggered at other times.
             */
        // Check if our nonce is set.
        if (!isset($_POST[OneSignal_Admin::$SAVE_POST_NONCE_KEY])) {
            // This is called on every new post ... not necessary to log it.
            // onesignal_debug('Nonce is not set for post ' . $post->post_title . ' (ID ' . $post_id . ')');
            return $post_id;
        }

        $nonce = $_POST[OneSignal_Admin::$SAVE_POST_NONCE_KEY];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, OneSignal_Admin::$SAVE_POST_NONCE_ACTION)) {
            onesignal_debug('Nonce is not valid for '.$post->post_title.' (ID '.$post_id.')');

            return $post_id;
        }

        /*
             * If this is an autosave, our form has not been submitted,
             * so we don't want to do anything.
             */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        /* OK, it's safe for us to save the data now. */

        /* Some WordPress environments seem to be inconsistent about whether on_save_post is called before transition_post_status
           * Check flag in case we just sent a notification for this post (this on_save_post is called after a successful send)
          */
        $just_sent_notification = (get_post_meta($post_id, 'onesignal_notification_already_sent', true) == true);

        if ($just_sent_notification) {
            // Reset our flag
            update_post_meta($post_id, 'onesignal_notification_already_sent', false);
            onesignal_debug('A notification was just sent, so ignoring on_save_post. Resetting check flag.');

            return;
        }

        if (array_key_exists('onesignal_meta_box_present', $_POST)) {
            update_post_meta($post_id, 'onesignal_meta_box_present', true);
            onesignal_debug('Set post metadata "onesignal_meta_box_present" to true.');
        } else {
            update_post_meta($post_id, 'onesignal_meta_box_present', false);
            onesignal_debug('Set post metadata "onesignal_meta_box_present" to false.');
        }

        /* Even though the meta box always contains the checkbox, if an HTML checkbox is not checked, it is not POSTed to the server */
        if (array_key_exists('send_onesignal_notification', $_POST)) {
            update_post_meta($post_id, 'onesignal_send_notification', true);
            onesignal_debug('Set post metadata "onesignal_send_notification" to true.');
        } else {
            update_post_meta($post_id, 'onesignal_send_notification', false);
            onesignal_debug('Set post metadata "onesignal_send_notification" to false.');
        }
    }

    public static function add_onesignal_post_options()
    {
        // If there is an error or success message we should display, display it now
        function admin_notice_error()
        {
            $onesignal_transient_error = get_transient('onesignal_transient_error');
            if (!empty($onesignal_transient_error)) {
                delete_transient('onesignal_transient_error');
                echo $onesignal_transient_error;
            }

            $onesignal_transient_success = get_transient('onesignal_transient_success');
            if (!empty($onesignal_transient_success)) {
                delete_transient('onesignal_transient_success');
                echo $onesignal_transient_success;
            }
        }
        add_action('admin_notices', 'admin_notice_error');

        // Add our meta box for the "post" post type (default)
        add_meta_box('onesignal_notif_on_post',
                 'OneSignal Push Notifications',
                 array(__CLASS__, 'onesignal_notif_on_post_html_view'),
                 'post',
                 'side',
                 'high');

        // Then add our meta box for all other post types that are public but not built in to WordPress
        $args = array(
      'public' => true,
      '_builtin' => false,
    );
        $output = 'names';
        $operator = 'and';
        $post_types = get_post_types($args, $output, $operator);
        foreach ($post_types  as $post_type) {
            add_meta_box(
        'onesignal_notif_on_post',
        'OneSignal Push Notifications',
        array(__CLASS__, 'onesignal_notif_on_post_html_view'),
        $post_type,
        'side',
        'high'
      );
        }
    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post the post object
     */
    public static function onesignal_notif_on_post_html_view($post)
    {
        $post_type = $post->post_type;
        $onesignal_wp_settings = OneSignal::get_onesignal_settings();

        // Add an nonce field so we can check for it later.
        wp_nonce_field(OneSignal_Admin::$SAVE_POST_NONCE_ACTION, OneSignal_Admin::$SAVE_POST_NONCE_KEY, true);

        // Our plugin config setting "Automatically send a push notification when I publish a post from the WordPress editor"
        $settings_send_notification_on_wp_editor_post = $onesignal_wp_settings['notification_on_post'];

        /* This is a scheduled post and the user checked "Send a notification on post publish/update". */
        $post_metadata_was_send_notification_checked = (get_post_meta($post->ID, 'onesignal_send_notification', true) == true);

        // We check the checkbox if: setting is enabled on Config page, post type is ONLY "post", and the post has not been published (new posts are status "auto-draft")
    $meta_box_checkbox_send_notification = ($settings_send_notification_on_wp_editor_post &&  // If setting is enabled
                                            $post->post_type == 'post' &&  // Post type must be type post for checkbox to be auto-checked
                                            in_array($post->post_status, array('future', 'draft', 'auto-draft', 'pending'))) || // Post is scheduled, incomplete, being edited, or is awaiting publication
                                            ($post_metadata_was_send_notification_checked);

        if (has_filter('onesignal_meta_box_send_notification_checkbox_state')) {
            $meta_box_checkbox_send_notification = apply_filters('onesignal_meta_box_send_notification_checkbox_state', $post, $onesignal_wp_settings);
        }
        onesignal_debug('$meta_box_checkbox_send_notification:', $meta_box_checkbox_send_notification);
        onesignal_debug('    [$meta_box_checkbox_send_notification]', 'has_filter(onesignal_meta_box_send_notification_checkbox_state):', has_filter('onesignal_meta_box_send_notification_checkbox_state'));
        onesignal_debug('    [$meta_box_checkbox_send_notification]', 'onesignal_meta_box_send_notification_checkbox_state filter result:', apply_filters('onesignal_meta_box_send_notification_checkbox_state', $post, $onesignal_wp_settings));
        onesignal_debug('    [$meta_box_checkbox_send_notification]', '$settings_send_notification_on_wp_editor_post:', $settings_send_notification_on_wp_editor_post);
        onesignal_debug('    [$meta_box_checkbox_send_notification]', '$settings_send_notification_on_wp_editor_post:', $settings_send_notification_on_wp_editor_post);
        onesignal_debug('    [$meta_box_checkbox_send_notification]', '$post->post_type == "post":', $post->post_type == 'post', '('.$post->post_type.')');
        onesignal_debug('    [$meta_box_checkbox_send_notification]', 'in_array($post->post_status, array("future", "draft", "auto-draft", "pending"):', in_array($post->post_status, array('future', 'draft', 'auto-draft', 'pending')), '('.$post->post_status.')'); ?>
    
	    <input type="hidden" name="onesignal_meta_box_present" value="true"></input>
      <input type="checkbox" name="send_onesignal_notification" value="true" <?php if ($meta_box_checkbox_send_notification) {
            echo 'checked';
        } ?>></input>
      <label>
        <?php if ($post->post_status == 'publish') {
            echo 'Send notification on '.$post_type.' update';
        } else {
            echo 'Send notification on '.$post_type.' publish';
        } ?>
      </label>
    <?php
    }

    public static function save_config_page($config)
    {
        if (!OneSignalUtils::can_modify_plugin_settings()) {
            onesignal_debug('Not saving plugin settings because the current user is not an administrator.');
            set_transient('onesignal_transient_error', '<div class="error notice onesignal-error-notice">
                    <p><strong>OneSignal Push:</strong><em> Only administrators are allowed to save plugin settings.</em></p>
                </div>', 86400);

            return;
        }

        $sdk_dir = plugin_dir_path(__FILE__).'sdk_files/';
        $onesignal_wp_settings = OneSignal::get_onesignal_settings();
        $new_app_id = $config['app_id'];

        // Validate the UUID
        if (preg_match('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $new_app_id, $m)) {
            $onesignal_wp_settings['app_id'] = trim($new_app_id);
        }

        if (array_key_exists('gcm_sender_id', $config) && (is_numeric($config['gcm_sender_id']) || $config['gcm_sender_id'] === '')) {
            $onesignal_wp_settings['gcm_sender_id'] = trim($config['gcm_sender_id']);
        }

        if (array_key_exists('subdomain', $config)) {
            $onesignal_wp_settings['subdomain'] = str_replace(' ', '', $config['subdomain']);
        } else {
            $onesignal_wp_settings['subdomain'] = '';
        }
        $onesignal_wp_settings['subdomain'] = trim($onesignal_wp_settings['subdomain']);

        $onesignal_wp_settings['is_site_https_firsttime'] = 'set';

        $booleanSettings = array(
      'is_site_https',
      'prompt_auto_register',
      'use_modal_prompt',
      'send_welcome_notification',
      'notification_on_post',
      'notification_on_post_from_plugin',
      'showNotificationIconFromPostThumbnail',
      'showNotificationImageFromPostThumbnail',
      'chrome_auto_dismiss_notifications',
      'prompt_customize_enable',
      'notifyButton_showAfterSubscribed',
      'notifyButton_enable',
      'notifyButton_prenotify',
      'notifyButton_showcredit',
      'notifyButton_customize_enable',
      'notifyButton_customize_colors_enable',
      'notifyButton_customize_offset_enable',
      'send_to_mobile_platforms',
      'show_gcm_sender_id',
      'use_custom_manifest',
      'use_custom_sdk_init',
      'show_notification_send_status_message',
      'use_http_permission_request',
      'customize_http_permission_request',
      'use_slidedown_permission_message_for_https',
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
      'prompt_auto_accept_title',
      'prompt_site_name',
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
      'utm_additional_url_params',
      'allowed_custom_post_types',
      'notification_title',
      'custom_manifest_url',
      'http_permission_request_modal_title',
      'http_permission_request_modal_message',
      'http_permission_request_modal_button_text',
      'persist_notifications',
    );
        OneSignal_Admin::saveStringSettings($onesignal_wp_settings, $config, $stringSettings);

        OneSignal::save_onesignal_settings($onesignal_wp_settings);

        return $onesignal_wp_settings;
    }

    public static function saveBooleanSettings(&$onesignal_wp_settings, &$config, $settings)
    {
        foreach ($settings as $setting) {
            if (array_key_exists($setting, $config)) {
                $onesignal_wp_settings[$setting] = true;
            } else {
                $onesignal_wp_settings[$setting] = false;
            }
        }
    }

    public static function saveStringSettings(&$onesignal_wp_settings, &$config, $settings)
    {
        foreach ($settings as $setting) {
            if (array_key_exists($setting, $config)) {
                $value = $config[$setting];
                $value = OneSignalUtils::normalize($value);
                $onesignal_wp_settings[$setting] = $value;
            }
        }
    }

    public static function add_admin_page()
    {
        $OneSignal_menu = add_menu_page('OneSignal Push',
                                    'OneSignal Push',
                                    'manage_options',
                                    'onesignal-push',
                                    array(__CLASS__, 'admin_menu')
    );

        OneSignal_Admin::save_config_settings_form();

        add_action('load-'.$OneSignal_menu, array(__CLASS__, 'admin_custom_load'));
    }

    public static function save_config_settings_form()
    {
        // If the user is trying to save the form, require a valid nonce or die
        if (array_key_exists('app_id', $_POST)) {
            // check_admin_referer dies if not valid; no if statement necessary
            check_admin_referer(OneSignal_Admin::$SAVE_CONFIG_NONCE_ACTION, OneSignal_Admin::$SAVE_CONFIG_NONCE_KEY);
            $onesignal_wp_settings = OneSignal_Admin::save_config_page($_POST);
        }
    }

    public static function admin_menu()
    {
        require_once plugin_dir_path(__FILE__).'/views/config.php';
    }

    public static function admin_custom_load()
    {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_custom_scripts'));

        $onesignal_wp_settings = OneSignal::get_onesignal_settings();
        if (
          $onesignal_wp_settings['app_id'] == '' ||
          $onesignal_wp_settings['app_rest_api_key'] == ''
      ) {
            function admin_notice_setup_not_complete()
            {
                ?>
              <script>
                  document.addEventListener('DOMContentLoaded', function() {
                      activateSetupTab('setup/0');
                  });
              </script>
			  <div class="error notice onesignal-error-notice">
				  <p><strong>OneSignal Push:</strong> <em>Your setup is not complete. Please follow the Setup guide to set up web push notifications. Both the App ID and REST API Key fields are required.</em></p>
			  </div>
			  <?php
            }

            add_action('admin_notices', 'admin_notice_setup_not_complete');
        }

        if (!function_exists('curl_init')) {
            function admin_notice_curl_not_installed()
            {
                ?>
			  <div class="error notice onesignal-error-notice">
				  <p><strong>OneSignal Push:</strong> <em>cURL is not installed on this server. cURL is required to send notifications. Please make sure cURL is installed on your server before continuing.</em></p>
			  </div>
			  <?php
            }
            add_action('admin_notices', 'admin_notice_curl_not_installed');
        }
    }

    public static function admin_custom_scripts()
    {
        add_filter('admin_footer_text', 'onesignal_change_footer_admin', 9999); // 9999 means priority, execute after the original fn executes

        wp_enqueue_style('icons', plugin_dir_url(__FILE__).'views/css/icons.css', false, OneSignal_Admin::$RESOURCES_VERSION);
        wp_enqueue_style('semantic-ui', plugin_dir_url(__FILE__).'views/css/semantic-ui.css', false, OneSignal_Admin::$RESOURCES_VERSION);
        wp_enqueue_style('site', plugin_dir_url(__FILE__).'views/css/site.css', false, OneSignal_Admin::$RESOURCES_VERSION);

        wp_enqueue_script('jquery.min', plugin_dir_url(__FILE__).'views/javascript/jquery.min.js', false, OneSignal_Admin::$RESOURCES_VERSION);
        wp_enqueue_script('semantic-ui', plugin_dir_url(__FILE__).'views/javascript/semantic-ui.js', false, OneSignal_Admin::$RESOURCES_VERSION);
        wp_enqueue_script('jquery.cookie', plugin_dir_url(__FILE__).'views/javascript/jquery.cookie.js', false, OneSignal_Admin::$RESOURCES_VERSION);
        wp_enqueue_script('site', plugin_dir_url(__FILE__).'views/javascript/site-admin.js', false, OneSignal_Admin::$RESOURCES_VERSION);
    }

    /**
     * Returns true if more than one notification has been sent in the last minute.
     */
    public static function get_sending_rate_limit_wait_time()
    {
        onesignal_debug('Called is_over_sending_rate_limit()');
        $last_send_time = get_option('onesignal.last_send_time');
        onesignal_debug('    [is_over_sending_rate_limit] Last send time:', $last_send_time);
        if ($last_send_time) {
            $time_elapsed_since_last_send = ONESIGNAL_API_RATE_LIMIT_SECONDS - (current_time('timestamp') - intval($last_send_time));
            if ($time_elapsed_since_last_send > 0) {
                onesignal_debug('    [is_over_sending_rate_limit] Current send time ('.current_time('timestamp').') is less than rate limit time after the last send time.');

                return $time_elapsed_since_last_send;
            }
        }

        return false;
    }

    /**
     * Updates the last sent timestamp, used in rate limiting notifications sent more than 1 per minute.
     */
    public static function update_last_sent_timestamp()
    {
        update_option('onesignal.last_send_time', current_time('timestamp'));
    }

    /**
     * hashes notification-title+timestamp and converts it into a uuid
     * meant to prevent duplicate notification issue started with wp5.0.0.
     *
     * @title - title of post
     * return - uuid of sha1 hash of post title + post timestamp
     */
    public static function uuid($title)
    {
        $now = explode(':', date('z:H:i'));
        $now_minutes = $now[0] * 60 * 24 + $now[1] * 60 + $now[2];
        $prev_minutes = get_option('TimeLastUpdated');
        $prehash = (string) $title;

        if ($prev_minutes !== false && ($now_minutes - $prev_minutes) > 0) {
            update_option('TimeLastUpdated', $now_minutes);
            $timestamp = $now_minutes;
        } elseif ($prev_minutes == false) {
            add_option('TimeLastUpdated', $now_minutes);
            $timestamp = $now_minutes;
        } else {
            $timestamp = $prev_minutes;
        }

        $prehash = $prehash.$timestamp;

        $sha1 = substr(sha1($prehash), 0, 32);

        return substr($sha1, 0, 8).'-'.substr($sha1, 8, 4).'-'.substr($sha1, 12, 4).'-'.substr($sha1, 16, 4).'-'.substr($sha1, 20, 12);
    }

    /**
     * The main function that actually sends a notification to OneSignal.
     */
    public static function send_notification_on_wp_post($new_status, $old_status, $post)
    {
        try {
            if (!function_exists('curl_init')) {
                onesignal_debug('Canceling send_notification_on_wp_post because curl_init() is not a defined function.');

                return;
            }

            $time_to_wait = self::get_sending_rate_limit_wait_time();
            if ($time_to_wait > 0) {
                set_transient('onesignal_transient_error', '<div class="error notice onesignal-error-notice">
                    <p><strong>OneSignal Push:</strong><em> Please try again in '.$time_to_wait.' seconds. Only one notification can be sent every '.ONESIGNAL_API_RATE_LIMIT_SECONDS.' seconds.</em></p>
                </div>', 86400);

                return;
            }

            $onesignal_wp_settings = OneSignal::get_onesignal_settings();

            /* Looks like on_save_post is called after transition_post_status so we'll have to check POST data in addition to post meta data */

            /* Settings related to creating a post involving the WordPress editor displaying the OneSignal meta box
             **********************************************************************************************************/

            /* Returns true if there is POST data */
            $was_posted = !empty($_POST);

            /* When this post was created or updated, the OneSignal meta box in the WordPress post editor screen was visible */
            $onesignal_meta_box_present = $was_posted && array_key_exists('onesignal_meta_box_present', $_POST) && $_POST['onesignal_meta_box_present'] == 'true';
            /* The checkbox "Send notification on post publish/update" on the OneSignal meta box is checked */
            $onesignal_meta_box_send_notification_checked = $was_posted && array_key_exists('send_onesignal_notification', $_POST) && $_POST['send_onesignal_notification'] == 'true';

            /* This is a scheduled post and the OneSignal meta box was present. */
            $post_metadata_was_onesignal_meta_box_present = (get_post_meta($post->ID, 'onesignal_meta_box_present', true) == true);
            /* This is a scheduled post and the user checked "Send a notification on post publish/update". */
            $post_metadata_was_send_notification_checked = (get_post_meta($post->ID, 'onesignal_send_notification', true) == true);

            /* Either we were just posted from the WordPress post editor form, or this is a scheduled notification and it was previously submitted from the post editor form */
            $posted_from_wordpress_editor = $onesignal_meta_box_present || $post_metadata_was_onesignal_meta_box_present;
            /* ********************************************************************************************************* */

            /* Settings related to creating a post outside of the WordPress editor NOT displaying the OneSignal meta box
             ************************************************************************************************************/

            /* OneSignal plugin setting "Automatically send a push notification when I create a post from 3rd party plugins"
             * If set to true, send only if *publishing* a post type *post* from *something other than the default WordPress editor*.
             * The filter hooks "onesignal_exclude_post" and "onesignal_include_post" can override this behavior as long as the option to automatically send from 3rd party plugins is set.
             */
            $settings_send_notification_on_non_editor_post_publish = $onesignal_wp_settings['notification_on_post_from_plugin'];
            $additional_custom_post_types_string = str_replace(' ', '', $onesignal_wp_settings['allowed_custom_post_types']);
            $additional_custom_post_types_array = array_filter(explode(',', $additional_custom_post_types_string));
            onesignal_debug('Additional allowed custom post types:', $additional_custom_post_types_string);
            $non_editor_post_publish_do_send_notification = $settings_send_notification_on_non_editor_post_publish &&
                                                        ($post->post_type == 'post' || in_array($post->post_type, $additional_custom_post_types_array)) &&
                                                        $old_status !== 'publish';
            /* ********************************************************************************************************* */

            if ($posted_from_wordpress_editor) {
                // Decide to send based on whether the checkbox "Send notification on post publish/update" is checked
                // This post may be scheduled or just submitted from the WordPress editor
                // Metadata may not be saved into post yet, so use $_POST form data if metadata not available
                $do_send_notification = ($was_posted && $onesignal_meta_box_send_notification_checked) ||
                                    (!$was_posted && $post_metadata_was_send_notification_checked);
            } else {
                // This was definitely not submitted via the WordPress editor
                // Decide to send based on whether the 3rd-party plugins setting is checked
                $do_send_notification = $non_editor_post_publish_do_send_notification;
            }

            if (has_filter('onesignal_include_post')) {
                if (apply_filters('onesignal_include_post', $new_status, $old_status, $post)) {
                    onesignal_debug('Will actually send a notification for this post because the filter opted to include the post.');
                    $do_send_notification = true;
                }
            }

            onesignal_debug('Post Status:', $old_status, '-->', $new_status);
            onesignal_debug_post($post);
            onesignal_debug('Has onesignal_include_post filter:', has_filter('onesignal_include_post'));
            onesignal_debug('    [onesignal_include_post Filter]', 'Filter Result:', apply_filters('onesignal_include_post', $new_status, $old_status, $post));
            onesignal_debug('Has onesignal_exclude_post filter:', has_filter('onesignal_exclude_post'));
            onesignal_debug('    [onesignal_exclude_post Filter]', 'Filter Result:', apply_filters('onesignal_exclude_post', $new_status, $old_status, $post));
            onesignal_debug('Posted from WordPress editor:', $posted_from_wordpress_editor);
            onesignal_debug('    [Posted from WordPress editor]', 'Just Posted Meta Box Present:', $onesignal_meta_box_present);
            onesignal_debug('    [Posted from WordPress editor]', 'Was Meta Box Ever Present:', $post_metadata_was_onesignal_meta_box_present);
            onesignal_debug('Editor Post Send:', $posted_from_wordpress_editor && $do_send_notification);
            onesignal_debug('    [Editor Post Send]', 'Meta Box Send Notification Just Checked:', $onesignal_meta_box_send_notification_checked);
            onesignal_debug('    [Editor Post Send]', 'Meta Box Send Notification Previously Checked:', $post_metadata_was_send_notification_checked);
            onesignal_debug('Non-Editor Post Send:', $non_editor_post_publish_do_send_notification);
            onesignal_debug('    [Non-Editor Post Send]', 'Auto Send Config Setting:', $settings_send_notification_on_non_editor_post_publish);
            onesignal_debug('    [Non-Editor Post Send]', 'Post Type:', ($post->post_type == 'post' || in_array($post->post_type, $additional_custom_post_types_array)), '('.$post->post_type.')');
            onesignal_debug('    [Non-Editor Post Send]', 'Old Post Status:', ($old_status !== 'publish'), '('.$old_status.')');
            onesignal_debug('Actually Sending Notification:', $do_send_notification);

            if ($do_send_notification) {
                /* Now that all settings are retrieved, and we are actually sending the notification, reset the post's metadata
                       * If this post is sent through a plugin in the future, existing metadata will interfere with the send condition logic
                       * If this post is re-sent through the WordPress editor, the metadata will be added back automatically
                      */
                update_post_meta($post->ID, 'onesignal_meta_box_present', false);
                update_post_meta($post->ID, 'onesignal_send_notification', false);
                onesignal_debug('Removed OneSignal metadata from post.');

                /* Some WordPress environments seem to be inconsistent about whether on_save_post is called before transition_post_status
                 * This sets the metadata back to true, and will cause a post to be sent even if the checkbox is not checked the next time
                 * We remove all related $_POST data to prevent this
                */
                if ($was_posted) {
                    if (array_key_exists('onesignal_meta_box_present', $_POST)) {
                        unset($_POST['onesignal_meta_box_present']);
                        onesignal_debug('Unset $_POST[\'onesignal_meta_box_present\']');
                    }
                    if (array_key_exists('send_onesignal_notification', $_POST)) {
                        unset($_POST['send_onesignal_notification']);
                        onesignal_debug('Unset $_POST[\'send_onesignal_notification\']');
                    }
                }

                $notif_content = OneSignalUtils::decode_entities(get_the_title($post->ID));

                $site_title = '';
                if ($onesignal_wp_settings['notification_title'] != '') {
                    $site_title = OneSignalUtils::decode_entities($onesignal_wp_settings['notification_title']);
                } else {
                    $site_title = OneSignalUtils::decode_entities(get_bloginfo('name'));
                }

                if (function_exists('qtrans_getLanguage')) {
                    try {
                        $qtransLang = qtrans_getLanguage();
                        $site_title = qtrans_use($qtransLang, $site_title, false);
                        $notif_content = qtrans_use($qtransLang, $notif_content, false);
                    } catch (Exception $e) {
                        onesignal_debug('Caught qTrans exception:', $e->getMessage());
                    }
                }

                $post_time = get_post_time('D M d Y G:i:', true, $post);

                if (!$post_time) {
                    error_log("OneSignal: Couldn't get post_time");

                    return;
                } else {
                    $post_time = $post_time.'00 GMT-0:00';
                }

                $old_uuid_array = get_post_meta($post->ID, 'uuid');
                $uuid = self::uuid($notif_content);
                update_post_meta($post->ID, 'uuid', $uuid);

                $fields = array(
                  'external_id' => $uuid,
                  'app_id' => $onesignal_wp_settings['app_id'],
                  'headings' => array('en' => $site_title),
                  'included_segments' => array('All'),
                  'isAnyWeb' => true,
                  'url' => get_permalink($post->ID),
                  'contents' => array('en' => $notif_content),
                );
                
                if ($new_status == 'future') {
                    if ($old_uuid_array && $old_uuid_array[0] != $uuid) {
                        self::cancel_scheduled_notification($post);
                    }
                    
                    $fields['send_after'] = $post_time;
                }

                $send_to_mobile_platforms = $onesignal_wp_settings['send_to_mobile_platforms'];
                if ($send_to_mobile_platforms == true) {
                    $fields['isIos'] = true;
                    $fields['isAndroid'] = true;
                }

                $config_utm_additional_url_params = $onesignal_wp_settings['utm_additional_url_params'];
                if (!empty($config_utm_additional_url_params)) {
                    $fields['url'] .= '?'.$config_utm_additional_url_params;
                }

                if (has_post_thumbnail($post->ID)) {
                    onesignal_debug('Post has featured image.');

                    $post_thumbnail_id = get_post_thumbnail_id($post->ID);
                    // Higher resolution (2x retina, + a little more) for the notification small icon
                    $thumbnail_sized_images_array = wp_get_attachment_image_src($post_thumbnail_id, array(192, 192), true);
                    // Much higher resolution for the notification large image
                    $large_sized_images_array = wp_get_attachment_image_src($post_thumbnail_id, 'large', true);

                    $config_use_featured_image_as_icon = $onesignal_wp_settings['showNotificationIconFromPostThumbnail'] == '1';
                    onesignal_debug('Post should use featured image for notification icon (small):', $config_use_featured_image_as_icon);
                    $config_use_featured_image_as_image = $onesignal_wp_settings['showNotificationImageFromPostThumbnail'] == '1';
                    onesignal_debug('Post should use featured image for notification image (large):', $config_use_featured_image_as_image);

                    // get the icon image from wordpress if it exists
                    if ($config_use_featured_image_as_icon) {
                        onesignal_debug('Featured post image thumbnail-sized array:', $thumbnail_sized_images_array);
                        $thumbnail_image = $thumbnail_sized_images_array[0];
                        // set the icon image for both chrome and firefox-1
                        $fields['chrome_web_icon'] = $thumbnail_image;
                        $fields['firefox_icon'] = $thumbnail_image;
                        onesignal_debug('Setting Chrome and Firefox notification icon to:', $thumbnail_image);
                    }
                    if ($config_use_featured_image_as_image) {
                        onesignal_debug('Featured post image large-sized array:', $large_sized_images_array);
                        $large_image = $large_sized_images_array[0];
                        $fields['chrome_web_image'] = $large_image;
                        onesignal_debug('Setting Chrome notification large image to:', $large_image);
                    }
                }

                if (has_filter('onesignal_send_notification')) {
                    onesignal_debug('Applying onesignal_send_notification filter.');
                    $fields = apply_filters('onesignal_send_notification', $fields, $new_status, $old_status, $post);
                    onesignal_debug('onesignal_send_notification filter $fields result:', $fields);

                    // If the filter adds "do_send_notification: false", do not send a notification
                    if (array_key_exists('do_send_notification', $fields) && $fields['do_send_notification'] == false) {
                        return;
                    }
                }

                if (defined('ONESIGNAL_DEBUG') || class_exists('WDS_Log_Post')) {
                    // http://blog.kettle.io/debugging-curl-requests-in-php/
                    ob_start();
                    $out = fopen('php://output', 'w');
                }

                $onesignal_post_url = 'https://onesignal.com/api/v1/notifications';

                if (defined('ONESIGNAL_DEBUG') && defined('ONESIGNAL_LOCAL')) {
                    $onesignal_post_url = 'https://localhost:3001/api/v1/notifications';
                }

                $onesignal_auth_key = $onesignal_wp_settings['app_rest_api_key'];

                $request = array(
    'headers' => array(
                  'content-type' => 'application/json;charset=utf-8',
                  'Authorization' => 'Basic '.$onesignal_auth_key,
        ),
    'body' => json_encode($fields),
    'timeout' => 60,
    );

                $response = wp_remote_post($onesignal_post_url, $request);

                if (is_wp_error($response) || !is_array($response) || !isset($response['body'])) {
                    $status = $response->get_error_code(); 				// custom code for WP_ERROR
                    $error_message = $response->get_error_message();
                    error_log('There was a '.$status.' error returned from OneSignal: '.$error_message);

                    return;
                }

                if (isset($response['body'])) {
                    $response_body = json_decode($response['body'], true);
                }

                if (isset($response['response'])) {
                    $status = $response['response']['code'];
                }

                update_post_meta($post->ID, 'response_body', json_encode($response_body));
                update_post_meta($post->ID, 'status', $status);

                if ($status != 200) {
                    error_log('There was a '.$status.' error sending your notification.');
                    if ($status != 0) {
                        set_transient('onesignal_transient_error', '<div class="error notice onesignal-error-notice">
                    <p><strong>OneSignal Push:</strong><em> There was a '.$status.' error sending your notification.</em></p>
                </div>', 86400);
                    } else {
                        // A 0 HTTP status code means the connection couldn't be established
                        set_transient('onesignal_transient_error', '<div class="error notice onesignal-error-notice">
                    <p><strong>OneSignal Push:</strong><em> There was an error establishing a network connection. Please make sure outgoing network connections from cURL are allowed.</em></p>
                </div>', 86400);
                    }
                } else {
                    if (!empty($response)) {
                        onesignal_debug('OneSignal API Raw Response:', $response);

                        // API can send a 200 OK even if the notification failed to send
                        if (isset($response['body'])) {
                            $response_body = json_decode($response['body'], true);

                            if (isset($response_body['recipients'])) {
                                $recipient_count = $response_body['recipients'];
                            } else {
                                error_log('OneSignal: recipients not set in response body');
                            }

                            if (isset($response_body['id'])) {
                                $notification_id = $response_body['id'];
                            } else {
                                error_log('OneSignal: notification id not set in response body');
                            }
                        } else {
                            error_log('OneSignal: body not set in HTTP response');
                        }

                        // updates meta so that recipient count is available for GET request from client
                        update_post_meta($post->ID, 'recipients', $recipient_count);

                        // updates meta for use in cancelling scheduled notifs
                        update_post_meta($post->ID, 'notification_id', $notification_id);

                        $sent_or_scheduled = array_key_exists('send_after', $fields) ? 'scheduled' : 'sent';
                        $config_show_notification_send_status_message = $onesignal_wp_settings['show_notification_send_status_message'] == '1';

                        if ($config_show_notification_send_status_message) {
                            if ($recipient_count != 0) {
                                set_transient('onesignal_transient_success', '<div class="components-notice is-success is-dismissible">
                  <div class="components-notice__content">
                  <p><strong>OneSignal Push:</strong><em> Successfully '.$sent_or_scheduled.' a notification to '.$recipient_count.' recipients. Go to your app\'s "Delivery" tab to check sent and scheduled messages: <a target="_blank" href="https://app.onesignal.com/apps/">https://app.onesignal.com/apps/</a></em></p>
                  </div>
                    </div>', 86400);
                            } else {
                                set_transient('onesignal_transient_success', '<div class="updated notice notice-success is-dismissible">
                        <p><strong>OneSignal Push:</strong><em>There were no recipients.</em></p>
                    </div>', 86400);
                            }
                        }
                    }
                }

                if (defined('ONESIGNAL_DEBUG') || class_exists('WDS_Log_Post')) {
                    fclose($out);
                    $debug_output = ob_get_clean();
                }

                self::update_last_sent_timestamp();

                return $response;
            }
        } catch (Exception $e) {
            onesignal_debug('Caught Exception:', $e->getMessage());
        }
    }

    public static function was_post_restored_from_trash($old_status, $new_status)
    {
        return $old_status === 'trash' && $new_status === 'publish';
    }

    public static function cancel_scheduled_notification($post)
    {
        $notification_id = get_post_meta($post->ID, 'notification_id', true);
        $onesignal_wp_settings = OneSignal::get_onesignal_settings();

        $onesignal_delete_url = 'https://onesignal.com/api/v1/notifications/'.$notification_id.'?app_id='.$onesignal_wp_settings['app_id'];
        $onesignal_auth_key = $onesignal_wp_settings['app_rest_api_key'];

        $request = array(
      'headers' => array(
                'content-type' => 'application/json;charset=utf-8',
                'Authorization' => 'Basic '.$onesignal_auth_key,
    ),
      'method' => 'DELETE',
      'timeout' => 60,
    );

        $response = wp_remote_get($onesignal_delete_url, $request);

        if (is_wp_error($response) || !is_array($response) || !isset($response['body'])) {
            $status = $response->get_error_code(); 				// custom code for WP_ERROR
            $error_message = $response->get_error_message();
            error_log("Couldn't cancel notification: There was a ".$status.' error returned from OneSignal: '.$error_message);

            return;
        }
    }

    public static function on_transition_post_status($new_status, $old_status, $post)
    {
        if ($post->post_type == 'wdslp-wds-log' || self::was_post_restored_from_trash($old_status, $new_status)) {
            // It's important not to call onesignal_debug() on posts of type wdslp-wds-log, otherwise each post will recursively generate 4 more posts
            return;
        }

        if ($new_status == 'future') {
            self::send_notification_on_wp_post($new_status, $old_status, $post);

            return;
        }

        if (has_filter('onesignal_include_post')) {
            onesignal_debug('Applying onesignal_include_post filter.');
            if (apply_filters('onesignal_include_post', $new_status, $old_status, $post)) {
                // If the filter returns "$do_include_post: true", always process this post
                onesignal_debug('Processing post because the filter opted to include the post.');
                self::send_notification_on_wp_post($new_status, $old_status, $post);

                return;
            }
        }

        if (has_filter('onesignal_exclude_post')) {
            onesignal_debug('Applying onesignal_exclude_post filter.');
            if (apply_filters('onesignal_exclude_post', $new_status, $old_status, $post)) {
                // If the filter returns "$do_exclude_post: false", do not process this post at all
                onesignal_debug('Not processing post because the filter opted to exclude the post.');

                return;
            }
        }

        if (!(empty($post) ||
        $new_status !== 'publish' ||
        $post->post_type == 'page')) {
            self::send_notification_on_wp_post($new_status, $old_status, $post);
        }
    }
}

?>
