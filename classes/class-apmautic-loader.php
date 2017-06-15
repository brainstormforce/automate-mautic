<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automate-mautic
 * @since 1.0.5
 */

if ( ! class_exists( 'APMautic_Loader' ) ) :

	/**
	 * Create class APMautic_Loader
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
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-helper.php' );

			if ( is_admin() ) {
				require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-adminsettings.php' );
			}
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-rulepanel.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-wp-hooks.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-services.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-wp-register.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-comment.php' );
			require_once( AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-comment-approve.php' );
		}
	}
	APMautic_Loader::instance();
endif;
