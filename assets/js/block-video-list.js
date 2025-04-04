(function() {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var TextControl = wp.components.TextControl;
    var select      = wp.components.SelectControl;
    var button      = wp.components.Button;

    registerBlockType('my-plugin/videobox-block', {
      title: 'Arvan video List',
      icon: 'playlist-video',
      category: 'common',

        attributes: {
            search: {
            type: 'string',
            default:'',
            },
            stype:{
                type: 'string',
                default:'title',
            },
            info:{
                type:'string',
                source: "html",
                selector: 'div',
            }
        },

        edit: function(props) {
            return el('div', {className: 'editor-styles-wrapper'},
            el(select,{
                label: wp.i18n.__('Select search type:','arvancloud-vod'),
                id:'type_box',
                options: [
                    { label: wp.i18n.__('Title','arvancloud-vod'), value: 'title' },
                    { label: wp.i18n.__('Description','arvancloud-vod'), value: 'descr' },
                    { label: wp.i18n.__('Tag','arvancloud-vod'), value: 'tag' },
                ],
                onChange: function(value) {
                    props.setAttributes({stype: value});
                },
                value: props.attributes.stype,
            },),
            el(TextControl, {
                    label: wp.i18n.__('Search text:','arvancloud-vod'),
                    id:'search_box',
                    placeholder:'Search',
                    value: props.attributes.search,
                    onChange: function(value) {
                        props.setAttributes({search: value});
                    }
            }),
            el(button,{
                icon: 'search',
                className: 'save_attr',
			    isPrimary: true,
                text : wp.i18n.__('Search','arvancloud-vod'),
                onClick: function(e) {
                    var data = [];
                    var btn  = jQuery(e.target);
                    btn.closest('div').find('.vloading').show();
                    btn.attr('disabled','disabled');
                    jQuery.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {action:'vod_search_video_list',type:props.attributes.stype,search:props.attributes.search},
                        async: true,
                        success: function(resp){
                            btn.removeAttr('disabled');
                            btn.closest('div').find('.vloading').hide();
                            btn.closest('div').find('.msg').text(resp.msg);
                            if(resp.status==0)
                            return;
                            props.setAttributes({info: resp.data});
                        }
                    },'text');

                }
            }),
            el('span',{
                className:'spinner is-active vloading',
                style:{
                    display:'none',
                },
            }),
            el('p',{
                className:'msg',
                style:{
                    textAlign:'center',
                    display:'inline-block',
                    margin:'-10px 20px 0 20px',
                    width:'70%',
                },
            }),
            );
        },
        save: function(props) {
            return el('div', {style: {
                display: 'flex',
                flexFlow: 'row wrap',
                width: '100%',
                justifyContent:'space-between',
                alignItems:'flex-start',
                gap:'10px',
                },
                className:'media_list_block',
                dangerouslySetInnerHTML: { __html: props.attributes.info }
            },);
        }
    });
    })();