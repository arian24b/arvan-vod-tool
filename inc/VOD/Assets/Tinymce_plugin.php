<?php

namespace WP_Arvan\Engine\VOD\Assets;


class Tinymce_plugin {

	public function custom_mce_buttons() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', array($this, 'add_buttons') );
		add_filter( 'mce_buttons', array($this, 'register_buttons') );
	}

	public function add_buttons( $plugin_array ) {
		$plugin_array['r1c_vod'] = \plugins_url( 'assets/js/tinymce_buttons.js', ACVOD_PLUGIN_ABSOLUTE );
		return $plugin_array;
	}

	public function register_buttons( $buttons ) {
		array_push( $buttons, 'r1c_vod' );
		return $buttons;
	}

	public function tinymce_extra_vars() { ?>
		<script type="text/javascript">
		var tinyMCE_object = <?php echo json_encode(
			array(
				'button_name' => esc_html__('Embed VOD', 'arvancloud-vod'),
				'button_title' => esc_html__('Select a video', 'arvancloud-vod'),
				'image_title' => esc_html__('Video', 'arvancloud-vod'),
				'image_button_title' => esc_html__('Select', 'arvancloud-vod'),
			)
		);
		?>;
		</script>
		<?php
	}
}
