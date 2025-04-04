<?php
$credentials_status = get_option( 'arvan-cloud-vod-status' );
?>

<div class="wrap">

	<h1 class="heading-title-one"><?php echo esc_html_e( 'Configure VOD API', 'arvancloud-vod' ) ?></h1>
	<form method="post">
	<div class="arvan-vod-wrapper">
		<div>
			<p>
			<a href="#"><?php _e('Video arvan cloud platform', 'arvancloud-vod' ); ?></a> <?php _e('Prepared for easier access to your videos', 'arvancloud-vod' ); ?> 
			</p>
			<p class="text-dark"><?php _e('In this section you can add your Arvan Video cloud Api to connect your wordpress to arvan server', 'arvancloud-vod'); ?></p>
		</div>

		<div class="vod-card-wide">
			<div class="d-flex">
				<img src="<?php echo ACVOD_PLUGIN_ROOT_URL . 'assets/images/arvan-vod.svg' ?>" alt="Arvan Vod">
				<div class="vod-card-wide__configure">
					<p class="text-bold"><?php _e('Arvanclude VOD plugin', 'arvancloud-vod'); ?></p>
					<p class="text-normal"><?php _e('Please enter valid api key', 'arvancloud-vod'); ?></p>
				</div>
			</div>

			<div class="vod-card-wide__configure-input">
				<input type="text"  name="acvod-api-key" class="vod-input wide" value="<?php echo !empty($credentials_status) ? esc_html_e( "-- not shown --", 'arvancloud-vod' ) : '' ?>" placeholder="<?php _e('Please enter API key...', 'arvancloud-vod'); ?>">
				<button type="submit" name="config_arvancloud_vod_api_key" class="vod-btn primary"><?php echo esc_html_e( "check & Save", 'arvancloud-vod' ) ?></button>
			</div>
		</div>
	</div>
	</form>
    <?php require_once( ACVOD_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>