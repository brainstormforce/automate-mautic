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
			$defaults = array(
				'enable-tracking'	=> true,
				'base-url'			=> '',
				'public-key'		=> '',
				'secret-key'		=> '',
				'callback-uri'		=> '',
			);

			// if empty add all defaults.
			if ( empty( $setting_options ) ) {
				$setting_options = $defaults;
				update_option( 'ampw_mautic_config', $setting_options );
			} else {

				foreach ( $defaults as $key => $value ) {
					if ( is_array( $setting_options ) && ! array_key_exists( $key, $setting_options ) ) {
						$setting_options[ $key ] = $value;
					} else {
						$setting_options = wp_parse_args( $setting_options, $defaults );
					}
				}
			}
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
 * Initialize the class after plugins loaded.
 *
 * @since 1.0.0
 * @return void
 */
function ampw_mautic_init() {
	$ampw_mautic_init = new AMPW_Mautic_Init();
}
add_action( 'plugins_loaded', 'ampw_mautic_init' );
