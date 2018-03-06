<?php
/**
 * Enable tracking render html
 *
 * @package automate-mautic
 * @since 1.0.5
 */

?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="postbox-container-2" class="postbox-container postbox ampw-authenti-container">
			<h2 class="hndle ui-sortable-handle apm-rule-heading" style="padding: 1em 2em;"><span><?php _e( ' Mautic Tracking', 'automate-mautic' ); ?></span></h2>
<?php

	$ap_enabled_track = apm_get_option( 'enable-tracking', 1 );
	APMautic_Helper::render_input_html(
		'enable-tracking', array(
			'row_class' => 'apm-service-row',
			'class'     => 'apm-service-input',
			'type'      => 'checkbox',
			'ischecked' => $ap_enabled_track,
			'text'      => __( 'Enable Tracking', 'automate-mautic' ),
			// translators: %1$s: opening anchor tag.
			// translators: %2$s: closing anchor tag.
			'desc'      => sprintf( __( 'This setting enables you to add Mautic tracking code in your site.<br>Need more information about tracking? Read %1$sThis article%2$s.', 'automate-mautic' ), '<a target="_blank" href="' . esc_url( 'https://mautic.org/docs/en/contacts/contact_monitoring.html' ) . '">', '</a>' ),
		)
	);
	APMautic_Helper::render_input_html(
		'save-apmw', array(
			'row_class'   => 'apm-service-row',
			'class'       => 'save-amp-settings',
			'type'        => 'submit',
			'def_value'   => 'Save Settings',
			'spinner'     => true,
			'nonce_acion' => 'apmautictrack',
			'nonce_name'  => 'ap-mautic-nonce-tracking',
		)
	);

?>

</div>
		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<div class="postbox">
					<button type="button" class="handlediv button-link ap-toogle-option" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Information</span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle"><span>Information</span></h2>
					<div class="inside">
						<ul class="apm-sidebar-link">
							<li><a href="https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/" target="_blank">Docs</a></li>
							<li><a href="#">FAQ</a></li>
							<li><a href="https://wordpress.org/support/plugin/automate-mautic" target="_blank">Support</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
