<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automate-mautic
 * @since 1.0.5
 */

if ( ! class_exists( 'APMautic_Comment' ) ) :

	/**
	 * Create class APMautic_Comment
	 * Handles comment post actions
	 */
	class APMautic_Comment {

		/**
		 * Declare a static variable instance.
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiate class
		 *
		 * @since 1.0.5
		 * @return object
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new APMautic_Comment();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Call comment post hook
		 *
		 * @since 1.0.5
		 * @return void
		 */
		public function hooks() {
			add_action( 'comment_post', array( $this, 'add_comment_author' ), 10, 3 );
		}

		/**
		 * Add comments author to Mautic contacts
		 *
		 * @since 1.0.5
		 * @param int    $id comment author ID.
		 * @param string $approved Comment status.
		 * @param array  $commentdata Comment author data.
		 * @return void
		 */
		public function add_comment_author( $id, $approved, $commentdata ) {

			// get comment post condition rules.
			$status = APMautic_RulePanel::get_comment_condition( $commentdata, 'CP' );

			// return if the $status is not as expected.
			if ( ! is_array( $status ) || sizeof( $status ) == 0 ) {
				return;
			}

			$set_actions = APMautic_RulePanel::get_all_actions( $status );

			$email = $commentdata['comment_author_email'];

			$body     = array(
				'firstname' => $commentdata['comment_author'],
				'email'     => $commentdata['comment_author_email'],
				'website'   => $commentdata['comment_author_url'],
			);
			$instance = APMautic_Services::get_service_instance( AP_MAUTIC_SERVICE );
			$instance->subscribe( $email, $body, $set_actions );
		}
	}
	APMautic_Comment::instance();
endif;
