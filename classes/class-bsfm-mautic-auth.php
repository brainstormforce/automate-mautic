<?php
/**
 * UABB initial setup
 *
 * @since 1.1.0.4
 */
if ( ! class_exists( 'BSFM_Mautic_Auth' ) ) :

	class BSFM_Mautic_Auth {

	private static $instance;

	public static $bsfm_options;

	/**
	*  Initiator
	*/
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BSF_Mautic();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function includes() {
		require_once 'classes/class-bsfm-init.php';
	}
	public function hooks() {
		//add_action( 'wp_head', array( $this, 'bsf_mautic_tracking_script' ) ); 
		// ajax hook here

	}
}
endif;
$BSFM_Mautic_Auth = new BSFM_Mautic_Auth::instance();