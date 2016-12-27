<?php
/**
 * admin ajax functions. 
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'AutomatePlusAdminAjax' ) ) :

class AutomatePlusAdminAjax {
	
	private static $instance;

	/**
	 * Initiator
	 */
	public static function instance(){
		if ( ! isset( self::$instance ) ) {
			self::$instance = new AutomatePlusAdminAjax();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public function hooks() {
		add_action( 'wp_ajax_clean_mautic_transient', array( $this, 'clean_mautic_transient' ) );
		add_action( 'wp_ajax_config_disconnect_mautic', array( $this, 'config_disconnect_mautic' ) );
		add_action( "admin_post_bsfm_rule_list", array( $this, "handle_bsfm_rule_list_actions" ) );
	}

	/** 
	 * disconnect mautic
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function config_disconnect_mautic() {
		delete_option( 'bsfm_mautic_credentials' );
		die();
	}

	/** 
	 * Refresh Mautic transients data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function clean_mautic_transient() {
		delete_transient( 'bsfm_all_segments' );
		delete_transient( 'bsfm_all_mforms' );
		delete_transient( 'bsfm_all_cfields' );
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
$AutomatePlusAdminAjax = AutomatePlusAdminAjax::instance();
endif;