<?php

/**
 * Helper class for connecting to third party services.
 *
 * @since 0.0.1
 */
final class APMauticServices {

	/**
	 * Data for working with each supported third party service.
	 *
	 * @since 0.0.1
	 * @access private
	 * @var array $services_data
	 */
	static private $services_data = array(
		'mautic'    => array(
			'type'              => 'autoresponder',
			'name'              => 'Mautic',
			'class'             => 'AutomatePlugServiceMautic',
			'url'				=> '',
		),
	);

	/**
	 * Get an array of default custom fields for any mailer
	 *
	 * @since 0.0.1
	 * @param string $mailer Slug of any mailer service
	 * @return array An array of default custom fields.
	 */
	static public function get_mapping_fields( $service ) {

		$instance = self::get_service_instance( $service );
		return $instance->render_mapping();
	}

	/**
	 * Get an array of services data of a certain type such as "autoresponder".
	 * If no type is specified, all services will be returned.
	 *
	 * @since 0.0.1
	 * @param string $type The type of service data to return.
	 * @return array An array of services and related data.
	 */
	static public function get_services_data( $type = null ) {
		$services = array();

		// Return all services.
		if ( ! $type ) {
			$services = self::$services_data;
		} // Return services of a specific type.
		else {

			foreach ( self::$services_data as $key => $service ) {
				if ( $service['type'] == $type ) {
					$services[ $key ] = $service;
				}
			}
		}

		return $services;
	}

	/**
	 * Get an instance of a service helper class.
	 *
	 * @since 0.0.1
	 * @param string $type The type of service.
	 * @return object
	 */
	static public function get_service_instance( $service ) {
		$services = self::get_services_data();

		//get static service name

		$data     = $services[ $service ];

		// Make sure the base class is loaded.
		if ( ! class_exists( 'ConvertPlugService' ) ) {
			require_once 'cp-service-class.php';
		}

		// Make sure the service class is loaded.
		if ( ! class_exists( $data['class'] ) ) {
			require_once CP_SERVICES_BASE_DIR . 'services/' . $service . '/cp-service-' . $service . '.php';
		}
		return new $data['class']();
	}

	/**
	 * Get scripts
	 *
	 * @since 0.0.1
	 * @param string $type The type of service.
	 * @return object
	 */
	static public function get_assets_data() {
		$assets = '';
		$errorResponse = array(
							'error'		=> true,
							'assets' 	=> $assets,
						);

		if ( ! isset( $_POST['service'] ) ) {
			return $errorResponse;
		}

		$service = $_POST['service'];
		$service_dir = CP_SERVICES_BASE_DIR . 'services/' . $service . '/';
		$service_url = CP_SERVICES_BASE_URL . 'services/' . $service . '/';

		if ( file_exists( $service_dir . $service . '.js' ) ) {
			$assets .= '<script class="cp-mailer-' . $service . '-js" src="' . $service_url . $service . '.js"></script>';
		}

		if ( file_exists( $service_dir . $service . '.css' ) ) {
			$assets .= '<link class="cp-mailer-' . $service . '-css" rel="stylesheet" href="' . $service_url . $service . '.css"></link>';
		}

		if ( $assets != '' ) {

			// Return assets.
			return array(
				'error'		=> false,
				'assets' 	=> $assets,
			);
		}

		return $errorResponse;
	}

	/**
	 * Save the API connection of a service and retrieve account settings markup.
	 *
	 * Called via the connect_service frontend AJAX action.
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function connect_service() {
		$saved_services = ConvertPlugHelper::get_saved_services();
		$post_data      = ConvertPlugHelper::get_post_data();
		$response       = array(
			'error'         => false,
			'html'          => '',
		);

		// Validate the service data.
		if ( ! isset( $post_data['service'] ) || empty( $post_data['service'] ) ) {
			$response['error'] = _x( 'Error: Missing service type.', 'Third party service such as MailChimp.', 'cp-v2-connects' );
		} elseif ( ! isset( $post_data['fields'] ) || 0 === count( $post_data['fields'] ) ) {
			$response['error'] = _x( 'Error: Missing service data.', 'Connection data such as an API key.', 'cp-v2-connects' );
		} elseif ( ! isset( $post_data['fields']['service_account'] ) || empty( $post_data['fields']['service_account'] ) ) {
			$response['error'] = _x( 'Error: Missing account name.', 'Account name for a third party service such as MailChimp.', 'cp-v2-connects' );
		}

		// Get the service data.
		$service         = $post_data['service'];
		$service_account = $post_data['fields']['service_account'];

		// Does this account already exist?
		if ( in_array( $service_account, $saved_services ) ) {
			$response['error'] = _x( 'Hey, looks like you already have an account with the same name. Please use another Account Name.', 'Account name for a third party service such as MailChimp.', 'cp-v2-connects' );
		}

		// Try to connect to the service.
		if ( ! $response['error'] ) {

			$instance   = self::get_service_instance( $service );
			$connection = $instance->connect( $post_data['fields'] );

			if ( $connection['error'] ) {
				$response['error'] = $connection['error'];
			}
		}

		// Return the response.
		return $response;
	}

	/**
	 * Save the connection settings or account settings for a service.
	 *
	 * Called via the save_service_settings frontend AJAX action.
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function save_settings() {
		$post_data          = ConvertPlugHelper::get_post_data();
		$serviceData    = $post_data['serviceData'];
		$account 		= $serviceData['service_account'];
		$service 		= $post_data['service'];

		$response           = array(
			'error'             => false,
			'html'              => '',
		);

		if ( $account != '' && $service != '' ) {

			$term = wp_insert_term( $account, CP_CONNECTION_TAXONOMY );

			if ( ! is_wp_error( $term ) ) {

				$newterm = update_term_meta( $term['term_id'], CP_API_CONNECTION_SERVICE, $service );

				$instance = self::get_service_instance( $service );

				$authMeta = $instance->render_auth_meta( $serviceData );

				update_term_meta( $term['term_id'], CP_API_CONNECTION_SERVICE_AUTH, $authMeta );
				$t = get_term( $term['term_id'], CP_CONNECTION_TAXONOMY );
				$response['term_id'] = $t->slug;

			} else {
				$response           = array(
					'error'             => $term->get_error_message(),
					'html'              => '',
					'term_id'			=> -1,
				);
			}
		} else {
			$response           = array(
				'error'             => __( 'Account Name should not be blank', 'cp-v2-connects' ),
				'html'              => '',
				'term_id'			=> -1,
			);
		}

		// Return the response.
		return $response;
	}

	/**
	 * Render the connection settings or account settings for a service.
	 *
	 * Called via the render_service_settings frontend AJAX action.
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function render_settings() {
		$post_data          = ConvertPlugHelper::get_post_data();

		$service            = $post_data['service'];
		$response           = array(
			'error'             => false,
			'html'              => '',
		);

		// Render the settings to connect a new account.
		$response['html']  = '<div class="cp-api-fields cp-new_account-wrap">';
		$response['html'] .= '<input type="text" name="service_account" id="cp_new_account_name" />';
		$response['html'] .= '<label for="cp_new_account_name">' . __( 'Account Name', 'cp-v2-connects' ) . '</label>';
		$response['html'] .= '</div>';
		$response['html'] .= self::render_connect_settings( $service );
		// Return the response.
		return $response;
	}

	static public function render_service_accounts() {

		$terms 			= get_terms( CP_CONNECTION_TAXONOMY, array( 'hide_empty' => false ) );

		$returnArray 	= array();
		$post_data 		= ConvertPlugHelper::get_post_data();

		$url = ( isset( $post_data['service'] ) ) ? ConvertPlugServices::$services_data[$post_data['service']]['url'] : '';

		$response           = array(
			'error'             => false,
			'html'              => '',
			'account_count'		=> 0,
			'url'				=> $url
		);
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $key => $term ) {
				if ( isset( $term->term_id ) ) {
					if ( get_term_meta( $term->term_id, CP_API_CONNECTION_SERVICE, true ) == $post_data['service'] ) {
						$returnArray[ $term->slug ] = $term->name;

						$args = array(
						    'tax_query' => array(
						        array(
						            'taxonomy' => CP_CONNECTION_TAXONOMY,
						            'field' => 'slug',
						            'terms' => $term->slug
						        ),
						    ),
						    'post_type' => CP_CUSTOM_POST_TYPE
						);
						$query = new WP_Query( $args );
						$associativeArray[ $term->slug ] = ( isset( $query->post_count ) ) ? $query->post_count : 0;
					}
				} else {
					$response['error'] = __( 'You have not added a account yet. Please add a new account.', 'cp-v2-connects' );
				}
			}

			if ( ! empty( $returnArray ) ) {
				ob_start();

				ConvertPlugHelper::render_input_html( 'service_accounts', array(
					'class'         => '',
					'type'          => 'radio',
					'label'         => __( 'Select Account', 'cp-v2-connects' ),
					'help'          => '',
					'default'		=> ( isset( $post_data['selected'] ) ) ? $post_data['selected'] : '',
					'options'		=> $returnArray,
					'association'	=> $associativeArray
				));

				$response['html'] = ob_get_clean();
				$response['account_count'] = count( $associativeArray );
			} else {
				$response['error'] = __( 'You have not added a account yet. Please add a new account.', 'cp-v2-connects' );
			}
		} else {
			$response['error'] = true;
			$response['html']  = __( 'You have not added a account yet. Please add a new account.', 'cp-v2-connects' );
		}

		return $response;
	}

	/**
	 * Render the settings to connect to a new account.
	 *
	 * @since 0.0.1
	 * @return string The settings markup.
	 */
	static public function render_connect_settings( $service ) {
		ob_start();

		$instance = self::get_service_instance( $service );

		echo $instance->render_connect_settings();

		return ob_get_clean();
	}

	/**
	 * Render the account settings for a saved connection.
	 *
	 * @since 0.0.1
	 * @param string $service The service id such as "mailchimp".
	 * @param string $active The name of the active account, if any.
	 * @return string The account settings markup.
	 */
	static public function render_account_settings( $service, $active = '' ) {
		ob_start();

		$saved_services             = ConvertPlugHelper::get_services();
		$settings                   = new stdClass();
		$settings->service_account  = $active;
		$options                    = array( '' => __( 'Choose...', 'cp-v2-connects' ) );

		// Build the account select options.
		foreach ( $saved_services[ $service ] as $account => $data ) {
			$options[ $account ] = $account;
		}

		$options['add_new_account'] = __( 'Add Account...', 'cp-v2-connects' );

		// Render the account select.
		ConvertPlugHelper::render_settings_field( 'service_account', array(
			'row_class'     => 'cp-v2-connects-service-account-row',
			'class'         => 'cp-v2-connects-service-account-select',
			'type'          => 'select',
			'label'         => __( 'Account', 'cp-v2-connects' ),
			'options'       => $options,
			'preview'       => array(
				'type'          => 'none',
			),
		), $settings);

		// Render additional service fields if we have a saved account.
		if ( ! empty( $active ) && isset( $saved_services[ $service ][ $active ] ) ) {

			$post_data  = ConvertPlugHelper::get_post_data();
			$module     = ConvertPlugHelper::get_module( $post_data['node_id'] );
			$instance   = self::get_service_instance( $service );
			$response   = $instance->render_fields( $active, $module->settings );

			if ( ! $response['error'] ) {
				echo $response['html'];
			}
		}

		return ob_get_clean();
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * Called via the render_service_fields frontend AJAX action.
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function render_fields() {

		$post_data  = ConvertPlugHelper::get_post_data();

		if( $post_data['isEdit'] ) {

			if( $post_data['noMapping'] == 'true' ) {
				$src = $post_data['src'];
				$opt = get_option( '_cp_v2_' . $src . '_form' );
				$cp_connect_settings = ( $opt['cp_connection_values'] != '' ) ? ConvertPlugHelper::get_decoded_array( stripslashes( $opt['cp_connection_values'] ) ) : array();
			} else {
				$post_id = ( isset( $post_data['style_id'] ) ) ? $post_data['style_id'] : 0;

				$meta = get_post_meta( $post_id, 'connect' );

				$meta = ( ! empty( $meta ) ) ? call_user_func_array( 'array_merge', call_user_func_array( 'array_merge', $meta ) ) : array();

				if( ! empty( $meta ) ) {

					$cp_connect_settings = ( $meta['cp_connect_settings'] != -1 ) ? ConvertPlugHelper::get_decoded_array( $meta['cp_connect_settings'] ) : array();
				}

			}
			if( ! empty( $cp_connect_settings ) ) {
				$service = $cp_connect_settings["cp-integration-service"];

				$account_name = $cp_connect_settings['cp-integration-account-slug'];

				if( $account_name == $post_data['account'] ) {

					unset( $cp_connect_settings['cp-integration-service'] );
					unset( $cp_connect_settings['cp-integration-account-slug'] );

					$post_data['default'] = $cp_connect_settings;
				}
			}			
		}

		$account = $post_data['account'];
		$response = '';
		$connection_data  = ConvertPlugHelper::get_connection_data( $account );
		
		if( isset($connection_data[ CP_API_CONNECTION_SERVICE ][0]) ) {

			$instance   = self::get_service_instance( $connection_data[ CP_API_CONNECTION_SERVICE ][0] );
			$response   = $instance->render_fields( $connection_data, $post_data );
		}
		else {
			$account = apply_filters( 'cp_static_account_service', $account );
			$instance   = self::get_service_instance( $account );
			$response   = $instance->render_fields( $account, $post_data );	
		}
		return $response;
	}

	/**
	 * Delete a saved account from the database.
	 *
	 * Called via the delete_service_account frontend AJAX action.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	static public function delete_account() {
		$post_data = ConvertPlugHelper::get_post_data();
		
		if ( ! isset( $post_data['account'] ) ) {
			return;
		}
		$response = array( 
			'error'		=> true
		);
		$result = ConvertPlugHelper::delete_service_account( $post_data['account'] );

		if( ! is_wp_error( $result ) ) {
			$response['error'] = false;
		} else {
			$response['error'] = $result->get_error_message();
		}
		return $response;
	}

	/**
	 * Renders the authentication details for the service.
	 *
	 * @since 0.0.1
	 * @param array $account Account details.
	 * @return array The connection settings markup.
	 */
	public static function get_account_data( $account ) {

		if( isset($account[ CP_API_CONNECTION_SERVICE_AUTH ][0]) ) {		
			return unserialize( $account[ CP_API_CONNECTION_SERVICE_AUTH ][0] );
		}
		return true;
	}

	/**
	 * Subscribe to specific lists and group
	 *
	 * Called via the cp_add_subscriber frontend AJAX action.
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function add_subscriber() {

		$post_data  = ConvertPlugHelper::get_post_data();
		$response     = array( 'error' => false, 'style_slug' => '' );
		$settings = $post_data;
		$metaMapping = array();
		$email_status = true;

		$style_id = $post_data['style_id'];

		$meta = call_user_func_array( 'array_merge', call_user_func_array( 'array_merge', get_post_meta( $style_id, 'connect' ) ) );

		$post = get_post( $style_id );

		foreach ( $meta as $key => $m ) {
			$meta[ $key ] = json_decode( $m );
		}

		$mailer = $mailer_name = '';
		
		if ( is_array( $meta['cp_connect_settings'] ) ) {
			foreach ( $meta['cp_connect_settings'] as $key => $t ) {
				if ( $t->name == 'cp-integration-account-slug' ) {
					$mailer_name = $t->value;
					$mailer = ConvertPlugHelper::get_connection_data( $t->value );
				} else {
					
					if( $mailer[ CP_API_CONNECTION_SERVICE ][0] == 'infusionsoft' && $t->name == 'infusionsoft_tags' ) {
						$settings['infusionsoft_tags'][] = $t->value;
					} else if( $mailer[ CP_API_CONNECTION_SERVICE ][0] == 'ontraport' && $t->name == 'ontraport_tags' ) {
						$settings['ontraport_tags'][] = $t->value;
					} else {
						$settings[ $t->name ] = $t->value;
					}
					$metaMapping[ $meta['cp_mapping'][ $key ]->name ] = $t->value;
				}
			}
		}

		$map = ConvertPlugHelper::get_decoded_array(get_post_meta( $style_id, 'map_placeholder', true ));

		$style_name = get_the_title( $settings['style_id'] );

		if( ! $mailer ) {

			$email_meta = get_post_meta( $style_id, 'email' );

			$admin_email = get_option( 'admin_email' );

			$template =  sprintf( __( "[FORM_SUBMISSION_DATA]\n\n -- \n\nThis e-mail was sent from a ConvertPlug Pro design [DESIGN_NAME] on %s (%s)", "convertplug-v2" ), get_bloginfo( 'name' ), site_url() );

			$subject = sprintf( __( 'Style "%s" not connected to any service.', 'cp-v2-connects' ), $style_name );

			$email_meta = ( ! empty( $email_meta ) ) ? call_user_func_array( 'array_merge', call_user_func_array( 'array_merge', $email_meta ) ) : array();

			if( ! empty( $email_meta ) ) {
				if ( $email_meta['admin_email'] == '1' ) {
					$admin_email = get_option( 'admin_email' );
				} else {
					if( $email_meta['custom_email'] != '' ) {
						$admin_email = $email_meta['custom_email'];
					}
				}
				$template = $email_meta['email_template'];
				$subject = $email_meta['email_template_subject'];
			}

			$template = str_replace( '[DESIGN_NAME]', '"<strong>' . $style_name . '</strong>"', $template );

			$template = str_replace( '[SITE_NAME]', get_bloginfo( 'name' ), $template );

			$subject = str_replace( '[SITE_NAME]', get_bloginfo( 'name' ), $subject );

			$subject = str_replace( '[DESIGN_NAME]', '"' . $style_name . '"', $subject );

			ConvertPlugServices::send_email( $admin_email, $subject, $template, $settings, $map );

			// $response['error'] = __( 'You are not connected to any service.', 'cp-v2-connects' );
			wp_send_json_success( $response );
			return $response;
		}

		if ( is_array( $meta['cp_mapping'] ) ) {

			foreach ( $meta['cp_mapping'] as $key => $t ) {

				$meta['cp_mapping'][ $key ]->name = str_replace( '{', '', $t->name );
				$meta['cp_mapping'][ $key ]->name = str_replace( '}', '', $t->name );
				$meta['cp_mapping'][ $key ]->name = str_replace( 'input', '-input', $t->name );
				$meta['cp_mapping'][ $key ]->name = str_replace( 'cp_mapping', '', $t->name );

				$metaMapping[ $meta['cp_mapping'][ $key ]->name ] = $t->value;
			}
		}

		$settings['meta'] = $metaMapping;
		$settings['api_connection'] = $mailer;

		if( isset( $mailer[ CP_API_CONNECTION_SERVICE ][0] ) ) {
			$instance = self::get_service_instance( $mailer[ CP_API_CONNECTION_SERVICE ][0] );
		} else {
			$instance = self::get_service_instance( $mailer_name );
		}

		$email = isset( $post_data['param']['email'] ) ? $post_data['param']['email'] : '';

		do_action( 'cp_before_subscribe', $email, $mailer_name );

		$response = $instance->subscribe( $settings, $email );

		$response['style_slug'] = $post->post_name;

		if ( $response['error'] !== false ) {

			$subject		= sprintf( __( 'Style "%s" Service %s not working.', 'cp-v2-connects' ), $style_name, $settings['cp-integration-service'] );

			$template 		=  sprintf( __( 'Style "<strong>%s</strong>" Service %s not working. Please check mailer configuration ASAP. The details of subscriber are given below.\n [FORM_SUBMISSION_DATA]', 'cp-v2-connects' ), $style_name, $settings['cp-integration-service'] );

			ConvertPlugServices::send_email( $admin_email, $subject, $template, $settings, $map );
		}
		
		do_action( 'cp_after_subscribe' );
		
		wp_send_json_success( $response );
		return $response;
	}

	/**
	 * Subscribe to specific lists and group
	 *
	 * Called via the in sync frontend addons
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function subscribe( $connection ) {

		$post_data  = ConvertPlugHelper::get_post_data();
		$response     = array( 'error' => false );
		$settings = $post_data;
		$service = '';

		$connection_data = isset( $connection['connection'] ) ? ConvertPlugHelper::get_decoded_array( stripslashes( $connection['connection'] ) ) : array();

		$service = $connection_data['cp-integration-service'];
		unset( $connection_data['cp-integration-source'] );

		if( ! $service ) {
			$response['error'] = __( 'You are not connected to any service.', 'cp-v2-connects' );
			return $response;
		}
		
		if ( is_array( $connection_data ) ) {
			foreach ( $connection_data as $key => $t ) {
				if ( $key == 'cp-integration-account-slug' ) {
					$account = ConvertPlugHelper::get_connection_data( $t );
				} else {
					
					if( $service == 'infusionsoft' && $key == 'infusionsoft_tags' ) {
						$settings['infusionsoft_tags'][] = $t;
					} else if( $service == 'ontraport' && $key == 'ontraport_tags' ) {
						$settings['ontraport_tags'][] = $t;
					} else {
						$settings[ $key ] = $t;
					}
				}
			}
		}

		$settings['meta'] = array();
		$settings['api_connection'] = $account;

		$email = isset( $connection['data']['email'] ) ? $connection['data']['email'] : '';

		$settings['param']['email'] = $email;

		do_action( 'cp_before_subscribe', $email, $service );

		$instance = self::get_service_instance( $service );
		$response = $instance->subscribe( $settings, $email );
		
		do_action( 'cp_after_subscribe' );

		return $response;
	}


	/**
	 * Asynchronously saves meta related to the style id
	 *
	 * Called via the save_meta_setting AJAX action.
	 *
	 * @since 0.0.1
	 * @return array The response array.
	 */
	static public function save_meta() {

		$post_data  = ConvertPlugHelper::get_post_data();
		$post_id = ( isset( $post_data['style_id'] ) ) ? $post_data['style_id'] : 0;

		$response = array( 
			'error' => true
		);

		if( $post_id != 0 ) {

			$meta_value[0]['cp_connect_settings'] = $post_data['cp_taxonomy'];
			$meta_value[1]['cp_mapping'] = $post_data['cp_mapping'];

			$result = update_post_meta( $post_id, 'connect', $meta_value );

			if ( ! is_wp_error( $result ) ) {
				$response['error'] = false;
			} else {
				// Error
				$response['error'] = $result->get_error_message();
			}
		} else {
			// Error
			$response['error'] = _( 'Wrong Style ID. Please check with admin.', 'cp-v2-connects' );
		}

		return $response;
	}


	/**
	 * Sends E-Mail to admin when something goes wrong in subscription
	 *
	 * Called via the add_subscriber function.
	 *
	 * @since 0.0.1
	 * @return void.
	 */
	static public function send_email( $email, $subject, $template, $settings, $map ) {

		$headers = array(
					'Reply-To: ' . get_bloginfo( 'name' ) . ' <' . $email . '>',
					'Content-Type: text/html; charset=UTF-8',
				);

		$param = '';

		if ( is_array( $settings['param'] ) && count( $settings['param'] ) ) {
			foreach ($settings['param'] as $key => $value) {
				$k = isset( $map[ $key ] ) ? $map[ $key ] : $key;
				$param .= '<p>' . ucfirst( $k ) . ' : ' . $value . '</p>';
			}
		}

		$template = str_replace( '[FORM_SUBMISSION_DATA]', $param, $template );

		wp_mail( $email, stripslashes( $subject), stripslashes( $template ), $headers );
	}
}