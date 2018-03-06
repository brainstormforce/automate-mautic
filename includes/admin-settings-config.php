<?php
/**
 * Config page view
 *
 * @package automate-mautic
 * @since 1.0.0
 */

?>
<div id="automate-config-form" class="ap-settings-form apm-config-form">
	<?php
		$active_tab = isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'all_rules';
	if ( isset( $_GET['action'] ) ) {
		$current_action = esc_attr( $_GET['action'] );
		$active_tab     = '';
	} else {
		$current_action = '';
	}

	?>
	<h2 class="nav-tab-wrapper">
		<?php
			$items = array(
				'all_rules'       => array(
					'label' => 'All Rules',
				),
				'add_new_rule'    => array(
					'label' => 'Add New',
				),
				'auth_mautic'     => array(
					'label' => 'Authenticate',
				),
				'enable_tracking' => array(
					'label' => 'Tracking',
				),
			);
			$items = apply_filters( 'amp_new_options_tab', $items );
			APMautic_AdminSettings::render_tab_items( $items, $active_tab );
		?>
	</h2>
	<?php
	if ( 'all_rules' == $active_tab ) {
		APMautic_AdminSettings::ampw_rules_list();
	}
	if ( 'add_new_rule' == $active_tab || 'edit' == $current_action ) {
		APMautic_RulePanel::ap_mautic_metabox_view();
	}
	?>
	<form id="apm-config-form" action="<?php APMautic_AdminSettings::render_page_url( '&tab=auth_mautic' ); ?>" method="post">
		<div class="amp-form-content ampw-authenticate-cont-70">
			<?php
			$active_path = str_replace( '_', '-', $active_tab );
			$active_path = 'admin-settings-' . $active_path;
			$tab_file    = AP_MAUTIC_PLUGIN_DIR . 'includes/' . sanitize_file_name( $active_path ) . '.php';

			if ( file_exists( $tab_file ) ) {
				require_once $tab_file;
			}
			do_action( 'amp_options_tab_content', $active_tab );
		?>
		</div>
	</form>
</div>
