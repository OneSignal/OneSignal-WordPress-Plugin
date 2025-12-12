<?php
/**
 * HTTP mocking helper for integration tests using WP_Mock
 */

// Global storage for HTTP request mocks
global $wp_http_requests_mock;
$wp_http_requests_mock = array();

/**
 * Mock an HTTP request URL with a specific response
 * 
 * @param string $url The URL to mock
 * @param array|WP_Error $response The response array or WP_Error object
 */
function mock_http_request($url, $response) {
    global $wp_http_requests_mock;
    $wp_http_requests_mock[$url] = $response;
}

/**
 * Reset HTTP request mocks
 */
function reset_http_mocks() {
    global $wp_http_requests_mock;
    $wp_http_requests_mock = array();
}

// Mock wp_remote_post and wp_remote_request using WP_Mock
// These will be set up in each test's setUp() method

