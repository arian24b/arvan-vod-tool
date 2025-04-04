<?php
    use WP_Arvan\Engine\API\HTTP\Request_Arvan;
    
	add_shortcode('arvan_video_list','arvan_video_list');
    
    function arvan_video_list($atts){
        if(empty($atts['tags'])and empty($atts['title'])and empty($atts['description']))
        return '';
        if(isset($atts['tags'])){
            $videoes = Request_Arvan::get("/tags?filter=".urlencode($atts['tags']));
            if($videoes['status_code']!=200)
                return '';
        }else{
            
            $vars = ['filters'=>[]];
            
            if(!empty($atts['title']))
                $vars['filters']['title'] = "like({$atts['title']})";
                
            if(!empty($atts['description']))
                $vars['filters']['description'] = "like({$atts['description']})";
                
            if(!empty($atts['channel_id']))
                $vars['filters']['channel_id'] = $atts['channel_id'];
                
            $videoes = Request_Arvan::get("/videos/search?".http_build_query($vars));
            if($videoes['status_code']!=200)
                return '';
        }
        if(count($videoes)<=1)
        return '';
        ob_start();

        ?>
        <table class="table">
            <tbody>
            <?php
            foreach($videoes as $video){
                if($video==200)
                break;
                $vid = Request_Arvan::get("/videos/{$video['id']}");
                if($vid['status_code']!=200)
                    continue;

                echo "
                <tr>
                <td>
                    <a href='{$vid['player_url']}' target='BLANK' title='{$vid['description']}'>{$vid['title']}</a>
                </td>
                <tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
?>