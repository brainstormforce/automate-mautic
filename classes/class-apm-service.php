<?php

/**
 * Base class for services.
 *
 * @since 1.0.4
 */
abstract class APMauticService {

	/**
	 * The ID for this service such as aweber or mailchimp.
	 *
	 * @since 1.0.4
	 * @var string $id
	 */
	public $id = '';

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.4
	 * @param array $fields
	 * @return array{
	 *      @type bool|string $error The error message or false if no error.
	 *      @type array $data An array of data used to make the connection.
	 * }
	 */
	// abstract public function connect( $fields = array() );

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.0.4
	 * @return string The connection settings markup.
	 */
	// abstract public function render_connect_settings( $service_data );

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
	abstract public function render_fields( $service_data );
}
