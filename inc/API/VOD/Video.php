<?php

namespace WP_Arvan\Engine\API\VOD;

use WP_Arvan\Engine\API\HTTP\Request_Arvan;

final class Video {
    public function showAll(string $channelId, array $options = null)
    {
		$route = Routes::BuildRoute( Routes::GET_VIDEOS, [ 'channel_id' => $channelId ] );
		$result = Request_Arvan::get( $route, true );

        return $result;
    }

	public function search(string $channelId, array $options = null){

		$router  = "videos/search?filters[channel_id]=in($channelId)";
		foreach($options as $key=>$val){
			$router .= "&filters[$key]=$val";
		}
		//.'&sorts=-created_at&page=1&per_page=9'
		$result = Request_Arvan::get( $router, true );
        return $result;
	}

    public function create(array $video, string $channelId)
    {

		$route = Routes::BuildRoute( Routes::CREATE_VIDEO, [ 'channel_id' => $channelId ] );

		$video = json_encode( $video ,JSON_UNESCAPED_SLASHES);


		$result = Request_Arvan::post( $route, $video );


		$result = (array) $result;

		if (is_wp_error($result) || !isset($result['body'])) {
			return false;
        }

		$status_code =  $result['status_code'] ?? 0;
        $result = json_decode($result['body'], true);
		$result['status_code'] = $status_code;
		$result['data']['status_code'] = $status_code;

		if (!isset($result['data'])) {
			return $result;
		}

        return $result['data'];
    }

	public function show(string $videoId)
    {

		$route = Routes::BuildRoute( Routes::GET_VIDEO, [ 'video_id' => $videoId ] );

		$result = Request_Arvan::get( $route );

		$result = (array) $result;

		if (is_wp_error($result) || !isset($result['status_code'])) {
            return false;
        }

        return $result;
    }


}
