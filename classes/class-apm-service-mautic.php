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
			'row_class'     => 'apm-service-row amp-connected-btn',
			'class'         => 'apm-service-input',
			'type'          => 'button',
			'def_value'		=> 'Connected',
			'nonce_acion'	=> 'apmwmautic',
			'nonce_name'	=> 'ap-mautic-nonce',
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
	 * Render contact field.
	 *
	 * @since 1.0.5
	 * @return string The markup for the list field.
	 */
	public function get_contact_field() {

		$mautic_cfields_trans = get_transient( 'mautic_all_cfields' );
		if ( $mautic_cfields_trans ) {
			$mautic_cfields = $mautic_cfields_trans;
		} else {
			$api = $this->get_api();
			$mautic_cfields = $api->get_all_contact_fields();
			set_transient( 'mautic_all_cfields', $mautic_cfields, DAY_IN_SECONDS );
		}
		return $mautic_cfields;
	}

	/**
	 * Subscribe an email address to Mautic.
	 *
	 * @since 1.0.5
	 * @param string  $email The email to subscribe.
	 * @param array   $settings body params.
	 * @param string  $actions all set actions in rule.
	 * @param boolean $new_contact if set create new contact.
	 * @return void
	 */
	public function subscribe( $email, $settings, $actions, $new_contact = false ) {

		$api = $this->get_api();

		if ( ! $new_contact ) {
			$api_data = $api->get_api_method_url( $email );
			$url = $api_data['url'];
			$method = $api_data['method'];
		} else {
			$method = 'POST';
			$url = '/api/contacts/new';
		}
		// add tags set in actions.
		if ( ! empty( $actions['add_tag'] ) ) {

			foreach ( $actions['add_tag'] as $tags ) {
				$all_tags .= $tags . ',';
			}

			$all_tags = rtrim( $all_tags ,',' );
			$settings['tags'] = $all_tags;
		}
		$api->ampw_mautic_api_call( $url, $method, $settings, $actions );
	}

	/**
	 * Remove contact form all segment.
	 *
	 * @since 1.0.5
	 * @param string $email The email to subscribe.
	 * @return void
	 */
	public function remove_from_all_segment( $email ) {

		$api = $this->get_api();
		$api->remove_from_all_segments( $email );
	}

	/**
	 * Check if contact is already published.
	 *
	 * @since 1.0.5
	 * @param int $id contact id.
	 * @return bool
	 */
	public function is_contact_published( $id ) {

		$api = $this->get_api();
		$status = $api->is_contact_published( $email );
		return $status;
	}

	/**
	 * Add/remove contacts from segment
	 *
	 * @since 1.0.5
	 * @param int    $segment_id api mautic segment ID.
	 * @param int    $contact_id mautic contact ID.
	 * @param array  $mautic_credentials mautic credentials.
	 * @param string $act operation to perform.
	 * @return array
	 */
	public function mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act ) {

		$api = $this->get_api();
		$response = $api->mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act );
		return $response;
	}
}
