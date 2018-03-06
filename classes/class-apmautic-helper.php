<?php
/**
 * MautiPress initial setup
 *
 * @package automate-mautic
 * @since 1.0.5
 */

if ( ! class_exists( 'APMautic_Helper' ) ) :

	/**
	 * Create class APMautic_Helper
	 * load text domain, get options
	 */
	class APMautic_Helper {

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
		 * @since 1.0.5
		 * @param string $id The unique name for input.
		 * @param array  $settings The input type settings array.
		 */
		static public function render_input_html( $id = '', $settings = array() ) {

			if ( '' != $id && ! empty( $settings ) ) {

				$row_class   = ( isset( $settings['row_class'] ) ) ? esc_html( $settings['row_class'] ) : '';
				$type        = ( isset( $settings['type'] ) ) ? sanitize_text_field( $settings['type'] ) : '';
				$class       = ( isset( $settings['class'] ) ) ? sanitize_html_class( $settings['class'] ) : '';
				$label       = ( isset( $settings['label'] ) ) ? esc_html( $settings['label'] ) : '';
				$placeholder = ( isset( $settings['placeholder'] ) ) ? sanitize_text_field( $settings['placeholder'] ) : '';
				$iswrap      = ( isset( $settings['iswrap'] ) ) ? $settings['iswrap'] : true;
				$id          = sanitize_html_class( $id );
				$help        = isset( $settings['help'] ) ? esc_html( $settings['help'] ) : '';
				$input       = '';

				if ( $iswrap ) {
					$input .= '<div class="apm-config-fields apm-' . $id . '-wrap  ' . $row_class . '">';
				}
				switch ( $type ) {
					case 'text':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';
						$input        .= '<h4>' . $label . '</h4>';

						if ( isset( $help ) && '' != $help ) {
							$input .= '<p class="admin-help">' . $help . '</p>';
						}

						$input .= '<input type="text" name="' . $id . '" id="' . $id . '" class="regular-text ' . $class . '" placeholder="' . $placeholder . '" value="' . $default_value . '"/>';
						break;

					case 'hidden':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';
						$input        .= '<input type="hidden" name="' . $id . '" id="' . $id . '" class="' . $class . '" value="' . $default_value . '"/>';
						break;

					case 'submit':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';

						if ( isset( $help ) && '' != $help ) {
							$input .= '<p class="admin-help">' . $help . '</p>';
						}

						$input .= '<p class="submit"><input type="submit" name="' . $id . '" id="' . $id . '" class="button-primary ' . $class . '" value="' . $default_value . '"/>';
						if ( isset( $settings['spinner'] ) && $settings['spinner'] ) {
							$input .= '<span class="spinner apm-wp-spinner" style=""></span></p>';
						}
						if ( isset( $settings['nonce_acion'] ) && '' != $settings['nonce_acion'] ) {
							$input .= wp_nonce_field( $settings['nonce_acion'], $settings['nonce_name'] );
						}
						break;

					case 'button':
						$default_value = ( isset( $settings['def_value'] ) ) ? $settings['def_value'] : '';

						if ( isset( $help ) && '' != $help ) {
							$input .= '<p class="admin-help">' . $help . '</p>';
						}
						$input .= '<p class="submit"><input type="button" name="' . $id . '" id="' . $id . '" class="button-primary ' . $class . '" value="' . $default_value . '"/></p>';
						if ( isset( $settings['nonce_acion'] ) && '' != $settings['nonce_acion'] ) {
							$input .= wp_nonce_field( $settings['nonce_acion'], $settings['nonce_name'] );
						}
						break;

					case 'checkbox':
						$checked = ( isset( $settings['ischecked'] ) ) ? $settings['ischecked'] : '';
						$input  .= '<h4>' . $label . '</h4>';
						if ( isset( $help ) && '' != $help ) {
							$input .= '<p class="admin-help">' . $help . '</p>';
						}
						$input .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" class="' . $class . '" value="" "' . checked( 1, $checked, false ) . '"/>' . esc_html( $settings['text'] );
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
		 * @since 1.0.5
		 * @param string $id unique settings name.
		 * @param array  $settings settings type settings array.
		 */
		public static function render_settings_field( $id = '', $settings = array() ) {

			$element_id = ( isset( $settings['id'] ) ) ? esc_attr( $settings['id'] ) : '';

			$class = ( isset( $settings['class'] ) ) ? esc_html( $settings['class'] ) : '';

			$return = ( isset( $settings['return'] ) ) ? $settings['return'] : false;

			$type = ( isset( $settings['type'] ) ) ? sanitize_text_field( $settings['type'] ) : '';

			if ( '' != $id && ! empty( $settings ) ) {

				$input = '';
				switch ( $type ) {
					case 'select':
						$input .= '<select id="' . $element_id . '" class="' . $class . '" name="' . $id . '" >';

						foreach ( $settings['options'] as $option_key => $option_val ) {
							$selected = selected( $option_key, $settings['selected'], false );
							$input   .= '<option value="' . esc_attr( $option_key ) . '"' . $selected . '>' . esc_html( $option_val ) . '</option>';
						}
						$input .= '</select>';
						break;
					default:
						$input .= '';
						break;
				}
			}
			if ( $return ) {
				return $input;
			}
			echo $input;
		}

		/**
		 * Return service configuration data
		 *
		 * @since 1.0.5
		 * @return array Service Data.
		 */
		public static function get_service_data() {
			$config      = get_option( AP_MAUTIC_PLUGIN_CONFIG );
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
