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
				self::$instance->includes();
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
			require_once AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-settings.php';
			self::load_plugin_textdomain();
		}

		/**
		 * Get config option
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function get_amp_options() {

			$setting_options = get_option( AP_MAUTIC_PLUGIN_CONFIG );
			return $setting_options;
		}

		/**
		 * Get credentials
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function get_mautic_credentials() {

			$mautic_credentials = get_option( AP_MAUTIC_APIAUTH );
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

		/**
		 * Renders html with respective input fields
		 *
		 * @since 1.0.4
		 * @param string $input The connection slug.
		 * @param array  $settings The input type settings array.
		 * @return string The html string.
		 */
		static public function render_input_html( $id = '', $settings = array() ) {

			if ( $id != '' && ! empty( $settings ) ) {

				$input = '<div class="apm-config-fields apm-' . $id . '-wrap '.$settings['row_class'].'">';
				switch ( $settings['type'] ) {
					case 'text':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';
						$input .= '<h4>' . $settings['label'] . '</h4>';
						
						if ( isset( $settings['help'] ) && $settings['help'] != '' ) {
							$input .= '<p class="admin-help">'.$settings['help'].'</p>';
						}

						$input .= '<input type="text" name="' . $id . '" id="' . $id . '" class="regular-text ' . $settings['class'] . '" value="' . $default_value . '"/>';
						break;

					case 'submit':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';

						if ( isset( $settings['help'] ) && $settings['help'] != '' ) {
							$input .= '<p class="admin-help">'.$settings['help'].'</p>';
						}

						$input .= '<p class="submit"><input type="submit" name="' . $id . '" id="' . $id . '" class="button-primary ' . $settings['class'] . '" value="' . $default_value . '"/>';
						if ( isset( $settings['spinner'] ) && $settings['spinner'] ) {
							$input .= '<span class="spinner ap_mautic_spinner" style=""></span></p>';
						}
						if ( isset( $settings['nonce_acion'] ) && $settings['nonce_acion'] != '' ) {
							$input .= wp_nonce_field( $settings['nonce_acion'], $settings['nonce_name'] );
						}
						break;

					case 'button':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';

						if ( isset( $settings['help'] ) && $settings['help'] != '' ) {
							$input .= '<p class="admin-help">'.$settings['help'].'</p>';
						}
						$input .= '<p class="submit"><input type="button" name="' . $id . '" id="' . $id . '" class="button-primary ' . $settings['class'] . '" value="' . $default_value . '"/></p>';
						if ( isset( $settings['nonce_acion'] ) && $settings['nonce_acion'] != '' ) {
							$input .= wp_nonce_field( $settings['nonce_acion'], $settings['nonce_name'] );
						}
					break;

					case 'checkbox':
						$checked = ( isset( $settings['ischecked'] ) ) ? $settings['ischecked'] : '';
						$input .= '<h4>' . $settings['label'] . '</h4>';
						if ( isset( $settings['help'] ) && $settings['help'] != '' ) {
							$input .= '<p class="admin-help">'.$settings['help'].'</p>';
						}
						$input .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" class="' . $settings['class'] . '" value="" "'.checked( 1, $checked, false).'"/>'.$settings['text'];
					break;

					default:
						$input .= '';
						break;
				}
				
				if ( isset( $settings['desc'] ) && $settings['desc'] != '' ) {
					$input .= '<p class="admin-help admin-field-desc">' . $settings['desc'] . '</p>';
				}

				$input .= '</div>';
			}

			echo $input;
		}

		/**
		 * Renders html with respective settings fields
		 *
		 * @since 1.0.4
		 * @param string $input The connection slug.
		 * @param array  $settings The input type settings array.
		 * @return string The html string.
		 */
		public static function render_settings_field( $id = '', $settings = array() ) {

			if ( $id != '' && ! empty( $settings ) ) {

				$input = '';
				switch ( $settings['type'] ) {
					case 'select':
						$input .= '<select id="'.esc_attr( $settings['id'] ).'" class="'.esc_attr( $settings['class'] ).'" name="'.$id.'" >';

						foreach( $settings['options'] as $option_key => $option_val ) {
							$selected = selected( $option_key, $settings['selected'], false );
							$input .='<option value="' . $option_key . '"' . $selected . '>' . $option_val . '</option>';
						}
						$input .= '</select>';
						break;
					default:
						$input .= '';
						break;
				}
			}
			echo $input;
		}

		/**
		 * Return service configuration data
		 *
		 * @since 1.0.4
		 * @return array Service Data.
		 */
		public static function get_service_data() {
			$config = get_option( AP_MAUTIC_PLUGIN_CONFIG );
			$credentials = get_option( 'ampw_mautic_credentials' );

			if ( is_array( $credentials ) ) {
				$config = array_merge( $config, $credentials );
			}

			return $config;
		}

		/**
		 * Check if Mautic is configured
		 *
		 * @since 1.0.4
		 * @return boolean
		 */
		public static function is_service_connected() {
			$credentials = self::get_mautic_credentials();

			if ( ! isset( $credentials['access_token'] ) ) {

				return false;
			}

			return true;
		}
	}
endif;


if ( ! function_exists( 'apm_get_option' ) ) :

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

		$amp_options = get_option( AP_MAUTIC_PLUGIN_CONFIG );

		$value = isset( $amp_options[ $key ] ) ? $amp_options[ $key ] : $default;

		return apply_filters( "apm_get_option_{$key}", $value, $key, $default );
	}

endif;

if ( ! function_exists( 'ampw_mautic_init' ) ) :
	/**
	 * Initialize the class after plugins loaded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function ampw_mautic_init() {
		APMautic_helper::instance();
	}
endif;
add_action( 'plugins_loaded', 'ampw_mautic_init' );
