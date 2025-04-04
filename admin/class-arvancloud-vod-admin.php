// ...existing code...

/**
 * Sync videos from ArvanCloud VOD
 */
public function sync_videos() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'arvancloud_vod_sync_videos')) {
        wp_send_json_error(['message' => __('Security check failed', 'arvancloud-vod')]);
        return;
    }

    // Load API helper
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-arvancloud-vod-api.php';
    $api = new Arvancloud_Vod_API();

    // Test connection first
    if (!$api->test_connection()) {
        wp_send_json_error([
            'message' => __('Failed to connect to ArvanCloud VOD API. Please check your API key and try again.', 'arvancloud-vod')
        ]);
        return;
    }

    // Fetch videos with timeout parameter
    $result = $api->get_videos(1, 50);

    // Add better logging for debugging
    if (is_wp_error($result)) {
        error_log('ArvanCloud VOD sync error: ' . $result->get_error_message());
        wp_send_json_error([
            'message' => __('Failed to fetch videos from ArvanCloud VOD: ', 'arvancloud-vod') . $result->get_error_message()
        ]);
        return;
    }

    if (empty($result) || !isset($result['data'])) {
        wp_send_json_error([
            'message' => __('No videos found or invalid response format from ArvanCloud VOD', 'arvancloud-vod')
        ]);
        return;
    }

    $videos = $result['data'];
    $imported = 0;

    // Process videos
    foreach ($videos as $video) {
        // Your existing video import logic here
        // ...
        $imported++;
    }

    wp_send_json_success([
        'message' => sprintf(__('Successfully imported %d videos from ArvanCloud VOD', 'arvancloud-vod'), $imported),
        'count' => $imported
    ]);
}

// ...existing code...
