<?php
use WP_Arvan\Engine\API\HTTP;
use WP_Arvan\Engine\API\HTTP\Request_Arvan;
use WP_Arvan\Engine\API\VOD\video_tags;

class Video_Hooks
{
    function __construct(){
        /**
         * add video thumbnil to admin media list
         */
        add_filter('wp_mime_type_icon',[$this,'wp_mime_type_icon'],9999,3);
        //add_filter('attachment_fields_to_edit', [$this,'attachment_fields_to_edit'], 10, 2);
        //add_filter('attachment_fields_to_save', [$this,'save_attachment_field'], 10, 2);
        /**
         * add slug metabox to edit attachment
         */
        add_action( 'add_meta_boxes', [$this,'add_attachment_meta_boxes'] );
        add_action( 'edit_attachment', [$this,'save_post_attachment'] ,10,1);
        if(isset($_GET['arvan_notice'])){
            add_action( 'admin_notices', function(){
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e('Error send tags to arvan','arvancloud-vod'); ?></p>
                </div>
                <?php
            } );
        }
    }
    
    function wp_mime_type_icon($icon, $mime = null, $post_id = null){
        if( $video = get_post_meta($post_id,'acv_video_data',true) ){
            return empty($video['thumbnail_url'])?$icon:$video['thumbnail_url'];
        }
        return $icon;
    }
    
    /*function attachment_fields_to_edit( $form_fields, $post ) {
        $mime = array(
			'video/mp4',
			'video/quicktime',
			'video/x-m4v',);

        if(!in_array($post->post_mime_type,$mime))
        return $form_fields;
        
        $vid_id = get_post_meta($post->ID,'ar-vod-media-id',true);
        if(empty($vid_id))
            return;
        
        $text_field = get_post_meta($post->ID, 'tags', true);

        $form_fields['stamp'] = array(
            'label' => '',
            'input' => 'html', // you may alos use 'textarea' field
            'html'  => '<script>jQuery(".compat-field-stamp td").css({width:"100%"});</script>
            <div id="dashboard-widgets" class="metabox-holder">
            <div id="postbox-container-1" class="postbox-container" style="width:100% !important;float:'.(is_rtl()?'right':'left').';">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    <div id="metabox" class="postbox">
                        <button type="button" class="handlediv" aria-expanded="true">
                            <span class="screen-reader-text">Metabox collapse</span>
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                        <h2 class="hndle ui-sortable-handle"><span>'.__('Tags','arvancloud-vod').'</span></h2>
                        <div class="inside">
                            <div class="main">
                                <p><strong>'.__('Add video tags','arvancloud-vod').'</strong></p>
                                <p>
                                <input name="attachments['.$post->ID.'][tags]" id="components-form-token-input-1" type="text" autocomplete="off" class="components-form-token-field__input" role="combobox" aria-expanded="false" aria-autocomplete="list" aria-describedby="components-form-token-suggestions-howto-1" value="'.$text_field.'" title="press inter to save">
                                </p>
                                <p id="components-form-token-suggestions-howto-0" class="components-form-token-field__help">'.__('separate tags with comma','arvancloud-vod').'</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>',
        );
        return $form_fields;
    }

    function save_attachment_field($post, $attachment){

        if(!empty($attachment['tags'])){
            $vid_id = get_post_meta($post['ID'],'ar-vod-media-id',true);
            if(empty($vid_id))
                return $post;

            $result = (new video_tags)->save_tags( $vid_id, trim( $attachment['tags'] ) );
            if(!empty($result)){
                update_post_meta($post['ID'], 'tags', sanitize_text_field( $attachment['tags'] ));
            }else{
                $post['errors']['post_title']['errors'][] = __( 'Empty Title filled from filename.' );
            }
        }
        return $post;
    }*/

    function add_attachment_meta_boxes(){
        $vid_id = get_post_meta(esc_sql($_GET['post']),'ar-vod-media-id',true);
        if(empty($vid_id))
            return;
        add_meta_box(
            'tag_attachment_mtbox',
            __( 'Tags','arvancloud-vod'),
            [$this,'attachment_tag_meta_box'],
            'attachment',
            'side',
            'default'
          );
    }

    function attachment_tag_meta_box($post){
        $vid_id = get_post_meta(esc_sql($_GET['post']),'ar-vod-media-id',true);
        $tags = (new video_tags)->get_video_tags($vid_id);
        
        //$tags = wp_get_post_tags($post->ID,['fields'=>'names']);
        ?>
            <div class="tagsdiv" id="post_tag">
                <div class="jaxtag">
                    <div class="ajaxtag hide-if-no-js">
                        <label class="screen-reader-text" for="new-tag-post_tag"><?php _e('Add','Add New Tag'); ?></label>
                        <input data-wp-taxonomy="post_tag" type="text" id="new-tag-post_tag" class="newtag form-input-tip ui-autocomplete-input" size="16" autocomplete="off" aria-describedby="new-tag-post_tag-desc" value="" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-owns="ui-id-1">
                        <input type="button" class="button tagadd" value="<?php _e('Add','arvancloud-vod'); ?>">
                        <input type="hidden" name="newtag" class="video_tags" value="<?php echo implode(',',$tags); ?>"/>
                    </div>
                    <p class="howto" id="new-tag-post_tag-desc"><?php _e('Separate tags with commas','arvancloud-vod'); ?></p>
                </div>
                <ul class="tagchecklist" role="list">
                <?php
                if(!empty($tags)){
                    foreach($tags as $tag){
                        echo '<li><button type="button" id="product_tag-check-num-2" class="ntdelbutton" data-item="'.$tag.'"><span class="remove-tag-icon" aria-hidden="true"></span><span class="screen-reader-text">حذف شرط: two</span></button>&nbsp;'.$tag.'</li>';
                    }
                }
                ?>
                </ul>
            </div>
            <script>
            jQuery(document).ready(function($) {
                jQuery('.tagadd').on('click',function(){
                
                    new_tags = jQuery('#new-tag-post_tag').val().split(',');
                    new_tags = new_tags.map(n=>n.trim());
                    new_tags = new_tags.filter(Boolean);
                    var old_tags = jQuery('.video_tags').val().split(',');
                    console.log(old_tags);
                    jQuery.each(new_tags,function(i,item){

                        if(old_tags.includes(item))
                        return;
                    
                        old_tags += old_tags == ''?item:','+item;
                        jQuery('.tagchecklist').append('<li><button type="button" id="product_tag-check-num-2" class="ntdelbutton" data-item="'+item+'"><span class="remove-tag-icon" aria-hidden="true"></span><span class="screen-reader-text">حذف شرط: two</span></button>&nbsp;'+item+'</li>');
                    });
                    jQuery('.video_tags').val(old_tags);
                    jQuery('#new-tag-post_tag').val('');
                });
            });

            jQuery("#new-tag-post_tag").keypress(function(event) {
                if (event.which == 13){
                    jQuery('.tagadd').trigger('click');
                    event.preventDefault();
                }
            });

            jQuery('body').on('click','.ntdelbutton',function(){
                jQuery(this.closest('li')).remove();
                old_tags = jQuery('.video_tags').val().split(',');
                old_tags.splice(old_tags.indexOf(jQuery(this).data('item')),1);
                jQuery('.video_tags').val(old_tags.join(','));
                console.log(old_tags);
            });
            </script>
        <?php
    }

    function save_post_attachment($post_id){
        if(empty($_POST['newtag']))
        return;
        //wp_set_post_tags($post_id,explode(',',$_POST['newtag']),false);
        $vid_id = get_post_meta($post_id,'ar-vod-media-id',true);
        $result = (new video_tags)->save_tags( $vid_id, trim( $_POST['newtag'] ) );

        if(empty($result)){
            $key = 'Error';
            add_filter('redirect_post_location',
                function ( $location ) use ( $key ) {
                    return add_query_arg( array( 'arvan_notice' => rawurlencode( sanitize_key( $key ) ) ), $location );
                });
        }
    }
}
