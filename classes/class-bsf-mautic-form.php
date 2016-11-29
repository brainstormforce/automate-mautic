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
		add_action( 'init', array( $this, 'bsfm_mautic_form_method' ) );
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
	public static function bsfm_mautic_form_method( $query ) {

		$query = array(
			'firstname'	=>	'aaa',
			'email'		=>	'rahul@fff.in',
			'message'	=>	'gdggdg'
		);

		if ( ! isset( $query['return'] ) ) {
			$query['return'] = get_home_url();
		}
		/*
			@filter rule_id array for form method
			@loop thorugh values
		*/
		$rule_id = 778;
		$mautic_method = get_post_meta( $rule_id, 'bsfm_mautic_method' );
		if (isset($mautic_method[0])) {
			$mautic_method = unserialize($mautic_method[0]);
		}
		//$mautic_method['mautic_form_field']
		$bsfm	=	BSF_Mautic_Helper::get_bsfm_mautic();

		// print_r($mautic_method);
		// print_r($bsfm);
		// print_r($query);
		// die();

		// $query = $this->_add_mautic_form_id( $query );
		// $query = $this->_remove_hyphen( $query );

		$query['formId'] = $mautic_method['form_fields'][0];
		$query['subject'] = 'hwleoksdkfsf';
		$data = array(
			'mauticform' => $query
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
	}
}
$BSF_Mautic_Form = new BSF_Mautic_Form();
endif;