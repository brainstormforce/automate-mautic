<div id="fl-bsfm-config-form" class="fl-settings-form bsfm-config-fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'Mautic Configuration', 'bsfmautic' ); ?></h3>

	<form id="mautic-config-form" action="<?php BSFMauticAdminSettings::render_form_action( 'bsfm-config' ); ?>" method="post">
		<div class="fl-settings-form-content">
				<?php
					$bsfm 	=	BSF_Mautic_Helper::get_bsfm_mautic();
					$bsfm_enabled_track = $bsfm_base_url = $bsfm_public_key = $bsfm_secret_key = $bsfm_callback_uri = $bsfm_enabled_track_img = '';
					if( is_array($bsfm) ) {
						$bsfm_enabled_track	= ( array_key_exists( 'bsfm-enabled-tracking', $bsfm ) && $bsfm['bsfm-enabled-tracking'] == 1 )  ? ' checked' : '';
						$bsfm_enabled_track_img	= ( array_key_exists( 'bsfm-enabled-tracking-img', $bsfm ) && $bsfm['bsfm-enabled-tracking-img'] == 1 )  ? ' checked' : '';				
						$bsfm_base_url = ( array_key_exists( 'bsfm-base-url', $bsfm ) ) ? $bsfm['bsfm-base-url'] : '';
						$bsfm_public_key = ( array_key_exists( 'bsfm-public-key', $bsfm ) ) ? $bsfm['bsfm-public-key'] : '';
						$bsfm_secret_key = ( array_key_exists( 'bsfm-secret-key', $bsfm ) ) ? $bsfm['bsfm-secret-key'] : '';
						$bsfm_callback_uri = ( array_key_exists( 'bsfm-callback-uri', $bsfm ) ) ? $bsfm['bsfm-callback-uri'] : '';
					}
				?>
			<!-- Load Panels -->
			<!-- Base Url -->
			<div class="uabb-form-setting">
				<h4><?php _e( 'Base URL', 'bsfmautic' ); ?></h4>
				<p class="uabb-admin-help">
					<?php _e('This setting is required for Mautic Integration.', 'bsfmautic'); ?>
				</p>
				<input type="text" class="regular-text" name="bsfm-base-url" value="<?php echo $bsfm_base_url; ?>" class="uabb-wp-text uabb-google-map-api" />
			</div>

			<!-- Load Panels -->
			<!-- Client Public Key -->
			<div class="uabb-form-setting">
				<h4><?php _e( 'Public Key', 'bsfmautic' ); ?></h4>
				<input type="text" class="regular-text" name="bsfm-public-key" value="<?php echo $bsfm_public_key; ?>" class="uabb-wp-text uabb-google-map-api" />
			</div>
			<!-- Load Panels -->
			<!-- Client Secret Key -->
			<div class="uabb-form-setting">
				<h4><?php _e( 'Secret Key', 'bsfmautic' ); ?></h4>	
				<input type="text" class="regular-text" name="bsfm-secret-key" value="<?php echo $bsfm_secret_key; ?>" class="uabb-wp-text uabb-google-map-api" />
				<p class="uabb-admin-help">
					<?php _e('This setting is required to integrate Mautic in your website.', 'bsfmautic'); ?>
					<?php
					if( empty( $branding_name ) && empty( $branding_short_name ) ) :
						_e('Need help to get Mautic API secret key? Read ', 'bsfmautic'); ?><a target="_blank" href="#"><?php _e('this article', 'bsfmautic'); ?></a>.</p><?php
					endif;
					?>
				</p>
			</div>
			<p class="submit">
				<input type="submit" name="bsfm-save-authenticate" class="button-primary" value="<?php esc_attr_e( 'Save and Authenticate', 'uabb' ); ?>" />
			</p>


			<!-- Load Panels -->
			<!-- Client Callback Url -->
			<div class="uabb-form-setting">
				<h4><?php _e( 'Callback URI -- NEED it?', 'bsfmautic' ); ?></h4>
				<p class="uabb-admin-help">
					<?php _e('Specify callback URI. leave blank to restrict callbacks', 'bsfmautic'); ?>
				</p>
				<input type="text" class="regular-text" name="bsfm-callback-uri" value="<?php echo $bsfm_callback_uri; ?>" class="uabb-wp-text" />
			</div>
			<p class="submit">
				<input type="submit" name="bsfm-push-data" class="button-primary" value="<?php esc_attr_e( 'Send to Mautic', 'uabb' ); ?>" />
			</p>

			<!-- Load Panels -->

			<!-- Enable pixel tracking -->
			<div class="uabb-form-setting">
				<h4><?php _e( 'Enable Pixel Tracking', 'bsfmautic' ); ?></h4>
				<p class="uabb-admin-help">
					<?php _e('This setting enable you to add Mautic tracking to your site. Mautic instance will be able to track information about your visitors that way.', 'bsfmautic'); ?>
				</p>
				<label>
					<input type="checkbox" class="uabb-enabled-panels" name="bsfm-enabled-tracking" value="" <?php echo $bsfm_enabled_track; ?> ><?php _e( 'Enable Pixel Tracking', 'bsfmautic' ); ?>
				</label>
			</div>

			<!-- Load Panels -->
			<!-- Enable image tracking -->
			<div class="uabb-form-setting">
				<h4><?php _e( 'Enable Image Tracking', 'bsfmautic' ); ?></h4>
				<p class="uabb-admin-help">
					<?php _e('This setting enable you to add Mautic tracking image to your site. Mautic instance will be able to track information about your visitors that way.', 'bsfmautic'); ?>
				</p>
				<label>
					<input type="checkbox" class="uabb-enabled-panels" name="bsfm-enabled-tracking-img" value="" <?php echo $bsfm_enabled_track_img; ?> ><?php _e( 'Enable Image Tracking', 'bsfmautic' ); ?>
				</label>
			</div>
		</div>

		<p class="submit">
			<input type="submit" name="fl-save-uabb" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'uabb' ); ?>" />
		</p>

		<?php wp_nonce_field('bsfmautic', 'bsf-mautic-nonce'); ?>
	</form>
</div>