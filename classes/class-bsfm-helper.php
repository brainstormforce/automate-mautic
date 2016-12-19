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
			define( 'BSFM_PREFIX', 'BSFM' );
		}
		
		static public function get_bsfm_mautic()
		{
			$bsfm = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$defaults = array(
				'bsfm-enabled-tracking'	=> true,
				'bsfm-tracking-type'	=> 'js',
				'bsfm-base-url'			=> '',
				'bsfm-public-key'		=> '',
				'bsfm-secret-key'		=> '',
				'bsfm-callback-uri'		=> '',
				'bsfm_edd_prod_slug'	=> '',
				'bsfm_edd_prod_cat'		=> '',
				'bsfm_edd_prod_tag'		=> '',
				'config_edd_segment'	=> '',
				'config_edd_segment_ab'	=> '',
				'bsfm_config_subscribe_optin' => ''
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
	}
$BSF_Mautic_Helper = BSF_Mautic_Helper::instance();
endif;