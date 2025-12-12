<?php
/**
 * Extended WordPress function stubs for integration testing
 * Adds support for hooks, actions, and more complex WordPress functionality
 */

// Global storage for hooks, filters, and actions
global $wp_actions, $wp_filters, $wp_current_filter;
$wp_actions = array();
$wp_filters = array();
$wp_current_filter = array();

// Global storage for HTTP request mocks
global $wp_http_requests_mock;
$wp_http_requests_mock = array();

function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    return add_filter($tag, $function_to_add, $priority, $accepted_args);
}


function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    global $wp_filters;

    if (!isset($wp_filters[$tag])) {
        $wp_filters[$tag] = array();
    }

    if (!isset($wp_filters[$tag][$priority])) {
        $wp_filters[$tag][$priority] = array();
    }

    $wp_filters[$tag][$priority][] = array(
        'function' => $function_to_add,
        'accepted_args' => $accepted_args
    );

    return true;
}

function do_action($tag, ...$args) {
    global $wp_filters, $wp_current_filter, $wp_actions;

    if (!isset($wp_actions[$tag])) {
        $wp_actions[$tag] = 1;
    } else {
        ++$wp_actions[$tag];
    }

    if (!isset($wp_filters[$tag])) {
        return;
    }

    $wp_current_filter[] = $tag;

    ksort($wp_filters[$tag]);

    foreach ($wp_filters[$tag] as $priority => $functions) {
        foreach ($functions as $function_data) {
            $function = $function_data['function'];
            $accepted_args = $function_data['accepted_args'];

            $function_args = array_slice($args, 0, $accepted_args);
            call_user_func_array($function, $function_args);
        }
    }

    array_pop($wp_current_filter);
}


function apply_filters($tag, $value, ...$args) {
    global $wp_filters, $wp_current_filter;

    if (!isset($wp_filters[$tag])) {
        return $value;
    }

    $wp_current_filter[] = $tag;

    array_unshift($args, $value);

    ksort($wp_filters[$tag]);

    foreach ($wp_filters[$tag] as $priority => $functions) {
        foreach ($functions as $function_data) {
            $function = $function_data['function'];
            $accepted_args = $function_data['accepted_args'];

            $function_args = array_slice($args, 0, $accepted_args);
            $value = call_user_func_array($function, $function_args);
            $args[0] = $value;
        }
    }

    array_pop($wp_current_filter);

    return $value;
}

function has_filter($tag, $function_to_check = false) {
    global $wp_filters;

    if (!isset($wp_filters[$tag])) {
        return false;
    }

    if ($function_to_check === false) {
        return true;
    }

    foreach ($wp_filters[$tag] as $priority => $functions) {
        foreach ($functions as $function_data) {
            if ($function_data['function'] === $function_to_check) {
                return $priority;
            }
        }
    }

    return false;
}

function has_action($tag, $function_to_check = false) {
    return has_filter($tag, $function_to_check);
}

function did_action($tag) {
    global $wp_actions;
    return isset($wp_actions[$tag]) ? $wp_actions[$tag] : 0;
}

function wp_remote_post($url, $args = array()) {
    return wp_remote_request($url, array_merge($args, array('method' => 'POST')));
}

function wp_remote_request($url, $args = array()) {
    global $wp_http_requests_mock;

    // Check if we have a mock response for this URL
    if (isset($wp_http_requests_mock[$url])) {
        return $wp_http_requests_mock[$url];
    }

    // Default success response
    return array(
        'response' => array('code' => 200),
        'body' => json_encode(array('id' => 'test-notification-id'))
    );
}

function wp_remote_retrieve_body($response) {
    return $response['body'] ?? '';
}

function get_permalink($post_id = 0) {
    return 'https://example.com/post/' . $post_id;
}

function get_bloginfo($show = '') {
    if ($show === 'name') {
        return 'Test Blog';
    }
    return '';
}

function has_post_thumbnail($post_id = null) {
    return false;
}

function get_post_thumbnail_id($post_id = null) {
    return 0;
}

function wp_get_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false) {
    return array('https://example.com/image.jpg');
}

function current_user_can($capability, ...$args) {
    // For testing, assume user has all capabilities
    return true;
}

function wp_verify_nonce($nonce, $action = -1) {
    // For testing, assume nonces are valid
    return 1;
}

function wp_is_post_autosave($post) {
    return false;
}

function wp_is_post_revision($post) {
    return false;
}

function get_post($post = null, $output = OBJECT) {
    global $wp_posts;

    if (is_numeric($post) && isset($wp_posts[$post])) {
        return $wp_posts[$post];
    }

    return $post;
}

function delete_post_meta($post_id, $meta_key, $meta_value = '') {
    global $wp_post_meta;

    if (empty($meta_value)) {
        unset($wp_post_meta[$post_id][$meta_key]);
    } else {
        if (isset($wp_post_meta[$post_id][$meta_key]) && $wp_post_meta[$post_id][$meta_key] === $meta_value) {
            unset($wp_post_meta[$post_id][$meta_key]);
        }
    }

    return true;
}

function sanitize_url($url) {
    return esc_url_raw($url);
}

function esc_url_raw($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function check_admin_referer($action = -1, $query_arg = '_wpnonce') {
    // For testing, assume nonces are valid
    return 1;
}

function wp_die($message = '', $title = '', $args = array()) {
    throw new Exception($message);
}

function wp_cache_delete($key, $group = '') {
    return true;
}

function __($text, $domain = 'default') {
    return $text;
}

function plugin_dir_path($file) {
    return dirname($file) . '/';
}

define('OBJECT', 'OBJECT');

// Helper function to mock HTTP requests in tests
function mock_http_request($url, $response) {
    global $wp_http_requests_mock;
    $wp_http_requests_mock[$url] = $response;
}

// Helper function to reset all global state
function reset_wordpress_state() {
    global $wp_options, $wp_post_meta, $wp_actions, $wp_filters, $wp_current_filter, $wp_http_requests_mock, $wp_posts;
    $wp_options = array();
    $wp_post_meta = array();
    $wp_actions = array();
    $wp_filters = array();
    $wp_current_filter = array();
    $wp_http_requests_mock = array();
    $wp_posts = array();
}
