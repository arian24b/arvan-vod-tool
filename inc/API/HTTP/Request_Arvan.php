<?php
namespace WP_Arvan\Engine\API\HTTP;

use WP_Encryption\Encryption;


/**
 * Manage request to Arvancloud API
 */
class Request_Arvan {

    /**
     * ArvanCloud API Endpoint
     *
     * @var string
     */
    public static $arvan_cloud = 'https://napi.arvancloud.ir/vod/2.0/';

    /**
     * @param string|null $api_key
     * @return array
     */
    private static function get_headers(?string $api_key): array
    {
        $api_key = (new Encryption)->decrypt($api_key == null ? get_option('arvan-cloud-vod-api_key') : $api_key);
        $api_key = 'Apikey ' . (substr($api_key, 0, 6) == 'Apikey' || substr($api_key, 0, 6) == 'apikey' ? substr($api_key, 6) : $api_key);
        $headers = array(
            'Authorization' => $api_key,
            'Content-Type' => 'application/json',
        );
        return $headers;
    }

    /**
     * @param array $response
     * @return false|mixed
     */
    private static function validate_get_response($response)
    {
        if (is_wp_error($response) || !isset($response['body'])) {
            return false;
        }

        $res = json_decode($response['body'], true);
        if(!isset($res['data']))
            return false;

        $res = $res['data'];
        $res['status_code'] = wp_remote_retrieve_response_code($response);

        return $res;
    }

    /**
     * Send Get request to ArvanCloud
     *
     * @param string $endpoint
     * @param bool $should_validate
     * @param string|null $api_key
     * @return false|mixed
     */
	public static function get(string $endpoint = 'channels/', $should_validate = true, string $api_key = null )
    {

		$args = ['headers' => self::get_headers($api_key)];
        $response = wp_remote_get( self::$arvan_cloud . $endpoint, $args );
		return $should_validate ? self::validate_get_response($response) : $response;

	}

    /**
     * Send Post request to ArvanCloud
     *
     * @param array $data
     * @param string $endpoint
     * @param bool $should_validate
     * @param string|null $api_key
     * @return false|mixed
     */
    public static function post(array $data, string $endpoint = '', $should_validate = true, string $api_key = null)
    {

		$headers = self::get_headers($api_key);
        $response = \Requests::post( self::$arvan_cloud . $endpoint, $headers, $data );

		return $response;

	}

    /**
     * Send Patch request to ArvanCloud
     *
     * @param string $endpoint
     * @param string $new_cache_setting
     * @param string|null $api_key
     * @return response|Requests_Response
     */
    public static function patch(string $endpoint, string $new_cache_setting, string $api_key = null)
    {

		$headers = self::get_headers($api_key);
		$url = self::$arvan_cloud . $endpoint;
		return \Requests::patch( $url,  $headers,  $new_cache_setting );
    }

    /**
     * Send Delete request to ArvanCloud
     *
     * @param array $data
     * @param string $endpoint
     * @param bool $should_validate
     * @param string|null $api_key
     * @return false|mixed
     */
    public static function delete(array $data, string $endpoint = '', $should_validate = true, string $api_key = null)
    {

        $headers = self::get_headers($api_key);

        $url = self::$arvan_cloud . $endpoint;
		return \Requests::delete( $url,  $headers);

    }

    /**
     * Send Put request to ArvanCloud
     *
     * @param array $data
     * @param string $endpoint
     * @param bool $should_validate
     * @param string|null $api_key
     * @return false|mixed
     */
    public static function put(array $data, string $endpoint = '', $should_validate = true, string $api_key = null)
    {

		$headers = self::get_headers($api_key);var_dump($headers);
        $response = \Requests::put( self::$arvan_cloud . $endpoint, $headers, $data );

		return $response;

    }

    /**
     * Send HEAD request to ArvanCloud
     *
     * @param string $data The request data
     * @param string $endpoint The API endpoint
     * @param string|null $api_key API key to use
     * @return mixed
     */
    public static function head(string $data, string $endpoint = 'channels/', string $api_key = null)
    {
        $headers = self::get_headers($api_key);
        $response = \Requests::head( self::$arvan_cloud . $endpoint, $headers, $data );

        return $response;
    }

    public static function get_stream(string $stream_id,$should_validate=true,string $api_key=null){

        $args = ['headers' => self::get_headers($api_key)];
        $response = wp_remote_get("https://napi.arvancloud.ir/live/2.0/streams/{$stream_id}z", $args );
        return $should_validate ? self::validate_get_response($response) : $response;
    }
}
