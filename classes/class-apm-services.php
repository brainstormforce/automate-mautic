<?php

/**
 * Helper class for connecting to third party services.
 *
 * @since 1.0.4
 */
final class APMauticServices {

	/**
	 * Data for working with each supported third party service.
	 *
	 * @since 1.0.4
	 * @access private
	 * @var array $services_data
	 */
	static private $services_data = array(
		'mautic'    => array(
			'type'              => 'autoresponder',
			'name'              => 'Mautic',
			'class'             => 'APMauticServiceMautic'
		)
	);

	/**
	 * Save mautic code.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function set_mautic_code() {
		if ( isset( $_GET['code'] ) && AP_MAUTIC_POSTTYPE == $_REQUEST['page'] ) {
			$credentials = APMautic_helper::get_mautic_credentials();
			$credentials['access_code'] = sanitize_key( $_GET['code'] );
			update_option( AP_MAUTIC_APIAUTH, $credentials );
			self::get_mautic_data();
		}
	}

	/**
	 * Update Mautic credentials
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function get_mautic_data() {
		$credentials = APMautic_helper::get_mautic_credentials();
		// If not authorized.
		if ( ! isset( $credentials['access_token'] ) ) {
			if ( isset( $credentials['access_code'] ) ) {
				$grant_type = 'authorization_code';
				$response = self::mautic_get_access_token( $grant_type );

				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
					$status   = 'error';
				} else {

					$response_body = wp_remote_retrieve_body( $response );

					$access_details               = json_decode( $response_body );
					$expiration                   = time() + $access_details->expires_in;
					$credentials['access_token']  = $access_details->access_token;
					$credentials['expires_in']    = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( AP_MAUTIC_APIAUTH, $credentials );
				}
			}
		}
	}

	/**
	 * Retrieve access token.
	 *
	 * @since 1.0.0
	 * @param string $grant_type grant type for request.
	 * @return array
	 */
	public static function mautic_get_access_token( $grant_type ) {
		$credentials = APMautic_helper::get_mautic_credentials();

		if ( ! isset( $credentials['baseUrl'] ) ) {

			return;
		}
		$url = $credentials['baseUrl'] . '/oauth/v2/token';
		$body = array(
		'client_id' => $credentials['clientKey'],
		'client_secret' => $credentials['clientSecret'],
		'grant_type' => $grant_type,
		'redirect_uri' => $credentials['callback'],
		'sslverify' => false,
		);
		if ( 'authorization_code' == $grant_type ) {
			$body['code'] = $credentials['access_code'];
		} else {
			$body['refresh_token'] = $credentials['refresh_token'];
		}
			// Request to get access token.
			$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $body,
				'cookies' => array(),
				)
			);
		return $response;
	}

	/**
	 * Add contacts to Mautic, Add to segments, return GET request data
	 *
	 * @since 1.0.0
	 * @param string $url api endpoint.
	 * @param string $method API menthod.
	 * @param array  $param parameters.
	 * @param array  $segments mautic segments ID.
	 * @return void
	 */
	public static function ampw_mautic_api_call( $url, $method, $param = array(), $segments = array() ) {
		$status = 'success';

		self::generate_access_token();
		// add contacts.
		$credentials = APMautic_helper::get_mautic_credentials();

		if ( ! isset( $credentials['access_token'] ) ) {
			return;
		}
		$access_token = $credentials['access_token'];
		$param['access_token'] = $access_token;

		$url = $credentials['baseUrl'] . $url;

		$param['ipAddress'] = $_SERVER['REMOTE_ADDR'];
		$response = wp_remote_post( $url, array(
			'method' => $method,
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $param,
			'cookies' => array(),
		));

		if ( is_wp_error( $response ) ) {
			$error_msg = $response->get_error_message();
			$status = 'error';
			echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );

		} else {

			if ( is_array( $response ) ) {

				$response_code = wp_remote_retrieve_response_code( $response );

				if ( 200 === $response_code || 201 === $response_code ) {

					$response_body = wp_remote_retrieve_body( $response );
					$contact_created = json_decode( $response_body );
					$contact = $contact_created->contact;

					if ( isset( $contact->id ) ) {

						$contact_id = (int) $contact->id;
						$res = self::contact_segment_subscribe( $contact_id, $credentials, $segments );
						$status = $res['status'];
					}
				} else {
					$ret = false;
					$status = 'error';
					$error_msg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
				}
			}
		}
	}


	/**
	 * GET api request data
	 *
	 * @since 1.0.0
	 * @param string $url api endpoint.
	 * @param string $url API menthod.
	 * @param array  $param parameters.
	 * @return array
	 */
	public static function mautic_api_get_data( $url, $param = array() ) {
		$status = 'success';
		// add contacts.
		self::generate_access_token();
		$credentials = APMautic_helper::get_mautic_credentials();

		if ( ! isset( $credentials['access_token'] ) ) {
			return;
		}
		$access_token = $credentials['access_token'];
		$param['access_token'] = $access_token;

		$url = $credentials['baseUrl'] . $url;

		$url = $url . '?access_token=' . $access_token;

		if ( isset( $param['limit'] ) ) {
			// make sure segments are not limited to 10.
			$url .= '&limit=' . $param['limit'];
		}
		$response = wp_remote_get( $url );

		if ( is_array( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			$body_data = json_decode( $response_body );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 201 !== $response_code  ) {

				if ( 200 !== $response_code ) {
					$ret = false;
					$status = 'error';
					$error_msg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
					echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
					return;
				}
			}
			return $body_data;
		}
	}

	/**
	 * check refresh token
	 *
	 * @since 1.0.0
	 * @param int    $segment_id api mautic segment ID.
	 * @param int    $contact_id mautic contact ID.
	 * @param array  $mautic_credentials mautic credentials.
	 * @param string $act operation to perform.
	 * @return array
	 */
	public static function generate_access_token() {

		$credentials = APMautic_helper::get_mautic_credentials();
		if ( isset( $credentials['access_code'] ) && ! empty( $credentials['access_code'] )  ) {
			// if token expired, get new access token.
			if ( $credentials['expires_in'] < time() ) {
				$grant_type = 'refresh_token';
				$response = self::mautic_get_access_token( $grant_type );
				if ( is_wp_error( $response ) ) {
					$error_msg = $response->get_error_message();
					$status = 'error';
					echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
				} else {

					$response_body = wp_remote_retrieve_body( $response );
					$access_details = json_decode( $response_body );
					$expiration = time() + $access_details->expires_in;
					$credentials['access_token'] = $access_details->access_token;
					$credentials['expires_in'] = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( AP_MAUTIC_APIAUTH, $credentials );
				}
			} // refresh code token ends.
		}
	}

	/**
	 * call contact add or remove to segment
	 *
	 * @since 1.0.0
	 * @param int    $segment_id api mautic segment ID.
	 * @param int    $contact_id mautic contact ID.
	 * @param array  $mautic_credentials mautic credentials.
	 * @param string $act operation to perform.
	 * @return array
	 */
	public static function contact_segment_subscribe( $contact_id, $credentials, $segments ) {

		// add contact to segment.
		$add_segment = $segments['add_segment'];
		if ( is_array( $add_segment ) ) {
			foreach ( $add_segment as $segment_id ) {
				$segment_id = (int) $segment_id;
				$action = 'add';
				$result = self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action );
			}
		}

		// remove contact from segment.
		$remove_segment = $segments['remove_segment'];
		if ( is_array( $remove_segment ) ) {
			foreach ( $remove_segment as $segment_id ) {
				$segment_id = (int) $segment_id;
				$action = 'remove';
				$result = self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action );
			}
		}
		return $result;
	}

	/**
	 * Add/remove contacts to segment
	 *
	 * @since 1.0.0
	 * @param int    $segment_id api mautic segment ID.
	 * @param int    $contact_id mautic contact ID.
	 * @param array  $mautic_credentials mautic credentials.
	 * @param string $act operation to perform.
	 * @return array
	 */
	public static function mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act ) {
		$error_msg = '';
		$status = 'error';
		if ( is_int( $segment_id ) && is_int( $contact_id ) ) {
			$url = $mautic_credentials['baseUrl'] . '/api/segments/' . $segment_id . '/contact/' . $act . '/' . $contact_id;
			$access_token = $mautic_credentials['access_token'];
			$body = array(
			'access_token' => $access_token,
			);
			$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $body,
				'cookies' => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
					$error_msg = $response->get_error_message();
					$status = 'error';
			} else {
				if ( is_array( $response ) ) {

					$response_code = wp_remote_retrieve_response_code( $response );

					if ( 200 != $response_code ) {
						$status = 'error';
						$error_msg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
					} else {
						$status = 'success';
					}
				}
			}
		}
		$response = array(
		'status' => $status,
		'error_message' => $error_msg,
		);
		return $response;
	}

	/**
	 * Get Mautic contact ID
	 *
	 * @since 1.0.0
	 * @param string $email contact email.
	 * @param array  $mautic_credentials mautic credentials.
	 * @return void
	 */
	public static function mautic_get_contact_by_email( $email, $mautic_credentials ) {
		if ( $mautic_credentials['expires_in'] < time() ) {
			$grant_type = 'refresh_token';
			$response = self::mautic_get_access_token( $grant_type );
			if ( is_wp_error( $response ) ) {
				$error_msg = $response->get_error_message();
				$status = 'error';
				echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
			} else {
				$response_body = wp_remote_retrieve_body( $response );
				$access_details = json_decode( $response_body );
				$expiration = time() + $access_details->expires_in;
				$mautic_credentials['access_token'] = $access_details->access_token;
				$mautic_credentials['expires_in'] = $expiration;
				$mautic_credentials['refresh_token'] = $access_details->refresh_token;
				update_option( AP_MAUTIC_APIAUTH, $mautic_credentials );
			}
		}

		$error_msg = $contact_id = '';
		$contacts = array();
		$access_token = $mautic_credentials['access_token'];
		$access_token = esc_attr( $access_token );
		$url = $mautic_credentials['baseUrl'] . '/api/contacts/?search=' . $email . '&access_token=' . $access_token;

		$response = wp_remote_get( $url );

		if ( ! is_wp_error( $response ) && is_array( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			$body_data = json_decode( $response_body );

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 201 !== $response_code ) {
				if ( 200 !== $response_code ) {
					$ret = false;
					$status = 'error';
					$error_msg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
					__( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
					return;
				}
			}

			if ( isset( $body_data->contacts ) ) {

				$contacts = $body_data->contacts;
			}

			foreach ( $contacts as $contact ) {

				$contact_id = $contact->id;
			}

			if ( 0 === $contact_id ) {
				return;
			}
		}
		return $contact_id;
	}

	/**
	 * Check if contact is exist in mautic
	 *
	 * @since 1.0.0
	 * @param id $id contact ID.
	 * @return void
	 */
	public static function is_contact_published( $id ) {

		$mautic_credentials = APMautic_helper::get_mautic_credentials();

		if ( $mautic_credentials['expires_in'] < time() ) {
			$grant_type = 'refresh_token';
			$response = self::mautic_get_access_token( $grant_type );
			if ( is_wp_error( $response ) ) {
				$error_msg = $response->get_error_message();
				$status = 'error';
				echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
			} else {

				$response_body = wp_remote_retrieve_body( $response );

				$access_details = json_decode( $response_body );
				$expiration = time() + $access_details->expires_in;
				$mautic_credentials['access_token'] = $access_details->access_token;
				$mautic_credentials['expires_in'] = $expiration;
				$mautic_credentials['refresh_token'] = $access_details->refresh_token;
				update_option( AP_MAUTIC_APIAUTH, $mautic_credentials );
			}
		}

		$access_token = $mautic_credentials['access_token'];
		$access_token = esc_attr( $access_token );
		$url = $mautic_credentials['baseUrl'] . '/api/contacts/?search=!is:anonymous%20AND%20ids:' . $id . '&access_token=' . $access_token;

		$response = wp_remote_get( $url );

		if ( ! is_wp_error( $response ) && is_array( $response ) ) {

			$response_body = wp_remote_retrieve_body( $response );

			$body_data = json_decode( $response_body );

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 201 !== $response_code ) {
				if ( 200 !== $response_code ) {
					$status = 'error';
					__( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
					return;
				}
			}
			// return if not found.
			if ( isset( $body_data->errors ) ) {

				return false;
			}

			if ( isset( $body_data->total ) && $body_data->total > 0 ) {

				return true;
			}
		}
		return false;
	}

	/**
	 * Authenticate credentials update
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function authenticate_update() {
		$mautic_api_url = $apm_public_key = $apm_secret_key = '';
		$post = $_POST;
		$cpts_err = false;
		$lists = null;
		$ref_list_id = null;

		$mautic_api_url = isset( $post['base-url'] ) ? esc_url( $post['base-url'] ) : '';
		$apm_public_key = isset( $post['public-key'] ) ? sanitize_key( $post['public-key'] ) : '';
		$apm_secret_key = isset( $post['secret-key'] ) ? sanitize_key( $post['secret-key'] ) : '';

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
	 * Get Method and URL according to user email
	 *
	 * @since 1.0.0
	 * @param string $email user email.
	 * @return array
	 */
	public static function get_api_method_url( $email ) {

		$credentials = APMautic_helper::get_mautic_credentials();
		$data = array();
		$contact_id = $email_cid = '';

		if ( isset( $_COOKIE['mtc_id'] ) ) {

			// for anonymous contacts.
			$contact_id = $_COOKIE['mtc_id'];
			$contact_id = (int) $contact_id;
			$data['method'] = 'PATCH';
			$data['url'] = '/api/contacts/' . $contact_id . '/edit';

			// known contacts with existing email.
			$email_cid = self::mautic_get_contact_by_email( $email, $credentials );

			if ( isset( $email_cid ) && ! empty( $email_cid ) ) {

				$contact_id = (int) $email_cid;
				$data['method'] = 'POST';
				$data['url'] = '/api/contacts/new';
			}
		} else {
			$contact_id = self::mautic_get_contact_by_email( $email, $credentials );
			if ( isset( $contact_id ) && ! empty( $contact_id ) ) {

				$data['method'] = 'POST';
				$data['url'] = '/api/contacts/new';
			}
		}

		if ( empty( $contact_id ) ) {
			$data['method'] = 'POST';
			$data['url'] = '/api/contacts/new';
		}
		return $data;
	}

	/**
	 * Check if Mautic is configured
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public static function is_connected() {
		$credentials = APMautic_helper::get_mautic_credentials();

		if ( ! isset( $credentials['access_token'] ) ) {

			return false;
		}

		return true;
	}

	/** --
	 * Get an array of services data, all services will be returned.
	 *
	 * @since 1.0.4
	 * @return array An array of services and related data.
	 */
	static public function get_services_data()
	{
		$services = self::$services_data;
		
		return $services;
	}

	/**
	 * Get an instance of a service helper class.
	 *
	 * @since 1.0.4
	 * @param string $type The type of service.
	 * @return object
	 */
	static public function get_service_instance( $service )
	{
		$services = self::get_services_data();
		$data     = $services[ $service ];

		// Make sure the base class is loaded.
		if ( ! class_exists( 'APMauticService' ) ) {
			require_once AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-service.php';
		}

		// Make sure the service class is loaded.
		if ( ! class_exists( $data['class'] ) ) {
			require_once AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-service-' . $service . '.php';
		}

		return new $data['class']();
	}

	/**
	 * Save the API connection of a service and retrieve account settings markup.
	 *
	 * Called via the connect_service frontend AJAX action.
	 *
	 * @since 1.5.4
	 * @return array The response array.
	 */
	static public function connect_service()
	{
		$saved_services = FLBuilderModel::get_services();
		$post_data      = FLBuilderModel::get_post_data();
		$response       = array(
			'error'         => false,
			'html'          => ''
		);

		// Validate the service data.
		if ( ! isset( $post_data['service'] ) || empty( $post_data['service'] ) ) {
			$response['error'] = _x( 'Error: Missing service type.', 'Third party service such as MailChimp.', 'fl-builder' );
		}
		else if ( ! isset( $post_data['fields'] ) || 0 === count( $post_data['fields'] ) ) {
			$response['error'] = _x( 'Error: Missing service data.', 'Connection data such as an API key.', 'fl-builder' );
		}
		else if ( ! isset( $post_data['fields']['service_account'] ) || empty( $post_data['fields']['service_account'] ) ) {
			$response['error'] = _x( 'Error: Missing account name.', 'Account name for a third party service such as MailChimp.', 'fl-builder' );
		}

		// Get the service data.
		$service         = $post_data['service'];
		$service_account = $post_data['fields']['service_account'];

		// Does this account already exist?
		if ( isset( $saved_services[ $service ][ $service_account ] ) ) {
			$response['error'] = _x( 'Error: An account with that name already exists.', 'Account name for a third party service such as MailChimp.', 'fl-builder' );
		}

		// Try to connect to the service.
		if ( ! $response['error'] ) {

			$instance   = self::get_service_instance( $service );
			$connection = $instance->connect( $post_data['fields'] );

			if ( $connection['error'] ) {
				$response['error'] = $connection['error'];
			}
			else {

				FLBuilderModel::update_services(
					$service,
					$service_account,
					$connection['data']
				);

				$response['html'] = self::render_account_settings( $service, $service_account );
			}
		}

		// Return the response.
		return $response;
	}

	/**
	 * Render the connection settings or account settings for a service.
	 *
	 * Called via the render_service_settings frontend AJAX action.
	 *
	 * @since 1.5.4
	 * @return array The response array.
	 */
	static public function render_settings()
	{
		$is_connected		= APMautic_helper::is_service_connected();
		$service            = AP_MAUTIC_SERVICE;
		$response_fields 	= '';

		// Render the settings to connect a new account.
		if ( $is_connected ) {
			$response_fields = self::render_connect_settings( $service );
		}
		// Render the settings to select a connected account.
		else {
			$response_fields = self::render_account_settings( $service );
		}

		// Return the response.
		echo $response_fields;
	}

	/**
	 * Render the settings to connect to a new account.
	 *
	 * @since 1.5.4
	 * @return string The settings markup.
	 */
	static public function render_connect_settings( $service )
	{
		ob_start();
		
		$saved_services = APMautic_helper::get_service_data();

		$instance = self::get_service_instance( $service );
		echo $instance->render_connect_settings( $saved_services );

		return ob_get_clean();
	}

	/**
	 * Render the account settings for a saved connection.
	 *
	 * @since 1.5.4
	 * @param string $service The service id such as "mailchimp".
	 * @param string $active The name of the active account, if any.
	 * @return string The account settings markup.
	 */
	static public function render_account_settings( $service )
	{
		$saved_services = APMautic_helper::get_service_data();
		
		ob_start();
		
		$instance   = self::get_service_instance( $service );
		echo $instance->render_fields( $saved_services );

		return ob_get_clean();
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * Called via the render_service_fields frontend AJAX action.
	 *
	 * @since 1.5.4
	 * @return array The response array.
	 */
	static public function render_fields()
	{
		$post_data  = FLBuilderModel::get_post_data();
		$module     = FLBuilderModel::get_module( $post_data['node_id'] );
		$instance   = self::get_service_instance( $post_data['service'] );
		$response   = $instance->render_fields( $post_data['account'], $module->settings );

		return $response;
	}

	/**
	 * Delete a saved account from the database.
	 *
	 * Called via the delete_service_account frontend AJAX action.
	 *
	 * @since 1.5.4
	 * @return void
	 */
	static public function delete_account()
	{
		$post_data = FLBuilderModel::get_post_data();

		if ( ! isset( $post_data['service'] ) || ! isset( $post_data['account'] ) ) {
			return;
		}

		FLBuilderModel::delete_service_account( $post_data['service'], $post_data['account'] );
	}
}
