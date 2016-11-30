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
			//self::$instance->render();
		}
		return self::$instance;
	}

	public function hooks() {
		add_action( 'admin_init', array( $this,'bsfm_set_mautic_code' ) );
		add_action( 'after_setup_theme', __CLASS__ . '::init_hooks' );
		add_action( 'admin_footer', array( $this, 'bsfm_mb_templates' ) );
	}
	/** 
	 * Include template to render meta box html
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bsfm_mb_templates() {
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : '';
		if(isset($_REQUEST['post']) || $post_type =='bsf-mautic-rule' ) {
			$post_type = isset( $_REQUEST['post'] ) ? get_post_type( $_REQUEST['post'] ) : '';
			if( 'bsf-mautic-rule' == $post_type || $_REQUEST['post_type']=='bsf-mautic-rule' ) {
				include BSF_MAUTIC_PLUGIN_DIR .'/assets/templates/meta-box-template.php';
			}
		}
	}

	/** 
	 * Adds the admin menu and enqueues CSS/JS if we are on
	 * the MauticPress admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function init_hooks()
	{
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'network_admin_menu', __CLASS__ . '::menu' );
		add_action( 'admin_menu', __CLASS__ . '::menu' );
		$post_type = '';
		if(isset($_REQUEST['post_type'])) {
			$post_type = $_REQUEST['post_type'];
		}
		elseif(isset($_REQUEST['post'])) {
			$post_type = get_post_type( $_REQUEST['post'] );
		}
		if( (isset( $_REQUEST['page']) && 'bsf-mautic-settings' == $_REQUEST['page'] ) || ('bsf-mautic-rule' == $post_type) ) {
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::styles_scripts' );
			self::save();
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
			add_submenu_page( 'edit.php?post_type=bsf-mautic-rule', 'Settings', 'Settings', $cap, $slug, $func );
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
		if ( (isset( $_REQUEST['page'] ) && 'bsf-mautic-settings' == $_REQUEST['page'] ) || ('bsf-mautic-rule' == $post_type) ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'bsfm-admin-script', BSF_MAUTIC_PLUGIN_URL . '/assets/js/bsfm-admin.js' , array( 'jquery','jquery-ui-sortable','wp-util' ) );
			wp_enqueue_style( 'bsfm-admin-style', BSF_MAUTIC_PLUGIN_URL . '/assets/css/bsfm-admin.css' );
			wp_enqueue_script( 'bsfm-select2-script', BSF_MAUTIC_PLUGIN_URL . '/assets/js/select2.min.js' , array( 'jquery' ) );
			wp_enqueue_style( 'bsfm-select2-style', BSF_MAUTIC_PLUGIN_URL . '/assets/css/select2.min.css' );
		}
		//Load AJAX script only on Builder UI Panel
		if ( ( isset( $_REQUEST['page'] ) && 'bsf-mautic-settings' == $_REQUEST['page'] ) ) {
			wp_enqueue_script( 'bsfm-admin-menu-js', BSF_MAUTIC_PLUGIN_URL . 'assets/js/bsfm-admin-menu.js' );
			wp_register_style( 'bsfm-admin-menu-css', BSF_MAUTIC_PLUGIN_URL . 'assets/css/bsfm-admin-menu.css' );
		}
		if( 'bsf-mautic-rule_page_bsf-mautic-settings' == $hook || 'bsf-mautic-rule_page_bsf-mautic-multisite-settings' == $hook ) {
			wp_enqueue_style( 'bsfm-admin-menu-css' );
		}
	}
	
	/** 
	 * Renders the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function render() {
		include BSF_MAUTIC_PLUGIN_DIR . 'includes/admin-settings.php';
	}
	
	/** 
	 * Renders the page class for network installs and single site installs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function render_page_class() {
		if ( self::multisite_support() ) {
			echo 'fl-settings-network-admin';
		}
		else {
			echo 'fl-settings-single-install';
		}
	}
	/***
	 * Renders the admin settings page heading.
	 * @since 1.0.0
	 * @return void
	 */
	static public function render_page_heading()
	{
		if ( ! empty( $icon ) ) {
			echo '<img src="' . $icon . '" />';
		}
		echo '<span>' . sprintf( _x( '%s Settings', '%s stands for custom branded "UABB" name.', 'bsfmautic' ), BSFM_PREFIX ) . '</span>';
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
	 * Renders the nav items for the admin settings menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */	
	static public function render_nav_items()
	{
		/*$items['bsfm-license'] = array(
			'title' 	=> __( 'License', 'bsfmautic' ),
			'show'		=>  true,
			'priority'	=> 504
		);*/
		$items['bsfm-config'] = array(
			'title' 	=> __( 'Mautic Configuration', 'bsfmautic' ),
			'show'		=>  true,
			'priority'	=> 505
		);

		if ( get_option( 'bsfm_hide_branding' ) != true ) {
			$items['bsfm-branding'] = array(
				'title' 	=> __( 'Branding', 'bsfmautic' ),
				'show'		=>  true,
				'priority'	=> 506
			);
		}

		$item_data = apply_filters( 'bsf_mautic_admin_settings_nav_items', $items );
		
		$sorted_data = array();
		foreach ( $item_data as $key => $data ) {
			$data['key'] = $key;
			$sorted_data[ $data['priority'] ] = $data;
		}
		ksort( $sorted_data );
		foreach ( $sorted_data as $data ) {
			if ( $data['show'] ) {
				echo '<li><a href="#' . $data['key'] . '">' . $data['title'] . '</a></li>';
			}
		}
	}
	
	/** 
	 * Renders the admin settings forms.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function render_forms()
	{
		// License
		if ( is_network_admin() || ! self::multisite_support() )  {
			//self::render_form( 'license' );
		}
		self::render_form( 'bsfm-config' );
		self::render_form( 'bsfm-branding' );
		// Let extensions hook into form rendering.
		do_action( 'bsf_mautic_admin_settings_render_forms' );
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
			// Prev : settings.php?page=uabb-builder-multisite-settings - need to add multisite part
			echo network_admin_url( '/edit.php?post_type=bsf-mautic-rule&page=bsf-mautic-settings#' . $type );
		}
		else {
			echo admin_url( '/edit.php?post_type=bsf-mautic-rule&page=bsf-mautic-settings#' . $type );
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
		if ( is_network_admin() ) {
			//pending
			return network_admin_url( '/settings.php?page=uabb-builder-multisite-settings#' . $type );
		}
		else {
			return admin_url( '/options-general.php?page=bsf-mautic-settings#' . $type );
		}
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
		if( isset($_GET['code']) ) {
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
		return is_multisite() && class_exists( 'FLBuilderMultisiteSettings' );
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
				$access_details = json_decode( $response['body'] );

					if( isset( $access_details->error ) ) {
						echo json_encode($result);
						exit('unable to connect');
					}

				$expiration = time() + $access_details->expires_in;
				$credentials['access_token'] = $access_details->access_token;
				$credentials['expires_in'] = $expiration;
				$credentials['refresh_token'] = $access_details->refresh_token;
				update_option( 'bsfm_mautic_credentials', $credentials );
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
		if ( isset( $_POST['bsf-mautic-nonce'] ) && wp_verify_nonce( $_POST['bsf-mautic-nonce'], 'bsfmautic' ) ) {
			$bsfm['bsfm-enabled-tracking'] = false;
			$bsfm['bsfm-enabled-tracking-img'] = false;
			if( isset( $_POST['bsfm-base-url'] ) ) {	$bsfm['bsfm-base-url'] = esc_url( $_POST['bsfm-base-url'] ); }
			if( isset( $_POST['bsfm-public-key'] ) ) {	$bsfm['bsfm-public-key'] = sanitize_key( $_POST['bsfm-public-key'] ); }
			if( isset( $_POST['bsfm-secret-key'] ) ) {	$bsfm['bsfm-secret-key'] = sanitize_key( $_POST['bsfm-secret-key'] ); }
			if( isset( $_POST['bsfm-callback-uri'] ) ) {	$bsfm['bsfm-callback-uri'] = esc_url( $_POST['bsfm-callback-uri'] ); }
			if( isset( $_POST['bsfm-enabled-tracking'] ) ) {	$bsfm['bsfm-enabled-tracking'] = true;	}
			if( isset( $_POST['bsfm-enabled-tracking-img'] ) ) {	$bsfm['bsfm-enabled-tracking-img'] = true;	}

			// Update the site-wide option since we're in the network admin.
			if ( is_network_admin() ) {
				update_site_option( '_bsf_mautic_config', $bsfm );
			}
			else {
				update_option( '_bsf_mautic_config', $bsfm );
			}
		}
		if ( isset( $_POST['bsfm-save-authenticate'] ) && $_POST['bsfm-save-authenticate']=='Save and Authenticate' ) {
			self::bsfm_authenticate_update();
			//provide action to authenticate differnet API's
		}
		if ( isset( $_POST['bsfm-branding-nonce'] ) && wp_verify_nonce( $_POST['bsfm-branding-nonce'], 'bsfm-branding' ) ) {
			if( isset( $_POST['bsfm-plugin-name'] ) ) 			{	$bsfm['bsfm-plugin-name']			= wp_kses_post( $_POST['bsfm-plugin-name'] );	}
			if( isset( $_POST['bsfm-plugin-short-name'] ) ) 	{	$bsfm['bsfm-plugin-short-name']		= wp_kses_post( $_POST['bsfm-plugin-short-name'] );	}
			if( isset( $_POST['bsfm-plugin-desc'] ) ) 			{	$bsfm['bsfm-plugin-desc']			= wp_kses_post( $_POST['bsfm-plugin-desc'] );	}
			if( isset( $_POST['bsfm-author-name'] ) ) 			{	$bsfm['bsfm-author-name']			= wp_kses_post( $_POST['bsfm-author-name'] );	}
			if( isset( $_POST['bsfm-author-url'] ) ) 			{	$bsfm['bsfm-author-url']			= sanitize_text_field( $_POST['bsfm-author-url'] );	}
			if( isset( $_POST['bsfm-knowledge-base-url'] ) ) 	{	$bsfm['bsfm-knowledge-base-url']	= sanitize_text_field( $_POST['bsfm-knowledge-base-url'] );	}
			if( isset( $_POST['bsfm-contact-support-url'] ) ) 	{	$bsfm['bsfm-contact-support-url']	= sanitize_text_field( $_POST['bsfm-contact-support-url'] );	}

			if( isset( $_POST['bsfm-hide-branding'] ) ) {
				update_option( 'bsfm_hide_branding', true );
			} else {
				update_option( 'bsfm_hide_branding', false );
			}
			// Update the site-wide option since we're in the network admin.
			if ( is_network_admin() ) {
				update_site_option( '_bsf_mautic_branding', $bsfm );
			}
			else {
				update_option( '_bsf_mautic_branding', $bsfm );
			}
		}
		/**
		 *	For Performance
		 *	Update UABB static object from database.
		 */
		//BSF_Mautic_Init::set_uabb_options();
		//Clear all asset cache.
		//FLBuilderModel::delete_asset_cache_for_all_posts();
	}

	static public function bsfm_authenticate_update()
	{
			// @todo check if the request is sent from user with admin rights
			// @todo check if Base URL, Consumer/Client Key and Consumer/Client secret are not empty
			// @todo load this array from database or config file
			$bsfm 	=	BSF_Mautic_Helper::get_bsfm_mautic();
			$mautic_api_url = $bsfm_public_key = $bsfm_secret_key = "";
			$post = $_POST;
			$cpts_err = false;
			$lists = null;
			$ref_list_id = null;

			$mautic_api_url = isset( $post['bsfm-base-url'] ) ? esc_attr( $post['bsfm-base-url'] ) : '';
			$bsfm_public_key = isset( $post['bsfm-public-key'] ) ? esc_attr( $post['bsfm-public-key'] ) : '';
			$bsfm_secret_key = isset( $post['bsfm-secret-key'] ) ? esc_attr( $post['bsfm-secret-key'] ) : '';

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
				'callback'		=> admin_url( 'edit.php?post_type=bsf-mautic-rule&page=bsf-mautic-settings#bsfm-config' ),
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