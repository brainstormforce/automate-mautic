<?php

/**
 * Helper class for the Mautic API.
 *
 * @since 1.0.5
 */
	class AP_MauticAPI {

	/**
	 * @since 1.5.4
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Update Mautic credentials
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_mautic_data() {
		$credentials = APMautic_Helper::get_mautic_credentials();
		// If not authorized.
		if ( ! isset( $credentials['access_token'] ) ) {
			if ( isset( $credentials['access_code'] ) ) {
				$grant_type = 'authorization_code';
				$response = self::mautic_get_access_token( $grant_type );

				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					echo __( 'There appears to be an error with the configuration.', 'automate-mautic' );
					$status   = 'error';
				} else {

					$response_body = wp_remote_retrieve_body( $response );
					$access_details               = json_decode( $response_body );
					// Check mautic errors array.
					if ( ! isset( $access_details->errors ) ) {
						$expiration                   = time() + $access_details->expires_in;
						$credentials['access_token']  = esc_attr( $access_details->access_token );
						$credentials['expires_in']    = esc_attr( $expiration );
						$credentials['refresh_token'] = esc_attr( $access_details->refresh_token );
						update_option( AP_MAUTIC_APIAUTH, $credentials );
					}
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
		$credentials = APMautic_Helper::get_mautic_credentials();

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
		$credentials = APMautic_Helper::get_mautic_credentials();

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
			echo __( 'There appears to be an error with the configuration.', 'automate-mautic' );

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
	 * Get Method and URL according to user email
	 *
	 * @since 1.0.0
	 * @param string $email user email.
	 * @return array $data
	 */
	public static function get_api_method_url( $email ) {

		$credentials = APMautic_Helper::get_mautic_credentials();
		$data = array();
		$contact_id = $email_cid = '';

		if ( isset( $_COOKIE['mtc_id'] ) ) {

			// for anonymous contacts.
			$contact_id = esc_attr( $_COOKIE['mtc_id'] );
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
	 * Check if contact is exist in mautic
	 *
	 * @since 1.0.0
	 * @param id $id contact ID.
	 * @return boolean
	 */
	public static function is_contact_published( $id ) {

		$mautic_credentials = APMautic_Helper::get_mautic_credentials();

		if ( $mautic_credentials['expires_in'] < time() ) {
			$grant_type = 'refresh_token';
			$response = self::mautic_get_access_token( $grant_type );
			if ( is_wp_error( $response ) ) {
				$error_msg = $response->get_error_message();
				$status = 'error';
				echo __( 'There appears to be an error with the configuration.', 'automate-mautic' );
			} else {

				$response_body = wp_remote_retrieve_body( $response );

				$access_details = json_decode( $response_body );
				// Check mautic errors array.
				if ( ! isset( $access_details->errors ) ) {
					$expiration = time() + $access_details->expires_in;
					$mautic_credentials['access_token'] = $access_details->access_token;
					$mautic_credentials['expires_in'] = $expiration;
					$mautic_credentials['refresh_token'] = $access_details->refresh_token;
				}
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
					__( 'There appears to be an error with the configuration.', 'automate-mautic' );
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
	 * Add/remove contacts to segment
	 *
	 * @since 1.0.5
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
	 * Call contact add or remove to segment
	 *
	 * @since 1.0.5
	 * @param int   $contact_id mautic contact ID.
	 * @param array $credentials mautic credentials.
	 * @param array $segments segments array.
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
	 * Get Mautic contact ID
	 *
	 * @since 1.0.0
	 * @param string $email contact email.
	 * @param array  $mautic_credentials mautic credentials.
	 * @return int
	 */
	public static function mautic_get_contact_by_email( $email, $mautic_credentials ) {
		if ( $mautic_credentials['expires_in'] < time() ) {
			$grant_type = 'refresh_token';
			$response = self::mautic_get_access_token( $grant_type );
			if ( is_wp_error( $response ) ) {
				$error_msg = $response->get_error_message();
				$status = 'error';
				echo __( 'There appears to be an error with the configuration.', 'automate-mautic' );
			} else {
				$response_body = wp_remote_retrieve_body( $response );
				$access_details = json_decode( $response_body );
				// Check mautic errors array.
				if ( ! isset( $access_details->errors ) ) {
					$expiration = time() + $access_details->expires_in;
					$mautic_credentials['access_token'] = $access_details->access_token;
					$mautic_credentials['expires_in'] = $expiration;
					$mautic_credentials['refresh_token'] = $access_details->refresh_token;
					update_option( AP_MAUTIC_APIAUTH, $mautic_credentials );
				}
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
					__( 'There appears to be an error with the configuration.', 'automate-mautic' );
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
	 * Get all segments
	 *
	 * @since 1.0.5
	 * @return array
	 */
	public static function get_all_segments() {
		$url = '/api/segments/';
		$body['limit'] = 100000;

		$segments = self::mautic_api_get_data( $url, $body );

		if ( ! APMautic_Services::is_connected() || isset( $segments->errors ) ) {
			return;
		}
		return $segments->lists;
	}

	/**
	 * Get all contact fields
	 *
	 * @since 1.0.5
	 * @return array
	 */
	public static function get_all_contact_fields() {
		// get all Mautic forms.
		$url = '/api/contacts/list/fields';
		$mautic_cfields = self::mautic_api_get_data( $url );

		if ( ! APMautic_Services::is_connected() || isset( $mautic_cfields->errors ) ) {

			return;
		}
		return $mautic_cfields;
	}

	/**
	 * Check refresh token
	 *
	 * @since 1.0.5
	 * @return void
	 */
	public static function generate_access_token() {

		$credentials = APMautic_Helper::get_mautic_credentials();
		if ( isset( $credentials['access_code'] ) && ! empty( $credentials['access_code'] )  ) {
			// if token expired, get new access token.
			if ( $credentials['expires_in'] < time() ) {
				$grant_type = 'refresh_token';
				$response = self::mautic_get_access_token( $grant_type );
				if ( is_wp_error( $response ) ) {
					$error_msg = $response->get_error_message();
					$status = 'error';
					echo __( 'There appears to be an error with the configuration.', 'automate-mautic' );
				} else {

					$response_body = wp_remote_retrieve_body( $response );
					$access_details = json_decode( $response_body );
					// Check mautic errors array.
					if ( ! isset( $access_details->errors ) ) {
						$expiration = time() + $access_details->expires_in;
						$credentials['access_token'] = $access_details->access_token;
						$credentials['expires_in'] = $expiration;
						$credentials['refresh_token'] = $access_details->refresh_token;
						update_option( AP_MAUTIC_APIAUTH, $credentials );
					}
				}
			} // refresh code token ends.
		}
	}

	/**
	 * GET api request data
	 *
	 * @since 1.0.0
	 * @param string $url api endpoint.
	 * @param array  $param parameters.
	 * @return array
	 */
	public static function mautic_api_get_data( $url, $param = array() ) {
		$status = 'success';
		// add contacts.
		self::generate_access_token();
		$credentials = APMautic_Helper::get_mautic_credentials();

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
					echo __( 'There appears to be an error with the configuration.', 'automate-mautic' );
					return;
				}
			}
			return $body_data;
		}
	}

	/**
	 * Remove contact from all segments
	 *
	 * @since 1.0.0
	 * @param string $email email to be removed.
	 * @return void
	 */
	public static function remove_from_all_segments( $email ) {

		$contact_id = self::get_mautic_contact_id( $email );
		if ( isset( $contact_id ) ) {
			// get all segments contact_id is member of.
			$url = '/api/contacts/' . $contact_id . '/segments';
			$method = 'GET';

			self::generate_access_token();
			// add contacts.
			$credentials = APMautic_Helper::get_mautic_credentials();

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

			$segments = array();
			if ( !is_wp_error( $response ) ) {

				if ( is_array( $response ) ) {

					$response_code = wp_remote_retrieve_response_code( $response );

					if ( 200 === $response_code || 201 === $response_code ) {

						$response_body = wp_remote_retrieve_body( $response );
						$segments = json_decode( $response_body );
						
					}
				}
			}
			
			if ( empty( $segments ) ) {
				return;
			}

			foreach ( $segments->lists as $list ) {

				$segment_id = $list->id;
				$segment_id = (int) $segment_id;
				$action = 'remove';
				self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action );
			}
		}
	}

   /**
	* Remove contact from all segments
	*
	* @since 1.0.0
	* @param string $email contact email.
	* @return int contact ID
	*/
	public static function get_mautic_contact_id( $email ) {

		$credentials = APMautic_Helper::get_mautic_credentials();

		if ( isset( $_COOKIE['mtc_id'] ) ) {

			$contact_id = esc_attr( $_COOKIE['mtc_id'] );

			$email_cid = self::mautic_get_contact_by_email( $email, $credentials );
			if ( isset( $email_cid ) ) {

				$contact_id = $email_cid;
			}
		} else {
			$contact_id = self::mautic_get_contact_by_email( $email, $credentials );

		}

		return $contact_id;
	}
}
