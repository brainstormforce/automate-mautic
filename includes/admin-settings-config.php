<?php
/**
 * Config page view
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

?>
<div id="automate-config-form" class="ap-settings-form ap-mautic-config-form">
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
					'label' => 'All Rules',
				),
				'auth_mautic' => array(
					'label' => 'Authenticate',
				),
				'enable_tracking' => array(
					'label' => 'Tracking',
				),
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
			APMautic_RulePanel::ap_mautic_metabox_view();
		?>
	<?php } ?>
	<form id="ap-mautic-config-form" action="<?php APMautic_AdminSettings::render_page_url( '&tab=auth_mautic' ); ?>" method="post">
		<div class="ap-mautic-form-content">
			<?php
			
			$ap_enabled_track = apm_get_option( 'enable-tracking', 1 );
			$ap_base_url = apm_get_option( 'base-url' );

			if ( 'auth_mautic' == $active_tab ) { ?>
			<?php
				APMauticServices::render_settings();
				if( APMautic_helper::is_service_connected() ) {
			?>
				<a class="ap-mautic-disconnect"> <?php _e( 'Disconnect Mautic', 'automateplus-mautic-wp' ); ?> </a>
				<?php
				}
			}

			if ( 'enable_tracking' == $active_tab ) { 

				// APMauticServices::render_tracking_settings();
			?>

				<div class="ap-config-fields">
				<h4><?php _e( 'Enable Mautic Tracking', 'automateplus-mautic-wp' ); ?></h4>
				<p class="admin-help">
					<?php
						echo sprintf( __( 'This setting enables you to add Mautic tracking code in your site.<br>Need more information about tracking? Read %1$sthis article%2$s.', 'automateplus-mautic-wp' ), '<a target="_blank" href="' . esc_url( 'https://mautic.org/docs/en/contacts/contact_monitoring.html' ) . '">', '</a>' );
					?>
				</p>
				<label>
					<input type="checkbox" class="enabled-panels" name="enable-tracking" value="" <?php checked( 1, $ap_enabled_track ); ?> ><?php _e( 'Enable Tracking', 'automateplus-mautic-wp' ); ?>
				</label><br>
			</div>
			<p class="submit">
				<input type="submit" name="save-apmw" id="save-amp-settings" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'automateplus-mautic-wp' ); ?>" />
				<span class="spinner apm-wp-spinner" style="float: none;margin-bottom: 0.5em;"></span>
			</p>
			<?php wp_nonce_field( 'apmautictrack', 'ap-mautic-nonce-tracking' ); ?>
		<?php }
			do_action( 'amp_options_tab_content', $active_tab );
		?>
		</div>
	</form>
</div>
