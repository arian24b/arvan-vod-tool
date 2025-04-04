<?php
use WP_Arvan\Engine\API\VOD\Channels;

$channels = (new Channels)->get_channels();

?>

<div class="wrap">

	<h1><?php esc_html_e( 'Settings', 'arvancloud-vod' ) ?></h1>
	<div class="arvan-vod-wrapper">
		<div class="arvan-vod-card">
            <div class="obs-box-outline-title mb-4"></div>
			<div class="obs-box-outline d-flex align-items-center justify-content-between" style="display: flex;align-items: center;justify-content: space-between;">
				<p><?php esc_html_e( 'Change default API Key', 'arvancloud-vod' ) ?></p>
				<a class="button" href="<?php echo add_query_arg(
					array(
						'page' => (isset($_GET['page'])?$_GET['page']:'arvancloud-vod'),
						'action' => 'config-api'
					),
					esc_url(admin_url( 'admin.php' ))
				); ?>"><?php _e( 'Change API Key', 'arvancloud-vod' ) ?></a>
			</div>
			<form class="arvancloud-vod-config-form selected_channel" method="post" action="<?php echo esc_url(admin_url( '/admin.php?page=arvancloud-vod' )); ?>">
				<div class="obs-box-outline d-flex align-items-center justify-content-between" class="arvancloud-vod-config-form-row">
					<?php
						if ( ! empty( $channels ) ) {
							?>
							<label for="selected_channel"><?php esc_html_e( 'Select default channel (ArvanCloud Video Channel for Uploading Videos)', 'arvancloud-vod' ) ?></label>
							<select name="selected_channel" id="selected_channel">
								<?php
								foreach ( $channels as $channel ) {
									$value = isset($channel['id']) ? $channel['id'] : '';
									if (strlen((string)$value) > 0) {
										?>
										<option value="<?php echo esc_attr( $channel['id'] ) ?>" <?php echo esc_attr( $channel['id'] ) == get_option( 'arvan-cloud-vod-selected_channel_id' ) ? 'selected' : '' ?>>
											<?php echo esc_html( $channel['title'] );
											echo strlen($channel['description']) > 0 ? '(' . esc_html( $channel['description'] ) . ')' : ''; ?>
										</option>
										<?php
									}
								}
								?>
							</select>

							<?php
						}
						?>
				</div>
				<?php
				$is_prevent_saving_video_on_local_checked = get_option('vod_prevent_saving_video_on_local', 'no');
				?>
				<div class="obs-box-outline d-flex align-items-center justify-content-between arvancloud-vod-config-form-row prevent-saving-video-on-local">
					<div>
                        <label for="prevent_saving_video_on_local"><?php _e('Prevent saving video files on local', 'arvancloud-vod') ?></label>
					    <input type="hidden" name="prevent_saving_video_on_local" value="no">
					    <div class="obs-form-check mt-1">
                           <input type="checkbox" class="obs-input" name="prevent_saving_video_on_local" id="prevent_saving_video_on_local" value="yes" <?php echo ( 'yes' == $is_prevent_saving_video_on_local_checked)?'checked=checked':''; ?>>
                            <div class="obs-custom-input"></div>
                        </div>
                        <div class="obs-box-outline-desc"><?php _e('The files should be deleted from the local host after uploading them to the Arvan video service.', 'arvancloud-vod'); ?></div>
					</div>
                    <div></div>
				</div>
                <div class="d-flex justify-left mt-4">
                    <button type="submit" class="obs-btn-primary" name="config_arvancloud_vod_selected_channel" value="1"><?php esc_html_e( "Save", 'arvancloud-vod' ) ?></button>
                </div>

			</form>
		</div>
	</div>

    <?php require_once( ACVOD_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>
