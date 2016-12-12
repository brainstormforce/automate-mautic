<div class="wrap">

	<h2 class="bsfm-settings-heading">
		<?php BSFMauticAdminSettings::render_page_heading(); ?>
	</h2>
	
	<?php BSFMauticAdminSettings::render_update_message(); ?>

	<?php //$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'auth_mautic'; ?>
	<!-- h2 class="nav-tab-wrapper">
		<a href="?page=bsf-mautic&tab=auth_mautic" class="nav-tab <?php //echo $active_tab == 'auth_mautic' ? 'nav-tab-active' : ''; ?>"> <?php _e('Authenticate', 'bsfmautic'); ?> </a>
		<a href="?page=bsf-mautic&tab=enable_tracking" class="nav-tab <?php //echo $active_tab == 'enable_tracking' ? 'nav-tab-active' : ''; ?>"> <?php _e('Tracking', 'bsfmautic'); ?> </a>
	</h2 -->

	<div class="bsfm-settings-nav">
		<ul>
			<?php BSFMauticAdminSettings::render_form( 'bsfm-config' ); ?>
		</ul>
	</div>
</div>
