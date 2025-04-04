<?php
/**
 * Manage media uploaded file.
 *
 * There are many filters in here for media. Plugins can extend functionality
 * by hooking into the filters.
 *
 * @package WordPress
 * @subpackage Administration
 */

use WP_Arvan\Engine\VOD\Assets\Media_Action;

/** Load WordPress Administration Bootstrap */
require_once ABSPATH . '/wp-admin/admin.php';

if ( ! current_user_can( 'upload_files' ) ) {
	wp_die( __( 'Sorry, you are not allowed to upload files.' ) );
}

if ( isset( $_GET['result'] ) && 'true' == $_GET['result'] ) {
	global $wpdb;

	// get attachments with acv_video_data_after_upload meta
	$attachments = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'acv_video_data_after_upload'", OBJECT );


	if ( count($attachments) > 0 ) {

			// loop through attachments
			foreach ( $attachments as $attachment ) {

			// get attachment id
			$attachment_id = $attachment->post_id;

			// get attachment meta
			$attachment_meta = unserialize( $attachment->meta_value);

			if ( $attachment_meta['recent_upload_success'] ) {
				$type = 'success';
				$message = Media_Action::get_message( 'copy_to_vod', $type );

			} else {
				$type = 'error';
				$message = Media_Action::get_message( 'copy_to_vod', $type, $attachment_meta['errors'] );
			}

			echo wp_kses_post(
				sprintf( '<div class="notice acs-notice %s is-dismissible"><p>%s</p></div>', $type, $message )
			);

			// remove meta
			delete_post_meta( $attachment_id, 'acv_video_data_after_upload' );

		}
	}
}

wp_enqueue_script( 'plupload-handlers' );

$post_id = 0;
if ( isset( $_REQUEST['post_id'] ) ) {
	$post_id = absint( $_REQUEST['post_id'] );
	if ( ! get_post( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		$post_id = 0;
	}
}

if ( $_POST ) {
	if ( isset( $_POST['html-upload'] ) && ! empty( $_FILES ) ) {
		check_admin_referer( 'media-form' );
		// Upload File button was clicked.
		$upload_id = media_handle_upload( 'async-upload', $post_id );
		if ( is_wp_error( $upload_id ) ) {
			wp_die( $upload_id );
		}
	}
	wp_redirect( admin_url( 'upload.php' ) );
	exit;
}

// Used in the HTML title tag.
$title       = __( 'Upload New Video', 'arvancloud-vod' );
$parent_file = 'upload.php';

get_current_screen()->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' =>
				'<p>' . __( 'You can upload media files here without creating a post first. This allows you to upload files to use with posts and pages later and/or to get a web link for a particular file that you can share. There are three options for uploading files:' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Drag and drop</strong> your files into the area below. Multiple files are allowed.' ) . '</li>' .
					'<li>' . __( 'Clicking <strong>Select Files</strong> opens a navigation window showing you files in your operating system. Selecting <strong>Open</strong> after clicking on the file you want activates a progress bar on the uploader screen.' ) . '</li>' .
					'<li>' . __( 'Revert to the <strong>Browser Uploader</strong> by clicking the link below the drag and drop box.' ) . '</li>' .
				'</ul>',
	)
);
get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/article/media-add-new-screen/">Documentation on Uploading Media Files</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/">Support</a>' ) . '</p>'
);

require_once ABSPATH . 'wp-admin/admin-header.php';

$form_class = 'media-upload-form type-form validate';

if ( get_user_setting( 'uploader' ) || isset( $_GET['browser-uploader'] ) ) {
	$form_class .= ' html-uploader';
}
?>
<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<form enctype="multipart/form-data" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=arvancloud-vod-videos-add' ) ); ?>" class="<?php echo esc_attr( $form_class ); ?>" id="file-form">

	<?php media_upload_form(); ?>

	<script type="text/javascript">
	var post_id = <?php echo absint( $post_id ); ?>, shortform = 3;
	</script>
	<input type="hidden" name="post_id" id="post_id" value="<?php echo absint( $post_id ); ?>" />
	<?php wp_nonce_field( 'media-form' ); ?>
	<div id="media-items" class="hide-if-no-js"></div>
	</form>

	<?php
	// accepted mime types hint
	$mime_types = 'MP4 MOV M4V';
	echo '<p class="help">' . sprintf( __( 'Accepted file types: %s', 'arvancloud-vod' ), $mime_types ) . '</p>';
	?>
</div>

<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
