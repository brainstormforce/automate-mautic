<?php
/**
 * Enable tracking render html
 *
 * @package automateplus-mautic
 * @since 1.0.5
 */

	$ap_enabled_track = apm_get_option( 'enable-tracking', 1 );
	APMautic_helper::render_input_html('enable-tracking', array(
		'row_class'     => 'apm-service-row',
		'class'         => 'apm-service-input',
		'type'          => 'checkbox',
		'ischecked'		=> $ap_enabled_track,
		'label'         => __( 'Enable Mautic Tracking', 'automateplus-mautic-wp' ),
		'text'         	=> __( 'Enable Tracking', 'automateplus-mautic-wp' ),
		'desc'          => sprintf( __( 'This setting enables you to add Mautic tracking code in your site.<br>Need more information about tracking? Read %1$sthis article%2$s.', 'automateplus-mautic-wp' ), '<a target="_blank" href="' . esc_url( 'https://mautic.org/docs/en/contacts/contact_monitoring.html' ) . '">', '</a>' ),
	));
	APMautic_helper::render_input_html('save-apmw', array(
		'row_class'		=> 'apm-service-row',
		'class'			=> 'save-amp-settings',
		'type'			=> 'submit',
		'def_value'		=> 'Save Settings',
		'spinner'		=> true,
		'nonce_acion'	=> 'apmautictrack',
		'nonce_name'	=> 'ap-mautic-nonce-tracking',
	));
