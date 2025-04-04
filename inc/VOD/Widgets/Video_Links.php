<?php
namespace WP_Arvan\Engine\VOD\Widgets;

use WP_Arvan\Engine\VOD\Assets;

class Video_Links {
	public function add_meta_boxes( $post ){
		$this->register_r1c_video_links( $post );
	}

	public function register_r1c_video_links( $post ) {
		$post_id = $post->ID;
		$mime_type = get_post_mime_type( $post_id );

		// return if not video
		if ( ! Assets::is_allowed_video_type( $mime_type ) ) {
			return;
		}

		// return if video not uploaded to VOD yet
		$video_data = get_post_meta( $post_id, 'acv_video_data', true );
		if ( empty($video_data) ) {
			add_meta_box(
				'r1c_video_copy',
				__( 'ArvanCloud VOD', 'arvancloud-vod' ),
				array( $this, 'render_r1c_video_copy' ),
				'attachment',
				'side',
				'core'
			);
		} else {
			add_meta_box(
				'r1c_video_links',
				__( 'ArvanCloud VOD', 'arvancloud-vod' ),
				array( $this, 'render_r1c_video_links' ),
				'attachment',
				'side',
				'core'
			);
		}

	}

	public function render_r1c_video_copy() {
		$post_id = get_the_ID();
		$video_data = get_post_meta( $post_id, 'acv_video_data', true );

		if ( !empty($video_data) ) {
			return;
		}

		$media_id    = get_post_meta( $post_id, 'ar-vod-media-id', true );

		if ( !empty($media_id) ) {
			echo '<p>' . __( 'The video has already been uploaded to ArvanCloud VOD and is being converted. Once the conversion process is complete, the video links will be displayed here.', 'arvancloud-vod' ) . '</p>';
			return;
		}

		$copy_to_url_link = sprintf(
			'<a class="button button-large" href="%s">%s</a>',
			add_query_arg(
				array(
					'action' => 'copy_to_vod',
					'ids' => $post_id,
					'_wpnonce' => wp_create_nonce( 'acs-copy_to_vod' ),
				),
				esc_url( admin_url( 'upload.php' ) )
			),
			__( 'Copy to ArvanVOD', 'arvancloud-vod' )
		);

		echo esc_url($copy_to_url_link);

	}


	public function render_r1c_video_links() {
		$post_id = get_the_ID();
		$video_data = get_post_meta( $post_id, 'acv_video_data', true );

		if ( empty($video_data) ) {
			return;
		}

		$this->print_iframe_snippet( $video_data['player_url'] );
		$this->maybe_print_code_snippet( 'HLS URL', $video_data['hls_playlist'] );
		$this->maybe_print_code_snippet( 'Config URL', $video_data['config_url'] );
		$this->maybe_print_code_snippet( 'Player URL', $video_data['player_url'] );
		$this->maybe_print_code_snippet( 'DASH URL', $video_data['dash_playlist'] );
		$this->maybe_print_code_snippet( 'Thumbnail URL', $video_data['thumbnail_url'] );
		$this->maybe_print_code_snippet( 'Tooltip URL', $video_data['tooltip_url'] );
		$this->maybe_print_code_snippet( 'Video URL', $video_data['video_url'] );

		return;
	}

	public function maybe_print_code_snippet( $title, $code ) {
		if ( empty( $code ) ) {
			return;
		}
		echo '<div class="acvod-code-snippet">';
		echo '<span class="acvod-copy-code">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="v1:ar-icon v1:ar-icon-copy c-vodCopyButton__copyIcon"><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13.3333 6H7.33333C6.59695 6 6 6.59695 6 7.33333V13.3333C6 14.0697 6.59695 14.6667 7.33333 14.6667H13.3333C14.0697 14.6667 14.6667 14.0697 14.6667 13.3333V7.33333C14.6667 6.59695 14.0697 6 13.3333 6Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M3.33398 10H2.66732C2.3137 10 1.97456 9.85956 1.72451 9.60952C1.47446 9.35947 1.33398 9.02033 1.33398 8.66671V2.66671C1.33398 2.31309 1.47446 1.97395 1.72451 1.7239C1.97456 1.47385 2.3137 1.33337 2.66732 1.33337H8.66732C9.02094 1.33337 9.36008 1.47385 9.61013 1.7239C9.86018 1.97395 10.0007 2.31309 10.0007 2.66671V3.33337" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="v1:ar-icon v1:ar-icon-check c-vodCopyButton__checkIcon"><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13.3346 4L6.0013 11.3333L2.66797 8"></path></g></svg>

		</span>';
		echo '<div class="code_snippet_data">';
		echo '<h4>' . esc_html($title) . '</h4>';
		echo '<pre>' . esc_html($code) . '</pre>';
		echo '</div>';
		echo '</div>';

		return;
	}

	public function print_iframe_snippet( $url ) {
		$html = '<style>.r1_iframe_embed {position: relative; overflow: hidden; width: 100%; height: auto; padding-top: 56.25%; } .r1_iframe_embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }</style><div class="r1_iframe_embed"><iframe src="'. $url .'" style="border:0 #ffffff none;" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe></div>';

		$this->maybe_print_code_snippet( 'iFrame', htmlentities($html) );
	}

}
