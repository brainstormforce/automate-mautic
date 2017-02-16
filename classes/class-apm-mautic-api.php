<?php
/**
 * Handles API operations
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'AP_Mautic_Api' ) ) :

	/**
	 * Create class AP_Mautic_Api
	 * Handles API operations
	 */
	class AP_Mautic_Api {

		/**
		 * Declare a static variable instance.
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiate class
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new AP_Mautic_Api();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {

			add_action( 'admin_init', array( $this, 'set_mautic_code' ) );

		}

		/**
		 * Save mautic code.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function set_mautic_code() {
			if ( isset( $_GET['code'] ) && 'automate-mautic' == $_REQUEST['page'] ) {
				$credentials = AMPW_Mautic_Init::get_mautic_credentials();
				$credentials['access_code'] = sanitize_key( $_GET['code'] );
				update_option( 'ampw_mautic_credentials', $credentials );
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
			$credentials = AMPW_Mautic_Init::get_mautic_credentials();
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
						update_option( 'ampw_mautic_credentials', $credentials );
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
			$credentials = AMPW_Mautic_Init::get_mautic_credentials();

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
			$credentials = AMPW_Mautic_Init::get_mautic_credentials();

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
						update_option( 'ampw_mautic_credentials', $credentials );
					}
				} // refresh code token ends.
			}

			// add contacts.
			$credentials = AMPW_Mautic_Init::get_mautic_credentials();

			$access_token = $credentials['access_token'];
			$param['access_token'] = $access_token;
			$url = $credentials['baseUrl'] . $url;
			if ( 'GET' == $method ) {

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
			} elseif ( 'POST' == $method || 'PATCH' == $method ) {	// add new contact to mautic request.

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

			}
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
							// add contact to segment.
							$add_segment = $segments['add_segment'];
							if ( is_array( $add_segment ) ) {
								foreach ( $add_segment as $segment_id ) {
									$segment_id = (int) $segment_id;
									$action = 'add';
									$res = self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action );
								}
							}

							// remove contact from segment.
							$remove_segment = $segments['remove_segment'];
							if ( is_array( $remove_segment ) ) {
								foreach ( $remove_segment as $segment_id ) {
									$segment_id = (int) $segment_id;
									$action = 'remove';
									$res = self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action );
								}
							}

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
		 * Add contacts to segment
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
					update_option( 'ampw_mautic_credentials', $mautic_credentials );
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

			$mautic_credentials = AMPW_Mautic_Init::get_mautic_credentials();

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
					update_option( 'ampw_mautic_credentials', $mautic_credentials );
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
			'callback'		=> APM_AdminSettings::get_render_page_url( '&tab=auth_mautic' ),
			'response_type'	=> 'code',
			);

			update_option( 'ampw_mautic_credentials', $settings );
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

			$credentials = AMPW_Mautic_Init::get_mautic_credentials();
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
			$credentials = AMPW_Mautic_Init::get_mautic_credentials();

			if ( ! isset( $credentials['access_token'] ) ) {

				return false;
			}

			return true;
		}
	}
	$apm_mautic_api = AP_Mautic_Api::instance();
endif;
