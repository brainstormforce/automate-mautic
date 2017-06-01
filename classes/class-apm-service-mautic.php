<?php

/**
 * Helper class for the Mautic API.
 *
 * @since 1.0.4
 */
final class APMauticServiceMautic extends APMauticService {

	/**
	 * The ID for this service.
	 *
	 * @since 1.5.4
	 * @var string $id
	 */  
	public $id = AP_MAUTIC_SERVICE;

	/**
	 * @since 1.5.4
	 * @var object $api_instance
	 * @access private
	 */  
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.5.4
	 * @param string $api_key A valid API key.
	 * @return object The API instance.
	 */  
	public function get_api( $api_key ) 
	{
		if ( $this->api_instance ) {
			return $this->api_instance;
		}
		if ( ! class_exists( 'Mailchimp' ) ) {
			require_once FL_BUILDER_DIR . 'includes/vendor/mailchimp/mailchimp.php';
		}
		
		$this->api_instance = new Mailchimp( $api_key );
		
		return $this->api_instance;
	}
	
	/**
	 * Test the API connection.
	 *
	 * @since 1.5.4
	 * @param array $fields {
	 *      @type string $api_key A valid API key.
	 * }
	 * @return array{
	 *      @type bool|string $error The error message or false if no error.
	 *      @type array $data An array of data used to make the connection.
	 * }
	 */  
	public function connect( $fields = array() ) 
	{
		$response = array( 
			'error'  => false,
			'data'   => array()
		);
		
		// Make sure we have an API key.
		if ( ! isset( $fields['api_key'] ) || empty( $fields['api_key'] ) ) {
			$response['error'] = __( 'Error: You must provide an API key.', 'fl-builder' );
		}
		// Try to connect and store the connection data.
		else {
			
			$api = $this->get_api( $fields['api_key'] );

			try {
				$api->helper->ping();
				$response['data'] = array( 'api_key' => $fields['api_key'] );
			} 
			catch ( Mailchimp_Invalid_ApiKey $e ) {
				$response['error'] = $e->getMessage();
			}
		}
		
		return $response;
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.0.4
	 * @return string The connection settings markup.
	 */  
	public function render_connect_settings( $service_data ) 
	{
		$base_url  = isset( $service_data['base-url'] ) ? $service_data['base-url'] : '';
		
		ob_start();
		
		APMautic_helper::render_input_html('base-url', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'def_value'		=> $base_url,
			'type'          => 'text',
			'label'         => __( 'Base URL', 'automateplus-mautic-wp' ),
			'help'          => __( 'This setting is required for Mautic Integration.', 'automateplus-mautic-wp' )
		));

		APMautic_helper::render_input_html('ampw-save-authenticate', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'type'          => 'button',
			'def_value'		=> 'connected',
			'nonce_acion'	=> 'apmwmautic',
			'nonce_name'	=> 'ap-mautic-nonce',
			'label'         => __( 'Save and Authenticate', 'automateplus-mautic-wp' )
		));
		return ob_get_clean();
	}

	/**
	 * Render the markup for service specific fields. 
	 *
	 * @since 1.0.4
	 * @param string $account The name of the saved account.
	 * @param object $settings Saved module settings.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 * }
	 */  
	public function render_fields( $service_data ) 
	{
		$base_url  = isset( $service_data['base-url'] ) ? $service_data['base-url'] : '';
		
		ob_start();
		
		APMautic_helper::render_input_html('base-url', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'def_value'		=> $base_url,
			'type'          => 'text',
			'label'         => __( 'Base URL', 'automateplus-mautic-wp' ),
			'help'          => __( 'This setting is required for Mautic Integration.', 'automateplus-mautic-wp' )
		));

		APMautic_helper::render_input_html('public-key', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'type'          => 'text',
			'label'         => __( 'Public Key', 'automateplus-mautic-wp' )
		));

		APMautic_helper::render_input_html('secret-key', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'apm-service-input',
			'type'          => 'text',
			'label'         => __( 'Secret Key', 'automateplus-mautic-wp' ),
			'desc'          => sprintf( __( 'This setting is required to integrate Mautic in your website.<br>Need help to get Mautic API public and secret key? Read %1$sthis article%2$s.', 'automateplus-mautic-wp' ), '<a target="_blank" href="'. esc_url( 'https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/' ) . '">', '</a>' )
		));

		APMautic_helper::render_input_html('ampw-save-authenticate', array(
			'row_class'     => 'apm-service-row',
			'class'         => 'save-amp-settings',
			'type'          => 'submit',
			'def_value'		=> 'Save and Authenticate',
			'spinner'		=> true,
			'nonce_acion'	=> 'apmwmautic',
			'nonce_name'	=> 'ap-mautic-nonce',
			'label'         => __( 'Save and Authenticate', 'automateplus-mautic-wp' )
		));

		return ob_get_clean();
	}

	/**
	 * Render markup for the list field. 
	 *
	 * @since 1.5.4
	 * @param array $lists List data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the list field.
	 * @access private
	 */  
	private function render_list_field( $lists, $settings ) 
	{
		ob_start();
		
		$options = array( '' => __( 'Choose...', 'fl-builder' ) );
		
		foreach ( $lists['data'] as $list ) {
			$options[ $list['id'] ] = $list['name'];
		}
		
		FLBuilder::render_settings_field( 'list_id', array(
			'row_class'     => 'fl-builder-service-field-row',
			'class'         => 'fl-builder-service-list-select fl-builder-mailchimp-list-select',
			'type'          => 'select',
			'label'         => _x( 'List', 'An email list from a third party provider.', 'fl-builder' ),
			'options'       => $options,
			'preview'       => array(
				'type'          => 'none'
			)
		), $settings); 
		
		return ob_get_clean();
	}

	/**
	 * Render markup for the groups field. 
	 *
	 * @since 1.6.0
	 * @param string $list_id The ID of the list for this groups.
	 * @param array $groups An array of group data.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the group field.
	 * @access private
	 */  
	private function render_groups_field( $list_id, $groups, $settings ) 
	{
		if ( ! is_array( $groups ) || 0 === count( $groups ) ) {
			return;
		}
		
		ob_start();
		
		$options = array( '' => __( 'No Group', 'fl-builder' ) );
		
		foreach ( $groups as $group ) {
			foreach ( $group['groups'] as $subgroup ) {
				$options[ $list_id . '_' . $group['id'] . '_' . $subgroup['id'] ] = $group['name'] . ' - ' . $subgroup['name'];
			}
		}
		
		FLBuilder::render_settings_field( 'groups', array(
			'row_class'     => 'fl-builder-service-field-row',
			'class'         => 'fl-builder-mailchimp-group-select',
			'type'          => 'select',
			'label'         => _x( 'Groups', 'MailChimp list group.', 'fl-builder' ),
			'multi-select'	=> true,
			'options'       => $options,
			'preview'       => array(
				'type'          => 'none'
			)
		), $settings); 
		
		return ob_get_clean();
	}

	/** 
	 * Subscribe an email address to Mautic.
	 *
	 * @since 1.5.4
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param string $name Optional. The full name of the person subscribing.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */  
	public function subscribe( $email, $settings, $actions ) {

		$api_data = APMauticServices::get_api_method_url( $email );
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
		APMauticServices::ampw_mautic_api_call( $url, $method, $settings, $actions );
	}
}