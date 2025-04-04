<?php

/**
 * Class to handle API requests to ArvanCloud VOD
 *
 * @since      1.0.2
 * @package    Arvancloud_Vod
 * @subpackage Arvancloud_Vod/includes
 */
class Arvancloud_Vod_API {
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://napi.arvancloud.com/vod/2.0';

    /**
     * API key
     *
     * @var string
     */
    private $api_key = '';

    /**
     * Constructor
     */
    public function __construct() {
        $options = get_option('arvancloud_vod_options');
        $this->api_key = isset($options['api_key']) ? sanitize_text_field($options['api_key']) : '';
    }

    /**
     * Make an API request
     *
     * @param string $endpoint The API endpoint
     * @param string $method The HTTP method
     * @param array $data Optional data to send
     * @return array|WP_Error The response or error
     */
    public function request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('API key is not set', 'arvancloud-vod'));
        }

        // Validate endpoint format
        if (!preg_match('/^\/[a-zA-Z0-9\/_\-?&=.]+$/', $endpoint)) {
            return new WP_Error('invalid_endpoint', __('Invalid API endpoint format', 'arvancloud-vod'));
        }

        $url = $this->api_base_url . $endpoint;

        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Apikey ' . $this->api_key,
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
            'sslverify' => true,
        ];

        if (!empty($data) && $method !== 'GET') {
            $args['body'] = json_encode($data);
            $args['headers']['Content-Type'] = 'application/json';
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('ArvanCloud VOD API Error: ' . $response->get_error_message());
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($response_code < 200 || $response_code >= 300) {
            $error_message = isset($data['message']) ? $data['message'] : __('Unknown error occurred', 'arvancloud-vod');
            error_log('ArvanCloud VOD API Error (' . $response_code . '): ' . $error_message);
            return new WP_Error('api_error', $error_message, ['status' => $response_code]);
        }

        return $data;
    }

    /**
     * Fetch videos
     *
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array|WP_Error Videos or error
     */
    public function get_videos($page = 1, $per_page = 50) {
        return $this->request('/channels/videos?page=' . $page . '&per_page=' . $per_page);
    }

    /**
     * Check if API connection is working
     *
     * @return bool True if connected
     */
    public function test_connection() {
        $result = $this->request('/channels');
        return !is_wp_error($result);
    }
}
