<div id="fl-bsfm-config-form" class="bsfm-settings-form bsfm-config-bsfm-settings-form">

	<?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'auth_mautic'; ?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=bsf-mautic&tab=all_rules" class="nav-tab <?php echo $active_tab == 'all_rules' ? 'nav-tab-active' : ''; ?>"> <?php _e('All Rule', 'bsfmautic'); ?> </a>
		<a href="?page=bsf-mautic&tab=add_new_rule" class="nav-tab <?php echo $active_tab == 'add_new_rule' ? 'nav-tab-active' : ''; ?>"> <?php _e('Add New Rule', 'bsfmautic'); ?> </a>
		<a href="?page=bsf-mautic&tab=bsfm_settings" class="nav-tab <?php echo $active_tab == 'bsfm_settings' ? 'nav-tab-active' : ''; ?>"> <?php _e('Settings', 'bsfmautic'); ?> </a>
		<a href="?page=bsf-mautic&tab=auth_mautic" class="nav-tab <?php echo $active_tab == 'auth_mautic' ? 'nav-tab-active' : ''; ?>"> <?php _e('Authenticate', 'bsfmautic'); ?> </a>
		<a href="?page=bsf-mautic&tab=enable_tracking" class="nav-tab <?php echo $active_tab == 'enable_tracking' ? 'nav-tab-active' : ''; ?>"> <?php _e('Tracking', 'bsfmautic'); ?> </a>
	</h2>
	<?php
	if( $active_tab == 'all_rules' ) {
		BSFMauticAdminSettings::bsfm_rules_list();
	}
	if( $active_tab == 'add_new_rule' ) { ?>
		<p> ADD NEW RULE </p>
		<?php
			$bsf_post_meta = new Bsfm_Postmeta();
			$bsf_post_meta->add_meta_box_b( 'bsf-mautic-rule', __( 'Trigger and Actions', 'bsfmautic' ), $bsf_post_meta->bsf_mautic_metabox_view() , 'normal' );
		?>
	<?php }
	if( $active_tab == 'bsfm_settings' ) { ?>
		<p> SETTINGS </p>
	<?php } ?>

	<form id="bsfm-config-form" action="<?php BSFMauticAdminSettings::render_form_action( 'bsfm-config' ); ?>" method="post">
		<div class="bsfm-settings-form-content">
			<?php
				$bsfm 	=	BSF_Mautic_Helper::get_bsfm_mautic();
				$bsfm_enabled_track = $bsfm_base_url = $bsfm_public_key = $bsfm_secret_key = $bsfm_callback_uri = $bsfm_enabled_track_img = $bsfm_enabled_track_show = '';
				if( is_array($bsfm) ) {
					$bsfm_enabled_track	= ( array_key_exists( 'bsfm-enabled-tracking', $bsfm ) && $bsfm['bsfm-enabled-tracking'] == 1 )  ? ' checked' : '';
					$bsfm_enabled_track_show = ( array_key_exists( 'bsfm-enabled-tracking', $bsfm ) && $bsfm['bsfm-enabled-tracking'] == 1 )  ? 'style="display: block;"' : 'style="display: none;"';
					$bsfm_tracking_type_js	= ( array_key_exists( 'bsfm-tracking-type', $bsfm ) && $bsfm['bsfm-tracking-type'] == 'js' )  ? ' checked' : '';
					$bsfm_tracking_type_img	= ( array_key_exists( 'bsfm-tracking-type', $bsfm ) && $bsfm['bsfm-tracking-type'] == 'img' )  ? ' checked' : '';
					$bsfm_base_url = ( array_key_exists( 'bsfm-base-url', $bsfm ) ) ? $bsfm['bsfm-base-url'] : '';
					$bsfm_public_key = ( array_key_exists( 'bsfm-public-key', $bsfm ) ) ? $bsfm['bsfm-public-key'] : '';
					$bsfm_secret_key = ( array_key_exists( 'bsfm-secret-key', $bsfm ) ) ? $bsfm['bsfm-secret-key'] : '';
					$bsfm_callback_uri = ( array_key_exists( 'bsfm-callback-uri', $bsfm ) ) ? $bsfm['bsfm-callback-uri'] : '';
				}

			if( $active_tab == 'auth_mautic' ) { ?>
			<!-- Base Url -->
			<div class="bsfm-config-fields">
				<h4><?php _e( 'Base URL', 'bsfmautic' ); ?></h4>
				<p class="bsfm-admin-help">
					<?php _e('This setting is required for Mautic Integration.', 'bsfmautic'); ?>
				</p>
				<input type="text" class="regular-text" name="bsfm-base-url" value="<?php echo $bsfm_base_url; ?>" class="bsfm-wp-text bsfm-google-map-api" />
			</div>

			<?php 
			$credentials = get_option( 'bsfm_mautic_credentials' );
			if( ! isset( $credentials['access_token'] ) ) { ?>
			<!-- Client Public Key -->
			<div class="bsfm-config-fields">
				<h4><?php _e( 'Public Key', 'bsfmautic' ); ?></h4>
				<input type="text" class="regular-text" name="bsfm-public-key" class="bsfm-wp-text bsfm-google-map-api" />
			</div>
			
			<!-- Client Secret Key -->
			<div class="bsfm-config-fields">
				<h4><?php _e( 'Secret Key', 'bsfmautic' ); ?></h4>	
				<input type="text" class="regular-text" name="bsfm-secret-key" class="bsfm-wp-text bsfm-google-map-api" />
				<p class="bsfm-admin-help">
					<?php _e('This setting is required to integrate Mautic in your website.<br>Need help to get Mautic API public and secret key? Read ', 'bsfmautic'); ?><a target='_blank' href="https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/"><?php _e('this article.', 'bsfmautic'); ?></a>
				</p>
			</div>
			<p class="submit">
				<input type="submit" name="bsfm-save-authenticate" class="button-primary" value="<?php esc_attr_e( 'Save and Authenticate', 'bsfmautic' ); ?>" />
			</p>
			<?php wp_nonce_field('bsfmautic', 'bsf-mautic-nonce'); ?>
			<?php } ?>
				<?php
				// If not authorized 
				if( isset( $credentials['access_token'] ) ) { ?>
				<p class="submit">
					<input type="button" name="bsfm-disconnect-mautic" class="button-primary" value="<?php _e( 'Connected', 'bsfmautic' ); ?>" />
					<!-- input type="submit" name="bsfm-disconnect-mautic" class="button" value="<?php esc_attr_e( 'Disconnect Mautic', 'bsfmautic' ); ?>" / -->
					<a class="bsfm-disconnect-mautic"> <?php _e( 'Disconnect Mautic', 'bsfmautic' ); ?> </a> 
				</p>
				<?php } ?>
			<!-- Enable pixel tracking -->
			<?php 
			}
			if( $active_tab == 'enable_tracking' ) { ?>
 			<div class="bsfm-config-fields">
				<h4><?php _e( 'Enable Mautic Tracking', 'bsfmautic' ); ?></h4>
				<p class="bsfm-admin-help">
					<?php _e( 'This setting enables you to add Mautic tracking code in your site.<br>Need more information about tracking? Read <a target="_blank" href="https://mautic.org/docs/en/contacts/contact_monitoring.html">this article</a>.', 'bsfmautic'); ?>
				</p>
				<label>
					<input type="checkbox" class="bsfm-enabled-panels" name="bsfm-enabled-tracking" value="" <?php echo $bsfm_enabled_track; ?> ><?php _e( 'Enable Tracking', 'bsfmautic' ); ?>
				</label><br>
			</div>

			<!-- Load Panels -->
			<!-- Enable image tracking -->
			<div class="bsfm-config-fields bsfm-config-select-tracking" <?php echo $bsfm_enabled_track_show; ?>>
				<h4><?php _e( 'Select Tracking Type', 'bsfmautic' ); ?></h4>
				<p>
					<input type="radio" name="bsfm-tracking-type" value="js" <?php echo $bsfm_tracking_type_js; ?> ><?php _e( ' Javascript (JS) Tracking', 'bsfmautic' ); ?><br>
					<input type="radio" name="bsfm-tracking-type" value="img" <?php echo $bsfm_tracking_type_img; ?> ><?php _e( ' Pixel Tracking', 'bsfmautic' ); ?>
				</p>
			</div>
			<p class="submit">
				<input type="submit" name="fl-save-bsfm" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'bsfmautic' ); ?>" />
			</p>
			<?php wp_nonce_field('bsfmautictrack', 'bsf-mautic-nonce-tracking'); ?>
		<?php } ?>
		</div>
	</form>
</div>