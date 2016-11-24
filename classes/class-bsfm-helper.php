<?php
/**
 * Custom modules
 */
if ( ! class_exists( 'BSF_Mautic_Helper' ) ) :
	class BSF_Mautic_Helper {

		// Class variables
		// public static $uabb_field;
		// public static $uabb_param;
		// public static $uabb_object;

		/*
		* Constructor function that initializes required actions and hooks
		* @Since 1.0
		*/
		function __construct() {
			$this->set_constants();
		}
		function set_constants() {
			$branding         = self::get_bsf_mautic_branding();
			$branding_name    = 'UABB';
			$branding_modules = __('UABB Modules', 'uabb');

			//	Branding - %s
			if (
				is_array( $branding ) &&
				array_key_exists( 'uabb-plugin-short-name', $branding ) &&
				$branding['uabb-plugin-short-name'] != ''
			) {
				$branding_name = $branding['uabb-plugin-short-name'];
			}

			//	Branding - %s Modules
			if ( $branding_name != 'UABB') {
				$branding_modules = sprintf( __( '%s Modules', 'uabb' ), $branding_name );
			}
			define( 'BSFM_PREFIX', 'BSFM' );
			//define( 'UABB_CAT', $branding_modules );			
		}
		
		static public function get_bsfm_mautic()
		{
			$bsfm = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$defaults = array(
				'bsfm-enabled-tracking'	=> '',
				'bsfm-base-url'			=> '',
				'bsfm-public-key'		=> '',
				'bsfm-secret-key'		=> '',
				'bsfm-callback-uri'		=> ''
			);

			//	if empty add all defaults
			if( empty( $bsfm ) ) {
				$bsfm = $defaults;
			} else {

				//	add new key
				foreach( $defaults as $key => $value ) {
					if( is_array( $bsfm ) && !array_key_exists( $key, $bsfm ) ) {
						$bsfm[$key] = $value;
					} else {
						$bsfm = wp_parse_args( $bsfm, $defaults );
					}
				}
			}

			return apply_filters( 'bsfm_get_mautic', $bsfm );
		}

		static public function get_bsf_mautic_branding( $request_key = '' )
		{
			$bsfm = BSF_Mautic_Init::$bsfm_options['bsf_mautic_branding'];

			$defaults = array(
				'bsfm-plugin-name' => '',
				'bsfm-plugin-short-name' => '',
				'bsfm-plugin-desc' => '',
				'bsfm-author-name' => '',
				'bsfm-author-url' => '',
				'bsfm-knowledge-base-url' => '',
				'bsfm-contact-support-url' => ''
			);

			//	if empty add all defaults
			if( empty( $bsfm ) ) {
				$bsfm = $defaults;
			} else {
				//	add new key
				foreach( $defaults as $key => $value ) {
					if( is_array( $bsfm ) && !array_key_exists( $key, $bsfm ) ) {
						$bsfm[$key] = $value;
					} else {
						$bsfm = wp_parse_args( $bsfm, $defaults );
					}
				}
			}

			/**
			 * Return specific requested branding value
			 */
			if( !empty( $request_key ) ) {
				if( is_array($bsfm) ) {
					$bsfm = ( array_key_exists( $request_key, $bsfm ) ) ? $bsfm[ $request_key ] : '';
				}				
			}
			return apply_filters( 'bsfm_get_bsfmautic_branding', $bsfm );
		}
	}
	new BSF_Mautic_Helper();
endif;