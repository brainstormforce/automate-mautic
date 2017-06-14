<?php
/**
 * Authenication render html
 *
 * @package automate-mautic
 * @since 1.0.5
 */

	APMautic_Services::render_settings();
if ( APMautic_Helper::is_service_connected() ) {
	?>
	<a class="ap-mautic-disconnect"> <?php _e( 'Disconnect Mautic', 'automate-mautic' ); ?> </a>
	<?php
}
?>
