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
    $post_id = isset($_GET['post_id']) ?
            (filter_var($_GET['post_id'], FILTER_SANITIZE_NUMBER_INT))
            : '';

    if (is_null($post_id)) {
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

    echo wp_json_encode($data);

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
                          exit(1);
                          break;

                      case E_USER_WARNING:
                          break;

                      case E_USER_NOTICE || E_NOTICE:
                          break;

                      case E_STRICT:
                          break;

                      default:
                          break;
                  }

                    return true;
                } catch (Exception $ex) {
                    return true;
                }
            }

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
                        return false;
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

        if ($post->post_type === 'wdslp-wds-log') {
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
            return $post_id;
        }

	    // Verify that the nonce is valid.
        if (!wp_verify_nonce((isset($_POST[OneSignal_Admin::$SAVE_POST_NONCE_KEY]) ?
                sanitize_text_field($_POST[OneSignal_Admin::$SAVE_POST_NONCE_KEY]) :
                 ''
            ), OneSignal_Admin::$SAVE_POST_NONCE_ACTION)) {
            return $post_id;
        }

        /*
        * If this is an autosave, our form has not been submitted,
        * so we don't want to do anything.
        */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        if (array_key_exists('onesignal_meta_box_present', $_POST)) {
            update_post_meta($post_id, 'onesignal_meta_box_present', true);
        } else {
            update_post_meta($post_id, 'onesignal_meta_box_present', false);
        }

        /* Even though the meta box always contains the checkbox, if an HTML checkbox is not checked, it is not POSTed to the server */
        if (array_key_exists('send_onesignal_notification', $_POST)) {
            update_post_meta($post_id, 'onesignal_send_notification', true);
        } else {
            update_post_meta($post_id, 'onesignal_send_notification', false);
        }

        if (array_key_exists('onesignal_modify_title_and_content', $_POST)) {
            update_post_meta($post_id, 'onesignal_modify_title_and_content', true);
            update_post_meta($post_id, 'onesignal_notification_custom_heading', sanitize_text_field($_POST['onesignal_notification_custom_heading']));
            update_post_meta($post_id, 'onesignal_notification_custom_content', sanitize_text_field($_POST['onesignal_notification_custom_content']));
        } else {
            update_post_meta($post_id, 'onesignal_modify_title_and_content', false);
            update_post_meta($post_id, 'onesignal_notification_custom_heading', null);
            update_post_meta($post_id, 'onesignal_notification_custom_content', null);
        }

    }

    public static function add_onesignal_post_options()
    {
        // If there is an error or success message we should display, display it now
        function admin_notice_error()
	{
	    $allowed_html = [
		'div' => [
		   'class' => []
		],
		'strong' => [],
		'a' => [],
		'p' => [],
		'em' => []
	    ];
            $onesignal_transient_error = get_transient('onesignal_transient_error');
            if (!empty($onesignal_transient_error)) {
                delete_transient('onesignal_transient_error');
                echo wp_kses($onesignal_transient_error, $allowed_html);
            }

            $onesignal_transient_success = get_transient('onesignal_transient_success');
            if (!empty($onesignal_transient_success)) {
                delete_transient('onesignal_transient_success');
                echo wp_kses($onesignal_transient_success, $allowed_html);
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
        if ((get_post_meta($post->ID, 'onesignal_send_notification', true) === '1')) {
            $meta_box_checkbox_send_notification = true;
        } else {
            // We check the checkbox if: setting is enabled on Config page, post type is ONLY "post", and the post has not been published (new posts are status "auto-draft")
            $meta_box_checkbox_send_notification = ($settings_send_notification_on_wp_editor_post &&  // If setting is enabled
                                                $post->post_type === 'post' &&  // Post type must be type post for checkbox to be auto-checked
                                                !in_array($post->post_status, array('publish', 'private', 'trash', 'inherit'), true)); // Post is scheduled, incomplete, being edited, or is awaiting publication
        }

        if (has_filter('onesignal_meta_box_send_notification_checkbox_state')) {
            $meta_box_checkbox_send_notification = apply_filters('onesignal_meta_box_send_notification_checkbox_state', $post, $onesignal_wp_settings);
        }

        if ($onesignal_wp_settings['notification_title'] !== '') {
            $site_title = OneSignalUtils::decode_entities($onesignal_wp_settings['notification_title']);
        } else {
            $site_title = OneSignalUtils::decode_entities(get_bloginfo('name'));
        }

        $onesignal_customize_content_checked = (get_post_meta($post->ID, 'onesignal_modify_title_and_content', true) === '1');
        $onesignal_notification_custom_content = get_post_meta($post->ID, 'onesignal_notification_custom_content', true);
        $onesignal_notification_custom_heading = get_post_meta($post->ID, 'onesignal_notification_custom_heading', true);

        ?>

	    <input type="hidden" name="onesignal_meta_box_present" value="true"></input>
      <div id="onesignal_send_preference">
        <label>
          <input type="checkbox" id="send_onesignal_notification" name="send_onesignal_notification" value="true" <?php if ($meta_box_checkbox_send_notification) {
                  echo 'checked';
              } ?>></input>

          <?php if ($post->post_status === 'publish') {
              echo esc_attr('Send notification on '.$post_type.' update');
          } else {
              echo esc_attr('Send notification on '.$post_type.' publish');

         } ?>
        </label>
      </div>
      <label>
      <div id="onesignal_custom_contents_preferences">
        <input type="checkbox" id="onesignal_modify_title_and_content" value="true" name="onesignal_modify_title_and_content" <?php if ($onesignal_customize_content_checked) {
                  echo 'checked';
              } ?>></input> Customize notification content</label>

        <div id="onesignal_custom_contents" style="display:none;padding-top:10px;">
          <div>
            <label>Notification Title<br/>
            <input type="text" size="16" style="width:220px;" name="onesignal_notification_custom_heading" value="<?php
              echo esc_attr(OneSignalUtils::decode_entities($onesignal_notification_custom_heading));
             ?>" id="onesignal_notification_custom_heading" placeholder="<?php echo esc_attr(OneSignalUtils::decode_entities($onesignal_wp_settings['notification_title'])); ?>"></input>
            </label>
          </div>
          <div style="padding-top:10px">
            <label>Notification Text<br/>
            <input type="text" size="16" style="width:220px;" name="onesignal_notification_custom_content" value="<?php
              echo esc_attr(OneSignalUtils::decode_entities($onesignal_notification_custom_content));
              ?>" id="onesignal_notification_custom_content" placeholder="The Post's Current Title"></input>
            </label>
          </div>
        </div>
      </div>

      <script>
        jQuery('#onesignal_modify_title_and_content').change( function() {
            if(jQuery(this).is(":checked")) {
              jQuery('#onesignal_custom_contents').show();
              if(!jQuery('#onesignal_notification_custom_content').val()) {
                jQuery('#onesignal_notification_custom_content').val(jQuery("#title").val());
              }
            } else {
              jQuery('#onesignal_custom_contents').hide();
            }
        });
        if(!jQuery("#send_onesignal_notification").is(":checked")) {
          jQuery('#onesignal_modify_title_and_content').prop("disabled",true);
          jQuery('#onesignal_modify_title_and_content').prop("checked",false).change();
        }

        jQuery("#send_onesignal_notification").change( function() {
          if(jQuery(this).is(":checked")) {
            jQuery('#onesignal_modify_title_and_content').prop("disabled",false);
          } else {
            jQuery('#onesignal_modify_title_and_content').prop("disabled",true);
            jQuery('#onesignal_modify_title_and_content').prop("checked",false).change();
          }

        })
        jQuery('#onesignal_modify_title_and_content').change();
      </script>
    <?php
    }

    public static function save_config_page($config)
    {
        if (!OneSignalUtils::can_modify_plugin_settings()) {
            set_transient('onesignal_transient_error', '<div class="error notice onesignal-error-notice">
                    <p><strong>OneSignal Push:</strong><em> Only administrators are allowed to save plugin settings.</em></p>
                </div>', 86400);

            return;
        }

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
      'use_native_prompt',
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
      'persist_notifications',
    );
        OneSignal_Admin::saveStringSettings($onesignal_wp_settings, $config, $stringSettings);

        OneSignal::save_onesignal_settings($onesignal_wp_settings);

        return;
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
            $value = sanitize_text_field($config[$setting]);

            if ($setting === 'app_rest_api_key') {
                // Only save key if the value has been changed.
                // This prevents its masked value from becoming the value saved to the DB
                if (OneSignal::maskedRestApiKey($onesignal_wp_settings[$setting]) === $value)
                    continue;
            }

            $onesignal_wp_settings[$setting] = $value;
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
            OneSignal_Admin::save_config_page($_POST);
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
          $onesignal_wp_settings['app_id'] === '' ||
          $onesignal_wp_settings['app_rest_api_key'] === ''
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

        wp_enqueue_script('semantic-ui', plugin_dir_url(__FILE__).'views/javascript/semantic-ui.js', array('jquery'), OneSignal_Admin::$RESOURCES_VERSION);
        wp_enqueue_script('site', plugin_dir_url(__FILE__).'views/javascript/site-admin.js', array('jquery'), OneSignal_Admin::$RESOURCES_VERSION);
    }

    /**
     * Returns true if more than one notification has been sent in the last minute.
     */
    public static function get_sending_rate_limit_wait_time()
    {
        $last_send_time = get_option('onesignal.last_send_time');
        if ($last_send_time) {
            $time_elapsed_since_last_send = ONESIGNAL_API_RATE_LIMIT_SECONDS - (current_time('timestamp') - intval($last_send_time));
            if ($time_elapsed_since_last_send > 0) {
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
        $now_minutes = floor(time()/60);
        $prev_minutes = get_option('TimeLastUpdated');
        $prehash = (string) $title;
        $updatedAMinuteOrMoreAgo = $prev_minutes !== false && ($now_minutes - $prev_minutes) > 0;

        if ($updatedAMinuteOrMoreAgo || $prev_minutes === false) {
            update_option('TimeLastUpdated', $now_minutes);
            $timestamp = $now_minutes;
        } else {
            $timestamp = $prev_minutes;
        }

        $prehash = $prehash.$timestamp;
        $sha1 = substr(sha1($prehash), 0, 32);
        return substr($sha1, 0, 8).'-'.substr($sha1, 8, 4).'-'.substr($sha1, 12, 4).'-'.substr($sha1, 16, 4).'-'.substr($sha1, 20, 12);
    }

    public static function exec_post_request($onesignal_post_url, $request, $retry_count) {
        if ($retry_count === 0) {
            return NULL;
        }

        $response = wp_remote_post($onesignal_post_url, $request);

        if (is_wp_error($response) || !is_array($response) || !isset($response['body'])) {
            return self::exec_post_request($onesignal_post_url, $request, $retry_count-1);
        }

        return $response;
    }

    /**
     * The main function that actually sends a notification to OneSignal.
     */
    public static function send_notification_on_wp_post($new_status, $old_status, $post)
    {

        try {
            // quirk of Gutenberg editor leads to two passes if meta box is added
            // conditional removes first pass
            if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return;
            }

            /* Returns true if there is POST data */
            $was_posted = !empty($_POST);

	        // Verify that the nonce is valid.
            if ($was_posted && !wp_verify_nonce((
                isset($_POST[OneSignal_Admin::$SAVE_POST_NONCE_KEY]) ?
                sanitize_text_field($_POST[OneSignal_Admin::$SAVE_POST_NONCE_KEY]) :
                ''
            ), OneSignal_Admin::$SAVE_POST_NONCE_ACTION)) {
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

            /* When this post was created or updated, the OneSignal meta box in the WordPress post editor screen was visible */
            $onesignal_meta_box_present = $was_posted && isset($_POST['onesignal_meta_box_present'], $_POST['onesignal_meta_box_present']) && $_POST['onesignal_meta_box_present'] === 'true';
            /* The checkbox "Send notification on post publish/update" on the OneSignal meta box is checked */
            $onesignal_meta_box_send_notification_checked = $was_posted && array_key_exists('send_onesignal_notification', $_POST) && $_POST['send_onesignal_notification'] === 'true';

            /* Check if the checkbox "Customize notification content" is selected */
            $onesignal_customize_content_checked = $was_posted && array_key_exists('onesignal_modify_title_and_content', $_POST) && $_POST['onesignal_modify_title_and_content'] === 'true';

            // If this post is newly being created and if the user has chosen to customize the content
            $onesignal_customized_content = $onesignal_customize_content_checked || (get_post_meta($post->ID, 'onesignal_modify_title_and_content', true) === '1');

            if($was_posted && $onesignal_customized_content) {
                $onesignal_custom_notification_heading = sanitize_text_field($_POST['onesignal_notification_custom_heading']);
                $onesignal_custom_notification_content = sanitize_text_field($_POST['onesignal_notification_custom_content']);
            } else { // If this post was created previously (eg: scheduled), and the user had chosen to customize the content
                $onesignal_custom_notification_heading = get_post_meta($post->ID, 'onesignal_notification_custom_heading', true);
                $onesignal_custom_notification_content = get_post_meta($post->ID, 'onesignal_notification_custom_content', true);
            }

            /* This is a scheduled post and the OneSignal meta box was present. */
            $post_metadata_was_onesignal_meta_box_present = (get_post_meta($post->ID, 'onesignal_meta_box_present', true) === '1');
            /* This is a scheduled post and the user checked "Send a notification on post publish/update". */
            $post_metadata_was_send_notification_checked = (get_post_meta($post->ID, 'onesignal_send_notification', true) === '1');


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
            $non_editor_post_publish_do_send_notification = $settings_send_notification_on_non_editor_post_publish &&
                                                        ($post->post_type === 'post' || in_array($post->post_type, $additional_custom_post_types_array, true)) &&
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

            // Prevent notifying updates for non-public post types.
            if ( ! is_post_type_viewable( $post->post_type ) ) {
	            $do_send_notification = false;
            }

            if (has_filter('onesignal_include_post')) {
                if (apply_filters('onesignal_include_post', $new_status, $old_status, $post)) {
                    $do_send_notification = true;
                }
            }

            if ($do_send_notification) {
                /* Now that all settings are retrieved, and we are actually sending the notification, reset the post's metadata
                       * If this post is sent through a plugin in the future, existing metadata will interfere with the send condition logic
                       * If this post is re-sent through the WordPress editor, the metadata will be added back automatically
                      */
                update_post_meta($post->ID, 'onesignal_meta_box_present', false);
                update_post_meta($post->ID, 'onesignal_send_notification', false);
                update_post_meta($post->ID, 'onesignal_modify_title_and_content', false);
                update_post_meta($post->ID, 'onesignal_notification_custom_heading', null);
                update_post_meta($post->ID, 'onesignal_notification_custom_content', null);

                /* Some WordPress environments seem to be inconsistent about whether on_save_post is called before transition_post_status
                 * This sets the metadata back to true, and will cause a post to be sent even if the checkbox is not checked the next time
                 * We remove all related $_POST data to prevent this
                */
                if ($was_posted) {
                    if (array_key_exists('onesignal_meta_box_present', $_POST)) {
                        unset($_POST['onesignal_meta_box_present']);
                    }
                    if (array_key_exists('send_onesignal_notification', $_POST)) {
                        unset($_POST['send_onesignal_notification']);
                    }
                    if (array_key_exists('onesignal_modify_title_and_content', $_POST)) {
                        unset($_POST['onesignal_modify_title_and_content']);
                    }
                    if (array_key_exists('onesignal_notification_custom_heading', $_POST)) {
                        unset($_POST['onesignal_notification_custom_heading']);
                    }
                    if (array_key_exists('onesignal_notification_custom_content', $_POST)) {
                        unset($_POST['onesignal_notification_custom_content']);
                    }
                }


                $site_title = '';
                if ($onesignal_wp_settings['notification_title'] !== '') {
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
                        return new WP_Error('err', __( "OneSignal: There was a problem sending a notification"));
                    }
                }

                $notif_content = wp_strip_all_tags(OneSignalUtils::decode_entities(get_the_title($post->ID)));

                //Override content and/or title if the user has chosen to do so
                if($onesignal_customized_content) {
                  if($onesignal_custom_notification_heading) {
                    $site_title = $onesignal_custom_notification_heading;
                  }
                  if($onesignal_custom_notification_content) {
                    $notif_content = $onesignal_custom_notification_content;
                  }
                }

                $fields = array(
                    'external_id' => self::uuid($notif_content),
                    'app_id' => $onesignal_wp_settings['app_id'],
                    'data' => array("post_id" => $post->ID),
                    'headings' => array('en' => stripslashes_deep(wp_specialchars_decode($site_title))),
                    'included_segments' => array('All'),
                    'isAnyWeb' => true,
                    'url' => get_permalink($post->ID),
                    'contents' => array('en' => stripslashes_deep(wp_specialchars_decode($notif_content))),
                );

                $send_to_mobile_platforms = $onesignal_wp_settings['send_to_mobile_platforms'];
                if ($send_to_mobile_platforms === true) {
                    $fields['isIos'] = true;
                    $fields['isAndroid'] = true;
                }

                $config_utm_additional_url_params = $onesignal_wp_settings['utm_additional_url_params'];
                if (!empty($config_utm_additional_url_params)) {
                    $fields['url'] .= '?'.$config_utm_additional_url_params;
                }

                if (has_post_thumbnail($post->ID)) {
                    $post_thumbnail_id = get_post_thumbnail_id($post->ID);
                    // Higher resolution (2x retina, + a little more) for the notification small icon
                    $thumbnail_sized_images_array = wp_get_attachment_image_src($post_thumbnail_id, array(192, 192), true);
                    // Much higher resolution for the notification large image
                    $large_sized_images_array = wp_get_attachment_image_src($post_thumbnail_id, 'large', true);

                    $config_use_featured_image_as_icon = $onesignal_wp_settings['showNotificationIconFromPostThumbnail'] === true;
                    $config_use_featured_image_as_image = $onesignal_wp_settings['showNotificationImageFromPostThumbnail'] === true;

                    // get the icon image from wordpress if it exists
                    if ($config_use_featured_image_as_icon) {
                        $thumbnail_image = $thumbnail_sized_images_array[0];
                        // set the icon image for both chrome and firefox-1
                        $fields['chrome_web_icon'] = $thumbnail_image;
                        $fields['firefox_icon'] = $thumbnail_image;
                    }
                    if ($config_use_featured_image_as_image) {
                        $large_image = $large_sized_images_array[0];
                        $fields['chrome_web_image'] = $large_image;
                    }
                }

                if (has_filter('onesignal_send_notification')) {
                    $fields = apply_filters('onesignal_send_notification', $fields, $new_status, $old_status, $post);

                    // If the filter adds "do_send_notification: false", do not send a notification
                    if (array_key_exists('do_send_notification', $fields) && $fields['do_send_notification'] === false) {
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
                    'body' => wp_json_encode($fields),
                    'timeout' => 3,
                );

		$response = self::exec_post_request($onesignal_post_url, $request, 20);  // try 20 times

		if (is_null($response)) {
            set_transient('onesignal_transient_error', '<div class="error notice onesignal-error-notice">
                <p><strong>OneSignal Push:</strong><em> There was a problem sending your notification.</em></p>
                </div>', 86400);
            return;
        }

		if (isset($response['body'])) {
                    $response_body = json_decode($response['body'], true);
                }

                if (isset($response['response'])) {
                    $status = $response['response']['code'];
                }

                update_post_meta($post->ID, 'response_body', wp_json_encode($response_body));
                update_post_meta($post->ID, 'status', $status);

                if ($status !== 200) {
                    if ($status !== 0) {
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
                        $config_show_notification_send_status_message = $onesignal_wp_settings['show_notification_send_status_message'] === true;

                        if ($config_show_notification_send_status_message) {
                              $app_id = $onesignal_wp_settings['app_id'];
                              $delivery_link_text = ' Go to your app\'s Delivery tab to check sent messages: </em><a target="_blank" href="https://dashboard.onesignal.com/apps/' . $app_id . '/notifications">https://dashboard.onesignal.com/apps/' . $app_id . '/notifications</a><em>';
                              set_transient('onesignal_transient_success', '<div class="updated notice notice-success is-dismissible">
                <div class="components-notice__content">
                <p><strong>OneSignal Push:</strong><em> Successfully scheduled a notification.' . $delivery_link_text . '</em></p>
                </div>
                  </div>', 86400);
                        }
                    }
                }

                if (defined('ONESIGNAL_DEBUG') || class_exists('WDS_Log_Post')) {
                    fclose($out);
                }

                self::update_last_sent_timestamp();
                return $response;
            }
        } catch (Exception $e) {
            return new WP_Error('err', __( "OneSignal: There was a problem sending a notification"));
        } }

    public static function was_post_restored_from_trash($old_status, $new_status)
    {
        return $old_status === 'trash' && $new_status === 'publish';
    }

    public static function on_transition_post_status($new_status, $old_status, $post)
    {
        if ($post->post_type === 'wdslp-wds-log' ||
        self::was_post_restored_from_trash($old_status, $new_status)) {
            // It's important not to call onesignal_debug() on posts of type wdslp-wds-log, otherwise each post will recursively generate 4 more posts
            return;
        }
        if (has_filter('onesignal_include_post')) {
            if (apply_filters('onesignal_include_post', $new_status, $old_status, $post)) {
                // If the filter returns "$do_include_post: true", always process this post
                self::send_notification_on_wp_post($new_status, $old_status, $post);

                return;
            }
        }
        if (has_filter('onesignal_exclude_post')) {
            if (apply_filters('onesignal_exclude_post', $new_status, $old_status, $post)) {
                // If the filter returns "$do_exclude_post: false", do not process this post at all

                return;
            }
        }
        if (!(empty($post) ||
        $new_status !== 'publish' ||
        $post->post_type === 'page')) {
            self::send_notification_on_wp_post($new_status, $old_status, $post);
        }
    }
}
?>
