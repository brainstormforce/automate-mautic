<?php
/**
 * Handle config page
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

?>
<div class="wrap">

	<h2 class="ampw-settings-heading">
		<?php APM_AdminSettings::render_page_heading(); ?>
	</h2>
	
	<?php APM_AdminSettings::render_update_message(); ?>
	
	<div class="ampw-settings-nav">
		<ul>
			<?php APM_AdminSettings::render_form( 'config' ); ?>
		</ul>
	</div>
</div>