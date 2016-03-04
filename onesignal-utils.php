<?php


class OneSignalUtils {

	/* If >= PHP 5.4, ENT_HTML401 | ENT_QUOTES will correctly decode most entities including both double and single quotes.
   In PHP 5.3, ENT_HTML401 does not exist, so we have to use `str_replace("&apos;","'", $value)` before feeding it to html_entity_decode(). */
	public static function decode_entities($string) {
		$HTML_ENTITY_DECODE_FLAGS = ENT_QUOTES;
		if (defined('ENT_HTML401')) {
			$HTML_ENTITY_DECODE_FLAGS = ENT_HTML401 | $HTML_ENTITY_DECODE_FLAGS;
		}
		return html_entity_decode(str_replace("&apos;", "'", $string), $HTML_ENTITY_DECODE_FLAGS, 'UTF-8');
	}

	public static function normalize($string) {
		$string = OneSignalUtils::decode_entities($string);
		$string = stripslashes($string);
		return $string;
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
}
?>