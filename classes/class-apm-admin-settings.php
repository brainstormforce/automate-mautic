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
	public static function instance(){
		if ( ! isset( self::$instance ) ) {
			self::$instance = new APM_AdminSettings();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() {

		add_action( 'after_setup_theme', __CLASS__ . '::init_hooks' );
		add_action( 'admin_footer', array( $this, 'bsfm_mb_templates' ) );
		add_action( 'wp_loaded', array( $this, 'bsf_mautic_authenticate_update' ) );
		add_action( 'admin_notices', array( $this, 'apm_notices' ), 100 );
	}
	/** 
	 * Include template to render meta box html
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bsfm_mb_templates() {
		$curr_screen = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if( 'bsf-mautic' == $curr_screen ) {
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
	static public function init_hooks()
	{
		add_action( 'admin_menu', __CLASS__ . '::menu' );
		if ( ! is_admin() ) {
			return;
		}

		if( ( isset( $_REQUEST['page']) && 'bsf-mautic' == $_REQUEST['page'] ) ) {
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
	static public function menu() 
	{
		if ( current_user_can( 'delete_users' ) ) {
			$cap	= 'delete_users';
			$slug	= 'bsf-mautic-settings';
			$func	= __CLASS__ . '::render';
			add_options_page( 'AutomatePlus Mautic',  __( 'AutomatePlus Mautic', 'automateplus-mautic-wp' ), 'administrator', 'bsf-mautic', $func );
		}
	}
	
	/** 
	 * Enqueues the needed CSS/JS for admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function styles_scripts( $hook ) {

		if ( ( isset( $_REQUEST['page'] ) && 'bsf-mautic' == $_REQUEST['page'] ) ) {
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
	static public function render() {
		include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-rules-table.php';
		include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-main.php';
	}

	static public function bsfm_rules_list() {

		$new_post_url = 'options-general.php?page=bsf-mautic&tab=add_new_rule';	
		?>
		<div class="wrap">
		<h1>
			<?php _e( 'Mautic Rules', 'automateplus-mautic-wp' ); ?> <a class="page-title-action" href="<?php echo $new_post_url; ?>" ><?php _e( 'Add New', 'automateplus-mautic-wp' ); ?> </a>
		</h1>
		<?php
		if ( ! empty( $_GET['s'] ) ) {
			printf( '<span >' . __( 'Search results for &#8220;%s&#8221;', 'convertplug-v2' ) . '</span>', esc_html( wp_unslash( $_GET['s'] ) ) );
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
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="bsfm_rule_list" />
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
	static public function render_page_heading()
	{
		$icon = AUTOMATEPLUS_MAUTIC_PLUGIN_URL . '/assets/icon/mt.png';
		if ( ! empty( $icon ) ) {
			echo '<img class="bsfm-heading-icon" src="' . $icon . '" />';
		}
		echo '<div class="bsfm-heading-config">' . __( 'AutomatePlus Mautic', 'automateplus-mautic-wp' ) . '</div>';
	}
	/** 
	 * Renders the update message.
	 *
	 * @since 1.0.0
	 * @return void
	 */	 
	static public function render_update_message() {
 	
 		// redirect
		if( ! empty( $_POST ) ) {
			echo '<div class="updated"><p>' . __( 'Settings updated!', 'bsfmautic' ) . '</p></div>';
		}
	}

	/** 
	 * Renders an admin settings form based on the type specified.
	 *
	 * @since 1.0.0
	 * @param string $type The type of form to render.
	 * @return void
	 */
	static public function render_form( $type )
	{
		if ( self::has_support( $type ) ) {
			include AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php';
		}
	}
	
	/** 
	 * Renders the action for a form.
	 *
	 * @since 1.0.0
	 * @param string $type The type of form being rendered.
	 * @return void
	 */	  
	static public function render_form_action( $type = '' )
	{
		echo admin_url( '/options-general.php?page=bsf-mautic&tab=auth_mautic' . $type );
	}

	/** 
	 * Renders tabs.
	 *
	 * @since 1.0.0
	 * @param string $type The type of tab being rendered.
	 * @return void
	 */	  
	static public function render_tab_action( $type = '' )
	{
		echo admin_url( '/options-general.php?page=bsf-mautic&action=' . $type );
	}
	
	/** 
	 * Returns the action for a form.
	 *
	 * @since 1.0.0
	 * @param string $type The type of form being rendered.
	 * @return string The URL for the form action.
	 */	 
	static public function get_form_action( $type = '' )
	{
		return admin_url( '/options-general.php?page=bsf-mautic#' . $type );
	}
	
	/** 
	 * Checks to see if a settings form is supported.
	 *
	 * @since 1.0.0
	 * @param string $type The type of form to check.
	 * @return bool
	 */ 
	static public function has_support( $type )
	{
		return file_exists( AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php' );
	}

	/** 
	 * Saves the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */	 
	static public function save()
	{
		// Only admins can save settings.
		if(!current_user_can('delete_users')) {
			return;
		}

		if ( isset( $_POST['bsf-mautic-post-meta-nonce'] ) && wp_verify_nonce( $_POST['bsf-mautic-post-meta-nonce'], 'bsfmauticpmeta' ) ) {
			
			if( isset($_POST['bsfm_rule_title']) ) {
				$rule_name = $_POST['bsfm_rule_title'];
			}
	
			// Gather post data.
			$rule_post_type = array(
				'post_title'    => $rule_name,
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_type'     => 'bsf-mautic-rule'
			);

			if( isset($_GET['action']) && $_GET['action']=='edit') {
				$rule_id = $_GET['post'];
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
						if($conditions[$i]=='UR') {
							$update_conditions[$i] = array( $conditions[$i] );
						}
						if ($conditions[$i]=='CP') {
							$sub_key = array_search($i,$cp_keys);
							$update_conditions[$i] = array(
								$conditions[$i],
								$_POST['sub_cp_condition'][$sub_key], 
								$_POST['ss_cp_condition'][$sub_key] );
						}
					}
					$update_conditions = serialize($update_conditions);
					update_post_meta( $post_id, 'bsfm_rule_condition', $update_conditions );
				}
				//update actions
				if ( isset( $_POST['pm_action'] ) ) {
					$actions = $_POST['pm_action'];
					$seg_keys = array_keys( $actions, "segment");
					$action_cnt = sizeof($actions);
					for($i=0; $i < $action_cnt; $i++) {
						if($actions[$i]=='tag') {
							$update_actions[$i] = $actions[$i];
						}
						if($actions[$i]=='segment') {
							$sub_key = array_search($i,$seg_keys);
							$update_actions[$i] = array(
								$actions[$i],
								$_POST['sub_seg_action'][$sub_key],
								$_POST['ss_seg_action'][$sub_key]
							);
						}
					}
					$update_actions = serialize($update_actions);
					update_post_meta( $post_id, 'bsfm_rule_action', $update_actions );
				}
				$redirect =	admin_url( '/options-general.php?page=bsf-mautic&action=edit&post=' . $post_id );
				wp_redirect( $redirect );
		}

		if ( isset( $_POST['bsf-mautic-nonce'] ) && wp_verify_nonce( $_POST['bsf-mautic-nonce'], 'bsfmautic' ) ) {
			$bsfm = get_option('_bsf_mautic_config');
			if( isset( $_POST['bsfm-base-url'] ) ) {	$bsfm['bsfm-base-url'] = esc_url( $_POST['bsfm-base-url'] ); }
			if( isset( $_POST['bsfm-public-key'] ) ) {	$bsfm['bsfm-public-key'] = sanitize_key( $_POST['bsfm-public-key'] ); }
			if( isset( $_POST['bsfm-secret-key'] ) ) {	$bsfm['bsfm-secret-key'] = sanitize_key( $_POST['bsfm-secret-key'] ); }
			if( isset( $_POST['bsfm-callback-uri'] ) ) {	$bsfm['bsfm-callback-uri'] = esc_url( $_POST['bsfm-callback-uri'] ); }
			$mautic_api_url = $bsfm['bsfm-base-url'];
			$bsfm['bsfm-base-url'] = rtrim( $mautic_api_url ,"/");

			// Update the site-wide option since we're in the network admin.
			if ( is_network_admin() ) {
				update_site_option( '_bsf_mautic_config', $bsfm );
			}
			else {
				update_option( '_bsf_mautic_config', $bsfm );
			}
		}
		if ( isset( $_POST['bsf-mautic-nonce-tracking'] ) && wp_verify_nonce( $_POST['bsf-mautic-nonce-tracking'], 'bsfmautictrack' ) ) {
			$bsfm = get_option('_bsf_mautic_config');
			$bsfm['bsfm-enabled-tracking'] = false;
			if( isset( $_POST['bsfm-enabled-tracking'] ) ) {	$bsfm['bsfm-enabled-tracking'] = true;	}

			// Update the site-wide option since we're in the network admin.
			if ( is_network_admin() ) {
				update_site_option( '_bsf_mautic_config', $bsfm );
			}
			else {
				update_option( '_bsf_mautic_config', $bsfm );
			}
			$redirect =	admin_url( '/options-general.php?page=bsf-mautic&tab=enable_tracking' );
			wp_redirect( $redirect );
		}

		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete-rule'.$_GET['rule_id'] ) ) {
			if ( isset($_GET['rule_id']) ) {
				$rule_id = $_GET['rule_id'];
				wp_delete_post( $rule_id );
				$redirect =	admin_url( '/options-general.php?page=bsf-mautic&tab=all_rules' );
				wp_redirect( $redirect );
			}
		}
	}

	static public function bsf_mautic_authenticate_update() 
	{
		if ( isset( $_POST['bsfm-save-authenticate'] ) && $_POST['bsfm-save-authenticate']=='Save and Authenticate' ) {
			AP_Mautic_Api::bsfm_authenticate_update();
		}
	}
	
	static public function get_bsfm_mautic() {

		$bsfm = get_option('_bsf_mautic_config');
		$defaults = array(
			'bsfm-enabled-tracking'	=> true,
			'bsfm-base-url'			=> '',
			'bsfm-public-key'		=> '',
			'bsfm-secret-key'		=> '',
			'bsfm-callback-uri'		=> ''
		);

		//	if empty add all defaults
		if( empty( $bsfm ) ) {
			$bsfm = $defaults;
			if ( is_network_admin() ) {
				update_site_option( '_bsf_mautic_config', $bsfm );
			}
			else {
				update_option( '_bsf_mautic_config', $bsfm );
			}
		} else {
			//	add new key
			foreach( $defaults as $key => $value ) {
				if( is_array( $bsfm ) && !array_key_exists( $key, $bsfm ) ) {
					$bsfm[$key] = $value;
				} else {
					$bsfm = wp_parse_args( $bsfm, $defaults );
				}
			}
		}
		return apply_filters( 'bsfm_get_mautic', $bsfm );
	}

	static public function apm_notices()
	{
		$curr_screen = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		$credentials = get_option( 'bsfm_mautic_credentials' );

		if( ! isset( $credentials['expires_in'] ) && $curr_screen=='bsf-mautic' ) {
			$redirect =	admin_url( '/options-general.php?page=bsf-mautic&tab=auth_mautic' );
			printf( '<div class="update-nag bsf-update-nag">' . __( 'Seems there appears error with the Mautic configuration.', 'automateplus-mautic-wp' ) . ' <a href="'.$redirect.'">'.__('click here','bsf').'</a>' . __( ' to authenticate Mautic.', 'automateplus-mautic-wp' ) . '</div>' );
		}

		// if( ! empty( $_POST ) && $curr_screen=='bsf-mautic' ) {
		// 	echo '<div class="updated"><p>' . __( 'Settings updated!', 'automateplus-mautic-wp' ) . '</p></div>';
		// }
	}

	static public function render_messages( $message )
	{
		$curr_screen = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';

		if( $message = 'update' && $curr_screen=='bsf-mautic' ) {
			echo '<div class="updated"><p>' . __( 'Settings updated!', 'automateplus-mautic-wp' ) . '</p></div>';
		}
	}
}
$APM_AdminSettings = APM_AdminSettings::instance();
endif;