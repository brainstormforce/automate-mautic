<?php
/**
 * Config page view
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

?>
<div id="automate-config-form" class="apmw-settings-form ampw-config-settings-form">
	<?php
		$active_tab = isset( $_GET['tab'] ) ?  esc_attr( $_GET['tab'] ) : 'all_rules';
	if ( isset( $_GET['action'] ) ) {
		$current_action = esc_attr( $_GET['action'] );
		$active_tab = '';
	} else {
		$current_action = '';
	}

	?>
	<h2 class="nav-tab-wrapper">
		<?php
			$items  = array(
				'all_rules' => array(
					'label' => 'All Rules'
				),
				'auth_mautic' => array(
					'label' => 'Authenticate'
				),
				'enable_tracking' => array(
					'label' => 'Tracking'
				)
			);
			$items = apply_filters( 'amp_new_options_tab', $items );
			APMautic_AdminSettings::render_tab_items( $items, $active_tab );
		?>
	</h2>
	<?php

	if ( 'all_rules' == $active_tab  ) {
		APMautic_AdminSettings::ampw_rules_list();
	}
	if ( 'add_new_rule' == $active_tab || 'edit' == $current_action ) { ?>
		<?php
			APMautic_RulePanel::apmw_metabox_view();
		?>
	<?php } ?>
	<form id="apmw-config-form" action="<?php APMautic_AdminSettings::render_page_url( '&tab=auth_mautic' ); ?>" method="post">
		<div class="ampw-settings-form-content">
			<?php
			$apmw_enabled_track = apm_get_option( 'enable-tracking', 1 );
			$apmw_base_url = apm_get_option( 'base-url' );
			$apmw_public_key = apm_get_option( 'public-key' );
			$apmw_secret_key = apm_get_option( 'secret-key' );

			if ( 'auth_mautic' == $active_tab ) { ?>
			<!-- Base Url -->
			<div class="apmw-config-fields">
				<h4><?php _e( 'Base URL', 'automateplus-mautic-wp' ); ?></h4>
				<p class="admin-help">
					<?php _e( 'This setting is required for Mautic Integration.', 'automateplus-mautic-wp' ); ?>
				</p>
				<input type="text" class="regular-text" name="base-url" value="<?php echo $apmw_base_url; ?>" class="regular-text" />
			</div>

			<?php

			if ( ! APMautic_API::is_connected() ) { ?>
			<!-- Client Public Key -->
			<div class="apmw-config-fields">
				<h4><?php _e( 'Public Key', 'automateplus-mautic-wp' ); ?></h4>
				<input type="text" class="regular-text" name="public-key" class="regular-text" />
			</div>
			
			<!-- Client Secret Key -->
			<div class="apmw-config-fields">
				<h4><?php _e( 'Secret Key', 'automateplus-mautic-wp' ); ?></h4>	
				<input type="text" class="regular-text" name="secret-key" class="regular-text" />
				<p class="admin-help">
					<?php _e( 'This setting is required to integrate Mautic in your website.<br>Need help to get Mautic API public and secret key? Read ', 'automateplus-mautic-wp' ); ?><a target='_blank' href="https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/"><?php _e( 'this article.', 'automateplus-mautic-wp' ); ?></a>
				</p>
			</div>
			<p class="submit">
				<input type="submit" name="ampw-save-authenticate" class="button-primary" value="<?php esc_attr_e( 'Save and Authenticate', 'automateplus-mautic-wp' ); ?>" />
			</p>

			<?php wp_nonce_field( 'apmwmautic', 'apmw-mautic-nonce' );
			}

			if ( APMautic_API::is_connected() ) { ?>
				<p class="submit">
					<input type="button" name="ampw-disconnect-mautic" class="button-primary" value="<?php _e( 'Connected', 'automateplus-mautic-wp' ); ?>" />
					<a class="ampw-disconnect-mautic"> <?php _e( 'Disconnect Mautic', 'automateplus-mautic-wp' ); ?> </a> 
				</p>
				<?php } ?>
			<!-- Enable pixel tracking -->
			<?php
			}

			if ( 'enable_tracking' == $active_tab ) { ?>
				<div class="apmw-config-fields">
				<h4><?php _e( 'Enable Mautic Tracking', 'automateplus-mautic-wp' ); ?></h4>
				<p class="admin-help">
					<?php _e( 'This setting enables you to add Mautic tracking code in your site.<br>Need more information about tracking? Read <a target="_blank" href="https://mautic.org/docs/en/contacts/contact_monitoring.html">this article</a>.', 'automateplus-mautic-wp' ); ?>
				</p>
				<label>
					<input type="checkbox" class="enabled-panels" name="enable-tracking" value="" <?php checked( 1, $apmw_enabled_track ); ?> ><?php _e( 'Enable Tracking', 'automateplus-mautic-wp' ); ?>
				</label><br>
			</div>
			<p class="submit">
				<input type="submit" name="save-apmw" id="save-amp-settings" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'automateplus-mautic-wp' ); ?>" />
				<span class="spinner apm-wp-spinner" style="float: none;margin-bottom: 0.5em;"></span>
			</p>
			<?php wp_nonce_field( 'apmautictrack', 'apmw-mautic-nonce-tracking' ); ?>
		<?php }
			do_action( 'amp_options_tab_content', $active_tab );
		?>
		</div>
	</form>
</div>
