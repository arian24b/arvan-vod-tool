<?php
use WP_Arvan\Engine\API\VOD\Channels;
use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\Helper;

$Channels = new Channels;
$video    = new Video;
$helper   = new Helper;

$default  = empty($_POST['channel_id'])?get_option( 'arvan-cloud-vod-selected_channel_id'):$_POST['channel_id'];
$chan_list= $Channels->get_channels();
$html_li  = $title_li = '';

if(!empty($chan_list)){
	foreach($chan_list as $chanl){
		$active = '';
		if($chanl['id'] == $default){
			$title_li = $chanl['title'];
			$active   = 'active';
		}
		$html_li .= "<li class='list-item $active' data-id='{$chanl['id']}'>{$chanl['title']}</li>";
	}
}

if(!empty($_POST['vod_search']))
	$vid_list = $video->search($default,['title'=>"like({$_POST['vod_search']})"]);
else
	$vid_list = $video->showAll($default);
?>
<style>
	li.active{
		background-color: silver;
	}
	.vod-input-icon .search{
		cursor:pointer;
	}
	.vod-input-icon .search:hover{
		opacity:0.6;
	}
</style>
<div class="wrap">
<form method="post" id="post_form">
	<input type="hidden" name="channel_id" value="<?php echo $default; ?>"/>

	<?php if(empty($vid_list[0])){ ?>

	<h1 class="heading-title-one"><?php echo esc_html_e( 'Video Library', 'arvancloud-vod' ) ?></h1>
	<div class="arvan-vod-wrapper">
		<div>
			<p>
			<a href="#"><?php _e('Video arvan cloud platform', 'arvancloud-vod' ); ?></a><?php _e('Prepared for easier access to your videos', 'arvancloud-vod' ); ?>
			</p>
		</div>

		<div class="vod-card-wide full-height">
			<div class="vod-card-wide__header">
				<div class="vod-input-dropdown">
					<div class="vod-input-dropdown__select">
						<span><?php echo $title_li; ?></span>
						<i class="arvicon arrow-down"></i>
					</div>
					<div class="vod-input-dropdown__list">
						<ul>
						<?php echo $html_li; ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="vod-card-wide__nothing">
					<img src="<?php echo ACVOD_PLUGIN_ROOT_URL . 'assets/images/nothing-vod.svg' ?>" alt="">
					<p class="vod-card-wide__nothing-title"><?php _e('No file found', 'arvancloud-vod'); ?></p>
					<p class="vod-card-wide__nothing-desc"><?php _e('No video was found in this channel', 'arvancloud-vod'); ?></p>
					<a href="<?php echo admin_url('admin.php?page=arvancloud-vod-new-video'); ?>" class="vod-btn primary"><?php _e('New video', 'arvancloud-vod'); ?>
						<i class="arvicon plus"></i>
					</a>
				</div>
		</div>
	</div>

<?php }else{ ?>
	<h1 class="heading-title-one"><?php echo esc_html_e( 'Video Library', 'arvancloud-vod' ) ?></h1>
	<div class="arvan-vod-wrapper">
		<div>
			<p>
			<a href="#"><?php _e('Video arvan cloud platform', 'arvancloud-vod' ); ?></a><?php _e('Prepared for easier access to your videos', 'arvancloud-vod' ); ?>
			</p>
		</div>

		<div class="vod-card-wide half-height">
			<div class="vod-card-wide__header">
				<div class="vod-input-dropdown">
					<div class="vod-input-dropdown__select">
						<span><?php echo $title_li; ?></span>
						<i class="arvicon arrow-down"></i>
					</div>
					<div class="vod-input-dropdown__list">
						<ul>
						<?php echo $html_li; ?>
						</ul>
					</div>
				</div>
				<div class="d-flex">
					<div class="vod-input-icon">
						<input type="text" name="vod_search" value="<?php echo isset($_POST['vod_search'])?$_POST['vod_search']:''; ?>" class="vod-input" placeholder="<?php _e('Search in videos ...', 'arvancloud-vod'); ?>">
						<i class="arvicon search"></i>
					</div>
					<a href="<?php echo admin_url('admin.php?page=arvancloud-vod-new-video'); ?>" class="vod-btn primary mr-8">
						<i class="arvicon plus"></i>
						<?php _e('New video', 'arvancloud-vod'); ?>
					</a>
					<!-- Add Sync Button -->
					<button type="button" id="sync-from-vod" class="vod-btn secondary mr-8">
						<i class="arvicon refresh"></i>
						<?php _e('Sync from VOD', 'arvancloud-vod'); ?>
					</button>
					<!-- Add Debug Button -->
					<button type="button" id="debug-vod-sync" class="vod-btn secondary mr-8">
						<i class="arvicon search"></i>
						<?php _e('Debug Sync', 'arvancloud-vod'); ?>
					</button>
					<!-- Add Repair Button -->
					<button type="button" id="repair-vod-media" class="vod-btn secondary mr-8">
						<i class="arvicon wrench"></i>
						<?php _e('Repair Media', 'arvancloud-vod'); ?>
					</button>
				</div>
			</div>

			<div class="vod-card-wide__card-row">
			<?php
			$pagination = $helper->paging($vid_list,8);
			foreach($vid_list as $vid){
				if(!is_array($vid))
				break;
				$img = empty($vid['thumbnail_url'])?ACVOD_PLUGIN_ROOT_URL.'assets/images/vid-cover-big.png':$vid['thumbnail_url'];
				echo '
				<div class="vod-card-wide__card-video">
					<div class="vod-card-wide__card-video--box">
						<div class="vod-card-wide__card-video--header">
							<figure>
								<img src="'.$img.'" alt="">
							</figure>
							<span class="vod-card-wide__card-video--quality">'.$vid['file_info']['video']['height'].'p</span>
							<span class="vod-card-wide__card-video--time">'.$vid['file_info']['general']['duration'].'S</span>
						</div>
						<div class="vod-card-wide__card-video--title">
							<h5 class="heading-title-five">'.pathinfo($vid['title'], PATHINFO_FILENAME).'</h5>
						</div>
						<div class="vod-card-wide__card-video--cta">
							<span><bdi>'.date('Y M d',strtotime($vid['created_at'])).'</bdi></span>
							<a href="'.admin_url("admin.php?page=arvancloud-vod-single-video&id={$vid['id']}").'" target="_blank" alt="'.__('Show video','arvancloud-vod').'">'.__('Show video','arvancloud-vod').'</a>
						</div>
					</div>
				</div>';
			}
			?>
			</div>
			<?php echo $pagination; ?>
		</div>
	</div>
	<?php } ?>
</form>
</div>

<!-- Add detailed sync notification div -->
<div id="sync-notification" style="display: none; position: fixed; top: 50px; right: 15px; z-index: 9999; padding: 15px; border-radius: 4px; background-color: #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); max-width: 400px; max-height: 80vh; overflow-y: auto;"></div>

<script>
	jQuery(document).ready(function($){
		$('.vod-input-dropdown__list li').click(function(){
			id = $(this).data('id');
			if(id != $('[name="channel_id"]').val()){
				$('[name="channel_id"]').val(id);
				$('#post_form').submit();
			}
		});
		$('.search').click(function(){
			$('#post_form').submit();
		});

		// Handle sync button click with improved error handling
		$('#sync-from-vod').click(function() {
			var $button = $(this);
			var $notification = $('#sync-notification');

			// Disable button and show loading state
			$button.prop('disabled', true).html('<i class="arvicon spinner"></i> <?php _e("Syncing...", "arvancloud-vod"); ?>');

			// Display notification
			$notification.html('<i class="arvicon spinner"></i> <?php _e("Syncing videos from ArvanCloud VOD...", "arvancloud-vod"); ?>').show();

			// Perform AJAX request with timeout
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'arvancloud_vod_manual_sync',
					nonce: '<?php echo wp_create_nonce("arvancloud_vod_nonce"); ?>'
				},
				timeout: 300000, // 5 minute timeout
				success: function(response) {
					if (response.success) {
						var message = '<i class="arvicon success"></i> ' + response.data.message;

						// Add details if there were errors
						if (response.data.details && response.data.details.length > 0) {
							message += '<br><br><strong><?php _e("Error Details:", "arvancloud-vod"); ?></strong><ul>';
							for (var i = 0; i < response.data.details.length; i++) {
								message += '<li>' + response.data.details[i] + '</li>';
							}
							message += '</ul>';
						}

						$notification.html(message).show();

						setTimeout(function() {
							$notification.fadeOut();
						}, 10000);
					} else {
						var errorMsg = '<i class="arvicon error"></i> ' + response.data.message;

						// Add details if available
						if (response.data.details && response.data.details.length > 0) {
							errorMsg += '<br><br><strong><?php _e("Error Details:", "arvancloud-vod"); ?></strong><ul>';
							for (var i = 0; i < response.data.details.length; i++) {
								errorMsg += '<li>' + response.data.details[i] + '</li>';
							}
							errorMsg += '</ul>';
						}

						$notification.html(errorMsg).show();
					}

					// Re-enable button
					$button.prop('disabled', false).html('<i class="arvicon refresh"></i> <?php _e("Sync from VOD", "arvancloud-vod"); ?>');

					// Refresh page after successful sync with imported videos
					if (response.success && response.data.counts && response.data.counts.imported > 0) {
						setTimeout(function() {
							window.location.reload();
						}, 3000);
					}
				},
				error: function(xhr, status, error) {
					var errorMessage = '';

					if (status === 'timeout') {
						errorMessage = '<?php _e("The request timed out. The sync process might still be running in the background.", "arvancloud-vod"); ?>';
					} else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
						errorMessage = xhr.responseJSON.data.message;
					} else {
						errorMessage = '<?php _e("An error occurred during the sync process.", "arvancloud-vod"); ?> ' + error;
					}

					$notification.html('<i class="arvicon error"></i> ' + errorMessage).show();
					$button.prop('disabled', false).html('<i class="arvicon refresh"></i> <?php _e("Sync from VOD", "arvancloud-vod"); ?>');
				}
			});
		});

		 // Add debug button handler
        $('#debug-vod-sync').click(function() {
            var $button = $(this);
            var $notification = $('#sync-notification');

            // Disable button and show loading state
            $button.prop('disabled', true).html('<i class="arvicon spinner"></i> <?php _e("Debugging...", "arvancloud-vod"); ?>');

            // Display notification
            $notification.html('<i class="arvicon spinner"></i> <?php _e("Getting debug information...", "arvancloud-vod"); ?>').show();

            // Perform AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'arvancloud_vod_debug',
                    nonce: '<?php echo wp_create_nonce("arvancloud_vod_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Format debug info nicely
                        var debugOutput = '<h3><?php _e("Debug Information", "arvancloud-vod"); ?></h3>';
                        debugOutput += '<table style="width:100%; border-collapse: collapse;">';

                        // Convert debug info to table
                        $.each(response.data.debug_info, function(key, value) {
                            if (typeof value === 'object') {
                                value = JSON.stringify(value);
                            }
                            debugOutput += '<tr style="border-bottom: 1px solid #eee;">';
                            debugOutput += '<td style="padding: 8px; font-weight: bold;">' + key + '</td>';
                            debugOutput += '<td style="padding: 8px;">' + value + '</td>';
                            debugOutput += '</tr>';
                        });

                        debugOutput += '</table>';

                        $notification.html(debugOutput).show();
                    } else {
                        $notification.html('<i class="arvicon error"></i> ' + response.data.message).show();
                    }

                    // Re-enable button
                    $button.prop('disabled', false).html('<i class="arvicon search"></i> <?php _e("Debug Sync", "arvancloud-vod"); ?>');
                },
                error: function(xhr, status, error) {
                    $notification.html('<i class="arvicon error"></i> <?php _e("An error occurred while getting debug information.", "arvancloud-vod"); ?>').show();
                    $button.prop('disabled', false).html('<i class="arvicon search"></i> <?php _e("Debug Sync", "arvancloud-vod"); ?>');
                }
            });
        });

		 // Add repair button handler
        $('#repair-vod-media').click(function() {
            var $button = $(this);
            var $notification = $('#sync-notification');

            // Disable button and show loading state
            $button.prop('disabled', true).html('<i class="arvicon spinner"></i> <?php _e("Repairing...", "arvancloud-vod"); ?>');

            // Display notification
            $notification.html('<i class="arvicon spinner"></i> <?php _e("Repairing video metadata...", "arvancloud-vod"); ?>').show();

            // Perform AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'arvancloud_vod_repair_media',
                    nonce: '<?php echo wp_create_nonce("arvancloud_vod_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var message = '<i class="arvicon success"></i> ' + response.data.message;

                        // Add details if there were errors
                        if (response.data.details && response.data.details.length > 0) {
                            message += '<br><br><strong><?php _e("Error Details:", "arvancloud-vod"); ?></strong><ul>';
                            for (var i = 0; i < response.data.details.length; i++) {
                                message += '<li>' + response.data.details[i] + '</li>';
                            }
                            message += '</ul>';
                        }

                        $notification.html(message).show();

                        // Refresh page if videos were fixed
                        if (response.data.fixed > 0) {
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                    } else {
                        var errorMsg = '<i class="arvicon error"></i> ' + response.data.message;

                        // Add details if available
                        if (response.data.details && response.data.details.length > 0) {
                            errorMsg += '<br><br><strong><?php _e("Error Details:", "arvancloud-vod"); ?></strong><ul>';
                            for (var i = 0; i < response.data.details.length; i++) {
                                errorMsg += '<li>' + response.data.details[i] + '</li>';
                            }
                            errorMsg += '</ul>';
                        }

                        $notification.html(errorMsg).show();
                    }

                    // Re-enable button
                    $button.prop('disabled', false).html('<i class="arvicon wrench"></i> <?php _e("Repair Media", "arvancloud-vod"); ?>');
                },
                error: function(xhr, status, error) {
                    $notification.html('<i class="arvicon error"></i> <?php _e("An error occurred during the repair process.", "arvancloud-vod"); ?>').show();
                    $button.prop('disabled', false).html('<i class="arvicon wrench"></i> <?php _e("Repair Media", "arvancloud-vod"); ?>');
                }
            });
        });

		// Close notification when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('#sync-notification, #sync-from-vod, #debug-vod-sync, #repair-vod-media').length) {
				$('#sync-notification').fadeOut();
			}
		});
	});
</script>
