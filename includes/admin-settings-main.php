<?php
/**
 * Handle config page
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

?>
<div class="wrap">

	<h2 class="ap-mautic-settings-heading">
		<?php APMautic_AdminSettings::render_page_heading(); ?>
	</h2>

	<?php APMautic_AdminSettings::render_update_message(); ?>

	<div class="ap-mautic-nav">
		<ul>
			<?php APMautic_AdminSettings::render_form( 'config' ); ?>
		</ul>
	</div>
</div>
