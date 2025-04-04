<?php

namespace WP_Arvan\Engine\API\VOD;

use WP_Arvan\Engine\API\HTTP\Request_Arvan;

class video_tags{

    private $tag_list = [];

    public function __construct(){
        $this->tag_list = Request_Arvan::get("tags");
    }

    public function add_tag($title){

        if(!empty($this->tag_list)){
            foreach($this->tag_list as $tags){
                if($tags['title'] == $title)
                    return $tags;
            }
        }
        
        $result = Request_Arvan::post("tags",json_encode(['title'=>$title]));
        return $result->status_code==201?json_decode($result->body, true)['data']:false;
    }

    public function save_tags($video_id,$tags){
        
        $tags = is_string($tags)?explode(',',$tags):$tags;
        if(empty($tags))
            return;
        $video = Request_Arvan::get("videos/$video_id");
        if(!isset($video['id']))
            return;

        $tg_list = [];
        foreach($tags as $tag){
            if($tg = self::add_tag($tag))
            $tg_list[] = $tg['id'];
        }
        $result = Request_Arvan::patch("videos/$video_id",json_encode( ['id'=>$video['id'],'tags'=>$tg_list] ) );
        return $result->status_code==200?json_decode($result->body)->data:false;
    }

    public function get_video_tags($video_id){
        $tags = Request_Arvan::get("videos/$video_id/tags",false);
        
        return $tags['response']['code']==200?array_column(json_decode($tags['body'], true),'title'):'';
    }

    public function get_video_by_tag($tags){
        if(empty($this->tag_list))
            return false;
        $tags  = explode(',',$tags);
        $found = [];
        foreach($tags as $tag){
            foreach($this->tag_list as $list){
                if($list['title'] == $tag){
                    $found[] = $list['id'];
                    break;
                }
            }    
        }
        if(empty($found))
        return false;

        return Request_Arvan::get("videos/search?filters[tag_id]=in(".implode(',',$found).')');
    }
}