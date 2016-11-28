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
		/*Load the appropriate text-domain
		$this->load_plugin_textdomain();
		*/
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

	/*function load_plugin_textdomain() {
		//Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'bsfmautic' );

		//Setup paths to current locale file
		$mofile_global = trailingslashit( WP_LANG_DIR ) . 'plugins/bb-ultimate-addon/' . $locale . '.mo';
		$mofile_local  = trailingslashit( BB_ULTIMATE_ADDON_DIR ) . 'languages/' . $locale . '.mo';

		if ( file_exists( $mofile_global ) ) {
			//Look in global /wp-content/languages/plugins/bb-ultimate-addon/ folder
			return load_textdomain( 'bsfmautic', $mofile_global );
		}
		else if ( file_exists( $mofile_local ) ) {
			//Look in local /wp-content/plugins/bb-ultimate-addon/languages/ folder
			return load_textdomain( 'bsfmautic', $mofile_local );
		}
		//Nothing found
		return false;
	} */
}
endif;
/**
 * Initialize the class only after all the plugins are loaded.
 */
function bsf_mautic_init() {
	$BSF_Mautic_Init = new BSF_Mautic_Init();
}
add_action( 'plugins_loaded', 'bsf_mautic_init' );