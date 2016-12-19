<?php
/**
 * Handles logic for the admin settings page. 
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSFMauticAdminSettings' ) ) :

final class BSFMauticAdminSettings {
	
	private static $instance;
	/**
	 * Holds any errors that may arise from
	 * saving admin settings.
	 *
	 * @since 1.0.0
	 * @var array $errors
	 */

	static public $errors = array();
	/**
	 * Initiator
	 */
	public static function instance(){
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BSFMauticAdminSettings();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() {
		add_action( 'admin_init', array( $this,'bsfm_set_mautic_code' ) );
		add_action( 'after_setup_theme', __CLASS__ . '::init_hooks' );
		add_action( 'admin_footer', array( $this, 'bsfm_mb_templates' ) );
		add_action( 'wp_loaded', array( $this, 'bsf_mautic_authenticate_update' ) );
	}
	/** 
	 * Include template to render meta box html
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bsfm_mb_templates() {
		$post_type = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		//if( isset($_REQUEST['post']) || $post_type_req =='bsf-mautic-rule' ) {
			//$post_type = isset( $_REQUEST['page'] ) ? get_post_type( $_REQUEST['page'] ) : '';
			if( 'bsf-mautic' == $post_type) {
				include BSF_MAUTIC_PLUGIN_DIR .'/assets/templates/meta-box-template.php';
			}
		//}
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
		//add_action( 'network_admin_menu', __CLASS__ . '::menu' );
		add_action( 'admin_menu', __CLASS__ . '::menu' );
		if ( ! is_admin() ) {
			return;
		}
		$post_type = '';
		if( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		}
		elseif( isset( $_REQUEST['post'] ) ) {
			$post_type = get_post_type( $_REQUEST['post'] );
		}
		if((isset( $_REQUEST['page']) && 'bsf-mautic' == $_REQUEST['page'] ) ) {
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
			//add_submenu_page( 'edit.php?post_type=bsf-mautic-rule', 'Settings',  __( 'Settings', 'bsfmautic' ) , $cap, $slug, $func );
			add_options_page( 'MautiPress',  __( 'MautiPress', 'bsfmautic' ), 'administrator', 'bsf-mautic', $func );
		}
	}
	
	/** 
	 * Enqueues the needed CSS/JS for admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function styles_scripts( $hook ) {
		if( isset($_REQUEST['post_type']) ) {
			$post_type = $_REQUEST['post_type'];
		}
		elseif( isset($_REQUEST['post']) ) {
			$post_type = get_post_type( $_REQUEST['post'] );
		}
		if ( (isset( $_REQUEST['page'] ) && 'bsf-mautic' == $_REQUEST['page'] ) ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'bsfm-admin-script', BSF_MAUTIC_PLUGIN_URL . '/assets/js/bsfm-admin.js' , array( 'jquery','jquery-ui-sortable','wp-util' ) );
			wp_enqueue_style( 'bsfm-admin-style', BSF_MAUTIC_PLUGIN_URL . '/assets/css/bsfm-admin.css' );
			wp_enqueue_script( 'bsfm-select2-script', BSF_MAUTIC_PLUGIN_URL . '/assets/js/select2.min.js' , array( 'jquery' ) );
			wp_enqueue_style( 'bsfm-select2-style', BSF_MAUTIC_PLUGIN_URL . '/assets/css/select2.min.css' );
		}
	}
	
	/** 
	 * Renders the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function render() {
		include BSF_MAUTIC_PLUGIN_DIR . 'classes/class-rules-table.php';
		include BSF_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-main.php';
	}

	static public function bsfm_rules_list() {

		$new_post_url = 'options-general.php?page=bsf-mautic&tab=add_new_rule';	
		?>
		<div class="wrap">
		<h1>
			Mautic Rules <a class="page-title-action" href="<?php echo $new_post_url; ?>" ><?php _e( 'Add New', 'bsfmautic' ); ?> </a>
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
			$cp_list_table = new Bsfm_Rules_Table();
			$cp_list_table->prepare_items();
			$cp_list_table->search_box( 'search', 'cp_popup_search' );
			?>
		</form>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="cp_popup_list" />
			<?php $cp_list_table->display(); ?>
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
		$icon = BSF_MAUTIC_PLUGIN_URL . '/assets/icon/mt.png';
		if ( ! empty( $icon ) ) {
			echo '<img class="bsfm-heading-icon" src="' . $icon . '" />';
		}
		echo '<div class="bsfm-heading-config"> MautiPress </div>';
	}
	/** 
	 * Renders the update message.
	 *
	 * @since 1.0.0
	 * @return void
	 */	 
	static public function render_update_message() {
		if ( ! empty( self::$errors ) ) {
			foreach ( self::$errors as $message ) {
				echo '<div class="error"><p>' . $message . '</p></div>';
			}
		}
		else if( ! empty( $_POST ) && ! isset( $_POST['email'] ) ) {
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
			include BSF_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php';
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
		if ( is_network_admin() ) {
			echo network_admin_url( '/edit.php?post_type=bsf-mautic-rule&page=bsf-mautic#' . $type );
		}
		else {
			echo admin_url( '/options-general.php?page=bsf-mautic&tab=auth_mautic' . $type );
		}
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
		if ( is_network_admin() ) {
			echo network_admin_url( '/options-general.php?page=bsf-mautic&action=' . $type );
		}
		else {
			echo admin_url( '/options-general.php?page=bsf-mautic&action=' . $type );
		}
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
		return file_exists( BSF_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php' );
	}
	
	/**
	 * Adds an error message to be rendered.
	 *
	 * @since 1.0.0
	 * @param string $message The error message to add.
	 * @return void
	 */	 
	static public function add_error( $message )
	{
		self::$errors[] = $message;
	}

	/**
	 * Save the mautic code.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function bsfm_set_mautic_code() {
		if( isset( $_GET['code'] ) && 'bsf-mautic' == $_REQUEST['page'] ) {
			$credentials = get_option( 'bsfm_mautic_credentials' );
			$credentials['access_code'] =  esc_attr( $_GET['code'] );
			update_option( 'bsfm_mautic_credentials', $credentials );
			self::get_mautic_data();
		}
	}
	/** 
	* Checks to see if multisite is supported.
	*
	* @since 1.0.0
	* @return void
	*/	 
	static public function multisite_support() {
		return is_multisite();
	}

	/** 
	 * Get Mautic Data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function get_mautic_data() {
		$credentials = get_option( 'bsfm_mautic_credentials' );
		// If not authorized 
		if( !isset( $credentials['access_token'] ) ) {
			if( isset( $credentials['access_code']  ) ) {
				$grant_type = 'authorization_code';
				$response = self::bsf_mautic_get_access_token( $grant_type );

				if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
					$access_details               = json_decode( $response['body'] );
					if( isset($access_details->error_description) ) {
						$errorMsg = $access_details->error_description;
					}
					$status   = 'error';
				} else {
					$access_details               = json_decode( $response['body'] );
					$expiration                   = time() + $access_details->expires_in;
					$credentials['access_token']  = $access_details->access_token;
					$credentials['expires_in']    = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( 'bsfm_mautic_credentials', $credentials );
				}
			}
		}
	}

	/** 
	 * Retrieve access token.
	 *
	 * @since 1.0.0
	 * @return response
	 */
	public static function bsf_mautic_get_access_token($grant_type) {
		$credentials = get_option('bsfm_mautic_credentials');

		if ( ! isset( $credentials['baseUrl'] ) ) {

			return;
		}
		$url = $credentials['baseUrl'] . "/oauth/v2/token";
		$body = array(	
			"client_id" => $credentials['clientKey'],
			"client_secret" => $credentials['clientSecret'],
			"grant_type" => $grant_type,
			"redirect_uri" => $credentials['callback'],
			'sslverify' => false
		);
		if( $grant_type == 'authorization_code' ) {
			$body["code"] = $credentials['access_code'];
		} else {
			$body["refresh_token"] = $credentials['refresh_token'];
		}
		// Request to get access token 
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $body,
			'cookies' => array()
			)
		);
		return $response;
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
					$cf7_keys = array_keys( $conditions, "CF7");
					$edd_keys = array_keys( $conditions, "EDD");
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
						if ($conditions[$i] == "CF7") {
							$sub_key = array_search($i,$cf7_keys);
							$update_maping = '';
							$form_id = $_POST['sub_cf_condition'][$sub_key];
							$update_maping['cf7_fields'] = $_POST['cf7_fields'][$form_id];
							$update_maping['mautic_cfields'] = $_POST['mautic_cfields'][$form_id];
							$update_conditions[$i] = array(
								$conditions[$i],
								$_POST['sub_cf_condition'][$sub_key],
								$update_maping );
						}
						if ($conditions[$i] == "EDD") {
							$sub_key = array_search($i,$edd_keys);
							$update_maping = '';
							$download_id = $_POST['sub_edd_condition'][$sub_key];
							$update_conditions[$i] = array(
								$conditions[$i],
								$_POST['sub_edd_condition'][$sub_key],
								$_POST['ss_edd_condition'][$sub_key],
								$_POST['ss_edd_var_price'][$sub_key] );
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

		// EDD Config
		if ( isset( $_POST['bsf-mautic-nonce-edd'] ) && wp_verify_nonce( $_POST['bsf-mautic-nonce-edd'], 'bsfmauticedd' ) ) {
			$bsfm = get_option('_bsf_mautic_config');
			$bsfm['bsfm_edd_prod_slug'] = $bsfm['bsfm_edd_prod_cat'] = $bsfm['bsfm_edd_prod_tag'] = false ;
			
			if( isset( $_POST['bsfm_edd_prod_slug'] ) ) {	$bsfm['bsfm_edd_prod_slug'] = true;	}
			if( isset( $_POST['bsfm_edd_prod_cat'] ) ) {	$bsfm['bsfm_edd_prod_cat'] = true;	}
			if( isset( $_POST['bsfm_edd_prod_tag'] ) ) {	$bsfm['bsfm_edd_prod_tag'] = true;	}

			if( isset( $_POST['ss_seg_action'][0] ) ) {	$bsfm['config_edd_segment'] = $_POST['ss_seg_action'][0]; }
			if( isset( $_POST['ss_seg_action'][1] ) ) {	$bsfm['config_edd_segment_ab'] = $_POST['ss_seg_action'][1]; }

			// Update the site-wide option since we're in the network admin.
			if ( is_network_admin() ) {
				update_site_option( '_bsf_mautic_config', $bsfm );
			}
			else {
				update_option( '_bsf_mautic_config', $bsfm );
			}
			$redirect =	admin_url( '/options-general.php?page=bsf-mautic&tab=edd_mautic' );
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
			if( isset( $_POST['bsfm-tracking-type'] ) ) {	$bsfm['bsfm-tracking-type'] = $_POST['bsfm-tracking-type'];	}

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
			self::bsfm_authenticate_update();
		}
	}
	
	static public function bsfm_authenticate_update()
	{
		$bsfm 	=	BSF_Mautic_Helper::get_bsfm_mautic();
		$mautic_api_url = $bsfm_public_key = $bsfm_secret_key = "";
		$post = $_POST;
		$cpts_err = false;
		$lists = null;
		$ref_list_id = null;

		$mautic_api_url = isset( $post['bsfm-base-url'] ) ? esc_attr( $post['bsfm-base-url'] ) : '';
		$bsfm_public_key = isset( $post['bsfm-public-key'] ) ? esc_attr( $post['bsfm-public-key'] ) : '';
		$bsfm_secret_key = isset( $post['bsfm-secret-key'] ) ? esc_attr( $post['bsfm-secret-key'] ) : '';
		$mautic_api_url = rtrim( $mautic_api_url ,"/");
		if( $mautic_api_url == '' ) {	
			$status = 'error';
			$message = 'API URL is missing.';
			$cpts_err = true;
		}
		if( $bsfm_secret_key == '' ) {
			$status = 'error';
			$message = 'Secret Key is missing.';
			$cpts_err = true;
		}
		$settings = array(
			'baseUrl'		=> $mautic_api_url,
			'version'		=> 'OAuth2',
			'clientKey'		=> $bsfm_public_key,
			'clientSecret'	=> $bsfm_secret_key, 
			'callback'		=> admin_url( 'options-general.php?page=bsf-mautic&tab=auth_mautic' ),
			'response_type'	=> 'code'
		);

		update_option( 'bsfm_mautic_credentials', $settings );
		$authurl = $settings['baseUrl'] . '/oauth/v2/authorize';
		//OAuth 2.0
		$authurl .= '?client_id='.$settings['clientKey'].'&redirect_uri='.urlencode( $settings['callback'] );
		$state    = md5(time().mt_rand());
		$authurl .= '&state='.$state;
		$authurl .= '&response_type='.$settings['response_type'];
		wp_redirect( $authurl );
		exit;
	}
}
$BSFMauticAdminSettings = BSFMauticAdminSettings::instance();
endif;