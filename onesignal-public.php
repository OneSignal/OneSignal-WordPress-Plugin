<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

function add_async_for_script($url)
{
	if (strpos($url, '#asyncload') === false)
	    return $url;
	else if (is_admin())
	    return str_replace('#asyncload', '', $url);
	else
	    return str_replace('#asyncload', '', $url)."' async='async";
}

class OneSignal_Public
{
    public function __construct()
    {
    }

    public static function init()
    {
        add_action('wp_head', array(__CLASS__, 'onesignal_header'), 10);
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'onesigal_amp_style' ) );
    }

    // For easier debugging of sites by identifying them as WordPress
    public static function insert_onesignal_stamp()
    {
        ?>
      <meta name="onesignal" content="wordpress-plugin"/>
    <?php
    }

    private static function valid_for_key($key, $array) {
        if(array_key_exists($key, $array) && $array[$key] !== ''){
            return true;
        }

        return false;
    }

    // Returns the OneSignal plugin URL path
    // Examples:
    //   /wp-content/plugins/onesignal-free-web-push-notifications
    //   /app/plugins/onesignal-free-web-push-notifications
    private static function getOneSignalPluginPath()
    {
        $path = parse_url(ONESIGNAL_PLUGIN_URL)['path'];
        return rtrim($path, '/');
    }

    public static function onesignal_header()
    {

        if ( self::onesignal_is_amp() ) {

            if ( function_exists( 'amp_is_legacy' ) && amp_is_legacy() ) {
                add_action( 'amp_post_template_body_open', array( __CLASS__, 'insert_amp_web_push' ) );
                add_action( 'amp_post_template_footer', array( __CLASS__, 'insert_amp_one_signal_widget' ) );
            } else {
                add_action( 'wp_body_open', array( __CLASS__, 'insert_amp_web_push' ) );
                add_action( 'wp_footer', array( __CLASS__, 'insert_amp_one_signal_widget' ) );
            }

            return;
        }

        $onesignal_wp_settings = OneSignal::get_onesignal_settings();

        if (array_key_exists('subdomain', $onesignal_wp_settings) && $onesignal_wp_settings['subdomain'] === '') {
            if (strpos(ONESIGNAL_PLUGIN_URL, 'http://localhost') === false && strpos(ONESIGNAL_PLUGIN_URL, 'http://127.0.0.1') === false) {
                $current_plugin_url = preg_replace("/(http:\/\/)/i", 'https://', ONESIGNAL_PLUGIN_URL);
            } else {
                $current_plugin_url = ONESIGNAL_PLUGIN_URL;
            }
            OneSignal_Public::insert_onesignal_stamp();
        } ?>
    <?php
    add_filter('clean_url', 'add_async_for_script', 11, 1);

    if (defined('ONESIGNAL_DEBUG') && defined('ONESIGNAL_LOCAL')) {
        wp_register_script('local_sdk', 'https://localhost:3001/sdks/OneSignalSDK.js#asyncload', array(), false, true);
        wp_enqueue_script('local_sdk');
    } else {
        wp_register_script('remote_sdk', 'https://cdn.onesignal.com/sdks/OneSignalSDK.js#asyncload', array(), false, true);
        wp_enqueue_script('remote_sdk');
    } ?>
    <script>

      window.OneSignal = window.OneSignal || [];

      OneSignal.push( function() {
        <?php
            if(array_key_exists('onesignal_sw_js', $onesignal_wp_settings)) {
                $swScope = self::getOneSignalPluginPath() . '/sdk_files/push/onesignal/';
                echo  "OneSignal.SERVICE_WORKER_UPDATER_PATH = 'OneSignalSDKUpdaterWorker.js';
                      OneSignal.SERVICE_WORKER_PATH = 'OneSignalSDKWorker.js';
                      OneSignal.SERVICE_WORKER_PARAM = { scope: '$swScope' };";
            } else {
                echo 'OneSignal.SERVICE_WORKER_UPDATER_PATH = "OneSignalSDKUpdaterWorker.js.php";
                      OneSignal.SERVICE_WORKER_PATH = "OneSignalSDKWorker.js.php";
                      OneSignal.SERVICE_WORKER_PARAM = { scope: "/" };';
            }
        ?>

        <?php
        if (self::valid_for_key('default_url', $onesignal_wp_settings)) {
            echo 'OneSignal.setDefaultNotificationUrl("'.esc_url($onesignal_wp_settings['default_url']).'");';
        } else {
            echo 'OneSignal.setDefaultNotificationUrl("'.esc_url(get_site_url())."\");\n";
        } ?>
        var oneSignal_options = {};
        window._oneSignalInitOptions = oneSignal_options;

        <?php
        echo "oneSignal_options['wordpress'] = true;\n";
        echo "oneSignal_options['appId'] = '".esc_html($onesignal_wp_settings['app_id'])."';\n";
        echo "oneSignal_options['allowLocalhostAsSecureOrigin'] = true;\n";

        if (array_key_exists('use_http_permission_request', $onesignal_wp_settings) && $onesignal_wp_settings['use_http_permission_request'] === true) {
            echo "oneSignal_options['httpPermissionRequest'] = { };\n";
            echo "oneSignal_options['httpPermissionRequest']['enable'] = true;\n";
        }

        if (array_key_exists('send_welcome_notification', $onesignal_wp_settings) && $onesignal_wp_settings['send_welcome_notification'] === true) {
            echo "oneSignal_options['welcomeNotification'] = { };\n";
            echo "oneSignal_options['welcomeNotification']['title'] = \"".esc_html($onesignal_wp_settings['welcome_notification_title'])."\";\n";
            echo "oneSignal_options['welcomeNotification']['message'] = \"".esc_html($onesignal_wp_settings['welcome_notification_message'])."\";\n";
            if ($onesignal_wp_settings['welcome_notification_url'] !== '') {
                echo "oneSignal_options['welcomeNotification']['url'] = \"".esc_html($onesignal_wp_settings['welcome_notification_url'])."\";\n";
            }
        } else {
            echo "oneSignal_options['welcomeNotification'] = { };\n";
            echo "oneSignal_options['welcomeNotification']['disable'] = true;\n";
        }

        if (self::valid_for_key('subdomain', $onesignal_wp_settings)) {
            echo "oneSignal_options['subdomainName'] = \"".esc_html($onesignal_wp_settings['subdomain'])."\";\n";
        } else {
            echo "oneSignal_options['path'] = \"".esc_html($current_plugin_url)."sdk_files/\";\n";
        }

        if ($onesignal_wp_settings['safari_web_id']) {
            echo "oneSignal_options['safari_web_id'] = \"".esc_html($onesignal_wp_settings['safari_web_id'])."\";\n";
        }

        if (array_key_exists('persist_notifications', $onesignal_wp_settings) && $onesignal_wp_settings['persist_notifications'] === 'platform-default') {
            echo "oneSignal_options['persistNotification'] = false;\n";
        } elseif (array_key_exists('persist_notifications', $onesignal_wp_settings) && $onesignal_wp_settings['persist_notifications'] === 'yes-all') {
            echo "oneSignal_options['persistNotification'] = true;\n";
        }

        echo "oneSignal_options['promptOptions'] = { };\n";
        if (array_key_exists('prompt_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings['prompt_customize_enable'] === true) {
            if (self::valid_for_key('prompt_action_message', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['actionMessage'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings["prompt_action_message"]))."\";\n"; 
            }
            if (self::valid_for_key('prompt_example_notification_title_desktop', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['exampleNotificationTitleDesktop'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_example_notification_title_desktop']))."\";\n";
            }
            if (self::valid_for_key('prompt_example_notification_message_desktop', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['exampleNotificationMessageDesktop'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_example_notification_message_desktop']))."\";\n";
            }
            if (self::valid_for_key('prompt_example_notification_title_mobile', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['exampleNotificationTitleMobile'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_example_notification_title_mobile']))."\";\n";
            }
            if (self::valid_for_key('prompt_example_notification_message_mobile', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['exampleNotificationMessageMobile'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_example_notification_message_mobile']))."\";\n";
            }
            if (self::valid_for_key('prompt_example_notification_caption', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['exampleNotificationCaption'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_example_notification_caption']))."\";\n";
            }
            if (self::valid_for_key('prompt_accept_button_text', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['acceptButtonText'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_accept_button_text']))."\";\n";
            }
            if (self::valid_for_key('prompt_cancel_button_text', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['cancelButtonText'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_cancel_button_text']))."\";\n";
            }
            if (self::valid_for_key('prompt_site_name', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['siteName'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_site_name']))."\";\n";
            }
            if (self::valid_for_key('prompt_auto_accept_title', $onesignal_wp_settings)) {
                echo "oneSignal_options['promptOptions']['autoAcceptTitle'] = \"".OneSignalUtils::decode_entities(esc_html($onesignal_wp_settings['prompt_auto_accept_title']))."\";\n";
            }
        }

        if (array_key_exists('notifyButton_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_enable'] === true) {
            echo "oneSignal_options['notifyButton'] = { };\n";
            echo "oneSignal_options['notifyButton']['enable'] = true;\n";

            if (self::valid_for_key('notifyButton_position', $onesignal_wp_settings)) {
                echo "oneSignal_options['notifyButton']['position'] = '".esc_html($onesignal_wp_settings['notifyButton_position'])."';\n";
            }
            if (self::valid_for_key('notifyButton_theme', $onesignal_wp_settings)) {
                echo "oneSignal_options['notifyButton']['theme'] = '".esc_html($onesignal_wp_settings['notifyButton_theme'])."';\n";
            }
            if (self::valid_for_key('notifyButton_size', $onesignal_wp_settings)) {
                echo "oneSignal_options['notifyButton']['size'] = '".esc_html($onesignal_wp_settings['notifyButton_size'])."';\n";
            }

            if (array_key_exists('notifyButton_showAfterSubscribed', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_showAfterSubscribed'] !== true) {
                echo "oneSignal_options['notifyButton']['displayPredicate'] = function() {
              return OneSignal.isPushNotificationsEnabled()
                      .then(function(isPushEnabled) {
                          return !isPushEnabled;
                      });
            };\n";
            }

            if (array_key_exists('use_modal_prompt', $onesignal_wp_settings) && $onesignal_wp_settings['use_modal_prompt'] === true) {
                echo "oneSignal_options['notifyButton']['modalPrompt'] = true;\n";
            }

            if (array_key_exists('notifyButton_showcredit', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_showcredit'] === true) {
                echo "oneSignal_options['notifyButton']['showCredit'] = true;\n";
            } else {
                echo "oneSignal_options['notifyButton']['showCredit'] = false;\n";
            }

            if (array_key_exists('notifyButton_customize_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_enable'] === true) {
                echo "oneSignal_options['notifyButton']['text'] = {};\n";
                if (self::valid_for_key('notifyButton_tip_state_unsubscribed', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['tip.state.unsubscribed'] = '".esc_html($onesignal_wp_settings['notifyButton_tip_state_unsubscribed'])."';\n";
                }
                if (self::valid_for_key('notifyButton_tip_state_subscribed', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['tip.state.subscribed'] = '".esc_html($onesignal_wp_settings['notifyButton_tip_state_subscribed'])."';\n";
                }
                if (self::valid_for_key('notifyButton_tip_state_blocked', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['tip.state.blocked'] = '".esc_html($onesignal_wp_settings['notifyButton_tip_state_blocked'])."';\n";
                }
                if (self::valid_for_key('notifyButton_message_action_subscribed', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['message.action.subscribed'] = '".esc_html($onesignal_wp_settings['notifyButton_message_action_subscribed'])."';\n";
                }
                if (self::valid_for_key('notifyButton_message_action_resubscribed', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['message.action.resubscribed'] = '".esc_html($onesignal_wp_settings['notifyButton_message_action_resubscribed'])."';\n";
                }
                if (self::valid_for_key('notifyButton_message_action_unsubscribed', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['message.action.unsubscribed'] = '".esc_html($onesignal_wp_settings['notifyButton_message_action_unsubscribed'])."';\n";
                }
                if (self::valid_for_key('notifyButton_dialog_main_title', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['dialog.main.title'] = '".esc_html($onesignal_wp_settings['notifyButton_dialog_main_title'])."';\n";
                }
                if (self::valid_for_key('notifyButton_dialog_main_button_subscribe', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['dialog.main.button.subscribe'] = '".esc_html($onesignal_wp_settings['notifyButton_dialog_main_button_subscribe'])."';\n";
                }
                if (self::valid_for_key('notifyButton_dialog_main_button_unsubscribe', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['dialog.main.button.unsubscribe'] = '".esc_html($onesignal_wp_settings['notifyButton_dialog_main_button_unsubscribe'])."';\n";
                }
                if (self::valid_for_key('notifyButton_dialog_blocked_title', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['dialog.blocked.title'] = '".esc_html($onesignal_wp_settings['notifyButton_dialog_blocked_title'])."';\n";
                }
                if (self::valid_for_key('notifyButton_dialog_blocked_message', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['text']['dialog.blocked.message'] = '".esc_html($onesignal_wp_settings['notifyButton_dialog_blocked_message'])."';\n";
                }
            }

            if (array_key_exists('notifyButton_customize_colors_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_colors_enable']) {
                echo "oneSignal_options['notifyButton']['colors'] = {};\n";
                if (self::valid_for_key('notifyButton_color_background', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['circle.background'] = '".esc_html($onesignal_wp_settings['notifyButton_color_background'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_foreground', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['circle.foreground'] = '".esc_html($onesignal_wp_settings['notifyButton_color_foreground'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_badge_background', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['badge.background'] = '".esc_html($onesignal_wp_settings['notifyButton_color_badge_background'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_badge_foreground', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['badge.foreground'] = '".esc_html($onesignal_wp_settings['notifyButton_color_badge_foreground'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_badge_border', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['badge.bordercolor'] = '".esc_html($onesignal_wp_settings['notifyButton_color_badge_border'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_pulse', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['pulse.color'] = '".esc_html($onesignal_wp_settings['notifyButton_color_pulse'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_popup_button_background', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.background'] = '".esc_html($onesignal_wp_settings['notifyButton_color_popup_button_background'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_popup_button_background_hover', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.background.hovering'] = '".esc_html($onesignal_wp_settings['notifyButton_color_popup_button_background_hover'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_popup_button_background_active', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.background.active'] = '".esc_html($onesignal_wp_settings['notifyButton_color_popup_button_background_active'])."';\n";
                }
                if (self::valid_for_key('notifyButton_color_popup_button_color', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['colors']['dialog.button.foreground'] = '".esc_html($onesignal_wp_settings['notifyButton_color_popup_button_color'])."';\n";
                }
            }

            if (array_key_exists('notifyButton_customize_offset_enable', $onesignal_wp_settings) && $onesignal_wp_settings['notifyButton_customize_offset_enable'] === true) {
                echo "oneSignal_options['notifyButton']['offset'] = {};\n";
                if (self::valid_for_key('notifyButton_offset_bottom', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['offset']['bottom'] = '".esc_html($onesignal_wp_settings['notifyButton_offset_bottom'])."';\n";
                }
                if (self::valid_for_key('notifyButton_offset_left', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['offset']['left'] = '".esc_html($onesignal_wp_settings['notifyButton_offset_left'])."';\n";
                }
                if (self::valid_for_key('notifyButton_offset_right', $onesignal_wp_settings)) {
                    echo "oneSignal_options['notifyButton']['offset']['right'] = '".esc_html($onesignal_wp_settings['notifyButton_offset_right'])."';\n";
                }
            }
        }

        $use_custom_sdk_init = $onesignal_wp_settings['use_custom_sdk_init'];
        if (!$use_custom_sdk_init) {
            if (has_filter('onesignal_initialize_sdk')) {
                if (apply_filters('onesignal_initialize_sdk', $onesignal_wp_settings)) {
                    // If the filter returns "$do_initialize_sdk: true", initialize the web SDK
              ?>
              OneSignal.init(window._oneSignalInitOptions);
              <?php
                } else {
                    ?>
              /* OneSignal: onesignal_initialize_sdk filter preventing SDK initialization. */
              <?php
                }
            } else {
                ?>
                OneSignal.init(window._oneSignalInitOptions);
                <?php
            }

            if (array_key_exists('prompt_auto_register', $onesignal_wp_settings) && $onesignal_wp_settings['prompt_auto_register'] === true) {
                    echo "OneSignal.showSlidedownPrompt();";
            }

            if (array_key_exists('use_native_prompt', $onesignal_wp_settings) && $onesignal_wp_settings['use_native_prompt'] === true) {
                echo "OneSignal.showNativePrompt();";
            }

        } else {
            ?>
          /* OneSignal: Using custom SDK initialization. */
          <?php
        } ?>
      });

      function documentInitOneSignal() {
        var oneSignal_elements = document.getElementsByClassName("OneSignal-prompt");

        <?php
        if (array_key_exists('use_modal_prompt', $onesignal_wp_settings) && $onesignal_wp_settings['use_modal_prompt'] === true) {
            echo "var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications', {modalPrompt: true}]); event.preventDefault(); };";
        } else {
            echo "var oneSignalLinkClickHandler = function(event) { OneSignal.push(['registerForPushNotifications']); event.preventDefault(); };";
        } ?>
        for(var i = 0; i < oneSignal_elements.length; i++)
          oneSignal_elements[i].addEventListener('click', oneSignalLinkClickHandler, false);
      }

      if (document.readyState === 'complete') {
           documentInitOneSignal();
      }
      else {
           window.addEventListener("load", function(event){
               documentInitOneSignal();
          });
      }
    </script>
<?php
    }

    /**
     * Enqueues style for onesignal push notification button.
     *
     * @return void
     */
    public static function onesigal_amp_style() {
        if ( ! self::onesignal_is_amp() ) {
           return;
        }

        wp_enqueue_style( 'onesignal-amp', plugin_dir_url( __FILE__ ) . 'css/onsignal-amp-style.css', '', '0.1' );
    }

    /**
     * OneSignal AMP endpoint.
     *
     * @return boolean
     */
    public static function onesignal_is_amp() {

        $is_amp = false;

        if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
            $is_amp = true;
        }

        /**
         * Filter to modify AMP check.
         */
        return apply_filters( 'onesignal_is_amp', $is_amp );
    }

    /**
     * Returns One Signal App ID
     *
     * @return int | false return one singal app ID false otherwise.
     */
    public static function get_onesignal_app_id() {
        $onesignal_wp_settings = OneSignal::get_onesignal_settings();
        $onesignal_app_id      = $onesignal_wp_settings['app_id'];
        return $onesignal_app_id;
    }

    /**
     * Add amp-web-push component.
     *
     * @return html
     */
    public static function insert_amp_web_push() {

        $onesignal_app_id = self::get_onesignal_app_id();

        if ( empty( $onesignal_app_id ) ) {
            return;
        }

        $one_signal_sdk_files_url = plugin_dir_url( __FILE__ ) . 'sdk_files/';

        $helper_iframe_url     = $one_signal_sdk_files_url . 'amp-helper-frame.html?appId=' . $onesignal_app_id;
        $permission_dialog_url = $one_signal_sdk_files_url . 'amp-permission-dialog.html?appId=' . $onesignal_app_id;
        $service_worker_url    = $one_signal_sdk_files_url . 'OneSignalSDKWorker.js.php?appId=' . $onesignal_app_id;

        echo sprintf(
            '<amp-web-push id="amp-web-push" layout="nodisplay" helper-iframe-url="%1$s" permission-dialog-url="%2$s" service-worker-url="%3$s"></amp-web-push>',
            esc_url( $helper_iframe_url ),
            esc_url( $permission_dialog_url ),
            esc_url( $service_worker_url )
        );
    }

    /**
     * Add AMP webpush widget.
     */
    public static function insert_amp_one_signal_widget() {
        $onesignal_app_id = self::get_onesignal_app_id();

        if ( empty( $onesignal_app_id ) ) {
            return;
        }
        ?>
        <!-- A subscription widget -->
        <amp-web-push-widget visibility="unsubscribed" layout="fixed" width="245" height="45">
            <button class="subscribe has-background has-text-color" on="tap:amp-web-push.subscribe">
                <svg class="onesignal-bell-svg" xmlns="http://www.w3.org/2000/svg" width="99.7" height="99.7" viewBox="0 0 99.7 99.7" style="filter: drop-shadow(0 2px 4px rgba(34,36,38,0.35));; -webkit-filter: drop-shadow(0 2px 4px rgba(34,36,38,0.35));;"><circle class="background" cx="49.9" cy="49.9" r="49.9" style=""></circle><path class="foreground" d="M50.1 66.2H27.7s-2-.2-2-2.1c0-1.9 1.7-2 1.7-2s6.7-3.2 6.7-5.5S33 52.7 33 43.3s6-16.6 13.2-16.6c0 0 1-2.4 3.9-2.4 2.8 0 3.8 2.4 3.8 2.4 7.2 0 13.2 7.2 13.2 16.6s-1 11-1 13.3c0 2.3 6.7 5.5 6.7 5.5s1.7.1 1.7 2c0 1.8-2.1 2.1-2.1 2.1H50.1zm-7.2 2.3h14.5s-1 6.3-7.2 6.3-7.3-6.3-7.3-6.3z" style=""></path><ellipse class="stroke" cx="49.9" cy="49.9" rx="37.4" ry="36.9" style=""></ellipse></svg>
                <span class="tooltiptext"><?php esc_html_e( 'Subscribe to notifications' ); ?></span>
            </button>
        </amp-web-push-widget>

        <!-- An unsubscription widget -->
        <amp-web-push-widget visibility="subscribed" layout="fixed" width="230" height="45">
            <button class="unsubscribe has-background has-text-color" on="tap:amp-web-push.unsubscribe">
                <svg class="onesignal-bell-svg" xmlns="http://www.w3.org/2000/svg" width="99.7" height="99.7" viewBox="0 0 99.7 99.7" style="filter: drop-shadow(0 2px 4px rgba(34,36,38,0.35));; -webkit-filter: drop-shadow(0 2px 4px rgba(34,36,38,0.35));;"><circle class="background" cx="49.9" cy="49.9" r="49.9" style=""></circle><path class="foreground" d="M50.1 66.2H27.7s-2-.2-2-2.1c0-1.9 1.7-2 1.7-2s6.7-3.2 6.7-5.5S33 52.7 33 43.3s6-16.6 13.2-16.6c0 0 1-2.4 3.9-2.4 2.8 0 3.8 2.4 3.8 2.4 7.2 0 13.2 7.2 13.2 16.6s-1 11-1 13.3c0 2.3 6.7 5.5 6.7 5.5s1.7.1 1.7 2c0 1.8-2.1 2.1-2.1 2.1H50.1zm-7.2 2.3h14.5s-1 6.3-7.2 6.3-7.3-6.3-7.3-6.3z" style=""></path><ellipse class="stroke" cx="49.9" cy="49.9" rx="37.4" ry="36.9" style=""></ellipse></svg>
                <span class="tooltiptext"><?php esc_html_e( 'Your\'e subscribed to notifications' ); ?></span>
            </button>
        </amp-web-push-widget>
        <?php
    }
}
