<?php
/**
 * MautiPress initial setup
 *
 * @package automate-mautic
 * @since 1.0.6
 */

/**
 * Create class APMautic_Auto_Update
 */
class APMautic_Auto_Update {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Plugin Updates.
		add_action( 'admin_init', __CLASS__ . '::apmautic_init' );
	}

	/**
	 * Implement plugin auto update logic.
	 *
	 * @since 1.0.6
	 * @return void
	 */
	public static function apmautic_init() {

		// Get auto saved version number.
		$saved_version = get_option( 'ap_mautic_version' );

		// If the version option not present then just create it.
		if ( false === $saved_version ) {
			update_option( 'ap_mautic_version', AP_MAUTIC_VERSION );
		}

		// Set the Mautic connection error message option.
		$mautic_user_pass_error_msg = get_option( 'ap_mautic_up_error_msg' );
		if ( false === $mautic_user_pass_error_msg ) {
			update_option( 'ap_mautic_up_error_msg', '' );
		}

		// Set the Mautic connection type option.
		$check_option = get_option( 'ap_mautic_connection_type' );
		if ( false === $check_option ) {
			update_option( 'ap_mautic_connection_type', 'mautic_api' );
		}

		// If equals then return.
		if ( version_compare( $saved_version, AP_MAUTIC_VERSION, '=' ) ) {
			return;
		}

		// Update auto saved version number.
		update_option( 'ap_mautic_version', AP_MAUTIC_VERSION );
	}
}

/**
 * calling 'APMautic_Auto_Update' Constructor
 */
new APMautic_Auto_Update();
