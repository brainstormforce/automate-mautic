<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */
if ( ! class_exists( 'APMautic_WP_register' ) ) :

	/**
	 * Create class APMautic_WP_register
	 * Handles register post type, trigger actions
	 */
	class APMautic_WP_register {

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
				self::$instance = new APMautic_WP_register();
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
			add_action( 'user_register', array( $this, 'add_registered_user' ), 10, 1 ); //
			add_action( 'profile_update', array( $this, 'add_registered_user' ), 10, 1 ); //
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
			$all_tags = '';

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
				'firstname'	=> $user_info->first_name,
				'lastname'	=> $user_info->last_name,
				'email'		=> $user_info->user_email,
				'website'	=> $user_info->user_url,
			);

			$api_data = APMautic_API::get_api_method_url( $email );
			$url = $api_data['url'];
			$method = $api_data['method'];

			// add tags set in actions.
			if ( isset( $set_actions['add_tag'] ) ) {

				foreach ( $set_actions['add_tag'] as $tags ) {
					$all_tags .= $tags . ',';
				}

				$all_tags = rtrim( $all_tags ,',' );
				$body['tags'] = $all_tags;
			}
			APMautic_API::ampw_mautic_api_call( $url, $method, $body, $set_actions );
		}
	}
	APMautic_WP_register::instance();
endif;
