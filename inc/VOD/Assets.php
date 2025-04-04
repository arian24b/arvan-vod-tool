<?php
namespace WP_Arvan\Engine\VOD;


use WP_Arvan\Engine\VOD\Assets\Media_Action;
use WP_Arvan\Engine\API\VOD\Video;

class Assets {


	public function upload_wp_attachment( $post_ids, $bulk ) {

		$count = 0;
		$errors = 0;

		foreach ( $post_ids as $post_id ) {
			//get video url by post id wordpress
			$video_url = wp_get_attachment_url( $post_id );
			$result = (new Video)->create(
				[
					'title' => get_the_title( $post_id ),
					'convert_mode'	=> 'auto',
					'video_url'	=> $video_url,

				],
				get_option('arvan-cloud-vod-selected_channel_id', true)
			);

			if ( $result && !isset($result['errors']) && ($result['status_code'] == 200 || $result['status_code'] == 201) ) {

				$this->set_media_id( $post_id, $result['id'] );


				$count++;
			} else {
				$errors = count( $post_ids ) == 1 ? $result['status_code'] : $errors++;
			}
		}



		return [
			'count' => $count,
			'errors' => $errors,
		];
	}


	public function set_media_id( $post_id, $media_id ) {
		update_post_meta( $post_id, 'ar-vod-media-id', $media_id );
	}

	public static function is_video( $mime_type ) {
		return preg_match( '/video/', $mime_type );
	}

	public static function is_allowed_video_type( $mime_type ) {
		return in_array( $mime_type, self::get_allowed_video_types() );
	}

	public static function get_allowed_video_types() {
		return array(
			'video/mp4',
			'video/quicktime',
			'video/x-m4v',
		);
	}

	public static function sanitize_recursive( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = self::sanitize_recursive( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}

	/**
	 * is page arvancloud-vod-videos-add
	 */
	public function is_page_arvancloud_vod_videos_add() {

		if ( (isset($_GET['page']) && sanitize_text_field( $_GET['page'] ) === 'arvancloud-vod-videos-add') ) {
			return true;
		}

		return false;

	}

	/**
	 * is page arvancloud-vod-videos
	 */
	public function is_page_arvancloud_vod_videos() {
		return !((isset($_GET['page']) && sanitize_text_field( $_GET['page'] ) !== 'arvancloud-vod-videos') || (isset($_POST['screen_id']) && sanitize_text_field( $_POST['screen_id'] ) !== 'videos_page_arvancloud-vod-videos'));
	}

	public function filter_media_library_with_videos( \WP_Query $query ){

		global $pagenow;

		if( ! in_array( $pagenow, array( 'admin.php') ) || ! sanitize_text_field($_GET['page']) == 'arvancloud-vod-videos' ) {
			return $query;
		}

		$query->set( 'post_mime_type', 'video' );
		$_GET['attachment-filter'] = 'post_mime_type:video';

		return $query;
	}

	public function get_all_videos() {
		// get posts with acv_video_data meta
		$posts = get_posts( [
			'post_type' => 'attachment',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'meta_query' => [
				[
					'key' => 'acv_video_data',
					'compare' => 'EXISTS',
				],
			],
		] );

		$videos = [];
		foreach ( $posts as $post ) {
			$data = get_post_meta( $post->ID, 'acv_video_data', true );
			if(empty($data['video_url']))
				continue;
			$videos[$data['id']] = [
				'post_id' => $post->ID,
				'title'   => $post->post_title,
				'id'	  => $data['id'],
				'player_url'=> $data['player_url'],
				'video_url'	=> $data['video_url'],
				'mp4_videos'=> $data['mp4_videos'],
				'channel'	=> $data['channel'],
			];
		}

		return $videos;
	}

	public function media_library_url_rewrite( $url, $attachment_id ) {

		$mime_type = get_post_mime_type( $attachment_id );

		// return if not video
		if ( ! Assets::is_allowed_video_type( $mime_type ) ) {
			return $url;
		}

		$video_data = get_post_meta( $attachment_id, 'acv_video_data', true );

		if ( !empty($video_data) ) {
			$url = !empty($video_data['video_url']) ? $video_data['video_url'] : $url;
		}

		return $url;

	}

	public function wp_update_attachment_metadata( $data, $post_id ) {

		if ( isset($_REQUEST['uploader']) && $_REQUEST['uploader'] == 'r1c_vod_uploader' ) {
			$d = ( new Assets )->upload_wp_attachment( [ $post_id ], false );

			if ( $d['count'] == 1 ) {
				\update_post_meta( $post_id, 'acv_video_data_after_upload', [
					'recent_upload_success' => true,
				] );
			} else {
				\update_post_meta( $post_id, 'acv_video_data_after_upload', [
					'errors' => $d['errors'],
					'recent_upload_success' => false,
				] );
			}
		}

		return $data;
	}

	public function media_library_title_rewrite( string $title, int $id ) : string {

		if ( current_user_can( 'manage_options' ) && get_post_type($id) == 'attachment' && !empty(get_post_meta( $id, 'acv_video_data', true )) ) {
			return '☁️ ' . $title;
		}

		return $title;
	}
}
