<?php
/**
 * Authenication render html
 *
 * @package automate-mautic
 * @since 1.0.5
 */

?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="postbox-container-2" class="postbox-container postbox ampw-authenti-container">
			<h2 class="hndle ui-sortable-handle apm-rule-heading" style="padding: 1em 2em;"><span><?php _e( 'Authenticate Mautic', 'automate-mautic' ); ?></span></h2>
<?php

	APMautic_Services::render_settings();
if ( APMautic_Helper::is_service_connected() ) {

	?>
	<a class="apm-disconnect"> <?php _e( 'Disconnect Mautic', 'automate-mautic' ); ?> </a>
	<?php
}
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
	
