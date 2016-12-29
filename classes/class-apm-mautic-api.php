<?php
/**
 * Rules Post Meta
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'AP_Mautic_Api' ) ) :
	
	class AP_Mautic_Api {

	private static $instance;

	/**
	* Initiator
	*/
	public static function instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new AP_Mautic_Api();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() {
		add_action( 'admin_init', array( $this,'set_mautic_code' ) );
	}

	/**
	 * Save the mautic code.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function set_mautic_code() 
	{
		if( isset( $_GET['code'] ) && 'bsf-mautic' == $_REQUEST['page'] ) {
			$credentials =  AMPW_Mautic_Init::get_mautic_credentials();
			$credentials['access_code'] = sanitize_key( $_GET['code'] );
			update_option( 'ampw_mautic_credentials', $credentials );
			self::get_mautic_data();
		}
	}

	/** 
	 * Get Mautic Data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function get_mautic_data() 
	{
		$credentials =  AMPW_Mautic_Init::get_mautic_credentials();
		// If not authorized 
		if( ! isset( $credentials['access_token'] ) ) {
			if( isset( $credentials['access_code']  ) ) {
				$grant_type = 'authorization_code';
				$response = self::mautic_get_access_token( $grant_type );

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
					update_option( 'ampw_mautic_credentials', $credentials );
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
	public static function mautic_get_access_token($grant_type) 
	{
		$credentials =  AMPW_Mautic_Init::get_mautic_credentials();

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
	 * Add contacts to Mautic, Add to segments, return GET request data
	 * 
	 * @since 1.0.0
	 */
	public static function ampw_mautic_api_call( $url, $method, $param = array(), $segments = array() ) 
	{
		$status = 'success';
		$credentials =  AMPW_Mautic_Init::get_mautic_credentials();
		if( isset( $credentials['access_code'] ) && ! empty ( $credentials['access_code'] )  ) {
			// if token expired, get new access token
			if( $credentials['expires_in'] < time() ) {
				$grant_type = 'refresh_token';
				$response = self::mautic_get_access_token( $grant_type );
				if ( is_wp_error( $response ) ) {
					$errorMsg = $response->get_error_message();
					$status = 'error';
					echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
				} else {
					$access_details = json_decode( $response['body'] );
					$expiration = time() + $access_details->expires_in;
					$credentials['access_token'] = $access_details->access_token;
					$credentials['expires_in'] = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( 'ampw_mautic_credentials', $credentials );
				}
			} // refresh code token ends
		}
		
		// add contacts
		$credentials =  AMPW_Mautic_Init::get_mautic_credentials();
		$access_token = $credentials['access_token'];
		$param['access_token'] = $access_token;
		$url = $credentials['baseUrl'] . $url;
		if( $method == "GET" ) {
			$url = $url .'?access_token='. $access_token;
			$response = wp_remote_get( $url );
			if( is_array($response) ) {
				$response_body = $response['body'];
				$body_data = json_decode( $response_body );
					$response_code = $response['response']['code'];
					if( $response_code != 201 ) {
						if( $response_code != 200 ) {
							$ret = false;
							$status = 'error';
							$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
							echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
							return;
						}
					}
				return $body_data;
			}
		}
		else if( $method == "POST" || $method == "PATCH" ) {	// add new contact to mautic request

			$param['ipAddress'] = $_SERVER['REMOTE_ADDR'];
			$response = wp_remote_post( $url, array(
				'method' => $method,
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $param,
				'cookies' => array()
			));
		}
		if ( is_wp_error( $response ) ) {
			$errorMsg = $response->get_error_message();
			$status = 'error';
			echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );

		} else {

			if( is_array( $response ) ) {
				
				$response_code = $response['response']['code'];

				if( $response_code == 200 || $response_code == 201 ) {

					$response_body = $response['body'];
					$contact_created = json_decode($response_body);
					$contact = $contact_created->contact;
					/**
					 * if contact is created add to segment here
					 */
					if( isset( $contact->id ) ) {
						$contact_id =  (int)$contact->id;
						// add contact to segment
						$add_segment = $segments['add_segment'];
						if( is_array( $add_segment ) ) {
							foreach ( $add_segment as $segment_id) {
								$segment_id = (int)$segment_id;
								$action = "add";
								$res = self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
							}
						}

						// remove contact from segment
						$remove_segment = $segments['remove_segment'];
						if( is_array( $remove_segment ) ) {
							foreach ( $remove_segment as $segment_id) {
								$segment_id = (int)$segment_id;
								$action = "remove";
								$res = self::mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
							}
						}

						$status = $res['status'];
						$errorMsg = $res['error_message'];
					}
					
				} else {
						$ret = false;
						$status = 'error';
						$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
				}
			}
		}
	}

	/** 
	 * Add contacts to segment
	 * 
	 * @since 1.0.0
	 */
	public static function mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act ) 
	{
		$errorMsg = '';
		$status = 'error';
		if( is_int($segment_id) && is_int($contact_id) ) {
			$url = $mautic_credentials['baseUrl'] . "/api/segments/".$segment_id."/contact/".$act."/".$contact_id;
			$access_token = $mautic_credentials['access_token'];
			$body = array(
				"access_token" => $access_token
			);
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
			if ( is_wp_error( $response ) ) {
				$errorMsg = $response->get_error_message();
				$status = 'error';
			} else {
				if( is_array($response) ) { 							
					$response_code = $response['response']['code'];
					if( $response_code != 200 ) {
						$status = 'error';
						$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
					} else {
						$status = 'success';
					}
				}
			}
		}
		$response = array(
			'status' => $status,
			'error_message' => $errorMsg            
		);
		return $response;
	}

	/** 
	 * Get Mautic contact ID
	 * @return mautic contact id 
	 * @since 1.0.0
	 */
	public static function mautic_get_contact_by_email( $email, $mautic_credentials ) 
	{
		if( $mautic_credentials['expires_in'] < time() ) { 
			$grant_type = 'refresh_token';
			$response = self::mautic_get_access_token( $grant_type );
			if ( is_wp_error( $response ) ) {
				$errorMsg = $response->get_error_message();
				$status = 'error';
				echo __( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
			} else {
				$access_details = json_decode( $response['body'] );
				$expiration = time() + $access_details->expires_in;
				$mautic_credentials['access_token'] = $access_details->access_token;
				$mautic_credentials['expires_in'] = $expiration;
				$mautic_credentials['refresh_token'] = $access_details->refresh_token;
				update_option( 'ampw_mautic_credentials', $mautic_credentials );
			}
		}

		$errorMsg = $contact_id = '';
		$access_token = $mautic_credentials['access_token'];
		$access_token = esc_attr($access_token);
		$url = $mautic_credentials['baseUrl'] . '/api/contacts/?search='. $email .'&access_token='. $access_token;

		$response = wp_remote_get( $url );

		if( ! is_wp_error( $response ) && is_array( $response ) ) {
			$response_body = $response['body'];
			$body_data = json_decode($response_body);

			$contact = $body_data->contacts;

			if( is_array($contact) && sizeof($contact)>0 ) {
				$contact_id = $contact[0]->id;
			}
			$response_code = $response['response']['code'];
			if( $response_code != 201 ) {
				if( $response_code != 200 ) {
					$ret = false;
					$status = 'error';
					$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
					__( 'There appears to be an error with the configuration.', 'automateplus-mautic-wp' );
					return;
				}
			}
			if ( $contact_id == 0) {
				return;
			}
		}
		return $contact_id;
	}
	
	public static function authenticate_update()
	{
		$bsfm = AMPW_Mautic_Init::get_amp_options();
		$mautic_api_url = $bsfm_public_key = $bsfm_secret_key = "";
		$post = $_POST;
		$cpts_err = false;
		$lists = null;
		$ref_list_id = null;

		$mautic_api_url = isset( $post['bsfm-base-url'] ) ? esc_url( $post['bsfm-base-url'] ) : '';
		$bsfm_public_key = isset( $post['bsfm-public-key'] ) ? sanitize_key( $post['bsfm-public-key'] ) : '';
		$bsfm_secret_key = isset( $post['bsfm-secret-key'] ) ? sanitize_key( $post['bsfm-secret-key'] ) : '';

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
			'callback'		=> APM_AdminSettings::get_render_page_url( "&tab=auth_mautic" ),
			'response_type'	=> 'code'
		);

		update_option( 'ampw_mautic_credentials', $settings );
		$authurl = $settings['baseUrl'] . '/oauth/v2/authorize';
		//OAuth 2.0
		$authurl .= '?client_id='.$settings['clientKey'].'&redirect_uri='.urlencode( $settings['callback'] );
		$state    = md5(time().mt_rand());
		$authurl .= '&state='.$state;
		$authurl .= '&response_type='.$settings['response_type'];
		wp_redirect( $authurl );
		exit;
	}

	public static function get_api_method_url( $email )
	{
		$credentials =  AMPW_Mautic_Init::get_mautic_credentials();
		$data = array();
		if( isset($_COOKIE['mtc_id']) ) {
			$contact_id = $_COOKIE['mtc_id'];
			$contact_id = (int)$contact_id;

			$email_cid = self::mautic_get_contact_by_email( $email, $credentials );
			if( isset( $email_cid ) ) {
				$contact_id = (int)$email_cid;
			}
		}
		else {
			$contact_id = self::mautic_get_contact_by_email( $email, $credentials );
		}

		if( isset($contact_id) ) {
			$data['method'] = 'PATCH';
			$data['url'] = '/api/contacts/'.$contact_id.'/edit';
		}
		else {
			$data['method'] = 'POST';
			$data['url'] = '/api/contacts/new';
		}
		return $data;
	}

}
$AP_Mautic_Api = AP_Mautic_Api::instance();
endif;