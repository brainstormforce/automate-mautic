<?php
/**
 * MautiPress initial setup
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSF_Mautic_Init' ) ) :
	
	class BSF_Mautic_Init {

	public static $bsfm_options;

	/**
	 *  Constructor
	 */
	public function __construct() {
		self::includes();
		add_action( 'wp_loaded', array( $this, 'get_bsfm_options') );
	}

	public function includes() {
		require_once AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-bsfm-admin-settings.php';
		//Load the appropriate text-domain
		$this->load_plugin_textdomain();
	}
	/**
	 *	For Performance
	 *  Set static object to store data from database.
	 */
	public static function get_bsfm_options() {
		self::$bsfm_options = array(
			'bsf_mautic_settings'     => get_option('_bsf_mautic_config')
		);
	}
	function load_plugin_textdomain() {
		load_plugin_textdomain( 'automateplus-mautic-wp');
	}
}
endif;
/**
 * Initialize the class only after all the plugins are loaded.
 */
function bsf_mautic_init() {
	$BSF_Mautic_Init = new BSF_Mautic_Init();
}
add_action( 'plugins_loaded', 'bsf_mautic_init' );