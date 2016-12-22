<?php
/**
 * Mautic for WordPress initiate
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'AutomatePlus_Mautic' ) ) :

	class AutomatePlus_Mautic {

		private static $instance;

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new AutomatePlus_Mautic();
				self::$instance->includes();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		public function includes() {
			require_once BSF_MAUTIC_PLUGIN_DIR . '/classes/class-apm-init.php';
			require_once BSF_MAUTIC_PLUGIN_DIR . '/classes/class-bsfm-postmeta.php';
		}
		public function hooks() {
			add_action( 'init', array( $this, 'mautic_register_posttype' ) );
			add_action( 'wp_head', array( $this, 'bsf_mautic_tracking_script' ) );
			add_action( 'user_register', array( $this, 'bsfm_add_registered_user' ), 10, 1 );
			add_action( 'comment_post', array( $this, 'bsfm_add_comment_author' ), 10, 3 );

			// add refresh links to footer
			add_filter( 'update_footer', array($this, 'bsfm_refresh_edit_text'),999);
			add_action( 'edd_purchase_form_user_info_fields', array( $this, 'mautic_edd_display_checkout_fields' ) );
		}

		public function bsfm_refresh_edit_text( $footer_text ) {

			$bsfm_screen = get_current_screen();
			if ( $bsfm_screen->id == 'settings_page_bsf-mautic' ) {
				$refresh_text = __( '<a type="button" name="refresh-mautic" id="refresh-mautic" class="refresh-mautic-data"> Refresh Mautic Data</a>');
				$text = $refresh_text.' | '.$footer_text;
				return $text;
			} else {
				return $footer_text;
			}
		}

		/**
		 * Register a bsf-mautic-rule post type.
		 * @since 1.0.0
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		public function mautic_register_posttype() {
			$labels = array(
				'name'               => _x( 'Rules', 'post type general name', 'bsfmautic' ),
				'singular_name'      => _x( 'Rule', 'post type singular name', 'bsfmautic' ),
				'menu_name'          => _x( 'Rules', 'admin menu', 'bsfmautic' ),
				'name_admin_bar'     => _x( 'Rule', 'add new on admin bar', 'bsfmautic' ),
				'add_new'            => _x( 'Add New', 'rule', 'bsfmautic' ),
				'add_new_item'       => __( 'Add New Rule', 'bsfmautic' ),
				'new_item'           => __( 'New Rule', 'bsfmautic' ),
				'edit_item'          => __( 'Edit Rule', 'bsfmautic' ),
				'view_item'          => __( 'View Rule', 'bsfmautic' ),
				'all_items'          => __( 'All Rules', 'bsfmautic' ),
				'search_items'       => __( 'Search Rules', 'bsfmautic' ),
				'parent_item_colon'  => __( 'Parent Rules:', 'bsfmautic' ),
				'not_found'          => __( 'No rules found.', 'bsfmautic' ),
				'not_found_in_trash' => __( 'No rules found in Trash.', 'bsfmautic' )
			);
			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'bsfmautic' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => 'options-general.php',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'bsf-mautic-rule' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'			 => 'dashicons-chart-line',
				'supports'           => array( 'title' )
			);
			register_post_type( 'bsf-mautic-rule', $args );
		}

		/** 
		 * Writes Mautic Tracking JS to the HTML source of WP head
		 *
		 * @since 1.0.0
		 */
		public function bsf_mautic_tracking_script()
		{
			$bsfm_options = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$enable_mautic_tracking	= false;
			if ( !empty( $bsfm_options ) && array_key_exists( 'bsfm-enabled-tracking', $bsfm_options ) ) {
				if( $bsfm_options['bsfm-enabled-tracking'] == 1 ) {
					$enable_mautic_tracking = true;
				} else {
					$enable_mautic_tracking = false;
				}
			}
			if ( $enable_mautic_tracking ) {
				$base_url = trim($bsfm_options['bsfm-base-url'], " \t\n\r\0\x0B/");
				$bsfm_trackingJS = "<script>
				(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
				w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
				m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
				})(window,document,'script','{$base_url}/mtc.js','mt');
				mt('send', 'pageview');
				</script>";
				echo $bsfm_trackingJS;
			}
		}
		
		/** 
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function bsfm_add_registered_user( $user_id ) {
			if( !$user_id ) return;
			//get user registerd condition rules
			$status = Bsfm_Postmeta::bsfm_get_wpur_condition();
			if( is_array($status) && sizeof($status)>0 ) {
				$set_actions = Bsfm_Postmeta::bsfm_get_all_actions($status);
			}
			else {
				return;
			}

			$user_info = get_userdata( $user_id );
			$email = $user_info->user_email;
			$credentials = get_option( 'bsfm_mautic_credentials' );

			if( isset($_COOKIE['mtc_id']) ) {
				$contact_id = $_COOKIE['mtc_id'];
				$contact_id = (int)$contact_id;
				
				$email_cid = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
				if( isset( $email_cid ) ) {
					$contact_id = (int)$email_cid;
				}
			}
			else {
				$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
			}
			
			$body = array(
				'firstname'	=> $user_info->first_name,
				'lastname'	=> $user_info->last_name,
				'email'		=> $user_info->user_email,
				'website'	=> $user_info->user_url
			);

			if( isset($contact_id) ) {
				$method = 'PATCH';
				$url = '/api/contacts/'.$contact_id.'/edit';
			}
			else {
				$method = 'POST';
				$url = '/api/contacts/new';
			}

			$add_segment = $set_actions['add_segment'];
			$remove_segment = $set_actions['remove_segment'];
			if( is_array( $set_actions ) && ( sizeof( $add_segment )>0 || sizeof( $remove_segment )>0 ) ) {
				self::bsfm_mautic_api_call($url, $method, $body, $set_actions);
			}
		}

		/** 
		 * Add comments author to Mautic contacts
		 *
		 * @since 1.0.0
		 * @return void
		 */
		//public function bsfm_add_comment_author( $new_status, $old_status, $commentdata ) {
		// if( 'approved' != $new_status ) {
		//  	return;
		// }
		// $commentdata =  (array) $commentdata;
		// -- end approved comment

		public function bsfm_add_comment_author( $id, $approved, $commentdata ) {
			//get comment post condition rules
			$status = Bsfm_Postmeta::bsfm_get_comment_condition( $commentdata );
			if( is_array($status) && sizeof($status)>0 ) {
				$set_actions = Bsfm_Postmeta::bsfm_get_all_actions($status);
			}
			else {
				return;
			}

			$email = $commentdata['comment_author_email'];
			$credentials = get_option( 'bsfm_mautic_credentials' );
			if( isset($_COOKIE['mtc_id']) ) {
				$contact_id = $_COOKIE['mtc_id'];
				$contact_id = (int)$contact_id;

				$email_cid = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
				if( isset( $email_cid ) ) {
					$contact_id = (int)$email_cid;
				}
			}
			else {
				$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
			}
			$body = array(
				'firstname'	=>	$commentdata['comment_author'],
				'email'		=>	$commentdata['comment_author_email'],
				'website'	=>	$commentdata['comment_author_url']
			);

			if( isset($contact_id) ) {
				$method = 'PATCH';
				$url = '/api/contacts/'.$contact_id.'/edit';
			}
			else {
				$method = 'POST';
				$url = '/api/contacts/new';
			}

			$add_segment = $set_actions['add_segment'];
			$remove_segment = $set_actions['remove_segment'];
			if( is_array( $set_actions ) && ( sizeof( $add_segment )>0 || sizeof( $remove_segment )>0 ) ) {
				self::bsfm_mautic_api_call( $url, $method, $body, $set_actions );
			}
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
		static function bsfm_remove_contact_from_segment( $param = array(), $set_actions = array() ) {
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
		static function bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act) {

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
		static function bsfm_mautic_get_contact_by_email( $email, $mautic_credentials ) {
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
endif;