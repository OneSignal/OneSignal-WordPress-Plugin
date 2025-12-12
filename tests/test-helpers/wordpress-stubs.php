<?php
/**
 * WordPress function stubs for unit testing
 * These are minimal implementations to allow testing helper functions in isolation
 */

// Global storage for options (simulating WordPress database)
global $wp_options;
$wp_options = array();

function get_option($option, $default = false) {
    global $wp_options;
    return $wp_options[$option] ?? $default;
}

function update_option($option, $value) {
    global $wp_options;
    $wp_options[$option] = $value;
    return true;
}

function get_post_meta($post_id, $key = '', $single = false) {
    global $wp_post_meta;
    if (!isset($wp_post_meta[$post_id][$key])) {
        return $single ? '' : array();
    }
    return $single ? $wp_post_meta[$post_id][$key] : array($wp_post_meta[$post_id][$key]);
}

function update_post_meta($post_id, $meta_key, $meta_value) {
    global $wp_post_meta;
    $wp_post_meta[$post_id][$meta_key] = $meta_value;
    return true;
}

function sanitize_text_field($str) {
    return trim(strip_tags($str));
}

function wp_specialchars_decode($string, $quote_style = ENT_NOQUOTES) {
    return htmlspecialchars_decode($string, $quote_style);
}

function stripslashes_deep($value) {
    return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
}

function wp_strip_all_tags($string, $remove_breaks = false) {
    $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
    $string = strip_tags($string);
    if ($remove_breaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }
    return trim($string);
}

function strip_shortcodes($content) {
    return preg_replace('/\[.*?\]/', '', $content);
}

function wp_remote_retrieve_response_code($response) {
    return $response['response']['code'] ?? 0;
}

function is_wp_error($thing) {
    return ($thing instanceof WP_Error);
}

class WP_Error {
    public $errors = array();
    public $error_data = array();

    public function __construct($code = '', $message = '', $data = '') {
        if (empty($code)) {
            return;
        }
        $this->errors[$code][] = $message;
        if (!empty($data)) {
            $this->error_data[$code] = $data;
        }
    }

    public function get_error_message($code = '') {
        if (empty($code)) {
            $code = $this->get_error_code();
        }
        return $this->errors[$code][0] ?? '';
    }

    public function get_error_code() {
        $codes = array_keys($this->errors);
        return $codes[0] ?? '';
    }
}
