<?php
/**
 * Authenication render html
 *
 * @package automate-mautic
 * @since 1.0.5
 */

	APMauticServices::render_settings();
if ( APMautic_helper::is_service_connected() ) {
	?>
	<a class="ap-mautic-disconnect"> <?php _e( 'Disconnect Mautic', 'automateplug-mautic-wp' ); ?> </a>
	<?php
}
?>
