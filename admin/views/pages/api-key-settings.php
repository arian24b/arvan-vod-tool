<?php
$credentials_status = get_option( 'arvan-cloud-vod-status' );
?>


<div class="wrap">

	<h1><?php echo esc_html_e( 'VOD General Settings', 'arvancloud-vod' ) ?></h1>
	<form class="arvancloud-vod-config-form" method="post" action="<?php echo esc_url(admin_url( '/admin.php?page=arvancloud-vod' )); ?>">
	<div class="arvan-vod-wrapper">
		<div class="arvan-vod-card">
			<h3><?php echo esc_html_e( 'Configure VOD API', 'arvancloud-vod' ) ?></h3>

				<div class="ar-box">
					<label for="acvod-api-key">API Key</label>
					<input type="text" name="acvod-api-key" id="acvod-api-key" value="<?php echo !empty($credentials_status) ? esc_html_e( "-- not shown --", 'arvancloud-vod' ) : '' ?>" autocomplete="off" placeholder="Apikey ********-****-****-****-************">
				</div>
				<div class="ar-box">
					<a class="get-api-key" href="https://panel.arvancloud.ir/profile/api-keys" target="_blank" rel="noopener noreferrer"><?php echo esc_html_e('Get API Key', 'arvancloud-vod'); ?></a>
				</div>
				<?php
				if (!empty($credentials_status)) {
					$url = add_query_arg(
						array(
							'page' => 'arvancloud-vod',
						),
						admin_url( 'admin.php' )
					);

					$text = __( "Cancel", 'arvancloud-vod' );

					echo '<a class="button" href="' . esc_url($url) . '">' . esc_html($text) . '</a>';
				}
				?>
				<button type="submit" class="button button-primary" name="config_arvancloud_vod_api_key" value="1"><?php echo esc_html_e( "Save", 'arvancloud-vod' ) ?></button>

		</div>
	</div>
	</form>

    <?php require_once( ACVOD_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>
