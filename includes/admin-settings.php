<div class="wrap">

	<h2 class="bsfm-settings-heading">
		<?php BSFMauticAdminSettings::render_page_heading(); ?>
	</h2>
	
	<?php BSFMauticAdminSettings::render_update_message(); ?>

	<div class="bsfm-settings-nav">
		<ul>
			<?php BSFMauticAdminSettings::render_form( 'bsfm-config' ); ?>
		</ul>
	</div>
</div>
