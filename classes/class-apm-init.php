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

	public static function get_amp_options()
	{
		$setting_options = get_option( 'ampw_mautic_config' );
		$defaults = array(
			'bsfm-enabled-tracking'	=> true,
			'bsfm-base-url'			=> '',
			'bsfm-public-key'		=> '',
			'bsfm-secret-key'		=> '',
			'bsfm-callback-uri'		=> ''
		);

		// if empty add all defaults
		if( empty( $setting_options ) ) {
			$setting_options = $defaults;
			update_option( 'ampw_mautic_config', $setting_options );
		} else {
			//	add new key
			foreach( $defaults as $key => $value ) {
				if( is_array( $setting_options ) && !array_key_exists( $key, $setting_options ) ) {
					$setting_options[ $key ] = $value;
				} else {
					$setting_options = wp_parse_args( $setting_options, $defaults );
				}
			}
		}
		return $setting_options;
	}

	public static function get_mautic_credentials()
	{
		$mautic_credentials = get_option( 'ampw_mautic_credentials' );
		return $mautic_credentials;
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