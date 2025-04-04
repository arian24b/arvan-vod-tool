<?php
namespace WP_Arvan\Engine\VOD\Assets;

use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\VOD\Assets\Upload_Process;

class Add_Video {
	public function change_allowed_mime_types( array $plupload_init ) : array {

		if ( isset($_GET['page']) && $_GET['page'] == 'arvancloud-vod-videos-add') {
			$plupload_init['filters']['mime_types'] = [
				array(
					'title' => 'Videos',
					'extensions' => 'mp4,mov,m4v',
					'mime_types' => 'video/mp4,video/quicktime,video/x-m4v',
				)
			];
			$plupload_init['multipart_params']['uploader'] = 'r1c_vod_uploader';
		}

		return $plupload_init;
	}

	public function maybe_update_video( $post_ID = null ) : bool {

		$post_ID = $post_ID ? sanitize_text_field($post_ID) : sanitize_text_field( $_GET['post'] );


		if ( empty(get_post_meta( $post_ID, 'ar-vod-media-id', true )) ) {
			return false;
		}

		if ( !empty(get_post_meta( $post_ID, 'acv_video_data', true )) ) {
			return false;
		}

		$last_fetch = get_post_meta( $post_ID, 'acv_video_data_last_fetch', true);
		if ( !empty($last_fetch) ) {
			if ( time() - $last_fetch < 60 ) { // 1 minute
				return false;
			}
		}

		$video = new Upload_Process($post_ID);
		if($video->check()) {
			return true;
		}

		return false;
	}

	public function get_not_uploaded_videos() {
		// DOING_AJAX
		if ( (defined( 'DOING_AJAX' ) && DOING_AJAX || !current_user_can( 'edit_posts' ))) {
			return false;
		}


		global $wpdb;

		// if there is post meta ar-vod-media-id without post meta acv_video_data
		$sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'ar-vod-media-id' AND meta_value NOT IN (SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'acv_video_data')";

		$sql = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta
		ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
		LEFT JOIN $wpdb->postmeta AS mt1
		ON ( $wpdb->posts.ID = mt1.post_id
		AND mt1.meta_key = 'acv_video_data' )
		WHERE 1=1
		AND ( $wpdb->postmeta.meta_key = 'ar-vod-media-id'
		AND mt1.post_id IS NULL )
		AND $wpdb->posts.post_type = 'attachment'
		AND (($wpdb->posts.post_status <> 'trash'
		AND $wpdb->posts.post_status <> 'auto-draft'))
		GROUP BY $wpdb->posts.ID
		ORDER BY $wpdb->posts.post_date DESC";

		$results = $wpdb->get_results( $sql );



		if ( $wpdb->num_rows > 0 ) {

			foreach ( $results as $key => $result ) {
				if ($this->maybe_update_video($result->ID)) {
					unset($results[$key]);
				}
			}

			if (count($results) > 0) {

				add_action('admin_notices', function() use ($results) {
					$this->print_uploading_notice($results);
				});

			}
		}

		return;
	}

	public function print_uploading_notice( array $not_uploaded_videos ) : void {

		echo '<div class="notice notice-info is-dismissible">';
		echo '<p>';
		echo '<strong>';
		echo __("The following videos are being copied and Converted: <br>", 'arvancloud-vod');
		echo '</strong>';
		foreach ( $not_uploaded_videos as $video ) {
			echo '<a href="' . esc_url(get_edit_post_link( $video->ID )) . '">' . esc_html($video->post_title) . '</a> ';
			if ($video !== array_key_last($not_uploaded_videos)) {
				echo ' - ';
			}
		}
		echo '</p>';
		echo '</div>';
	}
}
