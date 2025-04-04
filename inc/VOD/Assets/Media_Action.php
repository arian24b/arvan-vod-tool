<?php
namespace WP_Arvan\Engine\VOD\Assets;

use WP_Arvan\Engine\Helper;
use WP_Arvan\Engine\VOD\Assets;

class Media_Action {
	/**
	 * Adds copy to bucket link to Bulk actions
	 *
	 * @param mixed $bulk_actions
	 * @return void
	 */
	public function bulk_actions_upload( $bulk_actions ) {

		$bulk_actions['bulk_copy_to_vod'] = __( 'Copy to ArvanVOD', 'arvancloud-vod' );

		return $bulk_actions;

	}

	/**
	 * Add an action link to the media actions array
	 *
	 * @param array  $actions
	 * @param int    $post_id
	 * @param string $action
	 * @param string $text
	 * @param bool   $show_warning
	 */
	function add_media_row_action( &$actions, $post_id, $action, $text = '', $show_warning = false ) {

		$url   = $this->get_media_action_url( $action, $post_id );
		$text  = __('Copy to ArvanVOD', 'arvancloud-vod');
		$class = $action;

		if ( $show_warning ) {
			$class .= ' local-warning';
		}

		$actions[ 'acs_' . $action ] = '<a href="' . $url . '" class="' . $class . '" title="' . esc_attr( $text ) . '">' . esc_html( $text ) . '</a>';

	}

		/**
	 * Generate the URL for performing copy to vod media actions
	 *
	 * @param string      $action
	 * @param int         $post_id
	 * @param null|string $sendback_path
	 *
	 * @return string
	 */
	function get_media_action_url( $action, $post_id, $sendback_path = null ) {

		$args = array(
			'action' => $action,
			'ids'    => $post_id,
		);

		if ( ! is_null( $sendback_path ) ) {
			$args['sendback'] = urlencode( admin_url( $sendback_path ) );
		}

		$url = add_query_arg( $args, admin_url( 'upload.php' ) );
		$url = wp_nonce_url( $url, 'acs-' . $action );

		return esc_url( $url );

	}





	/**
	 * Conditionally adds media action links for an attachment on the Media library list view.
	 *
	 * @param array       $actions
	 * @param WP_Post|int $post
	 *
	 * @return array
	 */
	function add_media_row_actions( array $actions, $post ) {

		$post_id     = ( is_object( $post ) ) ? $post->ID : $post;
		$mime_type = get_post_mime_type( $post_id );

		// Early return if the attachment is not an video.
		if ( ! Assets::is_video( $mime_type ) ) {
			return $actions;
		}

		$available_actions = self::get_available_media_actions( 'singular' );

		if ( ! $available_actions ) {
			return $actions;
		}

		$file        = get_attached_file( $post_id, true );
		$file_exists = file_exists( $file );
		$media_id    = get_post_meta( $post_id, 'ar-vod-media-id', true );

		// if file is video
		if ( Assets::is_allowed_video_type( $mime_type ) && in_array( 'copy_to_vod', $available_actions ) && $file_exists ) {
			if ( $file_exists && empty($media_id) ) {

				// add copy to VOD action
				$this->add_media_row_action( $actions, $post_id, 'copy_to_vod' );

			}
		}
		return $actions;

	}

	/**
	 * Display notices after processing media actions
	 *
	 * @return void
	 */
	function maybe_display_media_action_message() {

		global $pagenow;

		if ( ! in_array( $pagenow, array( 'upload.php', 'post.php', 'admin.php' ) ) ) {
			return;
		}

		if ( isset( $_GET['acv-action'] ) && isset( $_GET['errors'] ) && isset( $_GET['count'] ) ) {
			$action 	  = sanitize_key( $_GET['acv-action'] ); // input var okay
			$error_count  = absint( $_GET['errors'] ); // input var okay
			$count        = absint( $_GET['count'] ); // input var okay

			echo wp_kses_post($this->get_media_action_result_message( $action, $count, $error_count ));
		}
	}

		/**
	 * Get the result message after an S3 action has been performed
	 *
	 * @param string $action      type of S3 action
	 * @param int    $count       count of successful processes
	 * @param int    $error_count count of errors
	 *
	 * @return bool|string
	 */
	function get_media_action_result_message( $action, $count = 0, $error_count = 0 ) {

		$class = 'updated';
		$type  = 'success';

		if ( 0 === $count && 0 === $error_count ) {
			// don't show any message if no attachments processed
			// i.e. they haven't met the checks for bulk actions
			return false;
		}

		if ( $error_count > 0 ) {
			$type = $class = 'error';

			// We have processed some successfully.
			if ( $count > 0 ) {
				$type = 'partial';
			}
		}

		$message = $error_count > 0 ? $message = self::get_message( $action, $type, $error_count ) : self::get_message( $action, $type);

		// can't find a relevant message, abort
		if ( ! $message ) {
			return false;
		}

		$id = $this->filter_input( 'acv_id', INPUT_GET, FILTER_VALIDATE_INT );

		// If we're uploading a single item, add an edit link.
		if ( 1 === ( $count + $error_count ) && ! empty( $id ) ) {
			$url = esc_url( get_edit_post_link( $id ) );

			// Only add the link if we have a URL.
			if ( ! empty( $url ) && $type == 'success') {
				$text    = esc_html__( 'Edit attachment', 'arvancloud-vod' );
				$message .= sprintf( ' <a href="%1$s">%2$s</a>', $url, $text );
			}
		}

		$message = sprintf( '<div class="notice acs-notice %s is-dismissible"><p>%s</p></div>', $class, $message );

		return $message;

	}

	/**
	 * Get a list of available media actions which can be performed according to plugin and user capability requirements.
	 *
	 * @param string|null $scope
	 *
	 * @return array
	 */
	public static function get_available_media_actions( $scope = null ) {

		$actions = array();

		$actions['copy_to_vod'] = array( 'singular', 'bulk' );

		if ( $scope ) {
			$in_scope = array_filter( $actions, function ( $scopes ) use ( $scope ) {
				return in_array( $scope, $scopes );
			} );

			return array_keys( $in_scope );
		}

		return $actions;

	}

	/**
	 * Get a specific media action notice message
	 *
	 * @param string $action type of action, e.g. copy, remove, download
	 * @param string $type   if the action has resulted in success, error, partial (errors)
	 *
	 * @return string|bool
	 */
	static function get_message( $action = 'copy_to_vod', $type = 'success', $error_count = 0 ) {

		$messages = self::get_messages();

		if ( $type == 'error' ) {
			if ( isset( $messages[$action]['error'][$error_count] ) ) {
				return $messages[$action]['error'][$error_count];
			}
		}

		if ( isset( $messages[ $action ][ $type ] ) ) {
			return $messages[ $action ][ $type ];
		}

		return false;

	}

	/**
	 * Retrieve all the media action related notice messages
	 *
	 * @return array
	 */
	static function get_messages() {
		$messages = array(
			'copy_to_vod'	=> array(
				'success' => __( 'Media successfully copied to ArvanCloud VoD service.', 'arvancloud-vod' ),
				'partial' => __( 'Media copied to ArvanCloud VOD with some errors.', 'arvancloud-vod' ),
				'error'   => [
					0 	=> __( 'There were errors when copying the media to ArvanCloud VOD.', 'arvancloud-vod' ),
					422 => __( 'The media could not be copied to VOD because it is not a valid video file.', 'arvancloud-vod' ),
					402 => __( 'The media could not be copied to VOD because of negative arvan wallet. Please charge your wallet then retry.', 'arvancloud-vod' ),
				],
			)
		);

		return $messages;
	}

	/**
	 * Helper function for filtering super globals. Easily testable.
	 *
	 * @param string $variable
	 * @param int    $type
	 * @param int    $filter
	 * @param mixed  $options
	 *
	 * @return mixed
	 */
	public function filter_input( $variable, $type = INPUT_GET, $filter = FILTER_DEFAULT, $options = array() ) {
		return filter_input( $type, $variable, $filter, $options );
	}

		/**
	 * Handler for single and bulk media actions
	 *
	 * @return void
	 */
	function process_media_actions() {
		global $pagenow;

		// Early return if the current page is not the media library.
		if ( (defined( 'DOING_AJAX' ) && DOING_AJAX) ||
		('upload.php' != $pagenow && ($pagenow == 'admin.php' && isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'arvancloud-vod-videos')) ||
		! isset( $_GET['action'] ) ) {
			return;
		}

		if ( ! empty( $_REQUEST['action2'] ) && '-1' != $_REQUEST['action2'] ) {
			// Handle bulk actions from the footer bulk action select
			$action = sanitize_key( $_REQUEST['action2'] ); // input var okay
		} else {
			$action = sanitize_key( $_REQUEST['action'] ); // input var okay
		}

		if ( false === strpos( $action, 'bulk_copy_to_vod' ) ) {
			$available_actions = Media_Action::get_available_media_actions( 'singular' );
			$referrer          = 'acs-' . $action;
			$doing_bulk_action = false;

			if ( ! isset( $_GET['ids'] ) ) {
				return;
			}

			$ids = explode( ',', sanitize_text_field( $_GET['ids'] ) ); // input var okay
			Helper::sanitize_recursive($ids, 'absint');
		} else {
			$available_actions = Media_Action::get_available_media_actions( 'bulk' );
			$action            = str_replace( 'bulk_copy_to_vod', '', $action );
			$referrer          = 'bulk-media';
			$doing_bulk_action = true;

			if ( ! isset( $_REQUEST['media'] ) ) {
				return;
			}

			$ids = Assets::sanitize_recursive( $_REQUEST['media'] ); // input var okay
		}


		if ( ! in_array( $action, $available_actions ) ) {
			return;
		}

		$ids      = array_map( 'intval', $ids );
		$id_count = count( $ids );

		check_admin_referer( $referrer );

		$sendback = isset( $_GET['sendback'] ) ? sanitize_text_field( $_GET['sendback'] ) : admin_url( 'upload.php' );

		$args = array(
			'acv-action' => $action,
		);

		$result = $this->maybe_do_provider_action( $action, $ids, $doing_bulk_action );

		if ( ! $result ) {
			unset( $args['acv-action'] );
			$result = array();
		}

		// If we're uploading a single file, add the id to the `$args` array.
		$error_count = !empty($result['errors']) ? 1 : 0;
		if ( 'copy_to_vod' === $action && 1 === $id_count && ! empty( $result ) && 1 === ( $result['count'] + $error_count ) ) {
			$args['acv_id'] = array_shift( $ids );
		}

		$args = array_merge( $args, $result );
		$url  = add_query_arg( $args, $sendback );

		wp_redirect( esc_url_raw( $url ) );
		exit();
	}

	function maybe_do_provider_action( $action, $ids, $doing_bulk_action ) {

		switch ( $action ) {
			case 'copy_to_vod':
				$result = (new Assets)->upload_wp_attachment( $ids, $doing_bulk_action );
				break;
		}

		return $result;
	}

	public function ajax_get_attachment_provider_details() {
		if ( ! isset( $_POST['id'] ) ) {
			return;
		}

		check_ajax_referer( 'get-attachment-vod-details', '_nonce' );

		$id = intval( sanitize_text_field( $_POST['id'] ) );

		// get the actions available for the attachment
		$data = array(
			'links' => (new Media_Action)->add_media_row_actions( array(), $id ),
		);

		wp_send_json_success( $data );
	}
}
