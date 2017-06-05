<?php
/**
 * Mautic API functions.
 *
 * @package automateplus-mautic
 * @since 1.0.5
 */

/**
 * Helper class for the Mautic API.
 *
 * @since 1.0.5
 */
final class APMauticServiceMautic extends APMauticService {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.5
	 * @var string $id
	 */
	public $id = AP_MAUTIC_SERVICE;

	/**
	 * Store API instance.
	 *
	 * @since 1.0.5
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.0.5
	 * @return object The API instance.
	 */
	public function get_api() {
		if ( $this->api_instance ) {
			return $this->api_instance;
		}
		if ( ! class_exists( 'AP_MauticAPI' ) ) {
			require_once AP_MAUTIC_PLUGIN_DIR . 'includes/vendor/mautic.php';
		}

		$this->api_instance = new AP_MauticAPI();
		return $this->api_instance;
	}

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.5
	 * @param array $data post authentication data.
	 * @return void
	 */
	public function connect( $data ) {

		$mautic_api_url = $apm_public_key = $apm_secret_key = '';
		$cpts_err = false;
		$lists = null;
		$ref_list_id = null;

		$mautic_api_url = isset( $data['base-url'] ) ? esc_url( $data['base-url'] ) : '';
		$apm_public_key = isset( $data['public-key'] ) ? sanitize_key( $data['public-key'] ) : '';
		$apm_secret_key = isset( $data['secret-key'] ) ? sanitize_key( $data['secret-key'] ) : '';

		$mautic_api_url = rtrim( $mautic_api_url ,'/' );
		if ( empty( $mautic_api_url ) ) {
			$status = 'error';
			$message = 'API URL is missing.';
			$cpts_err = true;
		}
		if ( empty( $apm_secret_key ) ) {
			$status = 'error';
			$message = 'Secret Key is missing.';
			$cpts_err = true;
		}
		$settings = array(
		'baseUrl'		=> $mautic_api_url,
		'version'		=> 'OAuth2',
		'clientKey'		=> $apm_public_key,
		'clientSecret'	=> $apm_secret_key,
		'callback'		=> APMautic_AdminSettings::get_render_page_url( '&tab=auth_mautic' ),
		'response_type'	=> 'code',
		);

		update_option( AP_MAUTIC_APIAUTH, $settings );
		$authurl = $settings['baseUrl'] . '/oauth/v2/authorize';
		// OAuth 2.0.
		$authurl .= '?client_id=' . $settings['clientKey'] . '&redirect_uri=' . urlencode( $settings['callback'] );
		$state    = md5( time() . mt_rand() );
		$authurl .= '&state=' . $state;
		$authurl .= '&response_type=' . $settings['response_type'];
		wp_redirect( $authurl );
		exit;
	}

	/**
	 * Update access token after authentication.
	 *
	 * @since 1.0.5
	 */
	public function update_token() {

		$api = $this->get_api();
		if ( isset( $_GET['code'] ) && AP_MAUTIC_POSTTYPE == $_REQUEST['page'] ) {
			$credentials = APMautic_helper::get_mautic_credentials();
			$credentials['access_code'] = sanitize_key( $_GET['code'] );
			update_option( AP_MAUTIC_APIAUTH, $credentials );
			$api->get_mautic_data();
		}
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.0.5
	 * @param array $service_data authenticated saved data.
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings( $service_data ) {
		$base_url  = isset( $service_data['base-url'] ) ? $service_data['base-url'] : '';

		ob_start();

		APMautic_helper::render_input_html('base-url', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'def_value'		=> $base_url,
			'type'          => 'text',
			'label'         => __( 'Base URL', 'automateplus-mautic-wp' ),
			'help'          => __( 'This setting is required for Mautic Integration.', 'automateplus-mautic-wp' ),
		));

		APMautic_helper::render_input_html('ampw-save-authenticate', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'type'          => 'button',
			'def_value'		=> 'connected',
			'nonce_acion'	=> 'apmwmautic',
			'nonce_name'	=> 'ap-mautic-nonce',
			'label'         => __( 'Save and Authenticate', 'automateplus-mautic-wp' ),
		));
		return ob_get_clean();
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.0.5
	 * @param array $service_data Saved module settings.
	 * @return string html data
	 */
	public function render_fields( $service_data ) {
		$base_url  = isset( $service_data['base-url'] ) ? $service_data['base-url'] : '';

		ob_start();

		APMautic_helper::render_input_html('base-url', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'def_value'		=> $base_url,
			'type'          => 'text',
			'label'         => __( 'Base URL', 'automateplus-mautic-wp' ),
			'help'          => __( 'This setting is required for Mautic Integration.', 'automateplus-mautic-wp' ),
		));

		APMautic_helper::render_input_html('public-key', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'type'          => 'text',
			'label'         => __( 'Public Key', 'automateplus-mautic-wp' ),
		));

		APMautic_helper::render_input_html('secret-key', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'type'          => 'text',
			'label'         => __( 'Secret Key', 'automateplus-mautic-wp' ),
			'desc'          => sprintf( __( 'This setting is required to integrate Mautic in your website.<br>Need help to get Mautic API public and secret key? Read %1$sthis article%2$s.', 'automateplus-mautic-wp' ), '<a target="_blank" href="' . esc_url( 'https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/' ) . '">', '</a>' ),
		));

		APMautic_helper::render_input_html('ampw-save-authenticate', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'save-amp-settings',
			'type'          => 'submit',
			'def_value'		=> 'Save and Authenticate',
			'spinner'		=> true,
			'nonce_acion'	=> 'apmwmautic',
			'nonce_name'	=> 'ap-mautic-nonce',
			'label'         => __( 'Save and Authenticate', 'automateplus-mautic-wp' ),
		));

		return ob_get_clean();
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.0.5
	 * @param object $select Saved list.
	 * @return string The markup for the list field.
	 */
	public function render_list_field( $select ) {

		$segments_trans = get_transient( 'apm_all_segments' );
		if ( $segments_trans ) {
			$segments = $segments_trans;
		} else {
			$api = $this->get_api();
			$segments = $api->get_all_segments();
			set_transient( 'apm_all_segments', $segments , DAY_IN_SECONDS );
		}
		if ( empty( $segments ) || ! APMauticServices::is_connected() ) {
			echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'automateplus-mautic-wp' );
			return;
		}
		$options = array( '' => __( 'Select Segment', 'automateplus-mautic-wp' ) );
		foreach ( $segments as $list ) {
			$options[ $list->id ] = $list->name;
		}
		APMautic_helper::render_settings_field( 'ss_seg_action[]', array(
			'type'			=> 'select',
			'id'			=> 'ss-cp-condition',
			'class'			=> 'root-seg-action',
			'options'		=> $options,
			'selected'		=> $select,
		));
	}

	/**
	 * Subscribe an email address to Mautic.
	 *
	 * @since 1.0.5
	 * @param string $email The email to subscribe.
	 * @param array  $settings body params.
	 * @param string $actions all set actions in rule.
	 * @return void
	 */
	public function subscribe( $email, $settings, $actions ) {

		$api = $this->get_api();
		$api_data = $api->get_api_method_url( $email );
		$url = $api_data['url'];
		$method = $api_data['method'];

		// add tags set in actions.
		if ( isset( $actions['add_tag'] ) ) {

			foreach ( $actions['add_tag'] as $tags ) {
				$all_tags .= $tags . ',';
			}

			$all_tags = rtrim( $all_tags ,',' );
			$settings['tags'] = $all_tags;
		}
		$api->ampw_mautic_api_call( $url, $method, $settings, $actions );
	}
}
