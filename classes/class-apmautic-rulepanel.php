<?php
/**
 * Rules Post Meta
 *
 * @package automate-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_RulePanel' ) ) :

	/**
	 * Create class APMautic_RulePanel
	 * Generate data for rule panel
	 */
	class APMautic_RulePanel {

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
				self::$instance = new APMautic_RulePanel();
				self::$instance->hooks();
				self::$instance->includes();
			}
			return self::$instance;
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-adminajax.php';
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_action( 'wp_trash_post', array( $this, 'clean_condition_actions' ) );
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function ap_mautic_metabox_view() {
			APMautic_AdminSettings::render_form( 'post-meta' );
		}

		/**
		 * Get all pages list
		 *
		 * @param string $select selected value.
		 * @since 1.0.0
		 * @return void
		 */
		public static function select_all_pages( $select = null ) {

			$pages   = get_pages();
			$options = array(
				'' => __( 'Select Page', 'automate-mautic' ),
			);
			foreach ( $pages as $page ) {
				$options[ $page->ID ] = $page->post_title;
			}

			APMautic_Helper::render_settings_field(
				'ss_cp_condition[]', array(
					'type'     => 'select',
					'id'       => 'sub-sub-condition',
					'class'    => 'root-cp-condition form-control',
					'options'  => $options,
					'selected' => $select,
				)
			);
		}

		/**
		 * Get all posts list
		 *
		 * @param string $select selected value.
		 * @since 1.0.0
		 * @return void
		 */
		public static function select_all_posts( $select = null ) {
			// get all posts.
			$args    = array(
				'posts_per_page' => -1,
			);
			$posts   = get_posts( $args );
			$options = array(
				'' => __( 'Select Post', 'automate-mautic' ),
			);
			foreach ( $posts as $post ) :
				setup_postdata( $post );
				$options[ $post->ID ] = $post->post_title;
			endforeach;

			APMautic_Helper::render_settings_field(
				'ss_cp_condition[]', array(
					'type'     => 'select',
					'id'       => 'ss-cp-condition',
					'class'    => 'root-cp-condition form-control',
					'options'  => $options,
					'selected' => $select,
				)
			);
			wp_reset_postdata();
		}

		/**
		 * Delete meta assosites with rule
		 *
		 * @param int $post_id rule ID.
		 * @since 1.0.0
		 * @return void
		 */
		public static function clean_condition_actions( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( AP_MAUTIC_POSTTYPE != $post_type ) {
				return;
			}
			delete_post_meta( $post_id, 'ampw_rule_condition' );
			delete_post_meta( $post_id, 'ampw_rule_action' );
		}

		/**
		 * Check if rule is set
		 *
		 * @param array  $comment_data comment data.
		 * @param string $condition is approved comment condition.
		 * @return rule id array
		 */
		public static function get_comment_condition( $comment_data = array(), $condition ) {
			$args      = array(
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'post_type'      => AP_MAUTIC_POSTTYPE,
			);
			$posts     = get_posts( $args );
			$set_rules = array();
			foreach ( $posts as $post ) :
				setup_postdata( $post );
				$rule_id         = $post->ID;
				$meta_conditions = get_post_meta( $rule_id, 'ampw_rule_condition' );
				$meta_conditions = unserialize( $meta_conditions[0] );
				foreach ( $meta_conditions as $order => $meta_condition ) :
					if ( $condition == $meta_condition[0] ) {
						if ( 'ao_website' == $meta_condition[1] ) {

							// add rule_id into array.
							array_push( $set_rules, $rule_id );
						}
						if ( 'os_page' == $meta_condition[1] || 'os_post' == $meta_condition[1] ) {
							if ( is_array( $comment_data ) && $meta_condition[2] == $comment_data['comment_post_ID'] ) {
								array_push( $set_rules, $rule_id );
							}
						}
					}
					endforeach;
				endforeach;
			return $set_rules;
		}

		/**
		 * Get rules where user register condition is set
		 *
		 * @return array
		 */
		public static function get_wpur_condition() {
			$args     = array(
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'post_type'      => AP_MAUTIC_POSTTYPE,
			);
			$posts    = get_posts( $args );
			$ur_rules = array();
			foreach ( $posts as $post ) :
				setup_postdata( $post );
				$rule_id         = $post->ID;
				$meta_conditions = get_post_meta( $rule_id, 'ampw_rule_condition' );
				$all_conditions  = unserialize( $meta_conditions[0] );
				foreach ( $all_conditions as $meta_condition ) :
					if ( 'UR' == $meta_condition[0] ) {
						// add rule_id into array.
						array_push( $ur_rules, $rule_id );
					}
					endforeach;
				endforeach;
			return $ur_rules;
		}

		/**
		 * Get all rules and manipulate actions
		 *
		 * @param array $rules rule ID's.
		 * @return array all actions
		 */
		public static function get_all_actions( $rules = array() ) {

			$all_actions = array(
				'add_segment'    => array(),
				'remove_segment' => array(),
				'add_tag'        => array(),
			);

			foreach ( $rules as $rule ) :
				$rule_id      = $rule;
				$meta_actions = get_post_meta( $rule_id, 'ampw_rule_action' );
				$meta_actions = unserialize( $meta_actions[0] );

				foreach ( $meta_actions as $order => $meta_action ) :
					if ( 'add_segment' == $meta_action[0] ) {

						$segment_id = $meta_action[1];
						array_push( $all_actions['add_segment'], $segment_id );
					}
					if ( 'remove_segment' == $meta_action[0] ) {

						$segment_id = $meta_action[1];
						array_push( $all_actions['remove_segment'], $segment_id );
					}
					if ( 'add_tag' == $meta_action[0] ) {
						array_push( $all_actions['add_tag'], $meta_action[1] );
					}
					endforeach;
				endforeach;

			return $all_actions;
		}

		/**
		 * Get all conditions list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_all_conditions_list( $select = '' ) {

			$options = array(
				'Default'    => __( 'Select Condition', 'automate-mautic' ),
				'UR'         => __( 'User Register on WordPress', 'automate-mautic' ),
				'CP'         => __( 'User Post a Comment', 'automate-mautic' ),
				'CP_APPROVE' => __( 'Approved Comment Post', 'automate-mautic' ),
			);

			$options = apply_filters( 'amp_mautic_conditions_list', $options );

			APMautic_Helper::render_settings_field(
				'pm_condition[]', array(
					'type'     => 'select',
					'id'       => 'selct-condition-list',
					'class'    => 'select-condition form-control',
					'options'  => $options,
					'selected' => $select,
				)
			);
		}

		/**
		 * Get all actons list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_all_actions_list( $select = '' ) {

			$options = array(
				'add_segment'    => __( 'Add to segment', 'automate-mautic' ),
				'remove_segment' => __( 'Remove from segment', 'automate-mautic' ),
				'add_tag'        => __( 'Add Tags', 'automate-mautic' ),
			);

			$options = apply_filters( 'amp_mautic_actions_list', $options );

			APMautic_Helper::render_settings_field(
				'sub_seg_action[]', array(
					'type'     => 'select',
					'id'       => 'sub-seg-action',
					'class'    => 'sub-seg-action form-control',
					'options'  => $options,
					'selected' => $select,
				)
			);

		}


		/**
		 * Get all actons list
		 *
		 * @param string $id unique value.
		 * @param string $value option label .
		 * @param string $selected sleceted option.
		 * @return string
		 */
		public static function make_option( $id, $value, $selected = null ) {
			$selected = selected( $id, $selected, false );
			return '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . esc_html( $value ) . '</option>';
		}

		/**
		 * Get comment subcondition list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_comment_condition_sublist( $select = '' ) {

			$options = array(
				'ao_website' => __( 'Anywhere On Website', 'automate-mautic' ),
				'os_page'    => __( 'On Specific Page', 'automate-mautic' ),
				'os_post'    => __( 'On Specific Post', 'automate-mautic' ),
			);

			$options = apply_filters( 'amp_mautic_comment_condition_sublist', $options );

			APMautic_Helper::render_settings_field(
				'sub_cp_condition[]', array(
					'type'     => 'select',
					'id'       => 'sub-cp-condition',
					'class'    => 'sub-cp-condition form-control',
					'options'  => $options,
					'selected' => $select,
				)
			);
		}



	}
	APMautic_RulePanel::instance();
endif;
