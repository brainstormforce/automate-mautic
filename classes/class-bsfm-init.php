<?php
/**
 * MauticPress initial setup
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
		/**
		 *	For Performance
		 *	Set static object to store data from database.
		 */
		self::get_bsfm_options();
		self::includes();
	}

	function includes() {
		require_once BSF_MAUTIC_PLUGIN_DIR . 'classes/class-bsfm-helper.php';
		require_once BSF_MAUTIC_PLUGIN_DIR . 'classes/class-bsfm-admin-settings.php';
		require_once BSF_MAUTIC_PLUGIN_DIR . 'classes/class-bsfm-branding.php';
		//Load the appropriate text-domain
		$this->load_plugin_textdomain();
	}
	/**
	*	For Performance
	*	Set static object to store data from database.
	*/

	static function get_bsfm_options() {
		self::$bsfm_options = array(
			'bsf_mautic_settings'     => get_option('_bsf_mautic_config'),
			'bsf_mautic_branding' => get_option('_bsf_mautic_branding')
		);
	}
	function load_plugin_textdomain() {
		load_plugin_textdomain( 'bsfmautic');
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