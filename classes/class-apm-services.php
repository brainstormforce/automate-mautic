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
