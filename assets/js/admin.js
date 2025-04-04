(function( $ ) {
	'use strict';

	$( document ).ready(function() {

		if ( $( '.toplevel_page_arvancloud-vod-videos .view-switch.media-grid-view-switch' ).length > 0 ) {
			let $list = $( '.toplevel_page_arvancloud-vod-videos .view-switch.media-grid-view-switch .view-list' );
			let $grid = $( '.toplevel_page_arvancloud-vod-videos .view-switch.media-grid-view-switch .view-grid' );

			$list.prop( 'href', AR_VOD.videoGallery + '&mode=list' );
			$grid.prop( 'href', AR_VOD.videoGallery + '&mode=grid' );
		}

		if(typeof vod_is_api_valid == 'undefined')
			return;
		// Local reference to the WordPress media namespace.
		var media = wp.media;
		// Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
		var wpAttachmentDetailsVOD = media.view.Attachment.Details.TwoColumn;
		if ( wpAttachmentDetailsVOD !== undefined ) {

			media.view.Attachment.Details.TwoColumn = wpAttachmentDetailsVOD.extend( {
				render: function() {
					// Retrieve the S3 details for the attachment
					// before we render the view
					this.fetchVODDetails( this.model.get( 'id' ) );
				},

				fetchVODDetails: function( id ) {
					const obj = this;
					let result = [];
					let counter =0;
					wp.ajax.send( 'aco_get_attachment_provider_details', {
						data: {
							_nonce: AR_VOD.nonces.get_attachment_provider_details,
							id: id
						}
					} ).done( function(res){
						result.push(res);

						if(typeof acs_media == 'undefined'){
							obj.renderView(result);
							return;
						}

						counter++;
						if(counter > 1)
							obj.renderView(result);
					} );
					if( typeof acs_media !== 'undefined') {
						wp.ajax.send('acs_get_attachment_provider_details', {
							data: {
								_nonce: acs_media.nonces.get_attachment_provider_details,
								id: id
							}
						}).done(function (res) {
							result.push(res)
							counter++;
							if (counter > 1)
								obj.renderView(result);
						});
					}

				},

				renderView: function( response ) {

					// Render parent media.view.Attachment.Details
					wpAttachmentDetailsVOD.prototype.render.apply( this );

					this.renderVODActionLinks( response );
				},
				renderVODActionLinks: function( response ) {

					var has_links = ( response && response.length >0 );
					if(!has_links)
						return false;
					var $actionsHtml = this.$el.find( '.actions' );
					var $s3Actions = $( '<div />', {
						'class': 'acs-actions'
					} );

					var s3Links = [];
					_( response ).each( function( link ) {

						if(link.links.acs_copy_to_vod)
							s3Links.push( link.links.acs_copy_to_vod );
						if(link.links.acs_copy)
							s3Links.push( link.links.acs_copy );
					} );

					$s3Actions.append( s3Links.join( ' | ' ) );
					$actionsHtml.append( $s3Actions );
				},

			} );
		}

	})


})( jQuery );

jQuery(document).ready(function($){
    $(document).on('click', '.mce-my_upload_button', upload_image_tinymce);




function upload_image_tinymce(e) {
	e.preventDefault();
	var $input_field = $('.mce-my_input_image');
	var custom_uploader = wp.media.frames.file_frame = wp.media({
		title: 'Add Video',
		button: {
			text: 'Add Video'
		},
		library: {
			type: [
				'video/MP4',
				'video/quicktime',
				'video/x-m4v',
			],
			r1c: true,
		},
		multiple: false
	});
	custom_uploader.on('select', function() {
		var attachment = custom_uploader.state().get('selection').first().toJSON();
		$input_field.val(attachment.id);
	});
	custom_uploader.open();
}

	$('.acvod-code-snippet').on( 'click', function() {
		var $self = $(this);
		copyTextToClipboard($self.children('.code_snippet_data').children('pre').html())

		$self.children('.acvod-copy-code').children('.c-vodCopyButton__copyIcon').addClass('c-vodCopyButton__copyIcon--hide');
		$self.children('.acvod-copy-code').children('.c-vodCopyButton__checkIcon').addClass('c-vodCopyButton__checkIcon--show');

		setTimeout(function() {
			$self.children('.acvod-copy-code').children('.c-vodCopyButton__copyIcon').removeClass('c-vodCopyButton__copyIcon--hide');
			$self.children('.acvod-copy-code').children('.c-vodCopyButton__checkIcon').removeClass('c-vodCopyButton__checkIcon--show');
		}, 800);
	})

});

function fallbackCopyTextToClipboard(text) {
	var textArea = document.createElement("textarea");
	textArea.value = text;

	// Avoid scrolling to bottom
	textArea.style.top = "0";
	textArea.style.left = "0";
	textArea.style.position = "fixed";

	document.body.appendChild(textArea);
	textArea.focus();
	textArea.select();

	try {
	  document.execCommand('copy');
	} catch (err) {
	  console.error('Fallback: Oops, unable to copy', err);
	}

	document.body.removeChild(textArea);
}

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

function copyTextToClipboard(text) {

	text = decodeHtml(text);

	if (!navigator.clipboard) {
	  fallbackCopyTextToClipboard(text);
	  return;
	}
	navigator.clipboard.writeText(text).then(function() {
	}, function(err) {
	  console.error('Async: Could not copy text: ', err);
	});
}

jQuery(document).on('click', '.vod-input-dropdown__select', function() {
	jQuery(this).next('.vod-input-dropdown__list').toggleClass('active');
	jQuery(this).parent('.vod-input-dropdown').toggleClass('active');
});

jQuery('.vod-input-dropdown__list ul li').on('click', function() {
	var selectedText = jQuery(this).text();
	jQuery(this).parents().prev('.vod-input-dropdown__select').find('span').html(selectedText);
	jQuery('.vod-input-dropdown__list').removeClass('active').addClass('selected');
	jQuery(this).parents('.vod-input-dropdown').removeClass('active');
});

function copyLinkVidAddress() {
  var copyText = document.getElementById("vid_url_address");
	var notif  = `<div class="url-vod">Copied <i class="arvicon success"></i></div>`;
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices
  copyText.focus();
  if (navigator.clipboard && window.isSecureContext)
  	navigator.clipboard.writeText(copyText.value);
  else
  document.execCommand('copy');
	jQuery('.vod-card-video-upload__details-link--value').append(notif);
	setTimeout(() => {
		jQuery('.vod-card-video-upload__details-link--value').find('.url-vod').remove();
	}, 2000);
}