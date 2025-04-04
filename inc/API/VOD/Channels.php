<?php
namespace WP_Arvan\Engine\API\VOD;

use WP_Arvan\Engine\API\HTTP\Request_Arvan;
use WP_Arvan\Engine\API\VOD\Video;

class Channels {

	public function get_channels() {
		$response = Request_Arvan::get('channels');

		if (is_wp_error($response)) {
			return false;
		}

		// request was successful
		if ($response['status_code'] == 200) {
			unset($response['status_code']);
			return $response;
		}

		return false;
	}

	/**
	 * Save default channel
	 */
	public static function set_default_channel() {
		if( ! isset( $_POST[ 'config_arvancloud_vod_selected_channel' ] ) ) {
			return;
		}

		$channel_id = sanitize_key( $_POST[ 'selected_channel' ] );
		if( get_option( 'arvan-cloud-vod-selected_channel_id') == $channel_id)
		return;

		if ( $channel_id == null || empty( $channel_id ) ) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>'. esc_html__( "Please select a channel", 'arvancloud-vod' ) .'</p>
					</div>');
			} );
			return false;
		}

		// save channel id

		$save_settings = update_option( 'arvan-cloud-vod-selected_channel_id', $channel_id );

		//if( $save_settings ) {
			update_option( 'arvan-cloud-vod-status', 'connected-channel');
		//}
		add_action( 'admin_notices', function () {
			echo wp_kses_post('<div class="notice notice-success is-dismissible">
					<p>'. esc_html__( "settings saved.", 'arvancloud-vod' ) .'</p>
				</div>');
		} );

		return true;
	}
}
