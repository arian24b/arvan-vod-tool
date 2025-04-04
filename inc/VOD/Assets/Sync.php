<?php
namespace WP_Arvan\Engine\VOD\Assets;

use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\Helper;

class Sync {
    /**
     * Synchronize videos from ArvanCloud VOD to WordPress media library
     *
     * @param bool $manual Whether this is a manual sync or automated
     * @return array Results of the synchronization process
     */
    public function sync_vod_to_wordpress($manual = false) {
        // Get all videos from ArvanCloud VOD
        $video = new Video();
        $channel_id = get_option('arvan-cloud-vod-selected_channel_id', '');

        if (empty($channel_id)) {
            $this->log_error('Sync failed: No default channel selected');
            return ['error' => __('No default channel selected', 'arvancloud-vod')];
        }

        $vod_videos = $video->showAll($channel_id);

        // Debug log the raw response to understand its structure
        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD Debug] API Response structure: ' . print_r(array_keys((array)$vod_videos), true));
        }

        // Check for API errors - only non-200 status codes are errors
        if (empty($vod_videos)) {
            $this->log_error('Failed to fetch videos: Empty response');
            return ['error' => __('Failed to fetch videos from ArvanCloud VOD', 'arvancloud-vod') . ' (Empty response)'];
        }

        // If status code exists and it's not 200 (success), it's an error
        if (isset($vod_videos['status_code']) && $vod_videos['status_code'] != 200) {
            $error_msg = 'Error code: ' . $vod_videos['status_code'];
            $this->log_error('Failed to fetch videos: ' . $error_msg);
            return ['error' => __('Failed to fetch videos from ArvanCloud VOD', 'arvancloud-vod') . ' (' . $error_msg . ')'];
        }

        // If we have a status code 200, it might be in the response structure but videos are in another array
        // This handles ArvanCloud API response format
        if (isset($vod_videos['status_code']) && $vod_videos['status_code'] == 200) {
            // Check if 'data' key exists in the response
            if (isset($vod_videos['data']) && is_array($vod_videos['data'])) {
                $this->log_info('Successfully fetched videos, found data key in response');
                $vod_videos = $vod_videos['data'];
            } else {
                $this->log_info('Successfully fetched videos with status 200, using response as is');
                // Keep the original response, but log that we're continuing with it
            }
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $error_details = [];

        // Add a check if there are videos to process
        if (empty($vod_videos) || !is_array($vod_videos) || count($vod_videos) == 0) {
            $this->log_error('No videos to process after parsing response');
            return ['error' => __('No videos found in the response from ArvanCloud VOD', 'arvancloud-vod')];
        }

        // Process each video
        foreach ($vod_videos as $vod_video) {
            // Skip the status code element if it exists
            if (!is_array($vod_video) || empty($vod_video['id'])) {
                continue;
            }

            // Check if this video is already in WordPress media library
            $existing = $this->find_existing_vod_video($vod_video['id']);

            if ($existing) {
                $skipped++;
                continue;
            }

            try {
                // Import the video
                $result = $this->import_vod_video($vod_video);

                if ($result) {
                    $imported++;
                    $this->log_info('Successfully imported video: ' . $vod_video['id'] . ' - ' . $vod_video['title']);
                } else {
                    $errors++;
                    $error_details[] = 'Failed to import: ' . $vod_video['id'] . ' - ' . $vod_video['title'];
                    $this->log_error('Failed to import video: ' . $vod_video['id'] . ' - ' . $vod_video['title']);
                }
            } catch (\Exception $e) {
                $errors++;
                $error_details[] = $e->getMessage();
                $this->log_error('Exception while importing video: ' . $e->getMessage());
            }
        }

        $result = [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'manual' => $manual
        ];

        if (!empty($error_details)) {
            $result['error_details'] = $error_details;
        }

        return $result;
    }

    /**
     * Find if a VOD video already exists in the WordPress media library
     * Improved to check multiple meta fields
     *
     * @param string $vod_id The ArvanCloud VOD video ID
     * @return int|false Post ID if found, false otherwise
     */
    private function find_existing_vod_video($vod_id) {
        // First check by acv_video_data
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'acv_video_data',
                    'value' => $vod_id,
                    'compare' => 'LIKE'
                ]
            ]
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }

        // If not found, try the ar-vod-media-id field
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'ar-vod-media-id',
                    'value' => $vod_id,
                    'compare' => '='
                ]
            ]
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }

        return false;
    }

    /**
     * Import a VOD video into WordPress media library
     * Improved error handling and metadata processing
     *
     * @param array $vod_video The VOD video data
     * @return int|false Post ID on success, false on failure
     */
    private function import_vod_video($vod_video) {
        // Get video details from VOD
        $video = new Video();
        $video_details = $video->show($vod_video['id']);

        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD Debug] Video details for import: ' . print_r(array_keys((array)$video_details), true));
        }

        if (!$video_details) {
            $this->log_error('Failed to get video details - empty response for ID: ' . $vod_video['id']);
            return false;
        }

        if (isset($video_details['status_code']) && $video_details['status_code'] != 200) {
            $this->log_error('Failed to get video details - error code: ' . $video_details['status_code'] . ' for ID: ' . $vod_video['id']);
            return false;
        }

        // Extract data from response if needed
        if (isset($video_details['data']) && is_array($video_details['data'])) {
            $this->log_info('Video details contains data key, extracting');
            $video_details = $video_details['data'];
        }

        // Validate required fields
        if (empty($video_details['title'])) {
            $this->log_error('Video details missing title for ID: ' . $vod_video['id']);
            $video_details['title'] = 'VOD Video ' . $vod_video['id']; // Default title
        }

        // Prepare attachment data
        $filename = sanitize_file_name($video_details['title']) . '.mp4';
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;

        // Create attachment post
        $attachment = [
            'post_mime_type' => 'video/mp4',
            'post_title' => $video_details['title'],
            'post_content' => isset($video_details['description']) ? $video_details['description'] : '',
            'post_status' => 'inherit',
            'guid' => isset($video_details['video_url']) ? $video_details['video_url'] : ''
        ];

        // Insert the attachment
        $attach_id = wp_insert_attachment($attachment, $file_path);

        if (is_wp_error($attach_id)) {
            $this->log_error('Failed to insert attachment: ' . $attach_id->get_error_message());
            return false;
        }

        // Generate metadata for the attachment
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Handle potentially missing fields
        $width = 0;
        $height = 0;
        $filesize = 0;

        if (isset($video_details['file_info']) && is_array($video_details['file_info'])) {
            if (isset($video_details['file_info']['video']['width'])) {
                $width = $video_details['file_info']['video']['width'];
            }
            if (isset($video_details['file_info']['video']['height'])) {
                $height = $video_details['file_info']['video']['height'];
            }
            if (isset($video_details['file_info']['general']['size'])) {
                $filesize = $video_details['file_info']['general']['size'];
            }
        }

        // Video URL fallback
        $video_url = '';
        if (isset($video_details['video_url'])) {
            $video_url = $video_details['video_url'];
        } elseif (isset($video_details['hls_playlist'])) {
            $video_url = $video_details['hls_playlist'];
        } elseif (isset($video_details['player_url'])) {
            $video_url = $video_details['player_url'];
        }

        // Create basic metadata
        $attach_data = [
            'file' => $filename,
            'width' => $width,
            'height' => $height,
            'filesize' => $filesize,
            'mime_type' => 'video/mp4',
            'url' => $video_url,
        ];

        wp_update_attachment_metadata($attach_id, $attach_data);

        // Save VOD data as post meta
        update_post_meta($attach_id, 'acv_video_data', $video_details);
        update_post_meta($attach_id, 'ar-vod-media-id', $vod_video['id']);

        // Don't save the actual file to local storage if that option is enabled
        $prevent_saving = get_option('arvan-cloud-vod-prevent-saving-video-local', 'no');
        if ($prevent_saving === 'yes') {
            // Mark as external URL only
            update_post_meta($attach_id, '_wp_attachment_metadata', ['filesize' => 0, 'file' => '']);
        }

        $this->log_info('Successfully created attachment in WordPress: ' . $attach_id . ' for VOD ID: ' . $vod_video['id']);
        return $attach_id;
    }

    /**
     * Schedule the sync cron job
     */
    public function schedule_sync() {
        if (!wp_next_scheduled('arvancloud_vod_sync_event')) {
            wp_schedule_event(time(), 'hourly', 'arvancloud_vod_sync_event');
            $this->log_info('Scheduled sync event');
        }
    }

    /**
     * Unschedule the sync cron job
     */
    public function unschedule_sync() {
        $timestamp = wp_next_scheduled('arvancloud_vod_sync_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'arvancloud_vod_sync_event');
            $this->log_info('Unscheduled sync event');
        }
    }

    /**
     * Get debug information about the sync process and API
     * Can be called from admin to troubleshoot issues
     *
     * @return array Debug information
     */
    public function get_debug_info() {
        $debug = [];

        // Check if API key is configured
        $api_key = get_option('arvan-cloud-vod-api-key', '');
        $debug['api_key_set'] = !empty($api_key);

        // Check channel configuration
        $channel_id = get_option('arvan-cloud-vod-selected_channel_id', '');
        $debug['channel_id'] = $channel_id;
        $debug['channel_set'] = !empty($channel_id);

        // Check cron schedule
        $next_sync = wp_next_scheduled('arvancloud_vod_sync_event');
        $debug['next_sync_scheduled'] = $next_sync ? date('Y-m-d H:i:s', $next_sync) : 'Not scheduled';

        // Test API connection
        $video = new Video();
        $test_result = $video->showAll($channel_id);

        if (empty($test_result)) {
            $debug['api_test'] = 'Failed - Empty response';
        } elseif (isset($test_result['status_code'])) {
            $debug['api_test'] = 'Response has status_code: ' . $test_result['status_code'];

            // If we have status 200 but no videos, check the response structure
            if ($test_result['status_code'] == 200) {
                $debug['response_has_data_key'] = isset($test_result['data']);
                if (isset($test_result['data'])) {
                    $debug['data_count'] = is_array($test_result['data']) ? count($test_result['data']) : 'Not an array';
                }
            }
        } else {
            // Direct array of videos
            $debug['api_test'] = 'Success - Direct video array';
            $debug['video_count'] = is_array($test_result) ? count($test_result) : 'Not an array';
        }

        // Include raw but limited response for inspection
        if (!empty($test_result)) {
            // Limit to first item or truncate to avoid huge logs
            if (is_array($test_result) && count($test_result) > 0) {
                $first_item = reset($test_result);
                $debug['sample_response'] = is_array($first_item) ? array_keys($first_item) : 'Not an array';
            } elseif (isset($test_result['data']) && is_array($test_result['data']) && count($test_result['data']) > 0) {
                $first_item = reset($test_result['data']);
                $debug['sample_response'] = is_array($first_item) ? array_keys($first_item) : 'Not an array';
            } else {
                $debug['sample_response'] = 'No items found in response';
            }
        }

        return $debug;
    }

    /**
     * Log error messages
     *
     * @param string $message The error message
     */
    private function log_error($message) {
        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD Error] ' . $message);
        }
    }

    /**
     * Log informational messages
     *
     * @param string $message The info message
     */
    private function log_info($message) {
        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD Info] ' . $message);
        }
    }
}
