<?php
/**
 * Authenticate mautic helper function
 *
 * @package automate-mautic
 * @since 1.0.5
 */

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
			'class'             => 'APMauticServiceMautic',
		),
	);

	/**
	 * Render the connection settings for a service.
	 *
	 * @since 1.0.5
	 * @return void
	 */
	static public function render_settings() {
		$is_connected		= APMautic_helper::is_service_connected();
		$service            = AP_MAUTIC_SERVICE;
		$response_fields 	= '';
		// Render the settings to connect a new account.
		if ( $is_connected ) {
			$response_fields = self::render_connect_settings( $service );
		} // Render the settings to select a connected account.
		else {
			$response_fields = self::render_account_settings( $service );
		}

		// Return the response.
		echo $response_fields;
	}

	/**
	 * Render the settings to connect to a service.
	 *
	 * @since 1.0.5
	 * @param string $service service name.
	 * @return string The settings markup.
	 */
	static public function render_connect_settings( $service ) {
		ob_start();

		$saved_services = APMautic_helper::get_service_data();
		$instance = self::get_service_instance( $service );
		echo $instance->render_connect_settings( $saved_services );

		return ob_get_clean();
	}

	/**
	 * Render the account settings for a saved connection.
	 *
	 * @since 1.0.5
	 * @param string $service The service id such as "mailchimp".
	 */
	static public function render_account_settings( $service ) {
		$saved_services = APMautic_helper::get_service_data();
		ob_start();
		$instance   = self::get_service_instance( $service );
		echo $instance->render_fields( $saved_services );

		return ob_get_clean();
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

	/**
	 * Get an array of services data, all services will be returned.
	 *
	 * @since 1.0.5
	 * @return array An array of services and related data.
	 */
	static public function get_services_data() {
		$services = self::$services_data;

		return $services;
	}

	/**
	 * Get an instance of a service helper class.
	 *
	 * @since 1.0.4
	 * @param string $service The type of service.
	 * @return object
	 */
	static public function get_service_instance( $service ) {
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
	 * Get an instance of a service helper class.
	 *
	 * @since 1.0.5
	 * @param string $select selected segment.
	 */
	public static function select_all_segments( $select = '' ) {
			// get all segments.
			$instance   = APMauticServices::get_service_instance( AP_MAUTIC_SERVICE );
			$instance->render_list_field( $select );
	}
}
