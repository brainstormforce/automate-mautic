<?php
/**
 * Custom modules
 */
if ( ! class_exists( 'BSF_Mautic_Helper' ) ) :
	class BSF_Mautic_Helper {
		private static $instance;
		/**
		* Initiator
		*/
		public static function instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new BSF_Mautic_Helper();
				self::$instance->set_constants();
			}
			return self::$instance;
		}
		public function set_constants() {
			$branding         = self::get_bsf_mautic_branding();
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
$BSF_Mautic_Helper = BSF_Mautic_Helper::instance();
endif;