<?php
/**
 * Mautic for WordPress Settings
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_Genral_Settings' ) ) :

	/**
	 * Create class APMautic_Genral_Settings
	 */
	class APMautic_Genral_Settings {

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
				self::$instance = new APMautic_Genral_Settings();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_filter( 'amp_new_options_tab', array( $this, 'render_settings_tab' ), 10, 1);
			add_action( 'amp_options_tab_content', array( $this, 'render_settings_tab_content' ) );
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $user_id WP Users unique ID.
		 * @return void
		 */
		public function render_settings_tab( $items ) {
			$items['mautic_settings']  = array(
				'label' => 'Settings'
			);
			return $items;
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $user_id WP Users unique ID.
		 * @return void
		 */
		public function render_settings_tab_content( $active ) {

			if( 'mautic_settings' == $active ) {
				APMautic_AdminSettings::render_form( 'general' );
		   }
		}
	}
	add_action( 'plugins_loaded', 'APMautic_Genral_Settings::instance' );
endif;
