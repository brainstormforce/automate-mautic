<?php
/**
 * Handles logic for the admin settings page. 
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'APM_AdminSettings' ) ) :

final class APM_AdminSettings {
	
	private static $instance;

	/**
	 * Initiator
	 */
	public static function instance() 
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new APM_AdminSettings();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() 
	{
		add_action( 'after_setup_theme', __CLASS__ . '::init_hooks' );
		add_action( 'admin_footer', array( $this, 'mb_templates' ) );
		add_action( 'wp_loaded', array( $this, 'mautic_authenticate_update' ) );
		add_action( 'admin_notices', array( $this, 'apmw_notices' ), 100 );
	}
	/** 
	 * Include template to render meta box html
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function mb_templates() 
	{
		$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		if( 'automate-mautic' == $curr_screen ) {
			include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR .'/assets/templates/meta-box-template.php';
		}
	}

	/** 
	 * Adds the admin menu and enqueues CSS/JS if we are on
	 * the MautiPress admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init_hooks()
	{
		add_action( 'admin_menu', __CLASS__ . '::menu' );
		if ( ! is_admin() ) {
			return;
		}
		$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		if( 'automate-mautic' == $curr_screen ) {
			self::save();
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::styles_scripts' );
		}
	}
	
	/** 
	 * Renders the admin settings menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function menu() 
	{
		if ( current_user_can( 'delete_users' ) ) {
			$cap	= 'delete_users';
			$slug	= 'automate-mautic-settings';
			$func	= __CLASS__ . '::render';
			add_options_page( 'AutomatePlus Mautic',  __( 'AutomatePlus Mautic', 'automateplus-mautic-wp' ), 'administrator', 'automate-mautic', $func );
		}
	}
	
	/** 
	 * Enqueues the needed CSS/JS for admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function styles_scripts( $hook ) 
	{

		$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		if( 'automate-mautic' == $curr_screen ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'apm-admin-script', AUTOMATEPLUS_MAUTIC_PLUGIN_URL . 'assets/js/admin.js' , array( 'jquery','jquery-ui-sortable','wp-util' ) );
			wp_enqueue_style( 'apm-admin-style', AUTOMATEPLUS_MAUTIC_PLUGIN_URL . 'assets/css/admin.css' );
			wp_enqueue_script( 'apm-select2-script', AUTOMATEPLUS_MAUTIC_PLUGIN_URL . 'assets/js/select2.min.js' , array( 'jquery' ) );
			wp_enqueue_style( 'apm-select2-style', AUTOMATEPLUS_MAUTIC_PLUGIN_URL . 'assets/css/select2.min.css' );
		}
	}
	
	/** 
	 * Renders the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render() 
	{
		include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-rules-table.php';
		include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-main.php';
	}

	public static function ampw_rules_list() 
	{

		$new_post_url = APM_AdminSettings::get_render_page_url( "&tab=add_new_rule" );
		?>
		<div class="wrap">
		<h1>
			<?php _e( 'Mautic Rules', 'automateplus-mautic-wp' ); ?> <a class="page-title-action" href="<?php echo $new_post_url; ?>" ><?php _e( 'Add New', 'automateplus-mautic-wp' ); ?> </a>
		</h1>
		<?php
		if ( ! empty( $_GET['s'] ) ) {
			printf( '<span >' . __( 'Search results for &#8220;%s&#8221;', 'automateplus-mautic-wp' ) . '</span>', esc_html( wp_unslash( $_GET['s'] ) ) );
		}
		?>
		<form method="get" action="" >

			<?php
			if ( isset( $_GET['page'] ) ) {
				echo '<input type="hidden" name="page" value="' . esc_attr( $_GET['page'] ) . '" />' . "\n";
			}
			$list_table = new APM_Rules_Table();
			$list_table->prepare_items();
			$list_table->search_box( 'search', 'apm_rule_search' );
			?>
		</form>
		<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
			<input type="hidden" name="action" value="apm_rule_list" />
			<?php $list_table->display(); ?>
		</form>
		</div>
		<?php
	}

	/**
	 * Renders the admin settings page heading.
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_page_heading()
	{
		$icon = AUTOMATEPLUS_MAUTIC_PLUGIN_URL . '/assets/icon/mt.png';
		if ( ! empty( $icon ) ) {
			echo '<img class="ampw-heading-icon" src="' . $icon . '" />';
		}
		echo '<div class="ampw-heading-config">' . __( 'AutomatePlus Mautic', 'automateplus-mautic-wp' ) . '</div>';
	}
	/** 
	 * Renders the update message.
	 *
	 * @since 1.0.0
	 * @return void
	 */	 
	public static function render_update_message() 
	{
 		// redirect
		if( ! empty( $_POST ) ) {
			echo '<div class="updated"><p>' . __( 'Settings updated!', 'automateplus-mautic-wp' ) . '</p></div>';
		}
	}

	/** 
	 * Renders an admin settings form based on the type specified.
	 *
	 * @since 1.0.0
	 * @param string $type The type of form to render.
	 * @return void
	 */
	public static function render_form( $type )
	{
		if ( self::has_support( $type ) ) {
			include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php';
		}
	}
	
	/** 
	 * Return page url.
	 *
	 * @since 1.0.0
	 */	  
	public static function render_page_url( $type = '' )
	{
		echo admin_url( '/options-general.php?page=automate-mautic' . $type );
	}

	/** 
	 * Return page url.
	 *
	 * @since 1.0.0
	 */	  
	public static function get_render_page_url( $type = '' )
	{
		return admin_url( '/options-general.php?page=automate-mautic' . $type );
	}
	
	/** 
	 * Checks to see if a settings form is supported.
	 *
	 * @since 1.0.0
	 * @param string $type The type of form to check.
	 * @return bool
	 */ 
	public static function has_support( $type )
	{
		return file_exists( AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php' );
	}

	/** 
	 * Saves the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */	 
	public static function save()
	{
		// Only admins can save settings.
		if( ! current_user_can('delete_users') ) {
			return;
		}

		if ( isset( $_POST['apmw-mautic-post-meta-nonce'] ) && wp_verify_nonce( $_POST['apmw-mautic-post-meta-nonce'], 'apmauticpmeta' ) ) {
			$rule_id = $update_conditions = '';
			if( isset( $_POST['ampw_rule_title'] ) ) {
				$rule_name = esc_attr( $_POST['ampw_rule_title'] );
			}
	
			// Gather post data.
			$rule_post_type = array(
				'post_title'    => $rule_name,
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_type'     => 'automate-mautic'
			);

			if( isset( $_GET['action'] ) && $_GET['action']=='edit' ) {
				$rule_id = esc_attr( $_GET['post'] );
			}

			if( $rule_id !== '' && $rule_id != null ) {
				$rule_post_type['ID'] = $rule_id;
			}

			$rule_id = wp_insert_post( $rule_post_type );
			$post_id = $rule_id;

				//update post meta
				if ( isset( $_POST['pm_condition'] ) ) {
					$conditions = $_POST['pm_condition'];
					$cp_keys = array_keys( $conditions, "CP");

					$condition_cnt = sizeof( $conditions );

					for($i=0; $i < $condition_cnt; $i++) {
						if( $conditions[$i]=='UR' ) {
							$base = sanitize_text_field( $conditions[ $i ] );
							$update_conditions[$i] = array( $base );
						}
						if ( $conditions[$i]=='CP' ) {
							$sub_key = array_search( $i, $cp_keys );
							$base = sanitize_text_field( $conditions[ $i ] );
							$sub_cp_condition = sanitize_text_field( $_POST['sub_cp_condition'][ $sub_key ] );
							$ss_cp_condition = sanitize_text_field( $_POST['ss_cp_condition'][ $sub_key ] );
							$update_conditions[$i] = array(
								$base,
								$sub_cp_condition, 
								$ss_cp_condition
							);
						}
						$action = 'update_condition_' . $conditions[$i];
						$update_conditions = apply_filters( $action, $update_conditions, $conditions, $i, $_POST );
					}
					$update_conditions = serialize( $update_conditions );
					update_post_meta( $post_id, 'ampw_rule_condition', $update_conditions );
				}
				//update actions
				if ( isset( $_POST['sub_seg_action'] ) ) {

					$actions = $_POST['sub_seg_action'];

					$action_cnt = sizeof( $actions );

					for( $i=0; $i < $action_cnt; $i++ ) {

							$sub_seg_action = sanitize_text_field( $_POST['sub_seg_action'][ $i ] );
							$ss_seg_action = sanitize_text_field( $_POST['ss_seg_action'][ $i ] );
							$update_actions[$i] = array(
								$sub_seg_action,
								$ss_seg_action
							);
						$action = 'update_action_' . $actions[$i];
						$update_action = apply_filters( $action, $update_actions, $actions, $i, $_POST );
					}

					$update_actions = serialize( $update_actions );
					update_post_meta( $post_id, 'ampw_rule_action', $update_actions );
				}
				$redirect = APM_AdminSettings::get_render_page_url( "&action=edit&post=$post_id" );
				wp_redirect( $redirect );
		}

		if ( isset( $_POST['apmw-mautic-nonce'] ) && wp_verify_nonce( $_POST['apmw-mautic-nonce'], 'apmwmautic' ) ) {

			$amp_options = AMPW_Mautic_Init::get_amp_options();

			if( isset( $_POST['base-url'] ) ) {	$amp_options['base-url'] = esc_url( $_POST['base-url'] ); }
			if( isset( $_POST['public-key'] ) ) {	$amp_options['public-key'] = sanitize_key( $_POST['public-key'] ); }
			if( isset( $_POST['secret-key'] ) ) {	$amp_options['secret-key'] = sanitize_key( $_POST['secret-key'] ); }
			if( isset( $_POST['callback-uri'] ) ) {	$amp_options['callback-uri'] = esc_url( $_POST['callback-uri'] ); }
			$mautic_api_url = $amp_options['base-url'];
			$amp_options['base-url'] = rtrim( $mautic_api_url ,"/");

			update_option( 'ampw_mautic_config', $amp_options );
		}
		if ( isset( $_POST['apmw-mautic-nonce-tracking'] ) && wp_verify_nonce( $_POST['apmw-mautic-nonce-tracking'], 'apmautictrack' ) ) {

			$amp_options = AMPW_Mautic_Init::get_amp_options();
			
			$amp_options['enable-tracking'] = false;
			if( isset( $_POST['enable-tracking'] ) ) {	
			
				$amp_options['enable-tracking'] = true;	
			
			}

			update_option( 'ampw_mautic_config', $amp_options );
			
			$redirect = APM_AdminSettings::get_render_page_url( "&tab=enable_tracking" );
			wp_redirect( $redirect );
		}

		do_action('amp_update_tab_content');

		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete-rule'.$_GET['rule_id'] ) ) {
			if ( isset( $_GET['rule_id'] ) ) {
				$rule_id = esc_attr( $_GET['rule_id'] );
				wp_delete_post( $rule_id );
				$redirect = APM_AdminSettings::get_render_page_url( "&tab=all_rules" );
				wp_redirect( $redirect );
			}
		}
	}

	public static function mautic_authenticate_update() 
	{
		if ( isset( $_POST['ampw-save-authenticate'] ) && $_POST['ampw-save-authenticate']=='Save and Authenticate' ) {
			AP_Mautic_Api::authenticate_update();
		}
	}

	public static function apmw_notices()
	{
		$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		if( ! AP_Mautic_Api::is_connected() && $curr_screen == 'automate-mautic' ) {

			$redirect = APM_AdminSettings::get_render_page_url( "&tab=auth_mautic" );
			printf( '<div class="update-nag">' . __( 'Seems there appears error with the Mautic configuration.', 'automateplus-mautic-wp' ) . ' <a href="'.$redirect.'">'.__('click here','automateplus-mautic-wp').'</a>' . __( ' to authenticate Mautic.', 'automateplus-mautic-wp' ) . '</div>' );
		}
	}

	public static function render_messages( $message )
	{
		$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		if( $message = 'update' && $curr_screen == 'automate-mautic' ) {
			echo '<div class="updated"><p>' . __( 'Settings updated!', 'automateplus-mautic-wp' ) . '</p></div>';
		}
	}
}
$APM_AdminSettings = APM_AdminSettings::instance();
endif;