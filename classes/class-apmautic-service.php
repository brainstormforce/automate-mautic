<?php
/**
 * Abstract class for Authentication
 *
 * @package automate-mautic
 * @since 1.0.5
 */

/**
 * Base class for services.
 *
 * @since 1.0.5
 */
abstract class APMautic_Service {

	/**
	 * The ID for this service such as aweber or mailchimp.
	 *
	 * @since 1.0.5
	 * @var string $id
	 */
	public $id = '';

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.5
	 * @param array $fields required fields for authentication.
	 */
	abstract public function connect( $fields );

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.0.5,
	 * @param array $service_data render fields for authentication.
	 * @return string The connection settings markup.
	 */
	abstract public function render_connect_settings( $service_data );

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.0.5
	 * @param object $service_data Saved module settings.
	 */
	abstract public function render_fields( $service_data );
}
