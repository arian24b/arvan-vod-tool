(function() {
    tinymce.PluginManager.add('r1c_vod', function( editor, url ) {
        editor.addButton( 'r1c_vod', {
            text: tinyMCE_object.button_name,
            icon: false,
            onclick: function() {
                editor.windowManager.open( {
                    title: tinyMCE_object.button_title,
                    body: [
                        {
                            type: 'textbox',
                            name: 'video_post_id',
                            label: tinyMCE_object.image_title,
                            value: '',
                            classes: 'my_input_image',
                        },
                        {
                            type: 'button',
                            name: 'my_upload_button',
                            label: '',
                            text: tinyMCE_object.image_button_title,
                            classes: 'my_upload_button',
                        },
                    ],
                    onsubmit: function( e ) {
                        editor.insertContent( '[r1c-vod video_post_id="' + e.data.video_post_id + '"]');
                    }
                });
            },
        });
    });

})();
