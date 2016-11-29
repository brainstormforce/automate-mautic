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
		// self::includes();
	}

	function includes() {
	
	}
	/**
	*	For Performance
	*	Set static object to store data from database.
	*/
	public static function bsfm_mautic_form_method( $query=array() ) {
		$ip = $this->_get_ip();
		if ( ! isset( $query['return'] ) ) {
			$query['return'] = get_home_url();
		}
		$bsfm 	=	BSF_Mautic_Helper::get_bsfm_mautic();
		// $query = $this->_add_mautic_form_id( $query );
		// $query = $this->_remove_hyphen( $query );
		$data = array(
			'mauticform' => $query,
		);
		$url = path_join( $settings['url'], "form/submit?formId={$query['formId']}" );
		$response = wp_remote_post(
			$url,
			array(
				'method' => 'POST',
				'timeout' => 45,
				'headers' => array(
					'X-Forwarded-For' => $ip,
				),
				'body' => $data,
				'cookies' => array()
			)
		);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			error_log( "CF7_Mautic Error: $error_message" );
			error_log( "      posted url: $url" );
		}
	}
}
$BSF_Mautic_Form = new BSF_Mautic_Form();
endif;