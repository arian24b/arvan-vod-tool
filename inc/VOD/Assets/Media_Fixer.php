<?php
namespace WP_Arvan\Engine\VOD\Assets;

use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\Helper;

/**
 * Class to fix ArvanCloud VOD media issues
 */
class Media_Fixer {
    /**
     * Fix metadata issues for all ArvanCloud VOD videos
     *
     * @return array Results of the fix operation
     */
    public function fix_all_media() {
        global $wpdb;

        $fixed = 0;
        $errors = 0;
        $error_details = [];

        // Find all attachments with ar-vod-media-id but missing or incomplete acv_video_data
        $results = $wpdb->get_results(
            "SELECT p.ID, pm.meta_value
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'attachment'
            AND pm.meta_key = 'ar-vod-media-id'",
            ARRAY_A
        );

        if (empty($results)) {
            return [
                'fixed' => 0,
                'errors' => 0,
                'message' => 'No ArvanCloud VOD videos found that need fixing'
            ];
        }

        foreach ($results as $result) {
            $post_id = $result['ID'];
            $vod_id = $result['meta_value'];

            // Check if acv_video_data exists and is complete
            $video_data = get_post_meta($post_id, 'acv_video_data', true);

            $needs_update = false;

            if (empty($video_data)) {
                $needs_update = true;
            } elseif (!isset($video_data['video_url']) || !isset($video_data['player_url'])) {
                $needs_update = true;
            }

            if ($needs_update) {
                try {
                    // Fetch updated data from ArvanCloud VOD
                    $video = new Video();
                    $video_details = $video->show($vod_id);

                    if (!$video_details) {
                        $errors++;
                        $error_details[] = "Failed to fetch video details for ID: {$vod_id} (Post ID: {$post_id})";
                        continue;
                    }

                    if (isset($video_details['status_code']) && $video_details['status_code'] != 200) {
                        $errors++;
                        $error_details[] = "API returned error code {$video_details['status_code']} for ID: {$vod_id} (Post ID: {$post_id})";
                        continue;
                    }

                    // Extract data if needed
                    if (isset($video_details['data']) && is_array($video_details['data'])) {
                        $video_details = $video_details['data'];
                    }

                    // Update post meta
                    update_post_meta($post_id, 'acv_video_data', $video_details);

                    // Update attachment metadata
                    $width = isset($video_details['file_info']['video']['width']) ? $video_details['file_info']['video']['width'] : 0;
                    $height = isset($video_details['file_info']['video']['height']) ? $video_details['file_info']['video']['height'] : 0;
                    $filesize = isset($video_details['file_info']['general']['size']) ? $video_details['file_info']['general']['size'] : 0;

                    $attach_data = [
                        'file' => basename(get_attached_file($post_id)),
                        'width' => $width,
                        'height' => $height,
                        'filesize' => $filesize,
                        'mime_type' => 'video/mp4',
                        'url' => isset($video_details['video_url']) ? $video_details['video_url'] : '',
                    ];

                    wp_update_attachment_metadata($post_id, $attach_data);

                    // Update the post title if needed
                    if (isset($video_details['title']) && !empty($video_details['title'])) {
                        wp_update_post([
                            'ID' => $post_id,
                            'post_title' => $video_details['title']
                        ]);
                    }

                    $fixed++;
                } catch (\Exception $e) {
                    $errors++;
                    $error_details[] = "Exception when fixing video ID: {$vod_id} (Post ID: {$post_id}) - " . $e->getMessage();
                }
            }
        }

        return [
            'fixed' => $fixed,
            'errors' => $errors,
            'error_details' => $error_details,
            'message' => sprintf('Fixed %d videos with %d errors', $fixed, $errors)
        ];
    }
}
