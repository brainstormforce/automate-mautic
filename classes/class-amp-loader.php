<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_Loader' ) ) :

	/**
	 * Create class APMautic_Loader
	 * Handles register post type, trigger actions
	 */
	class APMautic_Loader {

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
				self::$instance = new APMautic_Loader();
				self::$instance->constants();
				self::$instance->includes();
			}

			return self::$instance;
		}

		/**
		 * Declare constants
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function constants() {

			define( 'AP_MAUTIC_FILE', trailingslashit( dirname( dirname( __FILE__ ) ) ) . 'automate-mautic.php' );
			define( 'AP_MAUTIC_BASE', plugin_basename( AP_MAUTIC_FILE ) );
			define( 'AP_MAUTIC_PLUGIN_DIR', plugin_dir_path( AP_MAUTIC_FILE ) );
			define( 'AP_MAUTIC_PLUGIN_URL', plugins_url( '/', AP_MAUTIC_FILE ) );
			define( 'AP_MAUTIC_PLUGIN_CONFIG', 'ampw_mautic_config' );
			define( 'AP_MAUTIC_APIAUTH', 'ampw_mautic_credentials' );
			define( 'AP_MAUTIC_POSTTYPE', 'automate-mautic' );
			define( 'AP_MAUTIC_SERVICE', 'mautic' );
		}

		/**
		 * Include files required to plugin
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-helper.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-amp-wp-hooks.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-services.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-rulepanel.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-wp-register.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-comment.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-settings.php' );
		}
	}
	APMautic_Loader::instance();
endif;
