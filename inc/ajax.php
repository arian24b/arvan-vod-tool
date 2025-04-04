<?php
use WP_Arvan\Engine\API\HTTP\Request_Arvan;
use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\API\VOD\video_tags;
/**
 * ajax function of block editor Arval Video List
 */
function vod_search_video_list(){
    if(empty($_POST['search']) or empty($_POST['type']))
    wp_send_json(['status'=>0,'data'=>[]]);

    $videoes         = [];
    $_POST['search'] = esc_sql($_POST['search']);

    if($_POST['type']=='title'){
        $vars = ['filters'=>[]];
        $vars['filters']['title'] = "like({$_POST['search']})";
        $videoes = Request_Arvan::get("videos/search?".http_build_query($vars));
            if($videoes['status_code']!=200)
                wp_send_json(['status'=>0,'data'=>[],'msg'=>__('Error connect to ARVAN','arvancloud-vod')]);

    }else if($_POST['type']=='descr'){
        $vars['filters']['description'] = "like({$_POST['search']})";
        $videoes = Request_Arvan::get("videos/search?".http_build_query($vars));
            if($videoes['status_code']!=200)
                wp_send_json(['status'=>0,'data'=>[],'msg'=>__('Error connect to ARVAN','arvancloud-vod')]);
    }else if($_POST['type']=='tag'){
        //$videoes = Request_Arvan::get("tags?filter=".$_POST['search']);
        $videoes = (new video_tags)->get_video_by_tag($_POST['search']);
        if(empty($videoes))
            wp_send_json(['status'=>0,'data'=>[],'msg'=>__('Tags not found','arvancloud-vod')]);
        if($videoes['status_code']!=200)
            wp_send_json(['status'=>0,'data'=>[],'msg'=>__('Error connect to ARVAN','arvancloud-vod')]);
    }
    if(count($videoes)<1)
        wp_send_json(['status'=>0,'data'=>[],'msg'=>__('Return Videos: 0','arvancloud-vod')]);

        $vid = '';
        $cnt = 0;
        foreach($videoes as $video){
            if($video==200)
                break;
            $vido = Request_Arvan::get("/videos/{$video['id']}");
            if($vido['status_code']!=200)
                continue;
            if(empty($vido['player_url']))
                continue;
            ++$cnt;
            $vid .= "
            <div class='card_item'>
                <a href='{$vido['player_url']}' target='BLANK' title='{$vido['description']}' rel='noopener' title='{$vido['title']}'>
                    <img src='{$vido['thumbnail_url']}'/>
                    <small>{$vido['title']}</small>
                </a>
            </div>\n";
        };
    wp_send_json(['status'=>1,'data'=>$vid,'msg'=>__('Return Videos: ','arvancloud-vod').$cnt]);
}
add_action('wp_ajax_vod_search_video_list','vod_search_video_list');
//add_action('wp_ajax_nopriv_vod_search_video_list','vod_search_video_list');

/**
 * ajax function for resulation list of uploaded video
 */
function vod_upload_custom_video(){

    global $wpdb;
    $attch_url = wp_get_attachment_url($_POST['id']);//'https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_1mb.mp4';
    if(empty($attch_url))
        wp_send_json(['status'=>0,'data'=>'attachment is invalid']);

    $video = [
        'title'       =>get_the_title($_POST['id']),
        'description' =>$_POST['descr'],
        'video_url'   =>$attch_url,
        'convert_mode'=>$_POST['otype'],
        'resType'     =>$_POST['ptype'],
        'file_id'     =>'',
        'profile_id'  =>$_POST['profile'],
        'parallel_convert'=>false,
        'thumbnail_time'=>1,
        'convert_infox' =>$_POST['resulation'],
    ];
    $channel= empty($_POST['channl'])?get_option('arvan-cloud-vod-selected_channel_id', true):$_POST['channl'];
    $result = (new Video)->create($video,$channel);

    if ( $result && !isset($result['errors']) && ($result['status_code'] == 200 || $result['status_code'] == 201) ){
        update_post_meta( $_POST['id'], 'ar-vod-media-id', $result['id'] );
        /*$data = (new Video)->show( $result['id']);
        if(!empty($data['id']))
        update_post_meta( $_POST['id'], 'acv_video_data', $data );*/
        $res=[];
        if(!empty($_POST['tags'])){
            $tags = json_decode(str_replace('\\', '',$_POST['tags']), true);
            $tags = array_column($tags,'value');
            $res  = (new video_tags)->save_tags($result['id'],$tags);
        }
        wp_send_json(['status'=>1,'data'=>$video,'res'=>$res]);
    }else
    wp_send_json(['status'=>0,'data'=>$result]);
}
add_action('wp_ajax_vod_upload_custom_video','vod_upload_custom_video');


/**
 * get profiles of a channel id
 */
function vod_get_profiles(){

    $result  = '';
    $profile = Request_Arvan::get("/channels/{$_POST['chanel']}/profiles");
    if(!empty($profile)){
        foreach($profile as $prof){
            if(!empty($prof['id']))
            $result .= "<option value='{$prof['id']}'>{$prof['title']}</option>";
        }
    }
    wp_send_json(['data'=>$result]);
}
add_action('wp_ajax_vod_get_profiles','vod_get_profiles');

/**
 * upload video file ajax admin
 */
function vod_video_upload(){
    if (!empty($_FILES['file']['name'])) {
        // Add debug logging
        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD] Starting video upload for file: ' . $_FILES['file']['name']);
        }

        if(!empty($_POST['filename']))
            $_FILES['file']['name'] = esc_sql($_POST['filename']).'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        // Handle file upload
        $fileid = media_handle_sideload($_FILES['file'], 0);

        if (is_wp_error($fileid)) {
            if (WP_DEBUG) {
                error_log('[ArvanCloud VOD Error] File upload failed: ' . $fileid->get_error_message());
            }
            wp_send_json(['success' => false, 'msg' => $fileid->get_error_message()]);
            return;
        }

        $attch_url = wp_get_attachment_url($fileid);
        if(empty($attch_url)) {
            if (WP_DEBUG) {
                error_log('[ArvanCloud VOD Error] Attachment URL not found for ID: ' . $fileid);
            }
            wp_send_json(['status'=>0, 'msg'=>__('attachment url not found','arvancloud-vod')]);
            return;
        }

        // Create video data
        $video = [
            'title'       => get_the_title($fileid),
            'description' => isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '',
            'video_url'   => $attch_url,
            'convert_mode'=> sanitize_text_field($_POST['convert']),
            'resType'     => '',
            'file_id'     => '',
            'profile_id'  => '',
            'parallel_convert' => false,
            'thumbnail_time' => 1,
            'convert_infox' => '',
        ];

        // Get channel ID
        $channel = empty($_POST['channl']) ? get_option('arvan-cloud-vod-selected_channel_id', true) : sanitize_text_field($_POST['channl']);

        if (empty($channel)) {
            if (WP_DEBUG) {
                error_log('[ArvanCloud VOD Error] No channel selected for upload');
            }
            wp_send_json(['success' => false, 'msg' => __('No channel selected', 'arvancloud-vod')]);
            return;
        }

        // Send to ArvanCloud VOD
        $result = (new Video)->create($video, $channel);

        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD] API Upload response: ' . print_r($result, true));
        }

        // Save response metadata
        update_post_meta($fileid, 'arvan_video_data', $result);

        if(isset($result['status_code']) && $result['status_code'] == 201){
            // Success case - save ID and return
            update_post_meta($fileid, 'ar-vod-media-id', $result['id']);
            wp_send_json([
                'success' => true,
                'name' => $_FILES['file']['name'],
                'edit' => admin_url("admin.php?page=arvancloud-vod-single-video&id={$result['id']}"),
                'url' => wp_get_attachment_url($fileid)
            ]);
        } else {
            // Error case
            $error_message = isset($result['message']) ? $result['message'] : __('Unknown error', 'arvancloud-vod');
            if (WP_DEBUG) {
                error_log('[ArvanCloud VOD Error] API returned error: ' . $error_message);
            }
            wp_send_json([
                'success' => false,
                'msg' => __('Arvan error: ', 'arvancloud-vod') . $error_message . ' (Code: ' . (isset($result['status_code']) ? $result['status_code'] : 'unknown') . ')'
            ]);
        }
    } else {
        if (WP_DEBUG) {
            error_log('[ArvanCloud VOD Error] No file provided for upload');
        }
        wp_send_json(['success' => false, 'msg' => __('No file provided', 'arvancloud-vod')]);
    }
}
add_action('wp_ajax_vod_video_upload','vod_video_upload');
?>
