<?php
/**
 * MauticPress Form method
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSF_Mautic_Form' ) ) :
	
	class BSF_Mautic_Form {

	/**
	 *  Constructor
	 */

	public function __construct() {
		/**
		 *	For Performance
		 *	Set static object to store data from database.
		 */
		self::includes();
	}

	function includes() {
		//add_action( 'init', array( $this, 'bsfm_mautic_form_method' ) );
	}

	public static function is_mautic_form_method() {
		$mautic_method = get_post_meta( $rule_id, 'bsfm_mautic_method' );
		if ( isset($mautic_method[0]) ) {
			$mautic_method = unserialize($mautic_method[0]);
		}
		if( $mautic_method['method']!='m_form' ) {
			return false;
		}
		return true;
	}
	/**
	*	For Performance
	*	Set static object to store data from database.
	*/
	public static function bsfm_mautic_form_method( $udata, $segments = array() ) {

		if ( ! isset( $query['return'] ) ) {
			$query['return'] = get_home_url();
		}
		$rule_id = 778;
		$mautic_method = get_post_meta( $rule_id, 'bsfm_mautic_method' );
		if (isset($mautic_method[0])) {
			$mautic_method = unserialize($mautic_method[0]);
		}
		$bsfm	=	BSF_Mautic_Helper::get_bsfm_mautic();
		//$mautic_fields = array_flip($mautic_method['form_fields']);
		$query = array();
		foreach ( $mautic_method['form_fields'] as $key => $form_field ) {
			//$query[$form_field] = $udata[$key];
		}

		$query['formId'] = $mautic_method['mautic_form_id'];
	 	$query['return'] = get_home_url();

	 	// 'firstname' => 'john',
		// 'lastname'	=> 'lukas',
		$query['firstname'] = 'Henry';
		$query['lastname'] = 'Cliff';
		$data = array(
			'mauticform' => $query,
		);
		$url = path_join( $bsfm['bsfm-base-url'], "form/submit?formId={$query['formId']}" );
		$response = wp_remote_post(
			$url,
			array(
				'method' => 'POST',
				'timeout' => 45,
				'body' => $data,
				'cookies' => array()
			)
		);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			error_log( "CF7_Mautic Error: $error_message" );
			error_log( "posted url: $url" );
		}
	 	else {
				$response_body = $response['body'];
				$contact_created = json_decode($response_body);
				$contact = $contact_created->contact;
				/*
				* if contact is created add to segment here
				*/
			if( isset($contact->id) ) {
				$contact_id =  (int)$contact->id;
				// fetch segment_id from rule and add contact to segment
				if( is_array( $segments ) ) {
					foreach ($segments as $segment_id) {
						$segment_id = (int)$segment_id;
						$res = BSF_Mautic::bsfm_mautic_add_contact_to_segment( $segment_id, $contact_id, $credentials);
					}
				}
				$status = $res['status'];
				$errorMsg  = $res['error_message'];
			}
		}
	}
}
$BSF_Mautic_Form = new BSF_Mautic_Form();
endif;