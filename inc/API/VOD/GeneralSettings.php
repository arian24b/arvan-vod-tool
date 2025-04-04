<?php

namespace WP_Arvan\Engine\API\VOD;

class GeneralSettings
{
	private static $instance;
	public static function get_instance(){
		if(!self::$instance)
			self::$instance = new GeneralSettings();
		return self::$instance;
	}

	public function set_prevent_saving_video_on_local_status(){

		if(isset($_POST['prevent_saving_video_on_local'])){

			$prevent_status = sanitize_text_field($_POST['prevent_saving_video_on_local']);

			if( 'no' == $prevent_status )
				update_option('vod_prevent_saving_video_on_local', 'no');
			else if( 'yes' == $prevent_status )
				update_option('vod_prevent_saving_video_on_local', 'yes');

		}


	}
}
