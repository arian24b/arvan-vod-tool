(function() {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var TextControl = wp.components.TextControl
    
    registerBlockType('my-plugin/livebox-block', {
      title: 'Arvan Live Stream',
      icon: 'format-video',
      category: 'common',

        attributes: {
            url: {
            type: 'string'
            },
        },

        edit: function(props) {
            return el('div', {className: 'editor-styles-wrapper'},
                el('p',null,wp.i18n.__('Please enter the “Player URL” you copied from the Stream Details page:','arvancloud-vod')),
                el(TextControl, {
                    value: props.attributes.url,
                    placeholder: wp.i18n.__('Enter Player URL','arvancloud-vod'),
                    dir:'ltr',
                    onChange: function(value) {
                        props.setAttributes({url: value});
                    }
                }),
            );
        },
        save: function(props) {
        return el('div', {style: {
            position: 'relative',
            overflow: 'hidden',
            width: '100%',
            height: 'auto',
            paddingTop: '56.25%',
        }},
    
        el('iframe', {
        //dangerouslySetInnerHTML: {__html: props.attributes.url},
        style:{position: 'absolute', top: '0', left: '0', width: '100%', height: '100%', border: '0',},
        src:props.attributes.url,
        name:wp.i18n.__("Live stream VOD",'arvancloud-vod') ,
        frameborder:"0",
        allow:"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture",
        allowFullScreen:"true",
        webkitallowfullscreen:"true",
        mozallowfullscreen:"true",
        })
        );
        }
    });
    })();