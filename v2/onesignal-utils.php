<?php

defined( 'ABSPATH' ) or die('This page may not be accessed directly.');

class OneSignalUtils {

	/* If >= PHP 5.4, ENT_HTML401 | ENT_QUOTES will correctly decode most entities including both double and single quotes.
   In PHP 5.3, ENT_HTML401 does not exist, so we have to use `str_replace("&apos;","'", $value)` before feeding it to html_entity_decode(). */
	public static function decode_entities($string) {
		$HTML_ENTITY_DECODE_FLAGS = ENT_QUOTES;
		if (defined('ENT_HTML401')) {
			$HTML_ENTITY_DECODE_FLAGS = ENT_HTML401 | $HTML_ENTITY_DECODE_FLAGS;
		}
		return html_entity_decode(str_replace(['&apos;', '&#x27;', '&#39;', '&quot;'], '\'', $string), $HTML_ENTITY_DECODE_FLAGS, 'UTF-8');
	}

	public static function url_contains_parameter($text) {
	  if (array_key_exists('REQUEST_URI', $_SERVER)) {
      return strpos(sanitize_text_field($_SERVER['REQUEST_URI']), $text);
    }
  }

	public static function html_safe($string) {
		$HTML_ENTITY_DECODE_FLAGS = ENT_QUOTES;
		if (defined('ENT_HTML401')) {
			$HTML_ENTITY_DECODE_FLAGS = ENT_HTML401 | $HTML_ENTITY_DECODE_FLAGS;
		}
		return htmlspecialchars($string, $HTML_ENTITY_DECODE_FLAGS, 'UTF-8');
	}

	public static function contains($string, $substring) {
		return strpos($string, $substring) !== false;
	}

  /**
   * Describes whether the user can view "OneSignal Push" on the left sidebar.
   */
  public static function can_modify_plugin_settings() {
      // Only administrators are allowed to do this, see:
      //   https://codex.wordpress.org/Roles_and_Capabilities#delete_users
      return OneSignalUtils::is_admin_user();
  }

  /**
   * Describes whether the user can send notifications for a post.
   */
  public static function can_send_notifications() {
      // Only administrators are allowed to do this, see:
      //   https://codex.wordpress.org/Roles_and_Capabilities#delete_users
      return current_user_can('publish_posts') || current_user_can('edit_published_posts');
  }

  /**
   * To keep the plugin working the same as it was before, only allow administrators to perform important actions.
   */
  public static function is_admin_user() {
    // Only administrators are allowed to do this, see:
    //   https://codex.wordpress.org/Roles_and_Capabilities#delete_users
    return current_user_can('delete_users');
  }
}