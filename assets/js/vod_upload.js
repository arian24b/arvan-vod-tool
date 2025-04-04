(function( $ ) {
	'use strict';

	$( document ).ready(function() {

		setTimeout(function() {
			if( typeof uploader == 'undefined' || uploader == null)
				return;
			uploader.bind('FileUploaded', function() {


				const slashPos = window.location.href.lastIndexOf('/');
				let newUrl = window.location.href.substr(0,slashPos);
				newUrl = newUrl.concat('/admin.php?page=arvancloud-vod-videos');
				let url = new URL(newUrl);
				url.searchParams.set('result', 'true');
				//window.location.replace(url);
			});
		}, 1000);

	});

})( jQuery );

setInterval(function(){
    div = jQuery('div.media-item');
    if(div.length==0)
    return;
    div.each(function(i){
        if((fname = jQuery(this).find('span.media-list-subtitle').text()) == '')
        return;
        if(!fname.match(/[mp4|mov|m4v]$/))
            return;
        if(jQuery(this).find('table.vod_upload').length > 0)
        return;
        cls = 'c'+(Math.random() + 1).toString(36).substring(7);

        str = '<details style="display:inline-block;margin-bottom:10px; width:100%;"><summary style="background-color:#d1dbe8;padding:10px 15px; cursor:pointer;">'+json_string['Upload video to arvan']+'</summary>'+
            '<table class="widefat vod_upload" style="margin-top:30px;">'+
                '<thead>'+
                '<tr class="vod_head">'+
                    '<td>'+json_string['Ready to upload the file']+'</td>'+
                    '<td>'+fname+'</td>'+
                '</tr>'+
                '</thead>'+
                '<tbody class="col-12 vod_body">'+

                    '<tr class="attachment-details">'+
                        '<td style="padding:15px;"><img class="pinkynail" src="http://arvancdn.local/wp-includes/images/media/video.png" alt=""></td>'+
                        '<td>'+
                            '<div class="col-4"><strong>'+json_string['File address']+'</strong></div>'+
                            '<div class="col-8"><span class="media-list-subtitle">'+fname+'</span></div>'+
                        '</td>'+
                    '</tr>'+
                    '<tr>'+
                        '<td colspan="2">'+
                            '<div class="column" style="padding-left:10px"><p>'+json_string['File name']+'</p><input type="text" id="fl_name" value="'+fname+'" class="w-100 element form-control" /></div>'+
                            '<div class="column"><p>'+json_string['Short description']+'</p><input type="text" id="describe" class="w-100 element form-control"/></div>'+
                        '</td>'+
                    '</tr>'+
                    '<tr>'+
                        '<td colspan="2">'+
                            '<div class="column" style="padding-left:10px"><p>'+json_string['Video tags']+'</p><input type="text" id="tags" style="background-color:#C2E2F3" class="w-100 element form-control tags '+cls+'" /></div>'+
                            '<div class="column"><p>'+json_string['Transfer to the channel']+'</p><select class="w-100 element form-control channel" style="background-color:#C2E2F3;margin-top:-3px;">'+option_channel+'</select></div>'+
                            '<p><small>'+json_string['You can choose 5 tags']+'</small></p>'+
                        '</td>'+
                    '</tr>'+
                    '<tr>'+
                        '<td colspan="2">'+
                            '<p><strong>'+json_string['Output type']+'</strong></p>'+
                            '<p><bdi><input type="radio" name="out_type" value="auto" checked="checked" /> '+json_string['Automatically']+' <input type="radio" name="out_type" value="manual" /> '+json_string['Manually']+' <input type="radio" name="out_type" value="profile" /> '+json_string['As a profile']+'</bdi></p>'+
                            '<p><small>'+json_string['Determine with what quality the face was ready for display.']+'</small></p>'+
                        '</td>'+
                    '</tr>'+
                    '<tr class="manual" style="display:none;">'+
                        '<td>'+
                            '<p>'+json_string['Video type']+'</p>'+
                            '<div class="vid_type" style="border:1px solid green;"><p>'+json_string['AutomatHorizontal modeically']+'</p><p><small>'+json_string['From 144p to 1080p quality']+'</small></p><input type="radio" name="ptype" value="landscape" class="d-none" checked="checked" /></div>'+
                            '<div class="vid_type"><p>'+json_string['Vertical mode']+'</p><p><small>'+json_string['From 144p to 1080p quality']+'</small></p><input type="radio" name="ptype" class="d-none" value="portrait" /></div>'+
                        '</td>'+
                        '<td>'+
                            '<p>'+json_string['Image resolution']+'</p>'+
                            '<div class="w-100">'+
                            '<table class="table resulation"><thead><tr><td><span class="dashicons dashicons-plus add-btn"></span></td><td>'+json_string['resolution']+'</td><td>'+json_string['Image bitrate']+'</td><td>'+json_string['Audio bitrate']+'</td></tr></thead><tbody></tbody></table>'+
                            '</div>'+
                        '</td>'+
                    '</tr>'+
                    '<tr class="profile" style="display:none;">'+
                        '<td colspan="2"><select class="prof_list"><option value="">'+json_string['Select']+'</option></select></td>'+
                    '</tr>'+
                    '<tr style="text-align:center;">'+
                        '<td colspan="2">'+
                            '<button type="button" class="btn button cancel" >'+json_string['Cancel']+'</button>'+
                            '<button type="button" class="btn send" ><span class="dashicons dashicons-arrow-up-alt"></span>'+json_string['Begin upload video']+'</button>'+
                        '</td>'+
                    '</tr>'+
                    '<tr><td colspan="2" class="msg"></td></tr>'+
                '</tbody>'+
            '</table>'+
        '</details>';
        jQuery(this).append(str);

        new Tagify(document.querySelector('input.'+cls),{
            maxTags: 5,
            duplicates: false,
        });

    });
}, 1000);

jQuery('body').on('click','.vid_type',function(){
   jQuery('.vid_type').removeAttr('style').find(':radio').removeAttr('checked');
   jQuery(this).css({border:'1px solid green'}).find(':radio').prop('checked', true);
});

jQuery('body').on('click','.add-btn',function(){
    str = '<tr class="bit"><td><span class="dashicons dashicons-trash delete"></span></td><td><select class="resulate"><option value="1920x1080">1080P</option><option value="1280x720">720P</option><option value="640x480">480P</option><option value="480x360">360P</option><option value="426x240">240P</option><option value="256x144">144P</option></select></td><td><input type="text" value="4500" class="bit_video"/></td><td><input type="text" value="320" class="bit_sound"/></td></tr>';
    jQuery('.resulation tbody').append(str);
});

jQuery('body').on('click','.delete',function(){
    if(confirm(json_string['Do you want delete this resulation?'])){
        jQuery(this).closest('tr').remove();
    }
});

jQuery('body').on('click','.send',function(){

    var btn = jQuery(this);
    var vid_id = jQuery(this).closest('div.media-item').find('a.edit-attachment').attr('href').match(/post=([0-9]+)/);

    if(vid_id.length>0){
            
            var div = jQuery(this).closest('table.vod_upload');
            channl= div.find('.channel').val();
            if(channl == ''){
                alert(json_string['Please select a channel']);
                return;
            }
            fname = div.find('#fl_name').val();
            descr = div.find('#describe').val();
            tags  = div.find('#tags').val();
            profl = '';
            otype = div.find('[name="out_type"]:checked').val();
            ptype = '';

            var resulation=[];
            if(otype=='manual'){
                if(jQuery('.bit').length>0){
                    ptype = div.find('[name="ptype"]:checked').val();
                    
                    jQuery('.bit').each(function(){
                        resul = jQuery(this).find('.resulate').val();
                        video = jQuery(this).find('.bit_video').val();
                        sound = jQuery(this).find('.bit_sound').val();
                        size = resul.split('x');
                        resulation.push({'selectedRes':{'title':size[1]+'p','value':size[1]},'resolution':resul,'video_bitrate':video,'audio_bitrate':sound,'width':size[0],'height':size[1]});
                    });
                }else{
                    alert(json_string['Please select a resulation']);
                    return;
                }
            }
            if(otype=='profile'){
                profl = div.find('.prof_list').val();
                if(profl==''){
                    alert(json_string['Please select a profile']);
                    return;
                }
            }
                
            btn.prop('disabled',true);
            jQuery.post(ajaxurl,{action:'vod_upload_custom_video',id:vid_id[1],fname:fname,descr:descr,tags:tags,channl:channl,otype:otype,ptype:ptype,resulation:resulation,profile:profl},function(resp){
                if(resp.status == 1){
                    div.find('.msg').html('<p style="color:green;text-align:center;">'+json_string['Upload video to arvan successfull.']+'</p>');
                }else{
                    div.find('.msg').html('<p style="color:red;text-align:center;">'+json_string['Error upload video to arvan server']+'</p>');
                }
                btn.prop('disabled',false);
            });
        }else
        alert(json_string['File ID not found']);

});

jQuery('body').on('click','.cancel',function(){
    if(confirm(json_string['Do you want cancel upload this video?'])){
        jQuery(this).closest('.vod_upload').remove();
    }
});

jQuery('#file-form').submit(function(e){
    e.preventDefault();
});
/**
 * select video type
 */
jQuery('body').on('click','[name="out_type"]',function (e) { 
    if(jQuery(this).val()=='manual')
        jQuery(this).closest('.vod_body').find('.manual').show();
    else
        jQuery(this).closest('.vod_body').find('.manual').hide();
    
    if(jQuery(this).val()=='profile')
        jQuery(this).closest('.vod_body').find('.profile').show();
    else
        jQuery(this).closest('.vod_body').find('.profile').hide();
});
/**
 * load profile settings
 */
jQuery('body').on('change','.channel',function(){
    jQuery.post(ajaxurl,{action:'vod_get_profiles',chanel:jQuery(this).val()},function(resp){
        if(resp.data != '')
        jQuery('.prof_list').html(resp.data);
    });
});
