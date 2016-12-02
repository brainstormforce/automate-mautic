<?php
/**
 * admin ajax functions. 
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSFMauticAdminAjax' ) ) :

final class BSFMauticAdminAjax {
	
	private static $instance;
	/**
	 * Holds any errors that may arise from
	 * saving admin settings.
	 *
	 * @since 1.0.0
	 * @var array $errors
	 */

	static public $errors = array();
	/**
	 * Initiator
	 */
	public static function instance(){
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BSFMauticAdminAjax();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() {
		add_action( 'wp_ajax_get_cf7_fields', array( $this, 'bsf_make_cf7_fields' ) );
		add_action( 'wp_ajax_get_edd_var_price', array( $this, 'bsf_get_edd_variable_price' ) );
		add_action( 'wp_ajax_clean_mautic_transient', array( $this, 'bsf_clean_mautic_transient' ) );
	}
	/** 
	 * Adds the admin menu and enqueues CSS/JS if we are on
	 * the MauticPress admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function ()
	{
	}
	
	/** 
	 * Renders the admin settings menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function () 
	{
	}
}
$BSFMauticAdminAjax = BSFMauticAdminAjax::instance();
endif;