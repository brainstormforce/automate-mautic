<div class="wrap <?php BSFMauticAdminSettings::render_page_class(); ?>">

	<h2 class="fl-settings-heading">
		<?php BSFMauticAdminSettings::render_page_heading(); ?>
	</h2>
	
	<?php //BSFMauticAdminSettings::render_update_message(); ?>

	<div class="fl-settings-nav">
		<ul>
			<?php BSFMauticAdminSettings::render_nav_items(); ?>
		</ul>
	</div>

	<div class="fl-settings-content">
		<?php BSFMauticAdminSettings::render_forms(); ?>
	</div>
</div>
