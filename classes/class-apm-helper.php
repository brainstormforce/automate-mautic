<?php
/**
 * MautiPress initial setup
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_helper' ) ) :

	/**
	 * Create class APMautic_helper
	 * load text domain, get options
	 */
	class APMautic_helper {

		/**
		 * Declare a static variable instance.
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiate class
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new APMautic_helper();
				self::includes();
			}
			return self::$instance;
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-settings.php';
			self::load_plugin_textdomain();
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

if ( ! function_exists( 'apm_get_option' ) ) :

function apm_get_option( $key = '', $default = false ) {

	$amp_options = get_option( 'ampw_mautic_config' );

	$value = isset( $amp_options[ $key ] ) ? $amp_options[ $key ] : $default;

	return apply_filters( "apm_get_option_{$key}", $value, $key, $default );
}

endif;
/**
 * Initialize the class after plugins loaded.
 *
 * @since 1.0.0
 * @return void
 */
if ( ! function_exists( 'ampw_mautic_init' ) ) :
function ampw_mautic_init() {
	APMautic_helper::instance();
}
endif;
add_action( 'plugins_loaded', 'ampw_mautic_init' );
