<div id="fl-bsfm-branding-form" class="fl-settings-form bsfm-branding-fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'Branding', 'bsfmautic' ); ?></h3>

	<form id="bsfm-branding-form" action="<?php BSFMauticAdminSettings::render_form_action( 'bsfm-branding' ); ?>" method="post">

		<?php /*if ( FLBuilderAdminSettings::multisite_support() && ! is_network_admin() ) : ?>
		<label>
			<input class="fl-override-ms-cb" type="checkbox" name="fl-override-ms" value="1" <?php if(get_option('_fl_builder_uabb_branding')) echo 'checked="checked"'; ?> />
			<?php _e('Override network settings?', 'bsfmautic'); ?>
		</label>
		<?php endif; */ ?>
		
		<div class="fl-settings-form-content">
			<?php
				// FLBuilderModel::update_admin_settings_option( '_fl_builder_uabb_branding', '' );
				//$bsfm    = BB_Ultimate_Addon_Helper::get_builder_uabb_branding();
				$checked = '';

				$bsfm 	= BSF_Mautic_Helper::get_bsf_mautic_branding();
				$bsfm_plugin_name = $bsfm_plugin_desc = $bsfm_author_name = $bsfm_author_url = $bsfm_plugin_short_name = $bsfm_knowledge_base_url = $bsfm_contact_support_url = $bsfm_hide_branding = '';
				if( is_array($bsfm) ) {
					// Check Neble Disable branding
					$bsfm_plugin_name         = ( array_key_exists( 'bsfm-plugin-name', $bsfm ) ) ? $bsfm['bsfm-plugin-name'] : '';
					$bsfm_plugin_short_name   = ( array_key_exists( 'bsfm-plugin-short-name', $bsfm ) ) ? $bsfm['bsfm-plugin-short-name'] : '';
					$bsfm_plugin_desc         = ( array_key_exists( 'bsfm-plugin-desc' , $bsfm ) ) ? $bsfm['bsfm-plugin-desc' ] : '';
					$bsfm_author_name         = ( array_key_exists( 'bsfm-author-name' , $bsfm ) ) ? $bsfm['bsfm-author-name' ] : '';
					$bsfm_author_url          = ( array_key_exists( 'bsfm-author-url' , $bsfm ) ) ? $bsfm['bsfm-author-url' ] : '';
					$bsfm_knowledge_base_url  = ( array_key_exists( 'bsfm-knowledge-base-url' , $bsfm ) ) ? $bsfm['bsfm-knowledge-base-url' ] : '';
					$bsfm_contact_support_url = ( array_key_exists( 'bsfm-contact-support-url' , $bsfm ) ) ? $bsfm['bsfm-contact-support-url' ] : '';
					$bsfm_hide_branding		  = ( get_option( 'bsfm_hide_branding' ) != false ) ? ' checked' : '' ;	
				} ?>

			<?php /* Plugin Name*/ ?> 
			<div class="bsfm-branding-fields" style="margin-top: 30px;">
			<h4 class="field-title"><?php _e( 'Plugin Name', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-plugin-name" placeholder="BSF Mautic" value="<?php echo $bsfm_plugin_name; ?>" class="regular-text bsfm-plugin-name" />
			</div>

			<?php /* Plugin Short Name*/ ?> 
			<div class="bsfm-branding-fields">
			<h4 class="field-title"><?php _e( 'Plugin Short Name', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-plugin-short-name" placeholder="BSFM" value="<?php echo $bsfm_plugin_short_name; ?>" class="regular-text bsfm-plugin-short-name" />
			</div>
		
			<?php /* Plugin Description */ ?> 
			<div class="bsfm-branding-fields">
			<h4 class="field-title"><?php _e( 'Plugin Description', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-plugin-desc" placeholder="Sync your new reigstered WP users with mautic contacts." value="<?php echo $bsfm_plugin_desc; ?>" class="regular-text bsfm-plugin-desc" />
			</div>
			
			<?php /* Author Name */ ?> 
			<div class="bsfm-branding-fields">
			<h4 class="field-title"><?php _e( 'Author / Agency Name', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-author-name" placeholder="Brainstorm Force" value="<?php echo $bsfm_author_name; ?>" class="regular-text bsfm-author-name" />
			</div>

			<?php /* Author URL */ ?>
			<div class="bsfm-branding-fields">
			<h4 class="field-title"><?php _e( 'Author / Agency URL', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-author-url" placeholder="http://www.brainstormforce.com" value="<?php echo $bsfm_author_url; ?>" class="regular-text bsfm-author-url" />
			</div>

			<?php /* Knowledge Base URL */ ?>
			<div class="bsfm-branding-fields">
			<h4 class="field-title"><?php _e( 'Knowledge Base URL', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-knowledge-base-url" placeholder="https://www.ultimatebeaver.com/docs/" value="<?php echo $bsfm_knowledge_base_url; ?>" class="regular-text bsfm-knowledge-base-url" />
			</div>

			<?php /* Contact Support URL */ ?>
			<div class="bsfm-branding-fields">
			<h4 class="field-title"><?php _e( 'Contact Support URL', 'bsfmautic' ); ?></h4>
			<input type="text" name="bsfm-contact-support-url" placeholder="https://store.brainstormforce.com/support/" value="<?php echo $bsfm_contact_support_url; ?>" class="regular-text bsfm-contact-support-url" />
			</div>
 		
			<?php /* Hide This Form */ ?>
			<div class="bsfm-form-setting">
				<h4><?php echo _e( 'Hide White Label Settings', 'bsfmautic' ); ?></h4>
				<p class="bsfm-admin-help"><?php _e('Enable this option to hide White Label settings. Re-activate the plugin to enable this form again.', 'bsfmautic'); ?></p>
				<label>					
					<input type="checkbox" class="bsfm-hide-branding" name="bsfm-hide-branding" value="" <?php echo $bsfm_hide_branding; ?> ><?php _e( 'Hide White Label Settings', 'bsfmautic' ); ?>
				</label>
			</div>

		</div>

		<p class="submit">
			<input type="submit" name="save-bsfm-branding" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'bsfmautic' ); ?>" />
			<?php wp_nonce_field( 'bsfm-branding', 'bsfm-branding-nonce'); ?>
		</p>
	</form>
</div>