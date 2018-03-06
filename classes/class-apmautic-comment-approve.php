<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automate-mautic
 * @since 1.0.5
 */

if ( ! class_exists( 'APMautic_Comment_Approve' ) ) :

	/**
	 * Create class APMautic_Comment_Approve
	 * Handles comment post actions
	 */
	class APMautic_Comment_Approve {

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
				self::$instance = new APMautic_Comment_Approve();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.5
		 * @return void
		 */
		public function hooks() {
			add_filter( 'amp_mautic_conditions_list', array( $this, 'approve_comment_condition' ), 10, 1 );
			add_action( 'transition_comment_status', array( $this, 'add_approve_comment_author' ), 10, 3 );
			add_action( 'new_condition_CP_APPROVE', array( $this, 'render_comment_approve_condition' ), 11, 1 );
			add_action( 'update_condition_CP_APPROVE', array( $this, 'update_approve_condition' ), 11, 4 );

		}

		/**
		 * Add comment approve in conditions list
		 *
		 * @since 1.0.0
		 * @param array $conditions all condition list.
		 * @return array $condtions
		 */
		public function approve_comment_condition( $conditions ) {

			$conditions['CP_APPROVE'] = __( 'Approved Comment Post', 'automate-mautic' );
			return $conditions;
		}

		/**
		 * Add approved comments author to Mautic contacts
		 *
		 * @since 1.0.5
		 * @param string $new_status new comment status.
		 * @param string $old_status old comment status.
		 * @param object $comment Comment data object.
		 * @return void
		 */
		public function add_approve_comment_author( $new_status, $old_status, $comment ) {
			if ( $old_status != $new_status ) {
				if ( 'approved' == $new_status ) {

					$comment_data = array(
						'comment_post_ID' => $comment->comment_post_ID,
					);
					// get comment post condition rules.
					$status = APMautic_RulePanel::get_comment_condition( $comment_data, 'CP_APPROVE' );

					// return if the $status is not as expected.
					if ( ! is_array( $status ) || sizeof( $status ) == 0 ) {
						return;
					}

					$set_actions = APMautic_RulePanel::get_all_actions( $status );
					$email       = $comment->comment_author_email;

					$body = array(
						'firstname' => $comment->comment_author,
						'email'     => $comment->comment_author_email,
						'website'   => $comment->comment_author_url,
					);

					$instance = APMautic_Services::get_service_instance( AP_MAUTIC_SERVICE );
					$instance->subscribe( $email, $body, $set_actions, true );
				}
			}
		}

		/**
		 * Render EDD condition
		 *
		 * @since 1.0.0
		 * @param array $meta_condition all EDD stored conditions.
		 * @return void
		 */
		public function render_comment_approve_condition( $meta_condition ) {
		?>
				<div class="first-condition">
					<?php APMautic_RulePanel::get_comment_condition_sublist( $meta_condition[1] ); ?>
				</div>
				<div class="second-condition">
						<?php
						if ( 'os_page' == $meta_condition[1] ) {
							APMautic_RulePanel::select_all_pages( $meta_condition[2] );
						} elseif ( 'os_post' == $meta_condition[1] ) {
							APMautic_RulePanel::select_all_posts( $meta_condition[2] );
						}
						?>
				</div>
		<?php
		}

		/**
		 * Upate if rule is set.
		 *
		 * @since 1.0.0
		 * @param array $update_conditions all conditions.
		 * @param array $conditions conditions slug.
		 * @param int   $index condition index.
		 * @param array $post post array.
		 * @return array
		 */
		public function update_approve_condition( $update_conditions, $conditions, $index, $post ) {

			$cp_keys          = array_keys( $conditions, 'CP_APPROVE' );
			$sub_key          = array_search( $index, $cp_keys );
			$ss_cp_condition  = isset( $post['ss_cp_condition'][ $sub_key ] ) ? $post['ss_cp_condition'][ $sub_key ] : '';
			$base             = sanitize_text_field( $conditions[ $index ] );
			$sub_cp_condition = sanitize_text_field( $post['sub_cp_condition'][ $sub_key ] );
			$ss_cp_condition  = sanitize_text_field( $ss_cp_condition );

			$update_conditions[ $index ] = array(
				$base,
				$sub_cp_condition,
				$ss_cp_condition,
			);
			return $update_conditions;
		}
	}
	APMautic_Comment_Approve::instance();
endif;
