<?php
/**
 * MautiPress initial setup
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'AMPW_Mautic_Init' ) ) :
	
	class AMPW_Mautic_Init {

	/**
	 *  Constructor
	 */
	public function __construct() 
	{
		self::includes();
	}

	public function includes() 
	{
		require_once AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-settings.php';
		$this->load_plugin_textdomain();
	}
	/**
	 *	For Performance
	 *  Set static object to store data from database.
	 */
	public static function get_amp_options( $option )
	{
		$bsfm_options = array(
			'mautic_settings'     => get_option('ampw_mautic_config'),
			'mautic_credentials'     => get_option( 'ampw_mautic_credentials' )
		);
		return $bsfm_options[$option];
	}

	function load_plugin_textdomain() {
		load_plugin_textdomain( 'automateplus-mautic-wp' );
	}
}
endif;
/**
 * Initialize the class only after all the plugins are loaded.
 */
function ampw_mautic_init() {
	$AMPW_Mautic_Init = new AMPW_Mautic_Init();
}
add_action( 'plugins_loaded', 'ampw_mautic_init' );