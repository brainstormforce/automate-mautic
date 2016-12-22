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
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new AP_Mautic_Api();
		}
		return self::$instance;
	}

		/** 
		 * Add contacts to Mautic, Add to segments, return GET request data
		 * 
		 * @since 1.0.0
		 */
		public static function bsfm_mautic_api_call( $url, $method, $param = array(), $segments = array() ) {

			$status = 'success';
			$credentials = get_option( 'bsfm_mautic_credentials' );
			if(!isset($credentials['expires_in'])) {
				return;
			}
			// if token expired, get new access token
			if( $credentials['expires_in'] < time() ) {
				$grant_type = 'refresh_token';
				$response = BSFMauticAdminSettings::bsf_mautic_get_access_token( $grant_type );
				if ( is_wp_error( $response ) ) {
					$errorMsg = $response->get_error_message();
					$status = 'error';
					echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
				} else {
					$access_details = json_decode( $response['body'] );
					$expiration = time() + $access_details->expires_in;
					$credentials['access_token'] = $access_details->access_token;
					$credentials['expires_in'] = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( 'bsfm_mautic_credentials', $credentials );
				}
			} // refresh code token ends
			// add contacts
			$credentials = get_option( 'bsfm_mautic_credentials' );
			$access_token = $credentials['access_token'];
			$param['access_token'] = $access_token;
			$url = $credentials['baseUrl'] . $url;
			if( $method=="GET" ) {
				$url = $url .'?access_token='. $access_token;
				$response = wp_remote_get( $url );
				if( is_array($response) ) {
					$response_body = $response['body'];
					$body_data = json_decode($response_body);
					return $body_data;
						$response_code = $response['response']['code'];
						if( $response_code != 201 ) {
							if( $response_code != 200 ) {
								$ret = false;
								$status = 'error';
								$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
								echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
								return;
							}
						}
				}
			}
			else if( $method=="POST" || $method=="PATCH" ) {	// add new contact to mautic request

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
				echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );

			} else {

				if( is_array($response) ) {
					$response_code = $response['response']['code'];

					if( $response_code == 200 || $response_code == 201 ) {

						$response_body = $response['body'];
						$contact_created = json_decode($response_body);
						$contact = $contact_created->contact;
						/**
						 * if contact is created add to segment here
						 */
						if( isset($contact->id) ) {
							$contact_id =  (int)$contact->id;
							// fetch segment_id from rule and add contact to segment
							$add_segment = $segments['add_segment'];
							if( is_array( $add_segment ) ) {
								foreach ( $add_segment as $segment_id) {
									$segment_id = (int)$segment_id;
									$action = "add";
									$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
								}
							}

							$remove_segment = $segments['remove_segment'];
							if( is_array( $remove_segment ) ) {
								foreach ( $remove_segment as $segment_id) {
									$segment_id = (int)$segment_id;
									$action = "remove";
									$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
								}
							}
							$status = $res['status'];
							$errorMsg  = $res['error_message'];
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
		 * Remove contacts from segment
		 * 
		 * @since 1.0.0
		 */
		public static function bsfm_remove_contact_from_segment( $param = array(), $set_actions = array() ) {
			//Remove contacts from segments
			$email = $param['email'];
			$remove_segment = $set_actions['remove_segment'];
			$add_segment = $set_actions['add_segment'];
			$credentials = get_option( 'bsfm_mautic_credentials' );

			$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );

			if( is_array( $remove_segment ) ) {
				$action = "remove";
				foreach ( $remove_segment as $segment_id ) {
					$segment_id = (int)$segment_id;
					if( isset( $contact_id ) ) {
						$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
						$status = $res['status'];
						$errorMsg  = $res['error_message'];
					}
				}
			}
			if( is_array( $add_segment ) ) {
				$action = "add";
				foreach ( $add_segment as $segment_id) {
					$segment_id = (int)$segment_id;
					if( isset( $contact_id ) ) {
						$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
					}
				}
			}
			return;
		}

		/** 
		 * Add contacts to segment
		 * 
		 * @since 1.0.0
		 */
		public static function bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act) {

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
		public static function bsfm_mautic_get_contact_by_email( $email, $mautic_credentials ) {
			$errorMsg = '';
			$status = 'error';
			$access_token = $mautic_credentials['access_token'];
			$url = $mautic_credentials['baseUrl'] . '/api/contacts/?search='. $email .'&access_token='. $access_token;
			$response = wp_remote_get( $url );

			if( !is_wp_error( $response ) && is_array($response) ) {
				$response_body = $response['body'];
				$body_data = json_decode($response_body);
				$contact = $body_data->contacts;
				$contact_id = $contact[0]->id;
				$response_code = $response['response']['code'];
				if( $response_code != 201 ) {
					if( $response_code != 200 ) {
						$ret = false;
						$status = 'error';
						$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
						echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
						return;
					}
				}
				return $contact_id;
			}
		}
}
$AP_Mautic_Api = AP_Mautic_Api::instance();
endif;