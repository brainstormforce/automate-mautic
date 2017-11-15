<?php
/**
 * Handle config page
 *
 * @package automate-mautic
 * @since 1.0.0
 */

?>
<div class="wrap">

	<h2 class="apm-settings-heading">
		<?php APMautic_AdminSettings::render_page_heading(); ?>
	</h2>

	<?php APMautic_AdminSettings::render_update_message(); ?>

	<div class="apm-config-tab">
		<ul>
			<?php APMautic_AdminSettings::render_form( 'config' ); ?>
		</ul>
	</div>
</div>
