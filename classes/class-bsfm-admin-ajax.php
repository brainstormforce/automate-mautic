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
		add_action( 'wp_ajax_config_disconnect_mautic', array( $this, 'bsf_config_disconnect_mautic' ) );
		// proactive tracking
		add_action( 'wp_ajax_nopriv_add_practive_leads', array( $this, 'add_proactive_abandoned_leads' ) );
		add_action( 'wp_ajax_add_practive_leads', array( $this, 'add_proactive_abandoned_leads' ) );
		add_action( "admin_post_bsfm_rule_list", array( $this, "handle_bsfm_rule_list_actions" ) );
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
			$cf7_fields_sel.= "<option> Select CF7 Field </option>";
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
		$edd_vprice_sel = '';
		if( $edd_prices ) {
		$edd_vprice_sel = "<select class='edd_var_price' name='ss_edd_var_price[]'>";
		$edd_vprice_sel .= "<option>Select Variable Price</option>";
			foreach( $edd_prices as $price_id => $price ) {
				$edd_vprice_sel.= Bsfm_Postmeta::make_option($price_id , $price['name'], $select);
			}
		$edd_vprice_sel .= "</select>";
		}
		echo $edd_vprice_sel;
		wp_die();
	}

	/** 
	 * disconnect mautic
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bsf_config_disconnect_mautic() {
		delete_option( 'bsfm_mautic_credentials' );
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

	/** 
	 * Add proactive abandoned leads to Mautic
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_proactive_abandoned_leads() {

		$ab_email = isset($_POST['email']) ? $_POST['email']:'';

		$bsfm_opt = get_option('_bsf_mautic_config');
		$seg_action_ab = array_key_exists( 'config_edd_segment_ab', $bsfm_opt ) ? $bsfm_opt['config_edd_segment_ab'] : '';

		// General global config conditions
		$all_customer_ab = array(
			'add_segment' => array(),
			'remove_segment' => array()
		);
		array_push( $all_customer_ab['add_segment'], $seg_action_ab );

		$credentials = get_option( 'bsfm_mautic_credentials' );

		if( isset($_COOKIE['mtc_id']) ) {
			$contact_id = $_COOKIE['mtc_id'];
			$contact_id = (int)$contact_id;
		}
		else {
			$contact_id = BSF_Mautic::bsfm_mautic_get_contact_by_email( $ab_email, $credentials );
		}

		if( isset( $contact_id ) ) {
			$method = 'PATCH';
			$url = '/api/contacts/'.$contact_id.'/edit';
		}
		else {
			$method = 'POST';
			$url = '/api/contacts/new';
		}

		$body = array(
			'email'		=>	$_POST['email']
		);

		$ab_segment = $all_customer_ab['add_segment'];
		if( is_array( $ab_segment ) && ( sizeof( $ab_segment )>0 ) ) {
			BSF_Mautic::bsfm_mautic_api_call($url, $method, $body, $all_customer_ab);
		}
		die();
	}

	/** 
	 * Handle multi rule delete
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_bsfm_rule_list_actions() {

		wp_verify_nonce( "_wpnonce" );
		
		if( isset( $_POST['bulk-delete'] ) ) {
			$rules_ids = $_POST['bulk-delete'];
			
			foreach ( $rules_ids as $id ) {
				if( current_user_can( 'delete_post', $id ) ) {
					wp_delete_post( $id );
				}
			}
		}

		$sendback = wp_get_referer();

		wp_redirect( $sendback );
		exit;
	}

}
$BSFMauticAdminAjax = BSFMauticAdminAjax::instance();
endif;