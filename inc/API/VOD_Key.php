<?php
namespace WP_Arvan\Engine\API;

use WP_Encryption\Encryption;
use WP_Arvan\Engine\API\HTTP\Request_Arvan;
use WP_Arvan\Engine\API\VOD\Channels;

class VOD_Key {
    /**
	 * check and validate api key by sending request to ArvanCloud
	 *
	 * @param string $api_key
	 * @return boolean
    */
    public static function validate_api_key($api_key = null) {

		$response = Request_Arvan::get('channels', false, $api_key);
		$http_code = wp_remote_retrieve_response_code($response);


		if ($http_code == 500) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>'. esc_html__( 'ArvanCloud is not responding right now. please try again later.', 'arvancloud-vod' ) .'</p>
					</div>');
			} );
		} else if ($http_code == 401) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
					<p>'. esc_html__( 'ArvanCloud API key is invalid. Please try again.', 'arvancloud-vod' ) .'</p>
				</div>');
			} );

			self::reset_api_key();
		}

		return $http_code == 200 ? true : false;
	}

    /**
	 * Sets the access control system and saves it to an option after encryption
	 *
	 * @since 0.0.1
	 * @return void
    */
	public static function set_acvod_api_key() {

		if( ! isset( $_POST[ 'config_arvancloud_vod_api_key' ] ) ) {
			return;
		}

		$api_key = sanitize_key( $_POST[ 'acvod-api-key' ] );
		if ( $api_key == null || ( ! empty( $api_key ) && $api_key === __( "-- not shown --", 'arvancloud-vod' )) ) {
			self::reset_api_key();
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>'. esc_html__( "Enter your API key", 'arvancloud-vod' ) .'</p>
					</div>');
			} );
			return false;
		}

		if ( !self::validate_api_key((new Encryption)->encrypt($api_key)) ) {
			return false;
		}

		$save_settings = update_option( 'arvan-cloud-vod-api_key', (new Encryption)->encrypt($api_key) );

		if( $save_settings ) {
			update_option( 'arvan-cloud-vod-status', 'connected');
			//set first channel as default
			$channels = (new Channels)->get_channels();
			if(empty($channels)){
				add_action( 'admin_notices', function () {
					echo wp_kses_post('<div class="notice notice-success is-dismissible">
							<p>'. esc_html__( "for use all ability you most create a channel.", 'arvancloud-vod' ) .'</p>
						</div>');
				} );	
			}else{
				if($chanel_id = get_option( 'arvan-cloud-vod-selected_channel_id' )){
					foreach($channels as $channel){
						if($channel['id'] == $chanel_id){
							add_action( 'admin_notices', function () {
								echo wp_kses_post('<div class="notice notice-success is-dismissible">
										<p>'. esc_html__( "settings saved.", 'arvancloud-vod' ) .'</p>
									</div>');
							} );
							update_option( 'arvan-cloud-vod-status', 'connected-channel');
							return;
						}
					}
				}

				update_option('arvan-cloud-vod-selected_channel_id',$channels[0]['id']);
				update_option( 'arvan-cloud-vod-status', 'connected-channel');
				add_action( 'admin_notices', function () {
					echo wp_kses_post('<div class="notice notice-success is-dismissible">
							<p>'. esc_html__( "settings saved.", 'arvancloud-vod' ) .'</p>
						</div>');
				} );
			}

			


		}

	}

	/**
	 * Get ArvanCloud Api key
	 *
	 * @return string
	 */
	public static function get_acvod_api_key() {
    	$api_key = get_option( 'arvan-cloud-vod-api_key' );

		if( empty( $api_key ) ) {
			return;
		}
		return (new Encryption)->decrypt( $api_key );
	}


	/**
	 * reset plugin by delete api key and status
	 *
	 * @return void
	 */
	public static function reset_api_key() {
		delete_option( 'arvan-cloud-vod-status' );
		delete_option( 'arvan-cloud-vod-api_key' );
	}
}
