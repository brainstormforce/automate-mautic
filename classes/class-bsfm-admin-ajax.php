<?php
/**
 * admin ajax functions. 
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSFMauticAdminAjax' ) ) :

class BSFMauticAdminAjax {
	
	private static $instance;

	/**
	 * Initiator
	 */
	public static function instance(){
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BSFMauticAdminAjax();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() {
		add_action( 'wp_ajax_get_cf7_fields', array( $this, 'bsf_make_cf7_fields' ) );
		add_action( 'wp_ajax_get_edd_var_price', array( $this, 'bsf_get_edd_variable_price' ) );
		add_action( 'wp_ajax_clean_mautic_transient', array( $this, 'bsf_clean_mautic_transient' ) );
	}
	/** 
	 * Make cf7 form fields select Html
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bsf_make_cf7_fields( $cf7_id='', $select='' ) {
		//get all contact form fields
		$cf7_id = $_POST['cf7Id'];
		$cf7_field_data = get_post_meta( $cf7_id, '_form' );
		$reg = '/(?<=\[)([^\]]+)/';
		$str = $cf7_field_data[0];
		preg_match_all($reg, $str, $matches);
		array_pop($matches[0]);
		$map_cf7fields = sizeof($matches[0]);
			$cf7_fields = "<table style='float: right;'><tbody>";
			$cf7_fields_sel = "<tr><td><select class='cf7_form' name='cf7_fields[$cf7_id][]'>";
				foreach ($matches[0] as $value) {
					$field = explode(' ',$value);
					$cf7_fields_sel.= Bsfm_Postmeta::make_option($field[1], $field[1], $select);
				}
			$cf7_fields_sel.= "</select></td></tr>";
				for ( $i=0; $i < $map_cf7fields; $i++) { 
					$cf7_fields.= $cf7_fields_sel;
				}
			$cf7_fields.= "</tbody></table>";
		print_r(json_encode( array(
				'fieldCount' => $map_cf7fields,
				'selHtml' => $cf7_fields
		)));
		wp_die();
	}
	/** 
	 * Get EDD - downloads variable price
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bsf_get_edd_variable_price( $download_id='', $select='' ) {
		//get all contact form fields
		$download_id = $_POST['download_id'];
		$edd_prices = edd_get_variable_prices( $download_id );
		$edd_vprice_sel = "<select class='edd_var_price' name='ss_edd_var_price[]'>";
		if( $edd_prices ) {
			foreach( $edd_prices as $price_id => $price ) {
				$edd_vprice_sel.= Bsfm_Postmeta::make_option($price_id , $price['name'], $select);
			}
		}
		$edd_vprice_sel .= "</select>";
		echo $edd_vprice_sel;
		wp_die();
	}
	/** 
	 * Refresh Mautic transients data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bsf_clean_mautic_transient() {
		delete_transient( 'bsfm_all_segments' );
		delete_transient( 'bsfm_all_mforms' );
		delete_transient( 'bsfm_all_cfields' );
		die();
	}
}
$BSFMauticAdminAjax = BSFMauticAdminAjax::instance();
endif;