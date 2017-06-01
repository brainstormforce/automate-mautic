<?php
	APMauticServices::render_settings();
	if( APMautic_helper::is_service_connected() ) {
	?>
		<a class="ap-mautic-disconnect"> <?php _e( 'Disconnect Mautic', 'automateplus-mautic-wp' ); ?> </a>
	<?php
	}
?>
