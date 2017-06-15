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
			
<?php 
	APMauticServices::render_settings();
if ( APMautic_helper::is_service_connected() ) {
	?>
	<a class="ap-mautic-disconnect"> <?php _e( 'Disconnect Mautic', 'automate-mautic' ); ?> </a>
	<?php
}
?>
	
		</div>
		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<div class="postbox ">
					<button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Information</span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle"><span>Information</span></h2>
					<div class="inside">
						<ul class="ap-sidebar-link">
							<li><a href="https://docs.brainstormforce.com/category/mautic-contacts-count/" target="_blank">Docs</a></li>
							<li><a href="#">FAQ</a></li>
							<li><a href="https://wordpress.org/support/plugin/automate-mautic" target="_blank">Support</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	