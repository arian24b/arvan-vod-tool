<?php

/**
 * Arvan_VOD_Tool
 *
 * @package   Arvan_VOD_Tool
 * @author    Arian Omrani <info@arian24.com>
 * @copyright 2024 Arian Omrani
 * @license   GPL-3.0+
 * @link      https://github.com/arian24b/arvan-vod-tool
 */

namespace WP_Arvan\Admin;

use WP_Arvan\Engine\API\VOD_Key;
use WP_Arvan\Engine\VOD\Assets;
use WP_Arvan\Engine\API\VOD\Channels;

/**
 * This class contain the Enqueue stuff for the backend
 */
class Enqueue {

	/**
	 * Initialize the class.
	 *
	 * @return void|bool
	 */
	public function initialize() {


		add_action('admin_enqueue_scripts',[$this,'override_delete_media_alert']);

		if ( defined('ACVOD_PLUGIN_STATUS') && ACVOD_PLUGIN_STATUS ) {

			\add_action('init', function () {
				register_block_type(
					'r1c/vod-select', array(
						// Enqueue blocks.style.build.css on both frontend & backend.
						'style'         => 'arvancloud-vod' . '-block-style-css',
						// Enqueue blocks.build.js in the editor only.
						'editor_script' => 'arvancloud-vod' . '-block-js',
						// Enqueue blocks.editor.build.css in the editor only.
						'editor_style'  => 'arvancloud-vod' . '-block-editor-css',
						'attributes'      => array(
							'videoId'     => array(
								'type' => 'string',
							),
						),
					)
				);
				//enqueue live video block
				wp_enqueue_script(
					'arvan-live-block-script',
					\plugins_url( 'assets/js/block-live_video.js', ACVOD_PLUGIN_ABSOLUTE ) ,
					array('wp-blocks', 'wp-element','wp-editor','wp-components')//,'wp-blockEditor','wp-i18n'
				);

				//enqueue video list block
				wp_enqueue_script(
					'arvan-video-list-block',
					\plugins_url( 'assets/js/block-video-list.js', ACVOD_PLUGIN_ABSOLUTE ) ,
					array('wp-blocks', 'wp-element','wp-editor','wp-components')//,'wp-blockEditor','wp-i18n'
				);

			});
		}
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_filter("media_view_strings", function($strings){
			$strings['warnDelete'] = __("You are about to permanently delete this item from your site.\nAlso, you are deleting this video from your local storage, this video is still available on Arvan Video Platform Storage.\nThis action cannot be undone.\n 'Cancel' to stop, 'OK' to delete.",'arvancloud-vod');
			return $strings;
		});

        \add_action( 'admin_head', array( $this, 'admin_head' ) );


	}

    public function admin_head(){
		$screen = get_current_screen() ;
        if((isset($_GET['page']) and $_GET['page']=='arvancloud-vod-videos-add')or ($screen->action == 'add' and $screen->base == 'media')){
            $chanel = '<option value="">'.__('Select','arvancloud-vod').'</option>';
            if($channels = (new Channels)->get_channels()){
                foreach($channels as $channel){
                    $chanel .= "<option value=\"{$channel['id']}\">{$channel['title']}</option>";
                }
            }

        ?>
        <script type="text/javascript">
        var option_channel = '<?php echo $chanel; ?>';
        </script>
        <?php
        }

		if(get_post_type() == 'post'){
		?>
        <style>
        .editor-styles-wrapper{border:1px solid silver;padding:10px !important;}
        </style>
        <?php
		}

    }


	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function enqueue_admin_styles() {

		\wp_enqueue_style( 'arvancloud-vod' . '-admin-styles', \plugins_url( 'assets/css/admin.css', ACVOD_PLUGIN_ABSOLUTE ), array( 'dashicons' ), ACVOD_VERSION );
        \wp_enqueue_style( 'arvancloud-vod' . '-tagify', \plugins_url( 'assets/css/tagify.css', ACVOD_PLUGIN_ABSOLUTE ), array(), ACVOD_VERSION );

		\wp_enqueue_style( 'arvancloud-vod-main-style' . '-main-styles', \plugins_url( 'assets/css/main.css', ACVOD_PLUGIN_ABSOLUTE ), ACVOD_VERSION );

		$style = ".attachment-preview.type-video img.thumbnail { width:50px; }";

		wp_add_inline_style('arvancloud-vod-admin-styles', $style);

		// enqueue if is rtl
		if ( \is_rtl() ) {
			\wp_enqueue_style( 'arvancloud-vod' . '-admin-rtl-styles', \plugins_url( 'assets/css/admin-rtl.css', ACVOD_PLUGIN_ABSOLUTE ), array( 'dashicons' ), ACVOD_VERSION );
		}

		if ( get_locale() == 'fa_IR' && explode('_', \get_current_screen()->id)[0] == '%d9%88%db%8c%d8%af%d8%a6%d9%88%d9%87%d8%a7' ) {
			add_action('admin_head', function() {
				echo '<style>.drag-drop #drag-drop-area { border: 4px dashed #00baba; height: 200px;} </style>';
			});
		}
	}

	public function enqueue_styles() {
		\wp_enqueue_style( 'arvancloud-vod' . '-front-styles', \plugins_url( 'assets/css/front.css', ACVOD_PLUGIN_ABSOLUTE ), array(), ACVOD_VERSION );

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$screen = \get_current_screen();

		\wp_enqueue_script(
			'arvan-vod-tool' . '-admin-scripts',
			\plugins_url( 'assets/js/admin.js', ACVOD_PLUGIN_ABSOLUTE ),
			array( 'jquery', 'media-grid', 'media' ),
			ACVOD_VERSION,
			true
		);


		$translate = [
			'Upload video to arvan'=>__('Upload video to arvan','arvan-vod-tool'),
			'Ready to upload the file'=>__('Ready to upload the file','arvan-vod-tool'),
			'File address'=>__('File address','arvan-vod-tool'),
			'File name'=>__('File name','arvan-vod-tool'),
			'Short description'=>__('Short description','arvan-vod-tool'),
			'Video tags'=>__('Video tags','arvan-vod-tool'),
			'Transfer to the channel'=>__('Transfer to the channel','arvan-vod-tool'),
			'You can choose 5 tags'=>__('You can choose 5 tags','arvan-vod-tool'),
			'Output type'=>__('Output type','arvan-vod-tool'),
			'Determine with what quality the face was ready for display.'=>__('Determine with what quality the face was ready for display.','arvan-vod-tool'),
			'Automatically'=>__('Automatically','arvan-vod-tool'),
			'Manually'=>__('Manually','arvan-vod-tool'),
			'As a profile'=>__('As a profile','arvan-vod-tool'),
			'Video type'=>__('Video type','arvan-vod-tool'),
			'Vertical mode'=>__('Vertical mode','arvan-vod-tool'),
			'Image resolution'=>__('Image resolution','arvan-vod-tool'),
			'Horizontal mode'=>__('Horizontal mode','arvan-vod-tool'),
			'From 144p to 1080p quality'=>__('From 144p to 1080p quality','arvan-vod-tool'),
			'From 144p to 1080p quality'=>__('From 144p to 1080p quality','arvan-vod-tool'),
			'resolution'=>__('resolution','arvan-vod-tool'),
			'Image bitrate'=>__('Image bitrate','arvan-vod-tool'),
			'Audio bitrate'=>__('Audio bitrate','arvan-vod-tool'),
			'Select'=>__('Select','arvan-vod-tool'),
			'Cancel'=>__('Cancel','arvan-vod-tool'),
			'Begin upload video'=>__('Begin upload video','arvan-vod-tool'),
			'Do you want delete this resulation?','arvan-vod-tool'=>__('Do you want delete this resulation?','arvan-vod-tool'),
			'Please select a channel'=>__('Please select a channel','arvan-vod-tool'),
			'Please select a resulation'=>__('Please select a resulation','arvan-vod-tool'),
			'Please select a profile'=>__('Please select a profile','arvan-vod-tool'),
			'File ID not found','arvan-vod-tool'=>__('File ID not found','arvan-vod-tool'),
			'Upload video to arvan successfull.'=>__('Upload video to arvan successfull.','arvan-vod-tool'),
			'Error upload video to arvan server'=>__('Error upload video to arvan server','arvan-vod-tool'),
			'Do you want cancel upload this video?'=>__('Do you want cancel upload this video?','arvan-vod-tool'),
		];

		if( (isset($_GET['page']) &&  strstr($_GET['page'], 'arvancloud-vod-videos') != false)or ($screen->action == 'add' and $screen->base == 'media') ){

			\wp_enqueue_script(
				'arvancloud-vod' . '-tagify',
				\plugins_url( 'assets/js/tagify.min.js', ACVOD_PLUGIN_ABSOLUTE ),
				array( 'jquery'),
				ACVOD_VERSION,
				true
			);

			\wp_register_script(
				'arvan-vod-tool' . '-upload-vod-redirect',
				plugins_url( 'assets/js/vod_upload.js', ACVOD_PLUGIN_ABSOLUTE ),
				array( 'jquery', 'media-grid', 'media', 'plupload','wp-i18n' ),
				ACVOD_VERSION,
				true
			);
			wp_localize_script('arvan-vod-tool' . '-upload-vod-redirect','json_string',$translate);
			wp_enqueue_script( 'arvan-vod-tool' . '-upload-vod-redirect' );
			wp_set_script_translations( 'arvan-vod-tool' . '-upload-vod-redirect', 'arvan-vod-tool', plugin_dir_path( dirname(__FILE__) ) . 'languages');

		}
		wp_localize_script(
			'arvan-vod-tool' . '-admin-scripts',
			'AR_VOD',
			array(
				'videoGallery' => \admin_url( 'admin.php?page=arvancloud-vod-videos' ),
				'strings'	  => array(
					'video_upload_error' => __( 'you are not allowed to upload this file type.', 'arvan-vod-tool' ),
					'copy_to_vod' => __( 'Copy to ArvanVOD', 'arvan-vod-tool' ),
				),
				'nonces'  => array(
					'get_attachment_provider_details' => wp_create_nonce( 'get-attachment-vod-details' ),
				),
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
			)
		);



		if ( $screen->id === 'videos_page_arvancloud-vod-videos-add' ||
			get_locale() == 'fa_IR' && explode('_', $screen->id)[0] == '%d9%88%db%8c%d8%af%d8%a6%d9%88%d9%87%d8%a7' ) {

			\wp_enqueue_script(
				'arvancloud-vod' . '-upload-scripts',
				\plugins_url( 'assets/js/vod_upload.js', ACVOD_PLUGIN_ABSOLUTE ),
				array( 'jquery', 'media-grid', 'media', 'plupload' ),
				ACVOD_VERSION,
				true
			);
			wp_localize_script('arvancloud-vod' . '-upload-scripts','json_string_item',$translate);
		}




		if ( defined('ACVOD_PLUGIN_STATUS') && ACVOD_PLUGIN_STATUS ) {

			\add_action('init', function () {
				register_block_type(
					'r1c/vod-select', array(
						// Enqueue blocks.style.build.css on both frontend & backend.
						'style'         => 'arvancloud-vod' . '-block-style-css',
						// Enqueue blocks.build.js in the editor only.
						'editor_script' => 'arvancloud-vod' . '-block-js',
						// Enqueue blocks.editor.build.css in the editor only.
						'editor_style'  => 'arvancloud-vod' . '-block-editor-css',
						'attributes'      => array(
							'videoId'            => array(
								'type' => 'string',
							),
						),
					)
				);
			});

			\wp_register_script(
				'arvancloud-vod' . '-block-js', // Handle.
				plugins_url( '/assets/js/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
				null,
				true
			);

			wp_register_style(
				'arvancloud-vod' . '-block-editor-css', // Handle.
				plugins_url( 'assets/css/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
				array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
				null // filemtime( plugin_dir_path( __DIR__ ) . 'assets/css/blocks.editor.build.css' ) // Version: File modification time.
			);

			\wp_register_style(
				'arvancloud-vod' . '-block-style-css', // Handle.
				plugins_url( 'assets/css/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
				is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
				null
			);

			wp_localize_script(
				'arvancloud-vod' . '-block-js',
				'r1cGlobal', // Array containing dynamic data for a JS Global.
				[
					'pluginDirPath' => plugin_dir_path( __DIR__ ),
					'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
					'arvanVideos'	=> (new Assets)->get_all_videos(),
					// Add more data here that you want to access from `r1cGlobal` object.
				]
			);

		}

		if(VOD_Key::validate_api_key()){
			wp_localize_script(
				'arvancloud-vod' . '-admin-scripts',
				'vod_is_api_valid', // Array containing dynamic data for a JS Global.
				[
					'value'=>true
				]
			);
		}

	}

	public function override_delete_media_alert(){
		wp_register_script( 'vod-override-delete-alert', '' );
		wp_enqueue_script( 'vod-override-delete-alert' ,null,null,null,true);

		$script = 'window.showNotice.warn = function() { if ( confirm( "'.__("You are about to permanently delete this item from your site.\\nAlso, you are deleting this video from your local storage, this video is still available on Arvan Video Platform Storage.\\nThis action cannot be undone.\\n 'Cancel' to stop, 'OK' to delete.",'arvancloud-vod').'" ) ) { return true;	} return false;	}';

		wp_add_inline_script('vod-override-delete-alert' , $script );
	}

}
