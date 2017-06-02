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
		 * Renders html with respective input fields
		 *
		 * @since 1.0.4
		 * @param string $input The connection slug.
		 * @param array  $settings The input type settings array.
		 * @return string The html string.
		 */
		static public function render_input_html( $id = '', $settings = array() ) {

			if ( '' != $id && ! empty( $settings ) ) {

				$row_class = ( isset( $settings['row_class'] ) ) ? $settings['row_class'] : '';
				$type = ( isset( $settings['type'] ) ) ? $settings['type'] : '';
				$class = ( isset( $settings['class'] ) ) ? $settings['class'] : '';
				$label = ( isset( $settings['label'] ) ) ? $settings['label'] : '';
				$iswrap = ( isset( $settings['iswrap'] ) ) ? $settings['iswrap'] : true;
				$input = '';
				if ( $iswrap ) {
					$input .= '<div class="apm-config-fields apm-' . $id . '-wrap ' . $row_class . '">';
				}
				switch ( $type ) {
					case 'text':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';
						$input .= '<h4>' . $label . '</h4>';

						if ( isset( $settings['help'] ) && '' != $settings['help'] ) {
							$input .= '<p class="admin-help">' . $settings['help'] . '</p>';
						}

						if ( isset( $settings['placeholder'] ) && '' != $settings['placeholder'] ) {
							$placeholder = $settings['placeholder'];
						}

						$input .= '<input type="text" name="' . $id . '" id="' . $id . '" class="regular-text ' . $class . '" placeholder="' . $placeholder . '" value="' . $default_value . '"/>';
						break;

					case 'hidden':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';
						$input .= '<input type="hidden" name="' . $id . '" id="' . $id . '" class="' . $class . '" value="' . $default_value . '"/>';
						break;

					case 'submit':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';

						if ( isset( $settings['help'] ) && '' != $settings['help'] ) {
							$input .= '<p class="admin-help">' . $settings['help'] . '</p>';
						}

						$input .= '<p class="submit"><input type="submit" name="' . $id . '" id="' . $id . '" class="button-primary ' . $class . '" value="' . $default_value . '"/>';
						if ( isset( $settings['spinner'] ) && $settings['spinner'] ) {
							$input .= '<span class="spinner ap_mautic_spinner" style=""></span></p>';
						}
						if ( isset( $settings['nonce_acion'] ) && '' != $settings['nonce_acion'] ) {
							$input .= wp_nonce_field( $settings['nonce_acion'], $settings['nonce_name'] );
						}
						break;

					case 'button':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';

						if ( isset( $settings['help'] ) && '' != $settings['help'] ) {
							$input .= '<p class="admin-help">' . $settings['help'] . '</p>';
						}
						$input .= '<p class="submit"><input type="button" name="' . $id . '" id="' . $id . '" class="button-primary ' . $class . '" value="' . $default_value . '"/></p>';
						if ( isset( $settings['nonce_acion'] ) && '' != $settings['nonce_acion'] ) {
							$input .= wp_nonce_field( $settings['nonce_acion'], $settings['nonce_name'] );
						}
					break;

					case 'checkbox':
						$checked = ( isset( $settings['ischecked'] ) ) ? $settings['ischecked'] : '';
						$input .= '<h4>' . $label . '</h4>';
						if ( isset( $settings['help'] ) && '' != $settings['help'] ) {
							$input .= '<p class="admin-help">' . $settings['help'] . '</p>';
						}
						$input .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" class="' . $class . '" value="" "' . checked( 1, $checked, false ) . '"/>' . $settings['text'];
					break;

					default:
						$input .= '';
						break;
				}

				if ( isset( $settings['desc'] ) && '' != $settings['desc'] ) {
					$input .= '<p class="admin-help admin-field-desc">' . $settings['desc'] . '</p>';
				}
				if ( $iswrap ) {
					$input .= '</div>';
				}
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

			if ( '' != $id && ! empty( $settings ) ) {

				$input = '';
				switch ( $settings['type'] ) {
					case 'select':
						$input .= '<select id="' . esc_attr( $settings['id'] ) . '" class="' . esc_attr( $settings['class'] ) . '" name="' . $id . '" >';

						foreach ( $settings['options'] as $option_key => $option_val ) {
							$selected = selected( $option_key, $settings['selected'], false );
							$input .= '<option value="' . $option_key . '"' . $selected . '>' . $option_val . '</option>';
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
