<div class="wrap">

	<h2 class="bsfm-settings-heading">
		<?php APM_AdminSettings::render_page_heading(); ?>
	</h2>
	
	<?php APM_AdminSettings::render_update_message(); ?>
	
	<div class="bsfm-settings-nav">
		<ul>
			<?php APM_AdminSettings::render_form( 'bsfm-config' ); ?>
		</ul>
	</div>
</div>
