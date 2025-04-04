<?php
    use WP_Arvan\Engine\API\HTTP\Request_Arvan;
    
	add_shortcode('arvan_show_stream','arvan_show_stream');
    
    function arvan_show_stream($atts){
        if(empty($atts['stream_id']))
        return '';
        $stream = Request_Arvan::get_stream($atts['stream_id']);
        if($stream['status_code']!=200)
        return '';
        //player_url
        return '<style>.r1_iframe_embed {position: relative; overflow: hidden; width: 100%; height: auto; padding-top: 56.25%; } .r1_iframe_embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }</style>
        <div class="r1_iframe_embed">
            <iframe src="'.$stream['input_url'].'" style="border:0 #ffffff none;" name="'.__('Arvan media stream', 'arvancloud-vod').'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true">
            </iframe>
        </div>';
    }
?>