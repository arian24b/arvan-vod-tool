<?php
use WP_Arvan\Engine\API\VOD\Video;
use WP_Arvan\Engine\API\VOD\video_tags;
use WP_Arvan\Engine\Helper;

$video   = new Video;
$tags    = new video_tags;
$helper  = new Helper;
$vid_tag = $vid_inf = [];
if(!empty($_GET['id'])){
	$vid_inf = $video->show($_GET['id']);
	if($vid_inf['status_code']!=200)
	$vid_inf = [];	
}

if(!empty($vid_inf))
$vid_tag = $tags->get_video_tags($_GET['id']);
?>

<div class="wrap">

	<h1 class="heading-title-one"><?php _e('Arvancloud video plugin','arvancloud-vod'); ?></h1>
	<div class="arvan-vod-wrapper">
		<div>
			<p>
			<a href="#"><?php _e('Video arvan cloud platform', 'arvancloud-vod' ); ?></a><?php _e('Prepared for easier access to your videos', 'arvancloud-vod' ); ?>
			</p>
		</div>

		<div class="vod-card-wide full-height">
			<div class="vod-card-wide__header border">
				<p><?php echo empty($vid_inf)?'':$vid_inf['title']; ?></p>
				<a href="https://panel.arvancloud.ir/video/vod/videos/<?php echo empty($vid_inf)?'':$vid_inf['id']; ?>?tab=general" class="vod-btn simple">
				<?php _e('Show video in panel', 'arvancloud-vod'); ?>
				<i class="arvicon arrow-left"></i>
				</a>			
			</div>

			<?php 
			/*<div class="vod-card-wide__video">
				<video class="vod-card-wide__video-source" poster="<?php echo empty($vid_inf['thumbnail_url'])?ACVOD_PLUGIN_ROOT_URL . 'assets/images/vid-cover-big.png':$vid_inf['thumbnail_url']; ?>" id="index-video">
					<source src="<?php echo empty($vid_inf)?'':$vid_inf['video_url']; ?>" type="video/mp4">
				</video>
			</div>*/?>
			<style>.r1_iframe_embed {position: relative; overflow: hidden; width: 100%; height: auto; padding-top: 56.25%; } .r1_iframe_embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }</style>
			<div class="r1_iframe_embed">
				<iframe src="<?php echo empty($vid_inf)?'':$vid_inf['player_url']; ?>" style="border:0 #ffffff none;" name="<?php echo empty($vid_inf)?'':$vid_inf['title']; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>
			</div>

			<div class="vod-card-wide__card-big">
				<div class="vod-card-wide__card-header">
					<h4 class="heading-title-four"><?php _e('General information', 'arvancloud-vod'); ?></h4>
					<div class="vod-card-wide__card-header-status">
						<span class="vod-card-wide__card-header-status-title"><?php _e('Conversion status', 'arvancloud-vod'); ?> : </span>
						<span class="vod-card-wide__card-header-status-value"><?php _e('ready to play', 'arvancloud-vod') ?></span>
					</div>
				</div>

				<div class="vod-card-wide__card-content">
					<div class="vod-card-wide__card-content--datas right">
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video title', 'arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_inf)?'':$vid_inf['title']; ?></span>
						</div>
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video Tags', 'arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_tag)?'':'#'.implode(' #',$vid_tag); ?></span>
						</div>
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video channel', 'arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_inf)?'':$vid_inf['channel']['title']; ?></span>
						</div>
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Parallel video conversion', 'arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value danger"><?php empty($vid_inf['parallel_convert'])?_e('Disabled', 'arvancloud-vod'):_e('Enable', 'arvancloud-vod'); ?></span>
						</div>
					</div>
					<div class="vod-card-wide__card-content--datas left">
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video description','arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_inf)?'':$vid_inf['description']; ?></span>
						</div>
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video creation date','arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_inf)?'':$vid_inf['created_at']; ?></span>
						</div>
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video conversion completion date','arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_inf)?'':$vid_inf['updated_at']; ?></span>
						</div>
						<div class="vod-card-wide__card-content--data">
							<span class="vod-card-wide__card-content--data-title"><?php _e('Video cover photo sec','arvancloud-vod'); ?> :</span>
							<span class="vod-card-wide__card-content--data-value"><?php echo empty($vid_inf)?'':$helper->convert_minsec($vid_inf['thumbnail_time']); ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="vod-card-wide__card-row">
				<div class="vod-card-wide__card-small">
					<div class="vod-card-wide__card-small-box">
						<div class="vod-card-wide__card-small-header">
							<h4 class="heading-title-four"><?php _e('General specifications','arvancloud-vod'); ?></h4>
						</div>
						<div class="vod-card-wide__card-small-content">
							<div class="vod-card-wide__card-small-content--datas right">
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Bitrate','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['general']['bit_rate']) ; ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Duration','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value"><?php echo empty($vid_inf)?'':$helper->convert_minsec($vid_inf['file_info']['general']['duration']); ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Format','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php empty($vid_inf)?'':$vid_inf['file_info']['general']['format']; ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Size','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php echo empty($vid_inf)?'':$helper->file_size_format($vid_inf['file_info']['general']['size']); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>	

				<div class="vod-card-wide__card-small">
					<div class="vod-card-wide__card-small-box">
						<div class="vod-card-wide__card-small-header">
							<h4 class="heading-title-four"><?php _e('Sound profile','arvancloud-vod'); ?> </h4>
						</div>
						<div class="vod-card-wide__card-small-content">
							<div class="vod-card-wide__card-small-content--datas right">
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Bitrate','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['audio']['bit_rate']) ; ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Type','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['audio']['channel_layout']); ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Codec','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php echo empty($vid_inf)?'':$vid_inf['file_info']['audio']['codec']; ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Sample rate','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['audio']['sample_rate']) ; ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>	
				
				<div class="vod-card-wide__card-small">
					<div class="vod-card-wide__card-small-box">
						<div class="vod-card-wide__card-small-header">
							<h4 class="heading-title-four"><?php _e('Video profile','arvancloud-vod'); ?></h4>
						</div>
						<div class="vod-card-wide__card-small-content">
							<div class="vod-card-wide__card-small-content--datas right">
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Bitrate','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['video']['bit_rate']) ; ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Codec','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value"><?php echo empty($vid_inf)?'':$vid_inf['file_info']['video']['codec']; ?></span>
								</div>
								<div class="vod-card-wide__card-small-content--data">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Frame rate','arvancloud-vod'); ?>  :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['video']['frame_rate']); ?></span>
								</div>

								<div class="vod-card-wide__card-small-content--data" style="display:flex">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Width','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['video']['width']); ?></span>
								</div>
							</div>

							<div class="vod-card-wide__card-small-content--datas left">
								<div class="vod-card-wide__card-small-content--data" style="margin-top:auto;">
									<span class="vod-card-wide__card-small-content--data-title"><?php _e('Height','arvancloud-vod'); ?> :</span>
									<span class="vod-card-wide__card-small-content--data-value" dir="ltr"><?php echo empty($vid_inf)?'':$helper->digits_enToFa($vid_inf['file_info']['video']['height']); ?></span>	
								</div>
							</div>
						</div>
					</div>
				</div>	
				
			</div>

		</div>
	</div>
	<?php require_once( ACVOD_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>