<?php
/**
 * MautiPress initial setup
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'AMPW_Mautic_Init' ) ) :

	/**
	 * Create class AMPW_Mautic_Init
	 * load text domain, get options
	 */
	class AMPW_Mautic_Init {

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __construct() {
			self::includes();
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-settings.php';
			$this->load_plugin_textdomain();
		}

		/**
		 * Get config option
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function get_amp_options() {

			$setting_options = get_option( 'ampw_mautic_config' );
			return $setting_options;
		}

		/**
		 * Get credentials
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function get_mautic_credentials() {

			$mautic_credentials = get_option( 'ampw_mautic_credentials' );
			return $mautic_credentials;
		}

		/**
		 * Load text domain
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'automateplus-mautic-wp' );
		}
	}
endif;

/**
 * Get options by key
 *
 * @since 1.0.2
 * @param string $key Options array key.
 * @param string $default The default option if the option isn't set.
 *
 * @return mixed Option value
 */
function apm_get_option( $key = '', $default = false ) {

	$amp_options = get_option( 'ampw_mautic_config' );

	if ( isset( $amp_options[ $key ] ) ) {

		$value = $amp_options[ $key ];

	} else {

		$value = $default;
	}
	return apply_filters( "apm_get_option_{$key}", $value, $key, $default );
}

/**
 * Initialize the class after plugins loaded.
 *
 * @since 1.0.0
 * @return void
 */
function ampw_mautic_init() {
	$ampw_mautic_init = new AMPW_Mautic_Init();
}
add_action( 'plugins_loaded', 'ampw_mautic_init' );
