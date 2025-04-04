<?php
use WP_Arvan\Engine\API\VOD\Channels;
use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\Helper;
$Channels = new Channels;
$video    = new Video;
$helper   = new Helper;

$default  = empty($_GET['channel_id'])?get_option( 'arvan-cloud-vod-selected_channel_id'):$_GET['channel_id'];
$chan_list= $Channels->get_channels();
$list     = array_column($chan_list,'id');
?>
<div class="wrap">

	<h1><?php echo esc_html_e( 'Add New Video', 'arvancloud-vod' ) ?></h1>
	<div class="arvan-vod-wrapper">
		<div>
			<p>
			<a href="#"><?php _e('Video arvan cloud platform', 'arvancloud-vod' ); ?></a><?php _e('Prepared for easier access to your videos', 'arvancloud-vod' ); ?> 
			</p>
		</div>
		<?php if(empty($_GET['channel_id']) or !in_array($default,$list)){?>
		<!----Video Channels---->
		<div class="vod-card-wide half-height">
			<div class="vod-card-wide__header">
				<div class="vod-input-icon">
					<input type="text" class="vod-input" placeholder="<?php _e('Search in channels','arvancloud-vod'); ?>...">
					<i class="arvicon search"></i>
				</div>				
			</div>
			<div class="arv-vod-table">
				<table>
					<thead>
						<tr>
							<th></th>
							<th><?php _e('Title','arvancloud-vod') ?></th>
							<th><?php _e('Description','arvancloud-vod') ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(!empty($chan_list)){
							foreach($chan_list as $i=>$chanl){
								echo '
								<tr>
								<td>
									<span class="arv-vod-table__id">‍'.($i+1).'</span>
								</td>
								<td class="channel_title">'.$chanl['title'].'</td>
								<td>
									<p>'.$chanl['description'].'</p>
								</td>
								<td>
									<a href="https://panel.arvancloud.ir/video/vod/channels/'.$chanl['id'].'/videos?page=1" class="vod-btn secondary">
										<i class="arvicon blank"></i>'.__('View channel','arvancloud-vod').'</a>
									<a href="'.admin_url("admin.php?page=arvancloud-vod-new-video&channel_id={$chanl['id']}").'" class="vod-btn primary">'.__('Select channel','arvancloud-vod').'</a>
								</td>
							</tr>';
							}	
						}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		}else{
		
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
		?>
		<!----Drag and drop---->
		<div class="vod-input-fulldrag">
				<img src="<?php echo ACVOD_PLUGIN_ROOT_URL . 'assets/images/upload-drag.svg' ?>" alt="">
				<strong class="vod-card-wide__nothing-title">
				<?php _e('Upload new video','arvancloud-vod'); ?>
				</strong>
				<p><?php _e('Drag and drop the file here','arvancloud-vod'); ?></p>
				<div>
					<span>
					<?php _e('Allowed format','arvancloud-vod'); ?>:
					</span>
					<span>					
						m4v, avi, mp4‌, mkv, mov
					</span>
				</div>
				<input class="vod-input-fulldrag__input" type="file" multiple>
				<button class="vod-btn simple-color">
				<?php _e('Select video file','arvancloud-vod'); ?>
				</button>
		</div>

		<div class="upload_continer"></div>
		
	<?php } ?>
	</div>
	<script>
	jQuery(document).ready(function($) {

		let itemsObject = {};

		$('.vod-input').on('keyup', function() {
			var searchTerm = $(this).val().toLowerCase();
			
			$('.arv-vod-table table tbody tr').filter(function() {
				$(this).toggle($(this).find('td.channel_title').text().toLowerCase().indexOf(searchTerm) > -1);
			});
		});


		$('.vod-input-fulldrag__input').change(function(){
			
			if(event.target.files.length>0){
				input_file = event.target.files
				for(i=0;i<input_file.length;i++){
					file   = input_file[i];
					dindex = file.name.lastIndexOf('.');
					if(dindex === -1)
					return;
					fname  = file.name.substr(0,dindex);
					extent = file.name.substr(dindex).toLowerCase();
					valid= ['.mov','.mp4','.m4v','.avi','.mkv'];
					if (valid.includes(extent)) {

						key = Date.now();
						itemsObject[key] = file;
						str = `
						<div class="vod-card-video-upload" data-file="${key}">
							<div class="vod-card-video-upload__header">
								<h2><bdi>${file.name}</bdi></h2>
								<div class="vod-card-video-upload__header-actions">
									<div class="vod-card-video-upload__status warning">
										<span class="vod-card-video-upload__status-title"><?php _e('Status','arvancloud-vod'); ?>:</span>
										<span class="vod-card-video-upload__status-value"><?php _e('Awaiting completion of video information','arvancloud-vod'); ?></span>
									</div>
									<button class="vod-btn upload_strt primary">
										<i class="arvicon upload"></i>
									<?php _e('Start upload video','arvancloud-vod'); ?>
									</button>
									<button class="vod-btn non-text-icon del">
										<i class="arvicon close"></i>
									</button>
								</div>
							</div>
							<div class="vod-card-video-upload__content">
								<div class="vod-card-video-upload__content-fileaddress">
									<div class="play-vod-icon">
										<i class="arvicon play-circle"></i>
									</div>
									<div class="vod-card-video-upload__content-fileaddress--name">
										<span><?php _e('File address','arvancloud-vod'); ?> :</span>
										<span><bdi>${file.name}</bdi></span>
									</div>
								</div>
								<div class="vod-card-video-upload__content-col">
									<div class="vod-card-video-upload__content-setting">
										<div class="vod-card-video-upload__content-setting--fields">
											<div class="vod-card-video-upload__content-setting--fields-title">
											<?php _e('File name','arvancloud-vod'); ?>
											</div>
											<div class="vod-card-video-upload__content-setting--fields-input">
												<input type="text" class="filename" dir="ltr" value="${fname}">
											</div>
										</div>
									</div>
									<div class="vod-card-video-upload__content-setting">
										<div class="vod-card-video-upload__content-setting--fields">
											<div class="vod-card-video-upload__content-setting--fields-title">
											<?php _e('Video output type','arvancloud-vod'); ?> :
											</div>
											<div class="vod-card-video-upload__content-setting--fields-input">
												<div class="vod-input-radio" style="display:flex;">
													<label class="label-radio"><?php _e('Automat','arvancloud-vod'); ?>
														<input type="radio" checked="checked" name="convert_mode" value="auto">
														<span class="checkmark-radio"></span>
													</label>
													<label class="label-radio disable"><?php _e('Use profiles','arvancloud-vod'); ?>
														<input type="radio" name="convert_mode" value="manual">
														<span class="checkmark-radio"></span>
													</label>
													<label class="label-radio disable"><?php _e('Manually','arvancloud-vod'); ?>
														<input type="radio" name="convert_mode" value="profile">
														<span class="checkmark-radio"></span>
													</label>
												</div>
												<div class="vod-card-video-upload__content-setting--fields-notice">
													<span>
													<?php _e('Video quality is processed automatically','arvancloud-vod') ?>
													</span>
												</div>
											</div>
										</div>
										<!--div class="vod-card-video-upload__content-setting--fields">
											<div class="vod-card-video-upload__content-setting--fields-title">
												<?php _e('The name of the selected video watermark','arvancloud-vod'); ?>:
											</div>
											<div class="vod-card-video-upload__content-setting--fields-input">
												<div class="vod-input-dropdown watermark">
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
										</div-->
									</div>
								</div>
							</div>
						</div>`;
						$('.upload_continer').prepend(str);
					}else
						alert(file.name+'<?php _e(' is not valid video','arvancloud-vod'); ?>');
				}
			}
			
			
		});


		$('body').on('click','.vod-input-dropdown__list ul li',function(){
			$('.vod-input-dropdown__list ul li').removeClass('active');
			$(this).addClass('active');
			parent = $(this).closest('.vod-input-dropdown');
			parent.find('.vod-input-dropdown__select span').text($(this).text());
			parent.find('.vod-input-dropdown__select').trigger('click');
		});


		$('body').on('click','.del',function(){
			if(confirm('<?php _e('do you want delete this box?','arvancloud-vod'); ?>'))
			div = $(this).closest('.vod-card-video-upload');
			delete itemsObject[div.data('file')];
			console.log(itemsObject);
			div.remove();
		});

		$('body').on('click','.upload_strt',function(){
			let parent = $(this).closest('.vod-card-video-upload');
			file = itemsObject[parent.data('file')];
			if(file === undefined || file === null)
			return;

			channel  = parent.find('.vod-input-dropdown__list ul li.active').data('id');
			filename = parent.find('.filename').val();
			convert  = parent.find('[name="convert_mode"]:checked').val();

			str = `
			<div class="vod-card-video-upload__header">
				<h2>`+file.name+`</h2>
				<div class="vod-card-video-upload__header-actions">
					<div class="vod-card-video-upload__status info">
						<span class="vod-card-video-upload__status-title">Status:</span>
						<span class="vod-card-video-upload__status-value"><?php _e('Processing video','arvancloud-vod'); ?></span>
					</div>
					<button class="vod-btn danger del">
						<i class="arvicon close"></i>
						Cancel upload
					</button>
				</div>
			</div>
			<div class="vod-card-video-upload__content">
				<div class="vod-progress-bar__details">
					<div class="vod-progress-bar__details-waiting">
						<div class="circle-wrap">
							<div class="circle">
								<div class="mask full">
									<div class="fill"></div>
								</div>
								<div class="mask half">
									<div class="fill"></div>
								</div>
								<div class="inside-circle"></div>
							</div>
						</div>
						<div class="vod-progress-bar__details-waiting--text">
							<?php _e('Processing','arvancloud-vod'); ?> 
							<span>
							(<?php _e('Video is being processed','arvancloud-vod'); ?>)
							</span>
						</div>
					</div>
					<!--div class="vod-progress-bar__details-duration">
						<div class="vod-progress-bar__details-duration--title"><?php _e('Approximate processing completion time','arvancloud-vod'); ?> :</div>
						<div class="vod-progress-bar__details-duration--value">05:00</div>
					</div-->
				</div>
				<div class="vod-progress-bar">					
					<span class="vod-progress-bar__percent">0%</span>
					<div class="vod-progress-bar__area">
						<div class="vod-progress-bar__fill"></div>
					</div>
				</div>
			</div>`;
			parent.html(str);

			parent.find('.vod-progress-bar__fill').css('width',  '0%');
			
			formData = new FormData();
        	formData.append('file', file);
			formData.append('channl', '<?php echo isset($_GET['channel_id'])?$_GET['channel_id']:'' ?>');
			formData.append('filename', filename);
			formData.append('convert', convert);
			formData.append('action', 'vod_video_upload');
			upd_item = $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				xhr: function() {
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener('progress', function(evt) {
						if (evt.lengthComputable) {
							var percentComplete = Math.floor((evt.loaded / evt.total) * 100);
							if(percentComplete==100){
								parent.find('.vod-card-video-upload__status-value').text('<?php _e('Create Attachment','arvancloud-vod'); ?>');
							}
							parent.find('.vod-progress-bar__fill').css('width', percentComplete + '%');
							parent.find('.vod-progress-bar__percent').text((percentComplete + '%').toLocaleString());
						}
					}, false);
					return xhr;
				},
				success: function(response) {
					
					if (response.success) {
						complete = `
								<div class="vod-card-video-upload__header success">
									<h2><bdi>${response.name}</bdi></h2>
									<div class="vod-card-video-upload__header-actions">
										<div class="vod-card-video-upload__status success">
											<span class="vod-card-video-upload__status-title"><?php _e('Status', 'vod_video_upload'); ?>:</span>
											<span class="vod-card-video-upload__status-value"><?php _e('Complete upload video', 'vod_video_upload'); ?></span>
										</div>
										<a href="${response.edit}" target="blank" class="vod-btn simple-color-none">
											<?php _e('View video and more settings', 'vod_video_upload'); ?>
											<i class="arvicon arrow-left"></i>
										</a>
										<button class="vod-btn non-text-icon del">
											<i class="arvicon close"></i>
										</button>
									</div>
								</div>
								<div class="vod-card-video-upload__content">
									<div class="vod-card-video-upload__details">
										<div class="vod-card-video-upload__details-success">
											<div class="d-flex align-items-center">
												<i class="arvicon success"></i>
												<?php _e('Upload complete', 'vod_video_upload'); ?>
												</div>
										</div>
										<div class="vod-card-video-upload__details-link">
											<div class="vod-card-video-upload__details-link--title"><?php _e('Video address', 'vod_video_upload'); ?> :</div>
											<div class="vod-card-video-upload__details-link--value">
												<input type="text" class="vod-input vod-input-non-border" id="vid_url_address" value="${response.url}">
												<span>:URL</span>
												<button class="vod-btn non-text-icon-third" onclick="copyLinkVidAddress()">
													<i class="arvicon copy"></i>
												</button>						 
											</div>
										</div>
									</div>
								</div>`;
							parent.html(complete);
					}else{
						alert(response.msg);
					}
					
				},
				error: function() {
					alert('<?php _e('An error occurred while uploading the file.','arvancloud-vod'); ?>');
				}
			});
			//console.log(upd_item);

		});

	});
	</script>
<?php require_once( ACVOD_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>