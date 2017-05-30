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
			add_filter( 'amp_new_options_tab', array( $this, 'render_settings_tab' ), 10, 1 );
			add_action( 'amp_options_tab_content', array( $this, 'render_settings_tab_content' ) );
			add_action( 'amp_update_tab_content', array( $this, 'update_general_settings' ) );
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $items all tabs items.
		 * @return array
		 */
		public function render_settings_tab( $items ) {
			$items['mautic_settings']  = array(
				'label' => 'Settings',
			);
			return $items;
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $active WP active tab.
		 * @return void
		 */
		public function render_settings_tab_content( $active ) {
			if ( 'mautic_settings' == $active ) {
				APMautic_AdminSettings::render_form( 'general' );
			}
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $active WP active tab.
		 * @return void
		 */
		public function update_general_settings( $active ) {
			if ( isset( $_POST['apm-general-settings-nonce'] ) && wp_verify_nonce( $_POST['apm-general-settings-nonce'], 'ampmauticgen' ) ) {
				$amp_edd = APMautic_helper::get_amp_options();

				$roles = $_POST['apmautic_access_role'];
				$roles = array_map('esc_attr', $roles );

				$amp_edd['apmautic_menu_position'] = $amp_edd['apmautic_access_role'] = false ;

				if ( isset( $_POST['apmautic_menu_position'] ) ) {

					$amp_edd['apmautic_menu_position'] = sanitize_text_field( $_POST['apmautic_menu_position'] );
				}
				if ( is_array( $roles ) ) {

					$amp_edd['apmautic_access_role'] = $roles;
				}
				update_option( 'ampw_mautic_config', $amp_edd );
				$redirect = APMautic_AdminSettings::get_render_page_url( '&tab=mautic_settings' );
				wp_redirect( $redirect );
			}
		}
	}
	add_action( 'plugins_loaded', 'APMautic_Genral_Settings::instance' );
endif;
