<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automate-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_WP_Register' ) ) :

	/**
	 * Create class APMautic_WP_Register
	 * Handles register post type, trigger actions
	 */
	class APMautic_WP_Register {

		/**
		 * Declare a static variable instance.
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiate class
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new APMautic_WP_Register();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_action( 'user_register', array( $this, 'add_registered_user' ), 10, 1 );
			add_action( 'profile_update', array( $this, 'add_registered_user' ), 10, 1 );
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $user_id WP Users unique ID.
		 * @return void
		 */
		public function add_registered_user( $user_id ) {

			// return if $user_id is not available.
			if ( ! $user_id ) {

				return;
			}

			// get user registerd condition rules.
			$status = APMautic_RulePanel::get_wpur_condition();

			// return if the $status is not as expected.
			if ( ! is_array( $status ) || 0 == sizeof( $status ) ) {
				return;
			}

			$set_actions = APMautic_RulePanel::get_all_actions( $status );

			$user_info = get_userdata( $user_id );

			$email = $user_info->user_email;

			$body = array(
				'firstname' => $user_info->first_name,
				'lastname'  => $user_info->last_name,
				'email'     => $user_info->user_email,
				'website'   => $user_info->user_url,
			);

			$instance = APMautic_Services::get_service_instance( AP_MAUTIC_SERVICE );
			$instance->subscribe( $email, $body, $set_actions );
		}
	}
	APMautic_WP_Register::instance();
endif;
