<?php
namespace WP_Arvan\Engine\VOD\Assets;

use WP_Arvan\Engine\API\VOD\Video;

class Upload_Process {

	public $videoId;
	public $postID;
	public $data;
	public $status;
	public $is_available;


	public function __construct( $postID )
	{

		$this->videoId	= get_post_meta( $postID, 'ar-vod-media-id', true );
		$this->postID	= $postID;
		$this->data		= (new Video)->show( $this->videoId );

		if ( !$this->data ) {
			return false;
		}

		$this->is_available = $this->data['available'];
		$this->status		= $this->data['status'];

	}

	public function check()
	{
		if ( $this->is_available ) {
			$this->store_video_data();
			return true;
		}

		\update_post_meta( $this->postID, 'acv_video_data_last_fetch', time() );

		if ( $this->status == 'downloading_fail' or $this->status == 'converting_fail' or (isset($this->data['status_code']) and $this->data['status_code'] != 200) ) {
			return true;
		}

		return false;
	}

	public function store_video_data()
	{
		\delete_post_meta( $this->postID, 'acv_video_data_last_fetch' );
		\update_post_meta( $this->postID, 'acv_video_data', $this->data );
	}
}
